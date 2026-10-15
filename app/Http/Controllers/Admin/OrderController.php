<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    private const STATUS_LABELS = [
        Order::STATUS_PENDING_CONFIRMATION => 'Menunggu konfirmasi',
        Order::STATUS_AWAITING_PAYMENT => 'Menunggu pembayaran',
        Order::STATUS_PAID => 'Dibayar',
        Order::STATUS_CANCELLED => 'Dibatalkan',
        Order::STATUS_EXPIRED => 'Kedaluwarsa',
    ];

    public function index(Request $request): View
    {
        $query = Order::query()->with('items');

        $query->when($request->filled('search'), function ($query) use ($request) {
            $search = (string) $request->string('search')->trim();

            $query->where(function ($query) use ($search) {
                $query->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        });

        $query->when(
            $request->filled('status') && array_key_exists($request->string('status')->toString(), self::STATUS_LABELS),
            fn ($query) => $query->where('status', $request->string('status')->toString()),
        );

        $orders = $query->latest()->paginate(12)->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['items.product', 'items.variant']);

        return view('admin.orders.show', [
            'order' => $order,
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    Order::STATUS_AWAITING_PAYMENT,
                    Order::STATUS_PAID,
                    Order::STATUS_CANCELLED,
                    Order::STATUS_EXPIRED,
                ]),
            ],
        ]);

        $message = DB::transaction(function () use ($order, $validated): ?string {
            $lockedOrder = Order::query()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($order->id);
            $targetStatus = $validated['status'];

            if ($targetStatus === Order::STATUS_AWAITING_PAYMENT) {
                if ($lockedOrder->status !== Order::STATUS_PENDING_CONFIRMATION) {
                    return 'Hanya pesanan yang menunggu konfirmasi yang bisa disetujui.';
                }

                $inventoryError = $this->reserveOrderItems($lockedOrder);
                if ($inventoryError !== null) {
                    return $inventoryError;
                }

                $lockedOrder->update([
                    'status' => Order::STATUS_AWAITING_PAYMENT,
                    'payment_due_at' => now()->addHours(24),
                ]);

                return null;
            }

            if ($targetStatus === Order::STATUS_PAID) {
                if ($lockedOrder->status !== Order::STATUS_AWAITING_PAYMENT) {
                    return 'Hanya pesanan yang menunggu pembayaran yang bisa dikonfirmasi lunas.';
                }

                if (! $lockedOrder->payment_due_at || $lockedOrder->payment_due_at->isPast()) {
                    return 'Batas pembayaran sudah lewat. Tandai pesanan ini kedaluwarsa terlebih dahulu.';
                }

                $inventoryError = $this->deductPaidOrderItems($lockedOrder);
                if ($inventoryError !== null) {
                    return $inventoryError;
                }

                $lockedOrder->update(['status' => Order::STATUS_PAID]);

                return null;
            }

            if ($targetStatus === Order::STATUS_CANCELLED) {
                if (! in_array($lockedOrder->status, [Order::STATUS_PENDING_CONFIRMATION, Order::STATUS_AWAITING_PAYMENT], true)) {
                    return 'Pesanan yang sudah dibayar atau selesai tidak dapat dibatalkan dari alur ini.';
                }

                $lockedOrder->update(['status' => Order::STATUS_CANCELLED]);

                return null;
            }

            if ($targetStatus === Order::STATUS_EXPIRED) {
                if (
                    $lockedOrder->status !== Order::STATUS_AWAITING_PAYMENT
                    || ! $lockedOrder->payment_due_at
                    || $lockedOrder->payment_due_at->isFuture()
                ) {
                    return 'Pesanan hanya bisa ditandai kedaluwarsa setelah batas pembayarannya lewat.';
                }

                $lockedOrder->update(['status' => Order::STATUS_EXPIRED]);

                return null;
            }

            return 'Perubahan status pesanan tidak valid.';
        });

        if ($message !== null) {
            return back()->with('error', $message);
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Status pesanan berhasil diperbarui.');
    }

    private function reserveOrderItems(Order $order): ?string
    {
        if ($order->items->isEmpty()) {
            return 'Pesanan ini belum memiliki item untuk ditahan stoknya.';
        }

        foreach ($this->groupItemsByInventory($order->items) as $inventoryKey => $items) {
            $quantity = $items->sum('quantity');
            $inventory = $this->lockInventory($inventoryKey, $items->first());

            if ($inventory === null) {
                return 'Produk atau varian pada pesanan ini sudah tidak tersedia di katalog.';
            }

            [, $reservedQuantity] = $this->stockAndActiveReservations($inventoryKey, $inventory['stock'], $order->id);
            if ($inventory['stock'] - $reservedQuantity < $quantity) {
                return "Stok tersedia tidak cukup untuk {$items->first()->product_name}.";
            }
        }

        return null;
    }

    private function deductPaidOrderItems(Order $order): ?string
    {
        if ($order->items->isEmpty()) {
            return 'Pesanan ini tidak memiliki item yang bisa diproses.';
        }

        $lockedInventories = [];

        foreach ($this->groupItemsByInventory($order->items) as $inventoryKey => $items) {
            $quantity = $items->sum('quantity');
            $inventory = $this->lockInventory($inventoryKey, $items->first());

            if ($inventory === null || $inventory['stock'] < $quantity) {
                return 'Stok fisik tidak cukup untuk mengonfirmasi pembayaran pesanan ini.';
            }

            $lockedInventories[$inventoryKey] = [
                'inventory' => $inventory,
                'quantity' => $quantity,
            ];
        }

        foreach ($lockedInventories as $lockedInventory) {
            $inventory = $lockedInventory['inventory'];
            $remainingStock = $inventory['stock'] - $lockedInventory['quantity'];

            if ($inventory['variant'] instanceof ProductVariant) {
                $inventory['variant']->update(['stock' => $remainingStock]);
                $inventory['product']->update([
                    'stock' => $inventory['product']->variants()->sum('stock'),
                ]);
            } else {
                $inventory['product']->update(['stock' => $remainingStock]);
            }
        }

        return null;
    }

    /**
     * @param  EloquentCollection<int, OrderItem>  $items
     * @return Collection<string, EloquentCollection<int, OrderItem>>
     */
    private function groupItemsByInventory(EloquentCollection $items): Collection
    {
        return $items
            ->groupBy(fn (OrderItem $item) => $item->product_variant_id
                ? 'variant:' . $item->product_variant_id
                : 'product:' . $item->product_id)
            ->sortKeys();
    }

    /**
     * @return array{product: Product, variant: ProductVariant|null, stock: int}|null
     */
    private function lockInventory(string $inventoryKey, OrderItem $item): ?array
    {
        [$type, $id] = explode(':', $inventoryKey, 2);

        if ($type === 'variant') {
            $variant = ProductVariant::query()->lockForUpdate()->find($id);
            if (! $variant || (int) $variant->product_id !== (int) $item->product_id) {
                return null;
            }

            $product = Product::query()->lockForUpdate()->find($variant->product_id);
            if (! $product) {
                return null;
            }

            return [
                'product' => $product,
                'variant' => $variant,
                'stock' => (int) $variant->stock,
            ];
        }

        $product = Product::query()->lockForUpdate()->find($id);
        if (! $product || $product->variants()->exists()) {
            return null;
        }

        return [
            'product' => $product,
            'variant' => null,
            'stock' => (int) $product->stock,
        ];
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function stockAndActiveReservations(string $inventoryKey, int $stock, int $excludingOrderId): array
    {
        [$type, $id] = explode(':', $inventoryKey, 2);

        $query = OrderItem::query()
            ->whereHas('order', function ($query) use ($excludingOrderId) {
                $query->where('status', Order::STATUS_AWAITING_PAYMENT)
                    ->where('payment_due_at', '>', now())
                    ->where('id', '!=', $excludingOrderId);
            });

        if ($type === 'variant') {
            $query->where('product_variant_id', $id);
        } else {
            $query->where('product_id', $id)->whereNull('product_variant_id');
        }

        return [$stock, (int) $query->sum('quantity')];
    }
}
