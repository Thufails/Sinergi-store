<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with('category')
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true));

        $query->when($request->filled('search'), function ($query) use ($request) {
            $search = (string) $request->string('search')->trim();
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        });

        $query->when($request->filled('category'), function ($query) use ($request) {
            $query->whereHas('category', fn ($categoryQuery) => $categoryQuery
                ->where('is_active', true)
                ->where('slug', $request->string('category')->trim()));
        });

        match ($request->input('sort', 'latest')) {
            'name_asc' => $query->orderBy('name'),
            'price_low' => $query->orderByRaw('(CASE WHEN discount_value > 0 THEN CASE WHEN discount_type = "fixed" THEN price - discount_value ELSE price - (price * discount_value / 100) END ELSE price END) asc'),
            'price_high' => $query->orderByRaw('(CASE WHEN discount_value > 0 THEN CASE WHEN discount_type = "fixed" THEN price - discount_value ELSE price - (price * discount_value / 100) END ELSE price END) desc'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('store.products.index', compact('products', 'categories'));
    }

    public function show(Product $product): View
    {
        $product->loadMissing(['category', 'variants']);
        abort_unless($product->is_active && $product->category?->is_active, 404);

        $activeHolds = OrderItem::query()
            ->where('product_id', $product->id)
            ->whereNotNull('product_variant_id')
            ->whereHas('order', fn ($query) => $query
                ->where('status', Order::STATUS_AWAITING_PAYMENT)
                ->where('payment_due_at', '>', now()))
            ->selectRaw('product_variant_id, SUM(quantity) as reserved_quantity')
            ->groupBy('product_variant_id')
            ->pluck('reserved_quantity', 'product_variant_id');

        foreach ($product->variants as $variant) {
            $variant->setAttribute(
                'available_stock',
                max(0, (int) $variant->stock - (int) $activeHolds->get($variant->id, 0)),
            );
        }

        $heldProductStock = OrderItem::query()
            ->where('product_id', $product->id)
            ->whereNull('product_variant_id')
            ->whereHas('order', fn ($query) => $query
                ->where('status', Order::STATUS_AWAITING_PAYMENT)
                ->where('payment_due_at', '>', now()))
            ->sum('quantity');
        $availableProductStock = max(0, (int) $product->stock - (int) $heldProductStock);

        $hasAvailableStock = $product->variants->isEmpty()
            ? $availableProductStock > 0
            : $product->variants->contains(fn ($variant) => $variant->available_stock > 0);

        return view('store.products.show', compact('product', 'availableProductStock', 'hasAvailableStock'));
    }

    public function order(Request $request, Product $product): RedirectResponse
    {
        $product->loadMissing(['category', 'variants']);
        abort_unless($product->is_active && $product->category?->is_active, 404);

        $hasVariants = $product->variants->isNotEmpty();
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+().\\s-]{8,30}$/'],
            'variant_id' => [Rule::requiredIf($hasVariants), 'nullable', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $quantity = (int) $validated['quantity'];
        $order = DB::transaction(function () use ($product, $validated, $quantity): Order {
            $lockedProduct = Product::query()
                ->with('category')
                ->lockForUpdate()
                ->find($product->id);

            if (! $lockedProduct || ! $lockedProduct->is_active || ! $lockedProduct->category?->is_active) {
                abort(404);
            }

            $variant = null;
            if ($lockedProduct->variants()->exists()) {
                if (empty($validated['variant_id'])) {
                    throw ValidationException::withMessages([
                        'variant_id' => 'Pilih varian yang tersedia untuk produk ini.',
                    ]);
                }

                $variant = ProductVariant::query()
                    ->where('product_id', $lockedProduct->id)
                    ->lockForUpdate()
                    ->find($validated['variant_id']);

                if (! $variant) {
                    throw ValidationException::withMessages([
                        'variant_id' => 'Pilih varian yang tersedia untuk produk ini.',
                    ]);
                }
            } elseif (! empty($validated['variant_id'])) {
                throw ValidationException::withMessages([
                    'variant_id' => 'Produk ini tidak memiliki varian yang dapat dipilih.',
                ]);
            }

            $reservedItems = OrderItem::query()
                ->whereHas('order', fn ($query) => $query
                    ->where('status', Order::STATUS_AWAITING_PAYMENT)
                    ->where('payment_due_at', '>', now()));

            if ($variant) {
                $reservedItems->where('product_variant_id', $variant->id);
            } else {
                $reservedItems
                    ->where('product_id', $lockedProduct->id)
                    ->whereNull('product_variant_id');
            }

            $reservedQuantity = (int) $reservedItems->sum('quantity');
            $physicalStock = (int) ($variant?->stock ?? $lockedProduct->stock);
            $availableStock = max(0, $physicalStock - $reservedQuantity);
            if ($availableStock < 1) {
                throw ValidationException::withMessages([
                    $variant ? 'variant_id' : 'quantity' => 'Maaf, stok produk ini sedang habis.',
                ]);
            }

            if ($quantity > $availableStock) {
                throw ValidationException::withMessages([
                    'quantity' => "Jumlah melebihi stok tersedia. Stok saat ini: {$availableStock}.",
                ]);
            }

            $unitPrice = (float) $lockedProduct->final_price;
            $variantName = collect([$variant?->size, $variant?->color])
                ->filter()
                ->implode(' · ');

            $order = Order::query()->create([
                'order_number' => 'SS-' . now('Asia/Jakarta')->format('Ymd') . '-' . Str::upper(Str::random(8)),
                'customer_name' => trim($validated['customer_name']),
                'customer_phone' => trim($validated['customer_phone']),
                'status' => Order::STATUS_PENDING_CONFIRMATION,
                'total_amount' => round($unitPrice * $quantity, 2),
            ]);

            $order->items()->create([
                'product_id' => $lockedProduct->id,
                'product_variant_id' => $variant?->id,
                'product_name' => $lockedProduct->name,
                'variant_name' => $variantName !== '' ? $variantName : null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => round($unitPrice * $quantity, 2),
            ]);

            $order->statusHistories()->create([
                'from_status' => null,
                'to_status' => Order::STATUS_PENDING_CONFIRMATION,
                'actor_name' => 'Pelanggan',
            ]);

            return $order->load('items');
        });

        $variantDetails = [];
        $variantName = $order->items->first()->variant_name;
        if ($variantName) {
            $variantDetails[] = 'Varian: ' . $variantName;
        }

        $unitPrice = (float) $order->items->first()->unit_price;
        $message = implode("\n", array_filter([
            'Halo, saya sudah mengisi pesanan di website Sinergi Store:',
            'Nomor pesanan: ' . $order->order_number,
            'Nama: ' . $order->customer_name,
            'WhatsApp pembeli: ' . $order->customer_phone,
            'Produk: ' . $order->items->first()->product_name,
            ...$variantDetails,
            'Jumlah: ' . $quantity,
            'Harga satuan: Rp ' . number_format($unitPrice, 0, ',', '.'),
            'Subtotal: Rp ' . number_format((float) $order->total_amount, 0, ',', '.'),
            'Status: Menunggu konfirmasi admin',
            'Link produk: ' . route('store.products.show', $product),
            '',
            'Mohon konfirmasi pesanan, ongkir, dan cara pembayarannya. Terima kasih.',
        ]));

        return redirect()->away('https://wa.me/6282142612382?text=' . urlencode($message));
    }
}
