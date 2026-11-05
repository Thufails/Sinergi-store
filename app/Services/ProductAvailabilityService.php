<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class ProductAvailabilityService
{
    public function refresh(Product $product): Product
    {
        $product->loadMissing('variants');

        $reservedItems = OrderItem::query()
            ->where('product_id', $product->id)
            ->whereHas('order', fn ($query) => $query->where(function ($statusQuery) {
                $statusQuery->where(function ($awaitingPaymentQuery) {
                    $awaitingPaymentQuery->where('status', Order::STATUS_AWAITING_PAYMENT)
                        ->where('payment_due_at', '>', now());
                })->orWhere('status', Order::STATUS_PAYMENT_REVIEW);
            }));

        if ($product->variants->isEmpty()) {
            $heldQuantity = (int) (clone $reservedItems)
                ->whereNull('product_variant_id')
                ->sum('quantity');

            $product->setAttribute('available_stock', max(0, (int) $product->stock - $heldQuantity));

            return $product;
        }

        $reservedByVariant = (clone $reservedItems)
            ->whereNotNull('product_variant_id')
            ->selectRaw('product_variant_id, SUM(quantity) as reserved_quantity')
            ->groupBy('product_variant_id')
            ->pluck('reserved_quantity', 'product_variant_id');

        foreach ($product->variants as $variant) {
            $variant->setAttribute(
                'available_stock',
                max(0, (int) $variant->stock - (int) $reservedByVariant->get($variant->id, 0)),
            );
        }

        return $product;
    }
}
