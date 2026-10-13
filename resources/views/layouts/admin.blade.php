<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Sinergi Store')</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <div class="min-h-screen lg:flex">
        <aside class="w-full border-b border-white/10 bg-slate-950 text-white lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col lg:border-b-0">
            <div class="flex items-center justify-between px-5 py-4 sm:px-6 sm:py-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-amber-400">Sinergi Store</p>
                    <p class="mt-2 text-sm text-slate-400">Admin workspace</p>
                </div>
                <button type="button" id="mobile-menu-button" aria-controls="admin-navigation" aria-expanded="false" class="rounded-lg p-2 text-slate-300 transition hover:bg-white/10 hover:text-white lg:hidden">
                    <span class="sr-only">Buka menu</span>
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
            </div>

            <nav id="admin-navigation" class="max-h-0 -translate-y-2 overflow-hidden space-y-2 px-4 opacity-0 transition-all duration-300 ease-out lg:max-h-none lg:translate-y-0 lg:overflow-visible lg:pb-0 lg:opacity-100">
                <a href="{{ route('admin.dashboard') }}"
                    class="block rounded-xl px-4 py-3 text-sm font-semibold transition
                    {{ request()->routeIs('admin.dashboard')
                            ? 'bg-amber-500 text-slate-950'
                            : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        Dashboard
                </a>
                <a href="{{ route('admin.products.index') }}"
                    class="block rounded-xl px-4 py-3 text-sm transition
                    {{ request()->routeIs('admin.products.*')
                            ? 'bg-amber-500 font-semibold text-slate-950'
                            : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        Produk
                </a>
                <a href="{{ route('admin.categories.index') }}"
                    class="block rounded-xl px-4 py-3 text-sm transition
                    {{ request()->routeIs('admin.categories.*')
                            ? 'bg-amber-500 font-semibold text-slate-950'
                            : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        Kategori
                </a>
            </nav>

            <div class="mt-auto hidden border-t border-white/10 p-4 lg:block">
                <a href="{{ route('store.home') }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="mb-2 block rounded-xl px-4 py-3 text-sm text-slate-400 hover:bg-white/5 hover:text-white">
                    Lihat website
                </a>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full rounded-xl px-4 py-3 text-left text-sm text-slate-400 hover:bg-red-400/10 hover:text-red-300">
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        <main class="w-full lg:ml-64">
            <header class="flex items-center justify-between border-b border-slate-200 bg-white px-6 py-5 sm:px-8">
                <div>
                    <p class="text-sm font-medium text-amber-600">Overview</p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">
                        @yield('heading', 'Dashboard')
                    </h1>
                </div>

                @auth
                    <div class="text-right">
                        <a href="{{ route('admin.profile') }}" class="text-sm font-medium text-slate-700 hover:text-amber-600">
                            {{ auth()->user()->name }}
                        </a>
                        <p class="text-xs text-slate-500">
                            {{ auth()->user()->email }}
                        </p>
                    </div>
                @endauth
            </header>

            <div class="space-y-8 p-6 sm:p-8">
                @yield('content')
            </div>
        </main>
    </div>
</body>
@stack('scripts')
<script>
    const menuButton = document.getElementById('mobile-menu-button');
    const adminNavigation = document.getElementById('admin-navigation');

    menuButton?.addEventListener('click', () => {
        const isOpen = menuButton.getAttribute('aria-expanded') === 'true';
        adminNavigation.classList.toggle('max-h-0', isOpen);
        adminNavigation.classList.toggle('-translate-y-2', isOpen);
        adminNavigation.classList.toggle('opacity-0', isOpen);
        adminNavigation.classList.toggle('max-h-96', !isOpen);
        adminNavigation.classList.toggle('translate-y-0', !isOpen);
        adminNavigation.classList.toggle('opacity-100', !isOpen);
        menuButton.setAttribute('aria-expanded', String(!isOpen));
    });
</script>
</html>
