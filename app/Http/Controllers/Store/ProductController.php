<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
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
        $query = Product::query()
            ->with('category')
            ->withCount([
                'reviews as approved_reviews_count' => fn (Builder $query) => $query->where('is_approved', true),
            ])
            ->withAvg([
                'reviews as approved_reviews_avg_rating' => fn (Builder $query) => $query->where('is_approved', true),
            ], 'rating')
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

        $productReviews = $product->reviews()
            ->where('is_approved', true)
            ->with('customer')
            ->latest()
            ->get();
        $reviewCount = $productReviews->count();
        $averageRating = $reviewCount > 0 ? (float) $productReviews->avg('rating') : 0;

        $activeHolds = OrderItem::query()
            ->where('product_id', $product->id)
            ->whereNotNull('product_variant_id')
            ->whereHas('order', fn ($query) => $query->where(function ($statusQuery) {
                $statusQuery->where(function ($awaitingPaymentQuery) {
                    $awaitingPaymentQuery->where('status', Order::STATUS_AWAITING_PAYMENT)
                        ->where('payment_due_at', '>', now());
                })->orWhere('status', Order::STATUS_PAYMENT_REVIEW);
            }))
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
            ->whereHas('order', fn ($query) => $query->where(function ($statusQuery) {
                $statusQuery->where(function ($awaitingPaymentQuery) {
                    $awaitingPaymentQuery->where('status', Order::STATUS_AWAITING_PAYMENT)
                        ->where('payment_due_at', '>', now());
                })->orWhere('status', Order::STATUS_PAYMENT_REVIEW);
            }))
            ->sum('quantity');
        $availableProductStock = max(0, (int) $product->stock - (int) $heldProductStock);

        $hasAvailableStock = $product->variants->isEmpty()
            ? $availableProductStock > 0
            : $product->variants->contains(fn ($variant) => $variant->available_stock > 0);

        return view('store.products.show', compact(
            'product',
            'availableProductStock',
            'hasAvailableStock',
            'productReviews',
            'reviewCount',
            'averageRating',
        ));
    }

    public function checkout(Request $request, Product $product): View
    {
        $product->loadMissing(['category', 'variants']);
        abort_unless($product->is_active && $product->category?->is_active, 404);

        /** @var Customer $customer */
        $customer = $request->user('customer');
        $addresses = $customer->addresses()
            ->orderByDesc('is_default')
            ->orderBy('label')
            ->get();

        $reservedItems = OrderItem::query()
            ->where('product_id', $product->id)
            ->whereHas('order', fn ($query) => $query->where(function ($statusQuery) {
                $statusQuery->where(function ($awaitingPaymentQuery) {
                    $awaitingPaymentQuery->where('status', Order::STATUS_AWAITING_PAYMENT)
                        ->where('payment_due_at', '>', now());
                })->orWhere('status', Order::STATUS_PAYMENT_REVIEW);
            }));

        if ($product->variants->isEmpty()) {
            $reservedStock = (int) (clone $reservedItems)->whereNull('product_variant_id')->sum('quantity');
            $product->setAttribute('available_stock', max(0, (int) $product->stock - $reservedStock));
        } else {
            $reservedByVariant = (clone $reservedItems)
                ->whereNotNull('product_variant_id')
                ->selectRaw('product_variant_id, SUM(quantity) as reserved_quantity')
                ->groupBy('product_variant_id')
                ->pluck('reserved_quantity', 'product_variant_id');

            foreach ($product->variants as $variant) {
                $variant->setAttribute('available_stock', max(0, (int) $variant->stock - (int) $reservedByVariant->get($variant->id, 0)));
            }
        }

        return view('store.products.checkout', [
            'product' => $product,
            'addresses' => $addresses,
            'submissionToken' => (string) Str::uuid(),
        ]);
    }

    public function order(Request $request, Product $product): RedirectResponse
    {
        $product->loadMissing(['category', 'variants']);
        abort_unless($product->is_active && $product->category?->is_active, 404);

        /** @var Customer $customer */
        $customer = $request->user('customer');
        $hasVariants = $product->variants->isNotEmpty();
        $validated = $request->validate([
            'submission_token' => ['required', 'uuid'],
            'address_id' => ['required', 'integer'],
            'variant_id' => [Rule::requiredIf($hasVariants), 'nullable', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $quantity = (int) $validated['quantity'];
        $existingOrder = Order::query()
            ->where('submission_token', $validated['submission_token'])
            ->first();

        if ($existingOrder) {
            if ((int) $existingOrder->customer_id !== (int) $customer->id) {
                throw ValidationException::withMessages([
                    'submission_token' => 'Token pengiriman formulir tidak valid. Muat ulang halaman lalu coba lagi.',
                ]);
            }

            return redirect()->route('account.orders')
                ->with('success', 'Pesanan sudah tercatat. Kamu bisa melihat statusnya di halaman Pesanan Saya.');
        }

        $order = DB::transaction(function () use ($product, $validated, $quantity, $customer): Order {
            $lockedCustomer = Customer::query()
                ->lockForUpdate()
                ->findOrFail($customer->id);
            $existingOrder = Order::query()
                ->where('submission_token', $validated['submission_token'])
                ->first();

            if ($existingOrder) {
                if ((int) $existingOrder->customer_id !== (int) $lockedCustomer->id) {
                    throw ValidationException::withMessages([
                        'submission_token' => 'Token pengiriman formulir tidak valid. Muat ulang halaman lalu coba lagi.',
                    ]);
                }

                return $existingOrder;
            }

            $lockedProduct = Product::query()
                ->with('category')
                ->lockForUpdate()
                ->find($product->id);

            if (! $lockedProduct || ! $lockedProduct->is_active || ! $lockedProduct->category?->is_active) {
                abort(404);
            }

            $address = $lockedCustomer->addresses()
                ->lockForUpdate()
                ->find($validated['address_id']);

            if (! $address instanceof CustomerAddress) {
                throw ValidationException::withMessages([
                    'address_id' => 'Pilih alamat yang tersimpan di akunmu.',
                ]);
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
                ->whereHas('order', fn ($query) => $query->where(function ($statusQuery) {
                    $statusQuery->where(function ($awaitingPaymentQuery) {
                        $awaitingPaymentQuery->where('status', Order::STATUS_AWAITING_PAYMENT)
                            ->where('payment_due_at', '>', now());
                    })->orWhere('status', Order::STATUS_PAYMENT_REVIEW);
                }));

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
            $unitWeight = (int) ($variant?->weight_grams ?? $lockedProduct->weight_grams);
            $subtotal = round($unitPrice * $quantity, 2);

            $order = Order::query()->create([
                'order_number' => 'SS-' . now('Asia/Jakarta')->format('Ymd') . '-' . Str::upper(Str::random(8)),
                'submission_token' => $validated['submission_token'],
                'customer_id' => $lockedCustomer->id,
                'customer_address_id' => $address->id,
                'customer_name' => $lockedCustomer->name,
                'customer_phone' => $lockedCustomer->phone ?: $address->recipient_phone,
                'status' => Order::STATUS_PENDING_CONFIRMATION,
                'total_amount' => $subtotal,
                'subtotal_amount' => $subtotal,
                'shipping_fee' => 0,
                'shipping_weight_grams' => $unitWeight * $quantity,
                'shipping_recipient_name' => $address->recipient_name,
                'shipping_recipient_phone' => $address->recipient_phone,
                'shipping_province' => $address->province,
                'shipping_city' => $address->city,
                'shipping_district' => $address->district,
                'shipping_village' => $address->subdistrict,
                'shipping_postal_code' => $address->postal_code,
                'shipping_address_line' => $address->address_line,
                'shipping_notes' => $address->notes,
                'shipping_latitude' => $address->latitude,
                'shipping_longitude' => $address->longitude,
            ]);

            $order->items()->create([
                'product_id' => $lockedProduct->id,
                'product_variant_id' => $variant?->id,
                'product_name' => $lockedProduct->name,
                'variant_name' => $variantName !== '' ? $variantName : null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ]);

            $order->statusHistories()->create([
                'from_status' => null,
                'to_status' => Order::STATUS_PENDING_CONFIRMATION,
                'actor_name' => 'Pelanggan',
            ]);

            return $order;
        });

        return redirect()->route('account.orders')
            ->with('success', 'Pesanan berhasil dibuat. Admin akan meninjau alamat dan menghitung ongkir sebelum total pembayaran ditampilkan.');
    }
}
