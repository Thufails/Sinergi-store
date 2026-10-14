<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
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
        abort_unless($product->is_active && $product->category?->is_active, 404);

        return view('store.products.show', compact('product'));
    }
}
