<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Testimonial;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featuredProducts = Product::with('category')
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->latest()
            ->limit(6)
            ->get();

        $testimonials = Testimonial::query()
            ->where('is_active', true)
            ->where('is_approved', true)
            ->latest()
            ->limit(6)
            ->get();

        return view('store.home', compact('featuredProducts', 'testimonials'));
    }
}
