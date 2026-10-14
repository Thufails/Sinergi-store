@extends('layouts.store')

@section('title', 'Produk - Sinergi Store')

@section('content')
    <section class="bg-slate-50">
        <div class="mx-auto max-w-7xl px-6 py-8 lg:px-8">
            <form method="GET" action="{{ route('store.products.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="grid gap-3 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)_minmax(0,1fr)_auto]">
                    <label class="relative block">
                        <span class="sr-only">Cari produk</span>
                        <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari produk..." class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-amber-400 focus:bg-white focus:ring-2 focus:ring-amber-100">
                    </label>

                    <label>
                        <span class="sr-only">Kategori</span>
                        <select name="category" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-amber-400 focus:bg-white focus:ring-2 focus:ring-amber-100">
                            <option value="">Semua kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span class="sr-only">Urutkan produk</span>
                        <select name="sort" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-amber-400 focus:bg-white focus:ring-2 focus:ring-amber-100">
                            <option value="latest">Terbaru</option>
                            <option value="name_asc" @selected(request('sort') === 'name_asc')>Nama A-Z</option>
                            <option value="price_low" @selected(request('sort') === 'price_low')>Harga terendah</option>
                            <option value="price_high" @selected(request('sort') === 'price_high')>Harga tertinggi</option>
                        </select>
                    </label>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">Cari</button>
                        <a href="{{ route('store.products.index') }}" class="inline-flex items-center rounded-xl px-3 py-3 text-sm text-slate-500 transition hover:bg-slate-100 hover:text-slate-900">Reset</a>
                    </div>
                </div>
            </form>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
                @forelse ($products as $product)
                    <a href="{{ route('store.products.show', $product->slug) }}" class="group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                        <div class="relative aspect-square overflow-hidden bg-slate-100">
                            @if ($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @else
                                <div class="flex h-full items-center justify-center text-sm text-slate-400">Tidak ada gambar</div>
                            @endif

                            @if ((float) $product->discount_value > 0)
                                <span class="absolute left-3 top-3 rounded-md bg-amber-500 px-2 py-1 text-[11px] font-bold text-slate-950">
                                    @if ($product->discount_type === 'percentage')
                                        {{ rtrim(rtrim(number_format((float) $product->discount_value, 2, ',', '.'), '0'), ',') }}% OFF
                                    @else
                                        Hemat
                                    @endif
                                </span>
                            @endif
                        </div>

                        <div class="p-4">
                            <p class="text-xs text-slate-500">{{ $product->category?->name }}</p>
                            <h2 class="mt-1 min-h-10 text-sm font-semibold leading-5 text-slate-900">{{ $product->name }}</h2>

                            <div class="mt-3">
                                @if ((float) $product->discount_value > 0)
                                    <p class="text-base font-bold text-amber-600">Rp {{ number_format($product->final_price, 0, ',', '.') }}</p>
                                    <p class="mt-1 text-xs text-slate-400 line-through">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</p>
                                @else
                                    <p class="text-base font-bold text-slate-900">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</p>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full rounded-xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center text-sm text-slate-500">Produk tidak ditemukan.</div>
                @endforelse
            </div>

            <div class="mt-8">{{ $products->links() }}</div>
        </div>
    </section>
@endsection
