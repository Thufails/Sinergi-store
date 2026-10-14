@extends('layouts.admin')

@section('title', 'Dashboard Admin - Sinergi Store')
@section('heading', 'Dashboard')

@section('content')
    <section class="relative isolate overflow-hidden rounded-2xl bg-gradient-to-br from-amber-400 via-orange-500 to-amber-600 p-6 shadow-sm sm:p-8">
        <div class="relative z-10 max-w-2xl">
            <p class="text-sm font-semibold text-amber-950">Selamat datang kembali, {{ auth()->user()->name }}</p>
            <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Kelola Sinergi Store dengan lebih mudah.</h2>
            <p class="mt-4 max-w-xl text-sm leading-6 text-amber-950/80 sm:text-base">Pantau katalog, stok, kategori, dan promosi produk dari satu ruang kerja yang rapi.</p>
            <div class="mt-6 flex flex-wrap gap-3 text-xs font-semibold text-amber-950/80"><span class="rounded-full bg-white/25 px-4 py-2">Katalog lebih rapi</span><span class="rounded-full bg-white/25 px-4 py-2">Produk mudah dikelola</span><span class="rounded-full bg-white/25 px-4 py-2">Promosi lebih fleksibel</span></div>
        </div>
        <div class="pointer-events-none absolute -right-10 -top-16 h-64 w-64 rounded-full border-[28px] border-white/15"></div>
        <div class="pointer-events-none absolute -bottom-28 right-24 h-72 w-72 rounded-full border-[42px] border-amber-200/20"></div>
        <div class="pointer-events-none absolute right-12 top-10 hidden h-28 w-44 rotate-6 rounded-2xl border border-white/25 bg-white/10 shadow-xl sm:block"></div>
        <div class="pointer-events-none absolute right-24 top-20 hidden h-28 w-44 -rotate-6 rounded-2xl border border-white/20 bg-white/10 sm:block"></div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total produk</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $totalProducts }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ $totalProducts ? 'Total katalog saat ini' : 'Belum ada data' }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Kategori</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $totalCategories }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ $totalCategories ? 'Kategori katalog saat ini' : 'Belum ada data' }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Produk aktif</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $activeProducts }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ $activeProducts ? 'Siap ditampilkan ke publik' : 'Belum ada data' }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Produk diskon</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $discountedProducts }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ $discountedProducts ? 'Sedang dipromosikan' : 'Belum ada diskon' }}</p>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5"><h2 class="text-lg font-semibold text-slate-900">Perhatian stok</h2><p class="mt-1 text-sm text-slate-500">Produk yang perlu segera dicek.</p></div>
            <div class="p-6">
                @if ($outOfStockProducts->isEmpty() && $lowStockProducts->isEmpty())<p class="text-sm text-slate-500">Semua stok masih aman.</p>@else
                    <div class="space-y-3">@foreach ($outOfStockProducts->take(3) as $product)<div class="flex items-center justify-between gap-4 rounded-xl bg-red-50 px-4 py-3"><span class="text-sm font-medium text-slate-800">{{ $product->name }}</span><span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">Habis</span></div>@endforeach @foreach ($lowStockProducts->take(3) as $product)<div class="flex items-center justify-between gap-4 rounded-xl bg-amber-50 px-4 py-3"><span class="text-sm font-medium text-slate-800">{{ $product->name }}</span><span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700">Stok {{ $product->stock }}</span></div>@endforeach</div>
                @endif
                <div class="mt-5 flex flex-wrap gap-2"><a href="{{ route('admin.products.index', ['stock' => 'out']) }}" class="rounded-xl border border-red-200 px-4 py-2 text-xs font-medium text-red-700 hover:bg-red-50">Lihat stok habis</a><a href="{{ route('admin.products.index', ['stock' => 'low']) }}" class="rounded-xl border border-amber-200 px-4 py-2 text-xs font-medium text-amber-700 hover:bg-amber-50">Lihat stok menipis</a></div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5"><h2 class="text-lg font-semibold text-slate-900">Produk terbaru</h2><p class="mt-1 text-sm text-slate-500">Lima produk terakhir ditambahkan.</p></div>
            <div class="p-6">@if ($latestProducts->isEmpty())<p class="text-sm text-slate-500">Belum ada produk.</p>@else<div class="space-y-3">@foreach ($latestProducts as $product)<div class="flex items-center justify-between gap-4"><div><p class="text-sm font-medium text-slate-800">{{ $product->name }}</p><p class="text-xs text-slate-500">{{ $product->category?->name ?? 'Tanpa kategori' }}</p></div><span class="text-sm font-medium text-slate-700">Rp {{ number_format($product->final_price, 0, ',', '.') }}</span></div>@endforeach</div>@endif</div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-200 px-6 py-5"><h2 class="text-lg font-semibold text-slate-900">Ringkasan kategori</h2><p class="mt-1 text-sm text-slate-500">Kategori dengan jumlah produk terbanyak.</p></div><div class="p-6">@if ($categorySummary->isEmpty())<p class="text-sm text-slate-500">Belum ada kategori.</p>@else<div class="space-y-4">@foreach ($categorySummary as $category)<div><div class="mb-1 flex justify-between text-sm"><span class="font-medium text-slate-700">{{ $category->name }}</span><span class="text-slate-500">{{ $category->products_count }} produk</span></div><div class="h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-amber-500" style="width: {{ $totalProducts ? min(100, ($category->products_count / $totalProducts) * 100) : 0 }}%"></div></div></div>@endforeach</div>@endif</div></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h2 class="text-lg font-semibold text-slate-900">Aksi cepat</h2><p class="mt-1 text-sm text-slate-500">Akses menu yang paling sering digunakan.</p><div class="mt-5 grid gap-3 sm:grid-cols-2"><a href="{{ route('admin.products.index') }}" class="rounded-xl bg-amber-500 px-4 py-3 text-center text-sm font-semibold text-slate-950 hover:bg-amber-400">Kelola produk</a><a href="{{ route('admin.categories.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Kelola kategori</a><a href="{{ route('admin.products.index', ['discount' => 'yes']) }}" class="rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Lihat produk diskon</a><a href="{{ route('admin.products.index', ['stock' => 'low']) }}" class="rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Cek stok menipis</a></div></div>
    </section>
@endsection
