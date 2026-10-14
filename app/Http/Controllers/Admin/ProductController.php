<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with('category');

        $query->when($request->filled('search'), function ($query) use ($request) {
            $search = (string) $request->string('search')->trim();
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        });

        $query->when($request->filled('category'), fn ($query) => $query->where('category_id', $request->integer('category')));

        $query->when($request->status === 'active', fn ($query) => $query->where('is_active', true));
        $query->when($request->status === 'inactive', fn ($query) => $query->where('is_active', false));
        $query->when($request->discount === 'yes', fn ($query) => $query->where('discount_value', '>', 0));
        $query->when($request->discount === 'no', fn ($query) => $query->where('discount_value', 0));
        $query->when($request->stock === 'out', fn ($query) => $query->where('stock', 0));
        $query->when($request->stock === 'low', fn ($query) => $query->whereBetween('stock', [1, 5]));

        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'name_asc' => $query->orderBy('name'),
            'price_low' => $query->orderBy('price'),
            'price_high' => $query->orderByDesc('price'),
            'stock_high' => $query->orderByDesc('stock'),
            default => $query->latest(),
        };

        $products = $query->paginate(10)->withQueryString();
        $categories = Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $editingProduct = request()->filled('edit')
            ? Product::findOrFail(request()->integer('edit'))
            : null;

        return view('admin.products.index', compact('products', 'categories', 'editingProduct'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);
        $validated['name'] = Str::title(trim($validated['name']));
        $validated['slug'] = $this->uniqueSlug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($validated);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request, $product);
        $validated['name'] = Str::title(trim($validated['name']));
        $validated['slug'] = $this->uniqueSlug($validated['name'], $product->id);
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $oldImage = $product->image;
            $validated['image'] = $request->file('image')->store('products', 'public');
            if ($oldImage) {
                Storage::disk('public')->delete($oldImage);
            }
        }

        $product->update($validated);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($validated['discount_type'] === 'percentage' && $validated['discount_value'] > 100) {
            throw ValidationException::withMessages(['discount_value' => 'Diskon persentase tidak boleh lebih dari 100%.']);
        }

        if ($validated['discount_type'] === 'fixed' && $validated['discount_value'] > $validated['price']) {
            throw ValidationException::withMessages(['discount_value' => 'Diskon nominal tidak boleh lebih besar dari harga produk.']);
        }

        return $validated;
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while (Product::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }
}
