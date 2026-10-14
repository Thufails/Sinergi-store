<!doctype html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title', 'Sinergi Store')</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen w-full bg-slate-50 text-slate-900 antialiased">
        <header class="store-header border-b border-slate-200/80 shadow-sm">
            <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
                <a href="{{ route('store.home') }}" class="flex shrink-0 items-center gap-3">
                    <img src="{{ asset('images/logo-sinergi.png') }}" alt="Sinergi Store" class="h-10 w-10 object-contain">
                    <span class="text-sm font-bold uppercase tracking-[0.22em] text-slate-900">Sinergi Store</span>
                </a>

                <nav class="hidden items-center gap-5 text-sm font-medium lg:flex xl:gap-8" aria-label="Navigasi utama">
                    <a href="{{ route('store.home') }}" class="store-nav-link" data-store-nav="home" @if (request()->routeIs('store.home')) aria-current="page" @endif>Home</a>
                    <a href="{{ route('store.products.index') }}" class="store-nav-link" data-store-nav="products" @if (request()->routeIs('store.products.*')) aria-current="page" @endif>Produk</a>
                    <a href="{{ route('store.home') }}#about" class="store-nav-link" data-store-nav="about">Tentang Kami</a>
                    <a href="{{ route('store.home') }}#contact" class="store-nav-link" data-store-nav="contact">Kontak</a>
                </nav>

                <button type="button" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-700 transition hover:bg-slate-100 lg:hidden" aria-label="Buka menu" aria-controls="mobile-menu" aria-expanded="false" data-store-menu-button>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <div id="mobile-menu" class="hidden border-t border-slate-200 bg-white lg:hidden" data-store-menu>
                <nav class="mx-auto grid max-w-7xl gap-1 px-6 py-3 text-sm font-medium text-slate-700" aria-label="Navigasi mobile">
                    <a href="{{ route('store.home') }}" class="store-mobile-nav-link rounded-lg px-3 py-2 hover:bg-slate-100" data-store-nav="home" @if (request()->routeIs('store.home')) aria-current="page" @endif>Home</a>
                    <a href="{{ route('store.products.index') }}" class="store-mobile-nav-link rounded-lg px-3 py-2 hover:bg-slate-100" data-store-nav="products" @if (request()->routeIs('store.products.*')) aria-current="page" @endif>Produk</a>
                    <a href="{{ route('store.home') }}#about" class="store-mobile-nav-link rounded-lg px-3 py-2 hover:bg-slate-100" data-store-nav="about">Tentang Kami</a>
                    <a href="{{ route('store.home') }}#contact" class="store-mobile-nav-link rounded-lg px-3 py-2 hover:bg-slate-100" data-store-nav="contact">Kontak</a>
                </nav>
            </div>
        </header>

        <main class="store-main">
            @yield('content')
        </main>

        <footer class="w-full bg-slate-950 text-slate-300">
            <div class="mx-auto grid w-full max-w-7xl gap-8 px-6 py-12 md:grid-cols-3 lg:px-8">
                <div>
                    <p class="font-bold uppercase tracking-[0.2em] text-amber-400">Sinergi Store</p>
                    <p class="mt-3 text-sm leading-6 text-slate-400">Produk pilihan untuk mendukung kebutuhan dan perkembanganmu.</p>
                </div>
                <div aria-hidden="true" class="hidden md:block"></div>
                <div id="contact" class="md:justify-self-end">
                    <h2 class="font-semibold text-white">Kontak</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-400">
                        Jl. Candi Waringin No. 4, Kota Malang<br>
                        info.sinergicreative@gmail.com<br>
                        +62 821-4261-2382
                    </p>
                    <a href="https://wa.me/6282142612382" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">
                        Hubungi Kami via WhatsApp
                    </a>
                </div>
            </div>
            <div class="border-t border-white/10 px-6 py-4 text-center text-xs text-slate-500">&copy; {{ date('Y') }} Sinergi Store.</div>
        </footer>

    </body>
</html>
