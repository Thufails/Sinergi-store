<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Category::withCount('products');
        $query->when($request->filled('search'), function ($query) use ($request) {
            $search = (string) $request->string('search')->trim();
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%");
            });
        });
        $query->when($request->status === 'active', fn ($query) => $query->where('is_active', true));
        $query->when($request->status === 'inactive', fn ($query) => $query->where('is_active', false));
        match ($request->input('sort', 'order')) {
            'name_asc' => $query->orderBy('name'),
            'products_high' => $query->orderByDesc('products_count'),
            default => $query->orderBy('sort_order')->orderBy('name'),
        };
        $categories = $query->paginate(10)->withQueryString();
        $editingCategory = request()->filled('edit')
            ? Category::findOrFail(request()->integer('edit'))
            : null;

        return view('admin.categories.index', compact('categories', 'editingCategory'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['name'] = Str::title(trim($validated['name']));
        $validated['slug'] = $this->uniqueSlug($validated['name']);
        $validated['sort_order'] = (Category::max('sort_order') ?? 0) + 1;
        $validated['is_active'] = $request->boolean('is_active');

        Category::create($validated);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
            'description' => ['nullable', 'string'],
        ]);

        $validated['name'] = Str::title(trim($validated['name']));
        $validated['slug'] = $this->uniqueSlug($validated['name'], $category->id);
        $validated['is_active'] = $request->boolean('is_active');

        $category->update($validated);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh produk.');
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while (Category::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }
}
