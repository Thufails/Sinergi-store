@extends('layouts.store')

@section('title', 'Sinergi Store - Home')
@section('meta_description', 'Belanja merchandise Sinergi Store dan lihat jasa foto produk, video, serta desain dari Sinergi Creative.')
@section('meta_image', asset('images/logo-sinergi.png'))
@section('meta_image_alt', 'Logo Sinergi Store')

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
                    Sinergi Store menyediakan merchandise dari Sinergi Grup. Kami juga menerima pesanan jasa kreatif—mulai dari foto produk dan video promosi hingga desain. Hubungi tim kami untuk membahas kebutuhanmu.
                </p>
            </div>
        </div>
    </section>

    @php
        $serviceWhatsapp = '6282142612382';
    @endphp
    <section id="services" class="scroll-mt-20 bg-amber-50/70 px-6 py-14 sm:py-18 lg:px-8" aria-labelledby="services-heading">
        <div class="mx-auto max-w-7xl">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-700">Jasa Sinergi Creative</p>
                <h2 id="services-heading" class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Butuh foto, video, atau desain produk?</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600 sm:text-base">Pilih jasa yang kamu perlukan, lalu hubungi Sinergi Creative lewat WhatsApp untuk membahas detailnya.</p>
            </div>

            <div class="mt-8 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                <article class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-amber-400 hover:shadow-md hover:ring-2 hover:ring-amber-400/30 focus-within:border-amber-400 focus-within:ring-2 focus-within:ring-amber-400/30 sm:p-7">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-700" aria-hidden="true">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h3l1.5-2h7L17 7h3a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1Z"/><circle cx="12" cy="13" r="3.5"/></svg>
                    </span>
                    <h3 class="mt-5 text-lg font-semibold text-slate-950">Foto Produk</h3>
                    <p class="mt-2 flex-1 text-sm leading-6 text-slate-600">Foto produk untuk katalog atau promosi. Ceritakan kebutuhan pemotretanmu saat menghubungi kami.</p>
                    <a href="https://wa.me/{{ $serviceWhatsapp }}?text={{ urlencode('Halo Sinergi Creative, saya tertarik dengan jasa Foto Produk. Boleh minta informasi paket, harga, dan proses pengerjaannya?') }}" target="_blank" rel="noopener noreferrer" class="mt-6 inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-900 transition hover:bg-amber-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2">
                        Konsultasi via WhatsApp <span aria-hidden="true">→</span>
                    </a>
                </article>

                <article class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-amber-400 hover:shadow-md hover:ring-2 hover:ring-amber-400/30 focus-within:border-amber-400 focus-within:ring-2 focus-within:ring-amber-400/30 sm:p-7">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-700" aria-hidden="true">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="13" height="14" rx="2"/><path d="m16 10 5-3v10l-5-3"/><path d="m9 9 4 3-4 3z"/></svg>
                    </span>
                    <h3 class="mt-5 text-lg font-semibold text-slate-950">Video Produk</h3>
                    <p class="mt-2 flex-1 text-sm leading-6 text-slate-600">Video untuk memperkenalkan produk atau menunjukkan cara penggunaannya. Sampaikan tujuan videomu kepada tim kami.</p>
                    <a href="https://wa.me/{{ $serviceWhatsapp }}?text={{ urlencode('Halo Sinergi Creative, saya tertarik dengan jasa Video Produk. Boleh minta informasi paket, harga, dan proses pengerjaannya?') }}" target="_blank" rel="noopener noreferrer" class="mt-6 inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-900 transition hover:bg-amber-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2">
                        Konsultasi via WhatsApp <span aria-hidden="true">→</span>
                    </a>
                </article>

                <article class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-amber-400 hover:shadow-md hover:ring-2 hover:ring-amber-400/30 focus-within:border-amber-400 focus-within:ring-2 focus-within:ring-amber-400/30 sm:p-7 md:col-span-2 lg:col-span-1">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-700" aria-hidden="true">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m14.5 6.5 3 3M4 20l4.5-1 10.8-10.8a2.1 2.1 0 0 0-3-3L5.5 16 4 20Z"/><path d="M13 8 16 11"/></svg>
                    </span>
                    <h3 class="mt-5 text-lg font-semibold text-slate-950">Desain</h3>
                    <p class="mt-2 flex-1 text-sm leading-6 text-slate-600">Desain produk dan materi promosi sesuai kebutuhan bisnismu. Kirim gambaran kebutuhanmu lewat WhatsApp.</p>
                    <a href="https://wa.me/{{ $serviceWhatsapp }}?text={{ urlencode('Halo Sinergi Creative, saya tertarik dengan jasa Desain. Boleh minta informasi paket, harga, dan proses pengerjaannya?') }}" target="_blank" rel="noopener noreferrer" class="mt-6 inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-900 transition hover:bg-amber-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2">
                        Konsultasi via WhatsApp <span aria-hidden="true">→</span>
                    </a>
                </article>
            </div>
        </div>
    </section>

    <section class="bg-slate-50 px-6 py-12 sm:py-14 lg:px-8">
        <div class="mx-auto {{ $featuredProducts->count() === 1 ? 'max-w-2xl' : ($featuredProducts->count() === 2 ? 'max-w-4xl' : 'max-w-7xl') }}">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-600">Jelajahi katalog</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">Produk-produk Sinergi Store</h2>
                    <p class="mt-2 text-sm text-slate-500">Pilih produk yang ingin kamu pesan.</p>
                </div>

                <a href="{{ route('store.products.index') }}" class="inline-flex w-fit items-center text-sm font-semibold text-amber-700 transition hover:text-amber-800">
                    Lihat semua produk <span class="ml-2" aria-hidden="true">→</span>
                </a>
            </div>

            <div class="mt-6 grid gap-4 sm:gap-5 {{ $featuredProducts->count() === 1 ? 'grid-cols-1' : 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3' }}">
                @forelse ($featuredProducts->take(6) as $product)
                    <a href="{{ route('store.products.show', $product->slug) }}" class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-md">
                        <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
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

    <section id="cara-pemesanan" class="scroll-mt-24 bg-white px-6 py-14 sm:py-16 lg:px-8" aria-labelledby="order-steps-heading">
        <div class="mx-auto max-w-7xl">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-600">Mudah dan dibantu admin</p>
                <h2 id="order-steps-heading" class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Cara Pemesanan</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600 sm:text-base">Pesan produk dalam beberapa langkah, lalu lanjutkan konfirmasi langsung melalui WhatsApp.</p>
            </div>

            <ol class="mt-9 grid gap-4 md:grid-cols-3">
                <li class="rounded-2xl border border-slate-200 bg-slate-50 p-6 sm:p-7">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-sm font-bold text-amber-800">01</span>
                    <h3 class="mt-5 text-lg font-semibold text-slate-950">Pilih produk</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Buka produk yang kamu inginkan, lalu pilih varian dan jumlah jika tersedia.</p>
                </li>
                <li class="rounded-2xl border border-slate-200 bg-slate-50 p-6 sm:p-7">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-sm font-bold text-amber-800">02</span>
                    <h3 class="mt-5 text-lg font-semibold text-slate-950">Isi data pesanan</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Masukkan nama dan nomor WhatsApp. Pesanan dicatat, lalu WhatsApp terbuka dengan ringkasan pesanan untuk kamu kirim.</p>
                </li>
                <li class="rounded-2xl border border-slate-200 bg-slate-50 p-6 sm:p-7">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-sm font-bold text-amber-800">03</span>
                    <h3 class="mt-5 text-lg font-semibold text-slate-950">Konfirmasi dengan admin</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Admin mengecek pesanan dan mengonfirmasi ongkir serta pembayaran. Setelah disetujui, stok di-hold 24 jam; stok fisik dikurangi setelah pembayaran dikonfirmasi.</p>
                </li>
            </ol>
        </div>
    </section>

    <section class="bg-slate-50 px-6 py-14 sm:py-16 lg:px-8" aria-labelledby="order-faq-heading">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[0.7fr_1.3fr] lg:gap-16">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-600">Info singkat</p>
                <h2 id="order-faq-heading" class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Pertanyaan Umum</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">Hal-hal yang sering ditanyakan sebelum memesan produk.</p>
            </div>

            <div class="space-y-3">
                <details class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2">
                        Apakah pesanan langsung dikonfirmasi setelah form dikirim?
                        <svg class="h-5 w-5 shrink-0 text-amber-700 transition group-open:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                    </summary>
                    <p class="mt-3 pr-8 text-sm leading-6 text-slate-600">Belum. Pesanan akan berstatus menunggu konfirmasi admin. Kirim pesan WhatsApp yang sudah disiapkan agar admin dapat memeriksa ketersediaan produk.</p>
                </details>

                <details class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2">
                        Kapan stok di-hold dan kapan stok fisik berkurang?
                        <svg class="h-5 w-5 shrink-0 text-amber-700 transition group-open:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                    </summary>
                    <p class="mt-3 pr-8 text-sm leading-6 text-slate-600">Hold dimulai setelah admin menyetujui pesanan dan berlaku 24 jam. Stok fisik baru dikurangi setelah admin mengonfirmasi pembayaran diterima.</p>
                </details>

                <details class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2">
                        Apakah ongkir sudah termasuk dalam total pesanan?
                        <svg class="h-5 w-5 shrink-0 text-amber-700 transition group-open:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                    </summary>
                    <p class="mt-3 pr-8 text-sm leading-6 text-slate-600">Belum. Total yang tampil adalah subtotal produk. Admin akan mengonfirmasi ongkir dan total pembayaran melalui WhatsApp.</p>
                </details>

                <details class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2">
                        Apakah saya perlu membuat akun untuk memesan?
                        <svg class="h-5 w-5 shrink-0 text-amber-700 transition group-open:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                    </summary>
                    <p class="mt-3 pr-8 text-sm leading-6 text-slate-600">Tidak perlu. Cukup isi nama dan nomor WhatsApp pada form pesanan produk, lalu lanjutkan konfirmasi dengan admin.</p>
                </details>
            </div>
        </div>
    </section>

    <section id="testimonials" class="scroll-mt-24 bg-white px-6 py-16 sm:py-20 lg:px-8">
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

    <div class="fixed bottom-5 right-5 z-40 flex flex-col items-end sm:bottom-6 sm:right-6" data-faq-bot>
        <section
            id="faq-bot-panel"
            class="mb-3 hidden w-[calc(100vw-2.5rem)] max-w-sm overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-2xl shadow-slate-950/20"
            aria-label="Bantuan Sinergi Store"
            aria-hidden="true"
        >
            <header class="flex items-start justify-between gap-4 border-b border-slate-200 bg-slate-50 px-5 py-4">
                <div>
                    <p class="font-semibold text-slate-900">Bantuan Sinergi Store</p>
                    <p class="mt-1 text-xs text-slate-500">Jawaban cepat untuk pertanyaan umum</p>
                </div>
                <button
                    type="button"
                    class="rounded-lg p-1 text-slate-500 transition hover:bg-slate-200 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500"
                    aria-label="Tutup chat bantuan"
                    data-faq-bot-close
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" />
                    </svg>
                </button>
            </header>

            <div class="flex h-[min(55vh,24rem)] flex-col">
                <div class="flex-1 space-y-3 overflow-y-auto px-4 py-4" data-faq-bot-messages aria-live="polite" aria-relevant="additions text">
                    <div class="max-w-[90%] rounded-2xl rounded-tl-sm bg-slate-100 px-4 py-3 text-sm leading-6 text-slate-700">
                        Halo! Ada yang ingin kamu tanyakan tentang produk atau pesanan?
                    </div>
                    <div class="flex flex-wrap gap-2" data-faq-bot-suggestions>
                        <button type="button" class="rounded-full border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-amber-500 hover:text-amber-700" data-faq-prompt="Cara memesan produk">Cara memesan</button>
                        <button type="button" class="rounded-full border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-amber-500 hover:text-amber-700" data-faq-prompt="Bagaimana ongkir dihitung?">Ongkir</button>
                        <button type="button" class="rounded-full border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-amber-500 hover:text-amber-700" data-faq-prompt="Cara cek status pesanan">Status pesanan</button>
                    </div>
                </div>

                <form class="border-t border-slate-200 p-3" data-faq-bot-form>
                    @csrf
                    <label class="sr-only" for="faq-bot-message">Tulis pertanyaan</label>
                    <div class="flex items-end gap-2">
                        <textarea
                            id="faq-bot-message"
                            name="message"
                            rows="1"
                            maxlength="500"
                            required
                            placeholder="Tulis pertanyaan..."
                            class="max-h-28 min-h-11 flex-1 resize-y rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20"
                            data-faq-bot-input
                        ></textarea>
                        <button
                            type="submit"
                            class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-slate-950 transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-60"
                            aria-label="Kirim pertanyaan"
                            data-faq-bot-submit
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m22 2-7 20-4-9-9-4 20-7ZM22 2 11 13" />
                            </svg>
                        </button>
                    </div>
                    <p class="mt-2 px-1 text-[11px] text-slate-500">Jawaban otomatis memakai FAQ. Pesan tidak disimpan.</p>
                </form>
            </div>
        </section>

        <button
            type="button"
            class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-500 text-slate-950 shadow-lg shadow-amber-950/20 transition hover:-translate-y-0.5 hover:bg-amber-400 focus:outline-none focus:ring-4 focus:ring-amber-500/30"
            aria-label="Buka chat bantuan"
            aria-controls="faq-bot-panel"
            aria-expanded="false"
            data-faq-bot-toggle
        >
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.5 11.5a8.5 8.5 0 0 1-12.7 7.4L3 20l1.2-4.3A8.5 8.5 0 1 1 20.5 11.5Z" />
            <circle cx="8.5" cy="11.5" r=".8" fill="currentColor" stroke="none" />
            <circle cx="12" cy="11.5" r=".8" fill="currentColor" stroke="none" />
            <circle cx="15.5" cy="11.5" r=".8" fill="currentColor" stroke="none" />
            </svg>
        </button>
    </div>

@endsection

@push('scripts')
    <script>
        (() => {
            const widget = document.querySelector('[data-faq-bot]');
            if (!widget) return;

            const panel = widget.querySelector('#faq-bot-panel');
            const toggle = widget.querySelector('[data-faq-bot-toggle]');
            const closeButton = widget.querySelector('[data-faq-bot-close]');
            const form = widget.querySelector('[data-faq-bot-form]');
            const input = widget.querySelector('[data-faq-bot-input]');
            const submitButton = widget.querySelector('[data-faq-bot-submit]');
            const messages = widget.querySelector('[data-faq-bot-messages]');
            const suggestions = widget.querySelector('[data-faq-bot-suggestions]');
            const csrfToken = form.querySelector('input[name="_token"]').value;
            const endpoint = @json(route('store.faq-bot.message'));

            const setPanelOpen = (isOpen) => {
                panel.classList.toggle('hidden', !isOpen);
                panel.setAttribute('aria-hidden', String(!isOpen));
                toggle.setAttribute('aria-expanded', String(isOpen));
                toggle.setAttribute('aria-label', isOpen ? 'Tutup chat bantuan' : 'Buka chat bantuan');

                if (isOpen) input.focus();
                else toggle.focus();
            };

            const appendMessage = (text, isCustomer = false) => {
                const bubble = document.createElement('div');
                bubble.className = isCustomer
                    ? 'ml-auto max-w-[90%] rounded-2xl rounded-tr-sm bg-amber-500 px-4 py-3 text-sm leading-6 text-slate-950'
                    : 'max-w-[90%] rounded-2xl rounded-tl-sm bg-slate-100 px-4 py-3 text-sm leading-6 text-slate-700';
                bubble.textContent = text;
                messages.append(bubble);
                messages.scrollTop = messages.scrollHeight;
                return bubble;
            };

            const appendContactLink = (url) => {
                let parsedUrl;
                try {
                    parsedUrl = new URL(url);
                } catch {
                    return;
                }

                if (parsedUrl.protocol !== 'https:' || parsedUrl.hostname !== 'wa.me') return;

                const link = document.createElement('a');
                link.href = parsedUrl.href;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.className = 'inline-flex rounded-full border border-amber-500 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-50';
                link.textContent = 'Hubungi admin via WhatsApp';
                messages.append(link);
                messages.scrollTop = messages.scrollHeight;
            };

            const sendMessage = async (message) => {
                const cleanMessage = message.trim();
                if (!cleanMessage || submitButton.disabled) return;

                if (suggestions) suggestions.remove();
                appendMessage(cleanMessage, true);
                input.value = '';
                submitButton.disabled = true;

                const waitingMessage = appendMessage('Sebentar, aku carikan jawabannya...');

                try {
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ message: cleanMessage }),
                    });
                    const result = await response.json();
                    waitingMessage.remove();

                    if (!response.ok) {
                        const errorMessage = response.status === 429
                            ? 'Terlalu banyak pertanyaan dalam waktu singkat. Coba lagi sebentar, ya.'
                            : (result.message || 'Pertanyaan belum bisa diproses. Coba lagi, ya.');
                        appendMessage(errorMessage);
                        return;
                    }

                    appendMessage(result.reply || 'Maaf, jawaban belum tersedia.');
                    if (result.contact_url) appendContactLink(result.contact_url);
                } catch {
                    waitingMessage.remove();
                    appendMessage('Koneksi sedang bermasalah. Coba kirim pertanyaan lagi atau hubungi admin melalui WhatsApp.');
                } finally {
                    submitButton.disabled = false;
                    input.focus();
                }
            };

            toggle.addEventListener('click', () => {
                setPanelOpen(toggle.getAttribute('aria-expanded') !== 'true');
            });
            closeButton.addEventListener('click', () => setPanelOpen(false));
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                sendMessage(input.value);
            });
            widget.querySelectorAll('[data-faq-prompt]').forEach((button) => {
                button.addEventListener('click', () => sendMessage(button.dataset.faqPrompt || ''));
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
                    setPanelOpen(false);
                }
            });
        })();
    </script>
@endpush
