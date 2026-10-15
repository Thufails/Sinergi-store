<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'variants']);

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
        $productIds = $products->getCollection()->modelKeys();
        $heldStockByProduct = OrderItem::query()
            ->whereIn('product_id', $productIds)
            ->whereHas('order', fn ($orderQuery) => $orderQuery
                ->where('status', Order::STATUS_AWAITING_PAYMENT)
                ->where('payment_due_at', '>', now()))
            ->selectRaw('product_id, SUM(quantity) as reserved_quantity')
            ->groupBy('product_id')
            ->pluck('reserved_quantity', 'product_id');
        $soldStockByProduct = OrderItem::query()
            ->whereIn('product_id', $productIds)
            ->whereHas('order', fn ($orderQuery) => $orderQuery->where('status', Order::STATUS_PAID))
            ->selectRaw('product_id, SUM(quantity) as sold_quantity')
            ->groupBy('product_id')
            ->pluck('sold_quantity', 'product_id');

        $products->getCollection()->each(function (Product $product) use ($heldStockByProduct, $soldStockByProduct) {
            $heldStock = (int) $heldStockByProduct->get($product->id, 0);
            $product->setAttribute('held_stock', $heldStock);
            $product->setAttribute('available_stock', max(0, (int) $product->stock - $heldStock));
            $product->setAttribute('sold_stock', (int) $soldStockByProduct->get($product->id, 0));
        });
        $categories = Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $editingProduct = request()->filled('edit')
            ? Product::with('variants')->findOrFail(request()->integer('edit'))
            : null;

        return view('admin.products.index', compact('products', 'categories', 'editingProduct'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);
        $validated['name'] = Str::title(trim($validated['name']));
        $validated['slug'] = $this->uniqueSlug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $hasVariants = $validated['has_variants'];
        $variants = $validated['variants'] ?? [];
        unset($validated['has_variants'], $validated['variants']);

        if ($hasVariants) {
            $validated['stock'] = 0;
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        DB::transaction(function () use ($validated, $variants, $hasVariants) {
            $product = Product::create($validated);
            $this->syncVariants($product, $variants, $hasVariants);
        });

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
        $hasVariants = $validated['has_variants'];
        $variants = $validated['variants'] ?? [];
        unset($validated['has_variants'], $validated['variants']);

        if ($hasVariants) {
            $validated['stock'] = 0;
        }

        $oldImage = $product->image;
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        DB::transaction(function () use ($product, $validated, $variants, $hasVariants) {
            $product->update($validated);
            $this->syncVariants($product, $variants, $hasVariants);
        });

        if ($request->hasFile('image') && $oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

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
        $hasVariants = $request->boolean('has_variants');
        $variantRows = $request->input('variants', []);
        $rules = [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'stock' => [Rule::requiredIf(! $hasVariants), 'nullable', 'integer', 'min:0'],
            'has_variants' => ['nullable', 'boolean'],
            'variants' => [Rule::requiredIf($hasVariants), 'array', $hasVariants ? 'min:1' : 'nullable'],
            'variants.*.id' => ['nullable', 'integer', 'distinct'],
            'variants.*.size' => ['nullable', 'string', 'max:50'],
            'variants.*.color' => ['nullable', 'string', 'max:50'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.stock' => [$hasVariants ? 'required' : 'nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];

        foreach (is_array($variantRows) ? $variantRows : [] as $index => $variant) {
            $variantId = filter_var($variant['id'] ?? null, FILTER_VALIDATE_INT);
            $rules["variants.{$index}.sku"][] = Rule::unique('product_variants', 'sku')->ignore($variantId ?: 0);
        }

        $validated = $request->validate([
            ...$rules,
        ]);

        $variantErrors = [];
        $variantCombinations = [];
        $variantSkus = [];

        foreach ($validated['variants'] ?? [] as $index => $variant) {
            $size = trim($variant['size'] ?? '');
            $color = trim($variant['color'] ?? '');
            $sku = trim($variant['sku'] ?? '');

            if ($size === '' && $color === '') {
                $variantErrors["variants.{$index}.size"] = 'Isi ukuran atau warna untuk setiap varian.';
            }

            $combinationKey = mb_strtolower($size . "\0" . $color);
            if (isset($variantCombinations[$combinationKey])) {
                $variantErrors["variants.{$index}.size"] = 'Kombinasi ukuran dan warna tidak boleh duplikat.';
            }
            $variantCombinations[$combinationKey] = true;

            if ($sku !== '') {
                $skuKey = mb_strtolower($sku);
                if (isset($variantSkus[$skuKey])) {
                    $variantErrors["variants.{$index}.sku"] = 'SKU tidak boleh digunakan lebih dari satu varian.';
                }
                $variantSkus[$skuKey] = true;
            }

            if (! empty($variant['id']) && (! $product || ! $product->variants()->whereKey($variant['id'])->exists())) {
                $variantErrors["variants.{$index}.id"] = 'Varian yang dipilih tidak termasuk dalam produk ini.';
            }

            $validated['variants'][$index] = [
                'id' => $variant['id'] ?? null,
                'size' => $size,
                'color' => $color,
                'sku' => $sku !== '' ? $sku : null,
                'stock' => (int) $variant['stock'],
            ];
        }

        if ($variantErrors !== []) {
            throw ValidationException::withMessages($variantErrors);
        }

        $validated['has_variants'] = $hasVariants;

        if ($validated['discount_type'] === 'percentage' && $validated['discount_value'] > 100) {
            throw ValidationException::withMessages(['discount_value' => 'Diskon persentase tidak boleh lebih dari 100%.']);
        }

        if ($validated['discount_type'] === 'fixed' && $validated['discount_value'] > $validated['price']) {
            throw ValidationException::withMessages(['discount_value' => 'Diskon nominal tidak boleh lebih besar dari harga produk.']);
        }

        return $validated;
    }

    private function syncVariants(Product $product, array $variants, bool $hasVariants): void
    {
        if (! $hasVariants) {
            $product->variants()->delete();

            return;
        }

        $variantIds = collect($variants)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        $product->variants()
            ->when($variantIds !== [], fn ($query) => $query->whereNotIn('id', $variantIds))
            ->when($variantIds === [], fn ($query) => $query->whereNotNull('id'))
            ->delete();

        foreach ($variants as $variant) {
            $attributes = [
                'size' => $variant['size'],
                'color' => $variant['color'],
                'sku' => $variant['sku'],
                'stock' => $variant['stock'],
            ];

            if (! empty($variant['id'])) {
                $product->variants()->whereKey($variant['id'])->update($attributes);
            } else {
                $product->variants()->create($attributes);
            }
        }

        $product->update(['stock' => $product->variants()->sum('stock')]);
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
