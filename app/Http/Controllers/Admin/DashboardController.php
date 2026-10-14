<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $activeProducts = Product::where('is_active', true)->count();
        $discountedProducts = Product::where('discount_value', '>', 0)->count();
        $lowStockProducts = Product::whereBetween('stock', [1, 5])->orderBy('stock')->orderBy('name')->get();
        $outOfStockProducts = Product::where('stock', 0)->orderBy('name')->get();
        $latestProducts = Product::with('category')->latest()->limit(5)->get();
        $categorySummary = Category::withCount('products')->orderByDesc('products_count')->orderBy('name')->limit(5)->get();

        return view('admin.dashboard', compact(
            'totalProducts',
            'totalCategories',
            'activeProducts',
            'discountedProducts',
            'lowStockProducts',
            'outOfStockProducts',
            'latestProducts',
            'categorySummary',
        ));
    }

    public function profile(): View
    {
        return view('admin.profile');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        return back()->with('success', 'Profil admin berhasil diperbarui.');
    }
}
