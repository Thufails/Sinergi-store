@extends('layouts.admin')

@section('title', 'Produk - Sinergi Store')
@section('heading', 'Produk')

@section('content')
    @php
        $variantRows = old('variants');
        if ($variantRows === null) {
            $variantRows = $editingProduct?->variants
                ->map(fn ($variant) => [
                    'id' => $variant->id,
                    'size' => $variant->size,
                    'color' => $variant->color,
                    'sku' => $variant->sku,
                    'stock' => $variant->stock,
                ])
                ->all() ?? [];
        }
        $hasVariants = (bool) old('has_variants', $editingProduct?->variants->isNotEmpty() ?? false);
    @endphp
    @if (session('success'))<div class="mb-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="mb-6 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700"><ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col justify-between gap-4 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center">
            <div><h2 class="text-xl font-semibold text-slate-900">Daftar produk</h2><p class="mt-1 text-sm text-slate-500">Kelola katalog produk Sinergi Store.</p></div>
            <button type="button" data-open-product-drawer class="relative z-10 touch-manipulation rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">+ Tambah produk</button>
        </div>
        <form method="GET" action="{{ route('admin.products.index') }}" class="grid gap-3 border-b border-slate-200 bg-slate-50 p-5 sm:grid-cols-2 lg:grid-cols-4">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama produk..." class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
            <select name="category" class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200"><option value="">Semua kategori</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select>
            <select name="status" class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200"><option value="">Semua status</option><option value="active" @selected(request('status') === 'active')>Aktif</option><option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option></select>
            <select name="sort" class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200"><option value="latest" @selected(request('sort', 'latest') === 'latest')>Terbaru</option><option value="name_asc" @selected(request('sort') === 'name_asc')>Nama A-Z</option><option value="price_low" @selected(request('sort') === 'price_low')>Harga terendah</option><option value="price_high" @selected(request('sort') === 'price_high')>Harga tertinggi</option><option value="stock_high" @selected(request('sort') === 'stock_high')>Stok terbanyak</option></select>
            <select name="discount" class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200"><option value="">Semua diskon</option><option value="yes" @selected(request('discount') === 'yes')>Sedang diskon</option><option value="no" @selected(request('discount') === 'no')>Tanpa diskon</option></select>
            <select name="stock" class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200"><option value="">Semua stok</option><option value="out" @selected(request('stock') === 'out')>Stok habis</option><option value="low" @selected(request('stock') === 'low')>Stok menipis (1-5)</option></select>
            <div class="flex gap-2 sm:col-span-2 lg:col-span-4"><button type="submit" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white hover:bg-slate-700">Terapkan filter</button><a href="{{ route('admin.products.index') }}" class="rounded-xl px-5 py-3 text-sm font-medium text-slate-500 hover:bg-slate-200">Reset</a></div>
        </form>
        @if ($products->isEmpty())
            <div class="px-6 py-16 text-center"><p class="font-medium text-slate-700">{{ request()->hasAny(['search', 'category', 'status', 'sort', 'discount', 'stock']) ? 'Produk tidak ditemukan.' : 'Belum ada produk.' }}</p><p class="mt-1 text-sm text-slate-500">{{ request()->hasAny(['search', 'category', 'status', 'sort', 'discount', 'stock']) ? 'Coba ubah kata kunci atau filter yang digunakan.' : 'Klik tombol tambah produk untuk memulai.' }}</p></div>
        @else
            <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-6 py-4 font-semibold">Gambar</th><th class="px-6 py-4 font-semibold">Produk</th><th class="px-6 py-4 font-semibold">Kategori</th><th class="px-6 py-4 font-semibold">Harga</th><th class="px-6 py-4 font-semibold">Diskon</th><th class="px-6 py-4 font-semibold">Stok</th><th class="px-6 py-4 font-semibold">Status</th><th class="px-6 py-4 text-right font-semibold">Aksi</th></tr></thead><tbody class="divide-y divide-slate-100">
                @foreach ($products as $product)
                    <tr class="hover:bg-slate-50"><td class="px-6 py-4">@if ($product->image)<img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-14 w-14 rounded-xl object-cover">@else<span class="flex h-14 w-14 items-center justify-center rounded-xl bg-slate-100 text-xs text-slate-400">Tidak ada</span>@endif</td><td class="px-6 py-4"><p class="font-medium text-slate-900">{{ $product->name }}</p><p class="text-xs text-slate-500">{{ $product->slug }}</p>@if ($product->variants->isNotEmpty())<p class="mt-1 text-xs font-medium text-amber-700">{{ $product->variants->count() }} varian</p>@endif</td><td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $product->category?->name ?? '-' }}</td><td class="whitespace-nowrap px-6 py-4">@if ((float) $product->discount_value > 0)<p class="font-semibold text-emerald-700">Rp {{ number_format($product->final_price, 0, ',', '.') }}</p><p class="text-xs text-slate-400 line-through">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</p>@else<span class="text-slate-700">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</span>@endif</td><td class="whitespace-nowrap px-6 py-4">@if ((float) $product->discount_value > 0)<span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700">{{ $product->discount_type === 'fixed' ? 'Rp ' . number_format((float) $product->discount_value, 0, ',', '.') : rtrim(rtrim(number_format((float) $product->discount_value, 2, ',', '.'), '0'), ',') . '%' }}</span>@else<span class="text-slate-400">Tidak ada</span>@endif</td><td class="px-6 py-4"><dl class="min-w-28 space-y-1.5 text-xs"><div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Fisik</dt><dd class="font-semibold text-slate-800">{{ $product->stock }}</dd></div><div class="flex items-center justify-between gap-3"><dt class="text-amber-700">Di-hold</dt><dd class="font-semibold text-amber-700">{{ $product->held_stock }}</dd></div><div class="flex items-center justify-between gap-3"><dt class="text-emerald-700">Tersedia</dt><dd class="font-semibold text-emerald-700">{{ $product->available_stock }}</dd></div><div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Terjual</dt><dd class="font-semibold text-slate-700">{{ $product->sold_stock }}</dd></div></dl></td><td class="whitespace-nowrap px-6 py-4">@if ($product->is_active)<span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700">Aktif</span>@else<span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500">Nonaktif</span>@endif</td><td class="px-6 py-4"><div class="flex flex-col items-end gap-1.5"><a href="{{ route('admin.products.index', ['edit' => $product->id]) }}" class="rounded-md px-2.5 py-1 text-sm font-medium text-amber-700 transition hover:bg-amber-50 hover:text-amber-800">Edit</a><form action="{{ route('admin.products.destroy', $product) }}" method="POST" data-confirm-title="Hapus produk ini?" data-confirm-description="Produk {{ $product->name }} akan dihapus permanen." data-confirm-button="Ya, hapus produk" data-confirm-tone="danger">@csrf @method('DELETE')<button type="submit" class="rounded-md px-2.5 py-1 text-sm font-medium text-rose-700 transition hover:bg-rose-50 hover:text-rose-800">Hapus</button></form></div></td></tr>
                @endforeach
            </tbody></table></div>
            <div class="border-t border-slate-200 px-6 py-4">{{ $products->links() }}</div>
        @endif
    </div>

    <div data-product-backdrop style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh; z-index: 9998; background: rgba(15, 23, 42, .42); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);" class="hidden"></div>
    <aside data-product-drawer style="left: 50%; top: 50%; height: 90vh; transform: translate(-50%, -45%); z-index: 9999;" class="pointer-events-none fixed flex max-h-[90vh] w-[calc(100%-2rem)] max-w-5xl flex-col overflow-hidden rounded-2xl bg-white opacity-0 shadow-2xl transition-all duration-300 ease-out">
        <div class="flex shrink-0 items-center justify-between border-b border-slate-200 px-6 py-5"><div><p class="text-sm font-medium text-amber-600">Katalog</p><h2 class="text-xl font-semibold text-slate-900">{{ $editingProduct ? 'Edit produk' : 'Tambah produk' }}</h2></div><button type="button" data-close-product-drawer aria-label="Tutup form" class="rounded-lg p-2 text-2xl leading-none text-slate-400 hover:bg-slate-100 hover:text-slate-700">&times;</button></div>
        <div class="relative max-h-[calc(90vh-12rem)] flex-none overflow-y-auto p-6">
            @if ($categories->isEmpty())<div class="mb-5 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-700">Buat kategori aktif terlebih dahulu sebelum menambahkan produk.</div>@endif
            <form id="product-form" method="POST" action="{{ $editingProduct ? route('admin.products.update', $editingProduct) : route('admin.products.store') }}" enctype="multipart/form-data" class="grid gap-5 sm:grid-cols-2">
                @csrf
                @if ($editingProduct) @method('PUT') @endif
                <div><label for="name" class="mb-2 block text-sm font-medium text-slate-700">Nama produk</label><input id="name" name="name" type="text" value="{{ old('name', $editingProduct?->name) }}" required placeholder="Contoh: Laptop Pro" class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200"></div>
                <div><label for="category_id" class="mb-2 block text-sm font-medium text-slate-700">Kategori</label><select id="category_id" name="category_id" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200"><option value="">Pilih kategori</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $editingProduct?->category_id) == $category->id)>{{ $category->name }}{{ $category->is_active ? '' : ' (Nonaktif)' }}</option>@endforeach</select></div>
                <div class="grid gap-4 sm:grid-cols-2"><div><label for="price" class="mb-2 block text-sm font-medium text-slate-700">Harga normal</label><input id="price" name="price" type="number" min="0" step="0.01" value="{{ old('price', $editingProduct?->price) }}" required placeholder="0" class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200"></div><div data-stock-field><label for="stock" class="mb-2 block text-sm font-medium text-slate-700">Stok / total stok</label><input id="stock" name="stock" type="number" min="0" value="{{ old('stock', $editingProduct?->stock ?? 0) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200"><p data-stock-hint class="mt-1 text-xs text-slate-400">Stok umum untuk produk tanpa varian.</p></div></div>
                <section class="sm:col-span-2 rounded-xl border border-slate-200 p-4">
                    <label for="has_variants" class="flex cursor-pointer items-start gap-3 text-sm font-medium text-slate-700">
                        <input type="hidden" name="has_variants" value="0">
                        <input id="has_variants" name="has_variants" type="checkbox" value="1" @checked($hasVariants) class="mt-0.5 h-4 w-4 rounded border-slate-300 text-amber-500 focus:ring-amber-400">
                        <span>Produk ini memiliki varian ukuran atau warna<p class="mt-1 font-normal text-slate-500">Aktifkan jika pembeli perlu memilih opsi dan setiap opsi punya stok sendiri.</p></span>
                    </label>
                    <div data-variant-fields class="{{ $hasVariants ? '' : 'hidden' }} mt-4 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-3"><p class="text-sm font-semibold text-slate-800">Daftar varian</p><button type="button" data-add-variant class="rounded-lg bg-amber-100 px-3 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-200">+ Tambah varian</button></div>
                        <div data-variant-rows class="space-y-3">
                            @foreach ($variantRows as $index => $variant)
                                <div data-variant-row class="rounded-xl bg-slate-50 p-4">
                                    <input type="hidden" data-variant-key="id" name="variants[{{ $index }}][id]" value="{{ data_get($variant, 'id') }}">
                                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1.2fr_1fr_auto]">
                                        <label class="text-xs font-medium text-slate-600">Ukuran <input data-variant-key="size" name="variants[{{ $index }}][size]" value="{{ data_get($variant, 'size') }}" placeholder="Contoh: M" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm"></label>
                                        <label class="text-xs font-medium text-slate-600">Warna <input data-variant-key="color" name="variants[{{ $index }}][color]" value="{{ data_get($variant, 'color') }}" placeholder="Contoh: Hitam" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm"></label>
                                        <label class="text-xs font-medium text-slate-600">SKU <span class="font-normal text-slate-400">(opsional)</span><input data-variant-key="sku" name="variants[{{ $index }}][sku]" value="{{ data_get($variant, 'sku') }}" placeholder="Kode produk" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm"></label>
                                        <label class="text-xs font-medium text-slate-600">Stok <input data-variant-key="stock" name="variants[{{ $index }}][stock]" type="number" min="0" value="{{ data_get($variant, 'stock', 0) }}" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm"></label>
                                        <button type="button" data-remove-variant class="self-end rounded-lg px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50">Hapus</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-xs text-slate-500">Isi ukuran, warna, atau keduanya. Total stok produk dihitung otomatis dari stok semua varian.</p>
                    </div>
                </section>
                <div class="grid gap-4 sm:grid-cols-2"><div><label for="discount_type" class="mb-2 block text-sm font-medium text-slate-700">Tipe diskon</label><select id="discount_type" name="discount_type" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200"><option value="percentage" @selected(old('discount_type', $editingProduct?->discount_type ?? 'percentage') === 'percentage')>Persentase (%)</option><option value="fixed" @selected(old('discount_type', $editingProduct?->discount_type) === 'fixed')>Nominal (Rp)</option></select></div><div><label for="discount_value" class="mb-2 block text-sm font-medium text-slate-700">Nilai diskon</label><input id="discount_value" name="discount_value" type="number" min="0" step="0.01" value="{{ old('discount_value', $editingProduct?->discount_value ?? 0) }}" required placeholder="0" class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200"><p id="discount-help" class="mt-1 text-xs text-slate-400">Isi persentase, contoh 10 untuk 10%.</p></div></div>
                <div><label for="description" class="mb-2 block text-sm font-medium text-slate-700">Deskripsi <span class="font-normal text-slate-400">(opsional)</span></label><textarea id="description" name="description" rows="5" placeholder="Deskripsi singkat produk" class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200">{{ old('description', $editingProduct?->description) }}</textarea></div>
                <div><label for="image" class="mb-2 block text-sm font-medium text-slate-700">Gambar <span class="font-normal text-slate-400">(opsional, max 2 MB)</span></label><img id="product-image-preview" src="{{ $editingProduct?->image ? asset('storage/' . $editingProduct->image) : '' }}" alt="Preview gambar produk" class="{{ $editingProduct?->image ? '' : 'hidden' }} mb-3 h-24 w-24 rounded-xl object-cover"><input id="image" name="image" type="file" accept="image/*" class="block w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-500 file:mr-4 file:rounded-lg file:border-0 file:bg-amber-100 file:px-3 file:py-2 file:font-medium file:text-amber-800"></div>
                <label class="flex items-center gap-3 text-sm font-medium text-slate-700"><input name="is_active" type="checkbox" value="1" @checked(old('is_active', $editingProduct ? $editingProduct->is_active : true)) class="h-4 w-4 rounded border-slate-300 text-amber-500 focus:ring-amber-400"><span>Produk aktif</span></label>
            </form>
        </div>
        <div class="flex shrink-0 gap-3 border-t border-slate-200 bg-white px-6 py-4"><button type="submit" form="product-form" class="flex-1 rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">{{ $editingProduct ? 'Simpan perubahan' : 'Simpan produk' }}</button>@if ($editingProduct)<button type="button" data-close-product-drawer class="rounded-xl px-5 py-3 text-sm font-medium text-slate-500 hover:bg-slate-100">Batal</button>@endif</div>
    </aside>
    <div data-image-modal class="fixed inset-0 z-[10000] hidden items-center justify-center bg-slate-950/80 p-6"><button type="button" data-close-image-modal aria-label="Tutup preview gambar" class="absolute right-6 top-5 text-4xl leading-none text-white/80 hover:text-white">&times;</button><img data-image-modal-content src="" alt="" class="max-h-[85vh] max-w-full rounded-2xl object-contain shadow-2xl"></div>
@endsection

@push('scripts')
<script>
    const productDrawer = document.querySelector('[data-product-drawer]');
    const productBackdrop = document.querySelector('[data-product-backdrop]');
    const productsUrl = @json(route('admin.products.index'));
    const hasEditingProduct = @json((bool) $editingProduct);
    const productImageInput = document.getElementById('image');
    const productImagePreview = document.getElementById('product-image-preview');
    const discountType = document.getElementById('discount_type');
    const discountValue = document.getElementById('discount_value');
    const discountHelp = document.getElementById('discount-help');
    const productForm = document.getElementById('product-form');
    const hasVariantsInput = document.getElementById('has_variants');
    const variantFields = document.querySelector('[data-variant-fields]');
    const variantRows = document.querySelector('[data-variant-rows]');
    const addVariantButton = document.querySelector('[data-add-variant]');
    const productStockInput = document.getElementById('stock');
    const stockHint = document.querySelector('[data-stock-hint]');
    const initiallyHadVariants = @json((bool) ($editingProduct?->variants->isNotEmpty() ?? false));
    const imageModal = document.querySelector('[data-image-modal]');
    const imageModalContent = document.querySelector('[data-image-modal-content]');
    const updateDiscountFields = () => {
        const isFixed = discountType?.value === 'fixed';
        if (discountValue) discountValue.max = isFixed ? '' : '100';
        if (discountHelp) discountHelp.textContent = isFixed ? 'Isi nominal rupiah, contoh 20000.' : 'Isi persentase, contoh 10 untuk 10%.';
    };
    const renumberVariantRows = () => {
        variantRows?.querySelectorAll('[data-variant-row]').forEach((row, index) => {
            row.querySelectorAll('[data-variant-key]').forEach((field) => {
                field.name = `variants[${index}][${field.dataset.variantKey}]`;
            });
        });
    };
    const updateProductStockFromVariants = () => {
        if (!hasVariantsInput?.checked || !productStockInput) return;
        const total = [...(variantRows?.querySelectorAll('[data-variant-key="stock"]') ?? [])]
            .reduce((sum, field) => sum + (Number.parseInt(field.value, 10) || 0), 0);
        productStockInput.value = total;
    };
    const addVariantRow = () => {
        if (!variantRows) return;
        const row = document.createElement('div');
        row.dataset.variantRow = '';
        row.className = 'rounded-xl bg-slate-50 p-4';
        row.innerHTML = `
            <input type="hidden" data-variant-key="id" value="">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1.2fr_1fr_auto]">
                <label class="text-xs font-medium text-slate-600">Ukuran <input data-variant-key="size" placeholder="Contoh: M" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm"></label>
                <label class="text-xs font-medium text-slate-600">Warna <input data-variant-key="color" placeholder="Contoh: Hitam" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm"></label>
                <label class="text-xs font-medium text-slate-600">SKU <span class="font-normal text-slate-400">(opsional)</span><input data-variant-key="sku" placeholder="Kode produk" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm"></label>
                <label class="text-xs font-medium text-slate-600">Stok <input data-variant-key="stock" type="number" min="0" value="0" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm"></label>
                <button type="button" data-remove-variant class="self-end rounded-lg px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50">Hapus</button>
            </div>`;
        variantRows.append(row);
        renumberVariantRows();
        updateProductStockFromVariants();
    };
    const updateVariantMode = () => {
        const enabled = Boolean(hasVariantsInput?.checked);
        variantFields?.classList.toggle('hidden', !enabled);
        if (productStockInput) {
            productStockInput.readOnly = enabled;
            productStockInput.required = !enabled;
        }
        if (stockHint) stockHint.textContent = enabled
            ? 'Dihitung otomatis dari seluruh stok varian.'
            : 'Stok umum untuk produk tanpa varian.';
        if (enabled && variantRows?.children.length === 0) addVariantRow();
        updateProductStockFromVariants();
    };
    const openProductDrawer = () => { productDrawer.style.transform = 'translate(-50%, -50%)'; productDrawer.classList.remove('pointer-events-none', 'opacity-0'); productBackdrop.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); };
    const closeProductDrawer = () => {
        if (hasEditingProduct) { window.location.href = productsUrl; return; }
        productDrawer.style.transform = 'translate(-50%, -45%)'; productDrawer.classList.add('pointer-events-none', 'opacity-0'); productBackdrop.classList.add('hidden'); document.body.classList.remove('overflow-hidden');
    };
    document.addEventListener('click', (event) => {
        const imageButton = event.target.closest('[data-image-preview]');
        if (imageButton) {
            imageModalContent.src = imageButton.dataset.imagePreview;
            imageModalContent.alt = imageButton.dataset.imageAlt;
            imageModal.classList.remove('hidden');
            imageModal.classList.add('flex');
            return;
        }
        if (event.target.closest('[data-open-product-drawer]')) {
            if (hasEditingProduct) { window.location.href = productsUrl; } else { openProductDrawer(); }
        }
    });
    document.querySelectorAll('[data-close-product-drawer]').forEach((button) => button.addEventListener('click', closeProductDrawer));
    productBackdrop?.addEventListener('click', closeProductDrawer);
    const closeImageModal = () => { imageModal.classList.add('hidden'); imageModal.classList.remove('flex'); imageModalContent.src = ''; };
    document.querySelector('[data-close-image-modal]')?.addEventListener('click', closeImageModal);
    imageModal?.addEventListener('click', (event) => { if (event.target === imageModal) closeImageModal(); });
    document.querySelectorAll('table tbody tr td:first-child img').forEach((image) => {
        image.classList.add('cursor-zoom-in');
        image.addEventListener('click', () => {
            imageModalContent.src = image.src;
            imageModalContent.alt = image.alt;
            imageModal.classList.remove('hidden');
            imageModal.classList.add('flex');
        });
    });
    productImageInput?.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (!file) return;
        productImagePreview.src = URL.createObjectURL(file);
        productImagePreview.classList.remove('hidden');
    });
    discountType?.addEventListener('change', updateDiscountFields);
    hasVariantsInput?.addEventListener('change', updateVariantMode);
    addVariantButton?.addEventListener('click', addVariantRow);
    variantRows?.addEventListener('click', (event) => {
        if (!event.target.closest('[data-remove-variant]')) return;
        event.target.closest('[data-variant-row]')?.remove();
        renumberVariantRows();
        updateProductStockFromVariants();
    });
    variantRows?.addEventListener('input', (event) => {
        if (event.target.matches('[data-variant-key="stock"]')) updateProductStockFromVariants();
    });
    productForm?.addEventListener('submit', (event) => {
        if (initiallyHadVariants && !hasVariantsInput?.checked) {
            productForm.dataset.confirmTitle = 'Hapus varian produk?';
            productForm.dataset.confirmDescription = 'Semua varian ukuran/warna akan dihapus dan stoknya dipindahkan menjadi stok umum produk.';
            productForm.dataset.confirmButton = 'Ya, hapus varian';
            productForm.dataset.confirmTone = 'danger';
        } else {
            delete productForm.dataset.confirmTitle;
            delete productForm.dataset.confirmDescription;
            delete productForm.dataset.confirmButton;
            delete productForm.dataset.confirmTone;
        }
    });
    updateDiscountFields();
    updateVariantMode();
    @if ($editingProduct || $errors->any()) openProductDrawer(); @endif
</script>
@endpush
