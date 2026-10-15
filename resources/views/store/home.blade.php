@extends('layouts.store')

@section('title', 'Sinergi Store - Home')

@section('content')
    <section id="home" class="bg-slate-50 px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        <div
            class="relative mx-auto max-w-7xl overflow-hidden rounded-3xl bg-slate-950 shadow-lg"
            data-store-carousel
            aria-roledescription="carousel"
            aria-label="Produk unggulan Sinergi Store"
        >
            <div class="flex transition-transform duration-500 ease-out motion-reduce:transition-none" data-store-carousel-track>
                @forelse ($featuredProducts as $product)
                    @php
                        $hasDiscount = (float) $product->discount_value > 0;
                    @endphp

                    <article
                        class="grid min-w-full items-center gap-4 px-6 py-8 text-white sm:px-10 sm:py-10 lg:grid-cols-[1fr_0.9fr] lg:gap-10 lg:px-16 lg:py-12"
                        data-store-carousel-slide
                        aria-roledescription="slide"
                        aria-label="Produk {{ $loop->iteration }} dari {{ $featuredProducts->count() }}"
                    >
                        <div class="relative z-10 order-2 lg:order-1">
                            <p class="text-sm font-semibold text-amber-400">{{ $product->category?->name ?? 'Produk unggulan' }}</p>
                            <h1 class="mt-3 max-w-2xl text-3xl font-semibold leading-tight tracking-tight sm:text-4xl lg:text-5xl">{{ $product->name }}</h1>
                            <p class="mt-3 max-w-xl text-sm leading-6 text-slate-300 sm:text-base">
                                {{ \Illuminate\Support\Str::limit($product->description ?: 'Temukan detail produk dan informasi selengkapnya di katalog Sinergi Store.', 135) }}
                            </p>

                            <div class="mt-5 flex flex-wrap items-center gap-x-3 gap-y-2">
                                <span class="text-xl font-bold text-amber-400 sm:text-2xl">Rp {{ number_format($product->final_price, 0, ',', '.') }}</span>
                                @if ($hasDiscount)
                                    <span class="text-sm text-slate-400 line-through">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</span>
                                    <span class="rounded-full bg-amber-400/15 px-2.5 py-1 text-xs font-bold text-amber-300">
                                        @if ($product->discount_type === 'percentage')
                                            Diskon {{ rtrim(rtrim(number_format((float) $product->discount_value, 2, ',', '.'), '0'), ',') }}%
                                        @else
                                            Harga spesial
                                        @endif
                                    </span>
                                @endif
                            </div>

                            <a href="{{ route('store.products.show', $product->slug) }}" class="mt-6 inline-flex items-center rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">
                                Lihat produk
                                <span class="ml-2" aria-hidden="true">→</span>
                            </a>
                        </div>

                        <a href="{{ route('store.products.show', $product->slug) }}" class="group relative order-1 mx-auto flex h-48 w-full max-w-md items-center justify-center overflow-hidden rounded-2xl bg-white/5 lg:order-2 lg:h-72">
                            @if ($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-full w-full object-contain p-3 transition duration-500 group-hover:scale-105">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-sm text-slate-400">Tidak ada gambar produk</div>
                            @endif
                        </a>
                    </article>
                @empty
                    <article class="flex min-w-full flex-col justify-center px-6 py-12 text-white sm:px-10 lg:min-h-[360px] lg:px-16">
                        <p class="text-sm font-semibold text-amber-400">Sinergi Store</p>
                        <h1 class="mt-3 text-3xl font-semibold sm:text-5xl">Temukan produk untuk kebutuhanmu.</h1>
                        <p class="mt-3 max-w-xl text-sm leading-6 text-slate-300">Produk yang tersedia akan tampil di sini.</p>
                        <a href="{{ route('store.products.index') }}" class="mt-6 inline-flex w-fit rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">Jelajahi produk</a>
                    </article>
                @endforelse
            </div>

            @if ($featuredProducts->count() > 1)
                <div class="absolute inset-x-0 bottom-4 z-20 flex justify-center px-5 sm:bottom-5 sm:px-8">
                    <div class="flex items-center gap-2" aria-label="Indikator slide">
                        @foreach ($featuredProducts as $product)
                            <button
                                type="button"
                                class="h-2.5 w-2.5 rounded-full bg-white/40 transition hover:bg-white/70"
                                data-store-carousel-dot="{{ $loop->index }}"
                                aria-label="Tampilkan produk {{ $loop->iteration }}"
                                aria-current="{{ $loop->first ? 'true' : 'false' }}"
                            ></button>
                        @endforeach
                    </div>

                </div>
            @endif
        </div>
    </section>

    <section id="about" class="bg-white px-6 py-12 sm:py-16 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-6 lg:grid-cols-[0.65fr_1.35fr] lg:items-center">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-600">Tentang kami</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Sinergi Store</h2>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 sm:p-7">
                <p class="text-base leading-7 text-slate-600">
                    Sinergi Store hadir untuk memudahkan Anda mendapatkan produk merchandise secara online, kapan pun dan di mana pun. Sebagai bagian dari Sinergi Grup, kami juga menyediakan layanan kreatif berupa pembuatan katalog, fotografi produk, video promosi, hingga desain produk untuk membantu bisnis Anda tampil lebih menarik dan profesional.
                </p>
            </div>
        </div>
    </section>

    <section class="bg-slate-50 px-6 py-16 sm:py-20 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-600">Jelajahi katalog</p>
                    <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Produk-produk Sinergi Store</h2>
                    <p class="mt-2 text-sm text-slate-500">Pilihan produk yang tersedia saat ini.</p>
                </div>

                <a href="{{ route('store.products.index') }}" class="inline-flex w-fit items-center text-sm font-semibold text-amber-700 transition hover:text-amber-800">
                    Lihat semua produk <span class="ml-2" aria-hidden="true">→</span>
                </a>
            </div>

            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($featuredProducts->take(6) as $product)
                    <a href="{{ route('store.products.show', $product->slug) }}" class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-md">
                        <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
                            @if ($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @else
                                <div class="flex h-full items-center justify-center text-sm text-slate-400">Tidak ada gambar produk</div>
                            @endif

                            @if ((float) $product->discount_value > 0)
                                <span class="absolute left-3 top-3 rounded-md bg-amber-500 px-2.5 py-1 text-xs font-bold text-slate-950">
                                    @if ($product->discount_type === 'percentage')
                                        {{ rtrim(rtrim(number_format((float) $product->discount_value, 2, ',', '.'), '0'), ',') }}% OFF
                                    @else
                                        Harga spesial
                                    @endif
                                </span>
                            @endif
                        </div>

                        <div class="p-5">
                            <p class="text-xs font-medium text-slate-500">{{ $product->category?->name }}</p>
                            <h3 class="mt-1 text-base font-semibold text-slate-900">{{ $product->name }}</h3>
                            <div class="mt-3 flex flex-wrap items-baseline gap-x-2 gap-y-1">
                                <span class="font-bold text-slate-950">Rp {{ number_format($product->final_price, 0, ',', '.') }}</span>
                                @if ((float) $product->discount_value > 0)
                                    <span class="text-xs text-slate-400 line-through">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">
                        Produk akan ditampilkan di sini setelah tersedia.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="bg-white px-6 py-16 sm:py-20 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-600">Cerita pelanggan</p>
            <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Testimoni</h2>

            @if ($testimonials->isEmpty())
                <div class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                    <p class="font-medium text-slate-700">Belum ada testimoni untuk ditampilkan.</p>
                    <p class="mt-2 text-sm text-slate-500">Testimoni pelanggan akan muncul di sini.</p>
                </div>
            @else
                <div class="mt-8" data-testimonial-carousel aria-roledescription="carousel" aria-label="Testimoni pelanggan">
                    <div class="overflow-hidden">
                        <div class="flex gap-5 transition-transform duration-500 ease-out motion-reduce:transition-none" data-testimonial-track>
                            @foreach ($testimonials as $testimonial)
                                <figure class="store-testimonial-slide flex min-w-0 flex-col rounded-2xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
                                    <figcaption class="flex items-center gap-3">
                                        @if ($testimonial->photo)
                                            <img src="{{ asset('storage/' . $testimonial->photo) }}" alt="Foto {{ $testimonial->name }}" class="h-11 w-11 rounded-full object-cover">
                                        @else
                                            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-amber-100 text-sm font-semibold text-amber-800" aria-hidden="true">{{ mb_strtoupper(mb_substr($testimonial->name, 0, 1)) }}</span>
                                        @endif
                                        <span class="font-semibold text-slate-900">{{ $testimonial->name }}</span>
                                    </figcaption>
                                    <blockquote class="mt-4 border-t border-slate-200 pt-4 whitespace-pre-line text-sm leading-7 text-slate-600">
                                        <span class="mr-1 text-2xl font-semibold leading-none text-amber-500" aria-hidden="true">“</span>{{ $testimonial->message }}
                                    </blockquote>
                                </figure>
                            @endforeach
                        </div>
                    </div>

                    @if ($testimonials->count() > 1)
                        <div class="mt-6 flex justify-center gap-2" data-testimonial-dots aria-label="Pilih posisi testimoni"></div>
                    @endif
                </div>
            @endif
        </div>
    </section>

@endsection
