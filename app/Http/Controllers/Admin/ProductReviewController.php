<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductReviewController extends Controller
{
    public function index(Request $request): View
    {
        $reviewSummary = ProductReview::query()
            ->where('is_approved', true)
            ->selectRaw('COUNT(*) as review_count, AVG(rating) as average_rating')
            ->first();

        $query = ProductReview::query()
            ->with([
                'product:id,name,slug',
                'customer:id,name',
                'orderItem.order:id,order_number',
            ])
            ->where('is_approved', true);

        $query->when($request->filled('order'), function (Builder $query) use ($request): void {
            $orderId = $request->integer('order');

            $query->whereHas('orderItem.order', function (Builder $orderQuery) use ($orderId): void {
                $orderQuery->whereKey($orderId);
            });
        });

        $query->when($request->filled('search'), function (Builder $query) use ($request): void {
            $search = (string) $request->string('search')->trim();

            $query->where(function (Builder $query) use ($search): void {
                $query->where('comment', 'like', "%{$search}%")
                    ->orWhereHas('product', fn (Builder $productQuery) => $productQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('customer', fn (Builder $customerQuery) => $customerQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('orderItem.order', fn (Builder $orderQuery) => $orderQuery->where('order_number', 'like', "%{$search}%"));
            });
        });

        $rating = $request->integer('rating');
        if (in_array($rating, [1, 2, 3, 4, 5], true)) {
            $query->where('rating', $rating);
        }

        $reviews = $query->latest()->paginate(10)->withQueryString();

        return view('admin.product-reviews.index', [
            'reviews' => $reviews,
            'reviewCount' => (int) ($reviewSummary->review_count ?? 0),
            'averageRating' => (float) ($reviewSummary->average_rating ?? 0),
        ]);
    }
}
