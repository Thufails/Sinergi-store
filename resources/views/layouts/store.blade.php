<!doctype html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script>
            (() => {
                const themeStorageKey = 'sinergi-admin-theme';
                const root = document.documentElement;
                const systemPreference = window.matchMedia('(prefers-color-scheme: dark)');
                const readPreference = () => {
                    try {
                        const savedPreference = localStorage.getItem(themeStorageKey);
                        return ['light', 'dark'].includes(savedPreference) ? savedPreference : 'system';
                    } catch {
                        return 'system';
                    }
                };
                const applyPreference = (preference = readPreference()) => {
                    root.dataset.adminThemePreference = preference;
                    root.dataset.adminTheme = preference === 'system'
                        ? (systemPreference.matches ? 'dark' : 'light')
                        : preference;
                };

                window.setAdminThemePreference = (preference) => {
                    if (!['system', 'light', 'dark'].includes(preference)) return;

                    try {
                        if (preference === 'system') {
                            localStorage.removeItem(themeStorageKey);
                        } else {
                            localStorage.setItem(themeStorageKey, preference);
                        }
                    } catch {
                        // Keep the public page usable when browser storage is unavailable.
                    }

                    applyPreference(preference);
                };

                applyPreference();
                systemPreference.addEventListener?.('change', () => {
                    if (readPreference() === 'system') applyPreference('system');
                });
            })();
        </script>
        @php
            $storePageTitle = trim($__env->yieldContent('title')) ?: 'Sinergi Store';
            $storeMetaDescription = trim($__env->yieldContent('meta_description')) ?: 'Temukan merchandise Sinergi Store dan jasa kreatif dari Sinergi Creative.';
            $storeMetaImage = trim($__env->yieldContent('meta_image')) ?: asset('images/logo-sinergi.png');
            $storeMetaImageAlt = trim($__env->yieldContent('meta_image_alt')) ?: 'Logo Sinergi Store';
            $storeTwitterCard = trim($__env->yieldContent('twitter_card')) ?: 'summary';
        @endphp
        <title>{{ $storePageTitle }}</title>
        <meta name="description" content="{{ $storeMetaDescription }}">
        <link rel="canonical" href="{{ url()->current() }}">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Sinergi Store">
        <meta property="og:title" content="{{ $storePageTitle }}">
        <meta property="og:description" content="{{ $storeMetaDescription }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:image" content="{{ $storeMetaImage }}">
        <meta property="og:image:alt" content="{{ $storeMetaImageAlt }}">
        <meta name="twitter:card" content="{{ $storeTwitterCard }}">
        <meta name="twitter:title" content="{{ $storePageTitle }}">
        <meta name="twitter:description" content="{{ $storeMetaDescription }}">
        <meta name="twitter:image" content="{{ $storeMetaImage }}">
        <link rel="icon" type="image/png" href="{{ asset('images/logo-sinergi.png') }}?v=20261015">
        <link rel="apple-touch-icon" href="{{ asset('images/logo-sinergi.png') }}?v=20261015">

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="store-app min-h-screen w-full bg-slate-50 text-slate-900 antialiased">
        <header class="store-header border-b border-slate-200/80 shadow-sm">
            <div class="relative mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
                <a href="{{ route('store.home') }}" class="flex shrink-0 items-center gap-3">
                    <img src="{{ asset('images/logo-sinergi.png') }}" alt="Sinergi Store" class="h-10 w-10 object-contain">
                    <span class="text-sm font-bold uppercase tracking-[0.22em] text-slate-900">Sinergi Store</span>
                </a>

                <nav class="hidden items-center gap-5 text-sm font-medium lg:flex xl:absolute xl:left-1/2 xl:-translate-x-1/2 xl:gap-8" aria-label="Navigasi utama">
                    <a href="{{ route('store.home') }}" class="store-nav-link" data-store-nav="home" @if (request()->routeIs('store.home')) aria-current="page" @endif>Home</a>
                    <a href="{{ route('store.products.index') }}" class="store-nav-link" data-store-nav="products" @if (request()->routeIs('store.products.*')) aria-current="page" @endif>Produk</a>
                    <a href="{{ route('store.home') }}#about" class="store-nav-link" data-store-nav="about">Tentang Kami</a>
                    <a href="{{ route('store.home') }}#services" class="store-nav-link" data-store-nav="services">Jasa</a>
                    <a href="{{ route('store.home') }}#contact" class="store-nav-link" data-store-nav="contact">Kontak</a>
                </nav>

                <details class="store-theme-menu relative">
                    <summary aria-label="Pengaturan tema" class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-xl border border-slate-200 text-slate-700 outline-none transition hover:bg-slate-100 focus-visible:ring-2 focus-visible:ring-amber-400 [&::-webkit-details-marker]:hidden">
                        <svg class="store-theme-chevron h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 3v18a9 9 0 0 0 0-18Z" />
                        </svg>
                        <span class="sr-only">Pilih tema tampilan</span>
                    </summary>
                    <div class="store-theme-dropdown absolute right-0 z-[60] mt-2 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-900/10">
                        <p class="px-3 pb-1 pt-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Tema tampilan</p>
                        <div class="space-y-1" role="group" aria-label="Pilihan tema">
                            <button type="button" data-store-theme-option="system" aria-pressed="false" class="store-theme-option flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm text-slate-700 transition hover:bg-slate-50 focus:bg-slate-50 focus:outline-none">
                                Ikuti perangkat <span data-theme-check class="text-amber-600" aria-hidden="true"></span>
                            </button>
                            <button type="button" data-store-theme-option="light" aria-pressed="false" class="store-theme-option flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm text-slate-700 transition hover:bg-slate-50 focus:bg-slate-50 focus:outline-none">
                                Terang <span data-theme-check class="text-amber-600" aria-hidden="true"></span>
                            </button>
                            <button type="button" data-store-theme-option="dark" aria-pressed="false" class="store-theme-option flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm text-slate-700 transition hover:bg-slate-50 focus:bg-slate-50 focus:outline-none">
                                Gelap <span data-theme-check class="text-amber-600" aria-hidden="true"></span>
                            </button>
                        </div>
                    </div>
                </details>

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
                    <a href="{{ route('store.home') }}#services" class="store-mobile-nav-link rounded-lg px-3 py-2 hover:bg-slate-100" data-store-nav="services">Jasa</a>
                    <a href="{{ route('store.home') }}#contact" class="store-mobile-nav-link rounded-lg px-3 py-2 hover:bg-slate-100" data-store-nav="contact">Kontak</a>
                </nav>
            </div>
        </header>

        <main class="store-main">
            @yield('content')
        </main>

        @php
            $storeLocationName = 'PEUBA Usman Bin Affan';
            $storeLocation = 'Jl Terusan Nusa Indah No.21, Dusun Durek, Giripurno, Kec. Bumiaji, Kota Batu, Jawa Timur 65333, Indonesia';
            $storeLocationQuery = urlencode($storeLocationName . ', ' . $storeLocation);
        @endphp
        <section aria-labelledby="store-cta-title" class="relative isolate overflow-hidden bg-gradient-to-br from-orange-700 via-orange-900 to-slate-950 px-6 py-10 sm:py-12 lg:px-8">
            <div aria-hidden="true" class="pointer-events-none absolute -left-24 -top-36 h-80 w-80 rounded-full border-[36px] border-amber-200/10"></div>
            <div aria-hidden="true" class="pointer-events-none absolute -bottom-48 -right-16 h-[26rem] w-[26rem] rounded-full border-[42px] border-amber-200/10"></div>
            <div aria-hidden="true" class="pointer-events-none absolute right-[18%] top-8 h-24 w-40 rotate-12 rounded-3xl border border-amber-100/15 bg-white/5 backdrop-blur-sm"></div>

            <div class="relative mx-auto grid max-w-7xl items-stretch gap-5 lg:grid-cols-[1.05fr_0.95fr]">
                <div class="flex flex-col items-center justify-center rounded-3xl border border-white/10 bg-slate-950/20 px-6 py-10 text-center shadow-xl shadow-orange-950/10 sm:px-10 sm:py-12">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-200">Sinergi Store &amp; Sinergi Creative</p>
                    <h2 id="store-cta-title" class="mx-auto mt-3 max-w-2xl text-3xl font-semibold tracking-tight text-white sm:text-4xl xl:text-[2.7rem]">
                        Cari merchandise atau butuh jasa kreatif?
                    </h2>
                    <p class="mx-auto mt-4 max-w-xl text-base leading-7 text-orange-50/90">
                        Lihat merchandise di katalog atau hubungi Sinergi Creative untuk membahas foto, video, dan desain produk.
                    </p>
                    <a href="https://wa.me/6282142612382?text=Halo%20Sinergi%20Store%2C%20saya%20ingin%20bertanya%20tentang%20produk%20atau%20jasa." target="_blank" rel="noopener noreferrer" class="mt-7 inline-flex min-h-12 items-center justify-center gap-2 rounded-full bg-white px-7 py-3 text-base font-semibold text-orange-700 shadow-lg shadow-orange-950/10 transition hover:-translate-y-0.5 hover:bg-orange-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-orange-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M20.52 3.48A11.8 11.8 0 0 0 12.1 0C5.55 0 .22 5.32.22 11.87c0 2.1.55 4.15 1.6 5.96L.12 24l6.32-1.66a11.9 11.9 0 0 0 5.66 1.44h.01c6.55 0 11.88-5.33 11.89-11.88a11.8 11.8 0 0 0-3.48-8.42ZM12.1 21.76h-.01a9.9 9.9 0 0 1-5.04-1.38l-.36-.21-3.75.98 1-3.65-.24-.38a9.83 9.83 0 0 1-1.51-5.25c0-5.47 4.45-9.92 9.92-9.92a9.85 9.85 0 0 1 7.02 2.91 9.85 9.85 0 0 1 2.9 7.02c0 5.47-4.45 9.92-9.93 9.92Zm5.45-7.43c-.3-.15-1.77-.87-2.05-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.47-.9-.8-1.5-1.78-1.68-2.08-.17-.3-.02-.46.13-.6.13-.13.3-.35.45-.52.15-.18.2-.3.3-.5.1-.2.05-.38-.03-.53-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.22 3.08c.15.2 2.1 3.2 5.1 4.49.72.31 1.28.5 1.72.64.72.23 1.37.2 1.88.12.58-.09 1.77-.72 2.02-1.42.25-.7.25-1.3.18-1.42-.08-.13-.28-.2-.58-.35Z" />
                        </svg>
                        Hubungi Kami
                    </a>
                </div>

                <section aria-labelledby="store-location-title" class="rounded-3xl border border-white/15 bg-slate-950/80 p-5 text-white shadow-xl shadow-slate-950/20 sm:p-6">
                    <div class="mb-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-300">Kunjungi kami</p>
                        <h2 id="store-location-title" class="mt-1 text-xl font-semibold tracking-tight sm:text-2xl">Lokasi Sinergi Store</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-300">Jl Terusan Nusa Indah No.21, Dusun Durek, Giripurno, Kec. Bumiaji, Kota Batu, Jawa Timur 65333</p>
                    </div>
                    <iframe
                        title="Peta lokasi Sinergi Store di Kota Batu"
                        src="https://maps.google.com/maps?q={{ $storeLocationQuery }}&amp;output=embed"
                        class="h-52 w-full rounded-2xl border border-white/10 bg-slate-800 sm:h-60"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen
                    ></iframe>
                </section>
            </div>
        </section>

        <footer class="w-full bg-slate-950 text-slate-300">
            <div class="mx-auto grid w-full max-w-7xl gap-10 px-6 pb-10 pt-7 md:grid-cols-2 lg:grid-cols-[1.15fr_0.75fr_0.85fr] lg:gap-12 lg:px-8">
                <section id="testimonial-form" class="scroll-mt-28">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-400">Punya pengalaman belanja?</p>
                    <h2 class="mt-2 text-xl font-semibold tracking-tight text-white">Bagikan ceritamu</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-400">Sudah pernah belanja di Sinergi Store? Ceritakan pengalamanmu.</p>
                    <button type="button" aria-haspopup="dialog" aria-controls="testimonial-modal" data-testimonial-modal-open class="mt-4 inline-flex min-h-10 items-center justify-center rounded-lg border border-amber-400/50 px-4 py-2.5 text-sm font-semibold text-amber-300 transition hover:border-amber-300 hover:bg-white/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950">
                        Kirim testimoni
                    </button>

                    <dialog id="testimonial-modal" aria-labelledby="testimonial-modal-title" aria-describedby="testimonial-modal-description" data-testimonial-modal @if ($errors->any() || session('testimonial_success')) data-open-on-load @endif class="m-auto max-h-[calc(100dvh-2rem)] w-[calc(100vw-2rem)] max-w-lg overflow-y-auto rounded-2xl bg-white p-0 text-slate-900 shadow-2xl backdrop:bg-slate-950/70">
                        <div class="flex items-start justify-between gap-5 border-b border-slate-200 px-5 py-5 sm:px-7">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-600">Cerita pelanggan</p>
                                <h3 id="testimonial-modal-title" class="mt-1 text-xl font-semibold tracking-tight text-slate-950">Bagikan testimoni</h3>
                                <p id="testimonial-modal-description" class="mt-2 text-sm leading-6 text-slate-600">Pesanmu akan ditinjau admin sebelum ditampilkan di website.</p>
                            </div>
                            <button type="button" aria-label="Tutup form testimoni" data-testimonial-modal-close class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18" />
                                </svg>
                            </button>
                        </div>

                        <div class="px-5 py-5 sm:px-7">
                            @if (session('testimonial_success'))
                                <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status" aria-live="polite">
                                    {{ session('testimonial_success') }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('store.testimonials.store') }}" class="space-y-4">
                                @csrf

                                <div>
                                    <label for="public-testimonial-name" class="mb-2 block text-sm font-medium text-slate-700">Nama</label>
                                    <input id="public-testimonial-name" name="name" type="text" value="{{ old('name') }}" required maxlength="255" autocomplete="name" placeholder="Nama kamu" @if ($errors->has('name')) aria-invalid="true" aria-describedby="public-testimonial-name-error" @endif class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                                    @error('name')
                                        <p id="public-testimonial-name-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="public-testimonial-message" class="mb-2 block text-sm font-medium text-slate-700">Pesan testimoni</label>
                                    <textarea id="public-testimonial-message" name="message" rows="4" required maxlength="2000" placeholder="Ceritakan pengalamanmu..." @if ($errors->has('message')) aria-invalid="true" aria-describedby="public-testimonial-message-error" @endif class="w-full resize-y rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-200">{{ old('message') }}</textarea>
                                    @error('message')
                                        <p id="public-testimonial-message-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between">
                                    <button type="button" data-testimonial-modal-close class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">
                                        Nanti saja
                                    </button>
                                    <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2">
                                        Kirim testimoni
                                    </button>
                                </div>
                            </form>
                        </div>
                    </dialog>
                </section>

                <div>
                    <p class="font-bold uppercase tracking-[0.2em] text-amber-400">Sinergi Store</p>
                    <p class="mt-3 text-sm leading-6 text-slate-400">Merchandise Sinergi Store dan jasa kreatif dari Sinergi Creative.</p>
                </div>
                <div id="contact" class="md:justify-self-end">
                    <h2 class="font-semibold text-white">Kontak</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-400">
                        Jl. Candi Waringin No. 4, Kota Malang<br>
                        info.sinergicreative@gmail.com<br>
                        +62 821-4261-2382
                    </p>
                </div>
            </div>
            <div class="border-t border-white/10 px-6 py-4 text-center text-xs text-slate-500">&copy; {{ date('Y') }} Sinergi Store.</div>
        </footer>

        <script>
            const storeThemeMenu = document.querySelector('.store-theme-menu');
            const storeThemeOptions = document.querySelectorAll('[data-store-theme-option]');
            const updateStoreThemeOptions = () => {
                const preference = document.documentElement.dataset.adminThemePreference || 'system';

                storeThemeOptions.forEach((option) => {
                    const isSelected = option.dataset.storeThemeOption === preference;
                    option.setAttribute('aria-pressed', String(isSelected));
                    option.querySelector('[data-theme-check]').textContent = isSelected ? '✓' : '';
                });
            };

            updateStoreThemeOptions();

            storeThemeOptions.forEach((option) => {
                option.addEventListener('click', () => {
                    window.setAdminThemePreference?.(option.dataset.storeThemeOption);
                    updateStoreThemeOptions();
                    storeThemeMenu.open = false;
                    storeThemeMenu.querySelector('summary')?.focus();
                });
            });

            document.addEventListener('click', (event) => {
                if (storeThemeMenu?.open && !storeThemeMenu.contains(event.target)) {
                    storeThemeMenu.open = false;
                }
            });

            storeThemeMenu?.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && storeThemeMenu.open) {
                    storeThemeMenu.open = false;
                    storeThemeMenu.querySelector('summary')?.focus();
                }
            });
        </script>

    </body>
</html>
