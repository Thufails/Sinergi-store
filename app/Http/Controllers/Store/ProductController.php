<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $hasAvailableStock = $product->variants->isEmpty()
            ? $product->stock > 0
            : $product->variants->contains(fn ($variant) => $variant->stock > 0);

        return view('store.products.show', compact('product', 'hasAvailableStock'));
    }

    public function order(Request $request, Product $product): RedirectResponse
    {
        $product->loadMissing(['category', 'variants']);
        abort_unless($product->is_active && $product->category?->is_active, 404);

        $hasVariants = $product->variants->isNotEmpty();
        $validated = $request->validate([
            'variant_id' => [Rule::requiredIf($hasVariants), 'nullable', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $variant = $hasVariants
            ? $product->variants->firstWhere('id', (int) $validated['variant_id'])
            : null;

        if ($hasVariants && ! $variant) {
            throw ValidationException::withMessages([
                'variant_id' => 'Pilih varian yang tersedia untuk produk ini.',
            ]);
        }

        $availableStock = $variant?->stock ?? $product->stock;
        if ($availableStock < 1) {
            throw ValidationException::withMessages([
                $hasVariants ? 'variant_id' : 'quantity' => 'Maaf, stok produk ini sedang habis.',
            ]);
        }

        if ($validated['quantity'] > $availableStock) {
            throw ValidationException::withMessages([
                'quantity' => "Jumlah melebihi stok tersedia. Stok saat ini: {$availableStock}.",
            ]);
        }

        $variantDetails = [];
        if ($variant?->size) {
            $variantDetails[] = 'Ukuran: ' . $variant->size;
        }
        if ($variant?->color) {
            $variantDetails[] = 'Warna: ' . $variant->color;
        }

        $quantity = (int) $validated['quantity'];
        $unitPrice = $product->final_price;
        $message = implode("\n", array_filter([
            'Halo, saya ingin memesan produk berikut:',
            'Produk: ' . $product->name,
            ...$variantDetails,
            'Jumlah: ' . $quantity,
            'Harga satuan: Rp ' . number_format($unitPrice, 0, ',', '.'),
            'Subtotal: Rp ' . number_format($unitPrice * $quantity, 0, ',', '.'),
            'Link produk: ' . route('store.products.show', $product),
            '',
            'Mohon konfirmasi stok, ongkir, dan cara pembayarannya. Terima kasih.',
        ]));

        return redirect()->away('https://wa.me/6282142612382?text=' . urlencode($message));
    }
}
