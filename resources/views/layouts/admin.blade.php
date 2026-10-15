<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Sinergi Store')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-sinergi.png') }}?v=20261015">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-sinergi.png') }}?v=20261015">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <div class="min-h-screen lg:flex">
        <aside class="w-full border-b border-white/10 bg-slate-950 text-white lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col lg:border-b-0">
            <div class="flex items-center justify-between border-b border-white/10 px-5 py-4 sm:px-6 sm:py-5 lg:border-b-0">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-900/40 p-1 shadow-lg shadow-amber-500/10"><img src="{{ asset('images/logo-sinergi.png') }}" alt="Logo Sinergi Store" class="h-full w-full object-contain"></span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-amber-400">Sinergi Store</p>
                        <p class="mt-1 text-xs text-slate-400">Admin workspace</p>
                    </div>
                </div>
                <button type="button" id="mobile-menu-button" aria-controls="admin-navigation" aria-expanded="false" class="rounded-lg p-2 text-slate-300 transition hover:bg-white/10 hover:text-white lg:hidden">
                    <span class="sr-only">Buka menu</span>
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
            </div>

            <nav id="admin-navigation" class="max-h-0 -translate-y-2 overflow-hidden px-4 opacity-0 transition-all duration-300 ease-out lg:max-h-none lg:translate-y-0 lg:overflow-visible lg:pb-0 lg:opacity-100">
                <p class="mb-2 px-4 pt-3 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500">Menu utama</p>
                <div class="space-y-2">
                <a href="{{ route('admin.dashboard') }}"
                    class="block rounded-xl px-4 py-3 text-sm font-semibold transition
                    {{ request()->routeIs('admin.dashboard')
                            ? 'bg-amber-500 text-slate-950'
                            : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        Dashboard
                </a>
                <a href="{{ route('admin.orders.index') }}"
                    class="block rounded-xl px-4 py-3 text-sm transition
                    {{ request()->routeIs('admin.orders.*')
                            ? 'bg-amber-500 font-semibold text-slate-950'
                            : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        Pesanan
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
                <a href="{{ route('admin.testimonials.index') }}"
                    class="block rounded-xl px-4 py-3 text-sm transition
                    {{ request()->routeIs('admin.testimonials.*')
                            ? 'bg-amber-500 font-semibold text-slate-950'
                            : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        Testimoni
                </a>
                </div>
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

    @if (session()->has('success') || session()->has('error'))
        @php($toastIsError = session()->has('error'))
        <div data-admin-toast role="{{ $toastIsError ? 'alert' : 'status' }}" aria-live="{{ $toastIsError ? 'assertive' : 'polite' }}" class="fixed right-4 top-4 z-[10001] flex w-[calc(100%-2rem)] max-w-md items-start gap-3 rounded-2xl border bg-white p-4 shadow-xl transition-all duration-300 {{ $toastIsError ? 'border-rose-200' : 'border-emerald-200' }}">
            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $toastIsError ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}" aria-hidden="true">
                @if ($toastIsError)
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm0-11a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 7Zm0 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" /></svg>
                @else
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.78-9.22a.75.75 0 0 0-1.06-1.06L9 11.44 7.28 9.72a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.06 0l4.25-4.25Z" clip-rule="evenodd" /></svg>
                @endif
            </span>
            <p class="flex-1 pt-1 text-sm font-medium {{ $toastIsError ? 'text-rose-800' : 'text-slate-800' }}">{{ session('success') ?? session('error') }}</p>
            <button type="button" data-dismiss-admin-toast aria-label="Tutup notifikasi" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" /></svg>
            </button>
        </div>
    @endif

    <dialog data-admin-confirmation-dialog aria-labelledby="admin-confirmation-title" aria-describedby="admin-confirmation-description" class="m-auto w-[calc(100%-2rem)] max-w-md overflow-hidden rounded-2xl p-0 shadow-2xl backdrop:bg-slate-950/60">
        <div class="bg-white p-6 sm:p-7">
            <div class="flex items-start gap-4">
                <span data-admin-confirmation-icon class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-700" aria-hidden="true">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" /></svg>
                </span>
                <div>
                    <p class="text-sm font-semibold text-amber-700">Konfirmasi tindakan</p>
                    <h2 data-admin-confirmation-title id="admin-confirmation-title" class="mt-1 text-xl font-semibold text-slate-900">Lanjutkan tindakan ini?</h2>
                </div>
            </div>
            <p data-admin-confirmation-description id="admin-confirmation-description" class="mt-5 text-sm leading-6 text-slate-600"></p>
            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" data-admin-confirmation-cancel class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Batal</button>
                <button type="button" data-admin-confirmation-submit class="rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">Ya, lanjutkan</button>
            </div>
        </div>
    </dialog>

    <script>
        (() => {
            const dialog = document.querySelector('[data-admin-confirmation-dialog]');
            const title = dialog?.querySelector('[data-admin-confirmation-title]');
            const description = dialog?.querySelector('[data-admin-confirmation-description]');
            const icon = dialog?.querySelector('[data-admin-confirmation-icon]');
            const submitButton = dialog?.querySelector('[data-admin-confirmation-submit]');
            const cancelButton = dialog?.querySelector('[data-admin-confirmation-cancel]');
            let pendingForm = null;

            const dangerIconClasses = ['bg-amber-100', 'text-amber-700'];
            const dangerButtonClasses = ['bg-amber-500', 'text-slate-950', 'hover:bg-amber-400'];
            const neutralIconClasses = ['bg-rose-100', 'text-rose-700'];
            const neutralButtonClasses = ['bg-rose-600', 'text-white', 'hover:bg-rose-700'];

            document.addEventListener('submit', (event) => {
                const form = event.target;
                if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm-title')) return;

                event.preventDefault();
                pendingForm = form;
                title.textContent = form.dataset.confirmTitle || 'Lanjutkan tindakan ini?';
                description.textContent = form.dataset.confirmDescription || 'Pastikan tindakan ini memang ingin dilanjutkan.';
                submitButton.textContent = form.dataset.confirmButton || 'Ya, lanjutkan';

                const isDestructive = form.dataset.confirmTone === 'danger';
                icon.classList.remove(...(isDestructive ? dangerIconClasses : neutralIconClasses));
                icon.classList.add(...(isDestructive ? neutralIconClasses : dangerIconClasses));
                submitButton.classList.remove(...(isDestructive ? dangerButtonClasses : neutralButtonClasses));
                submitButton.classList.add(...(isDestructive ? neutralButtonClasses : dangerButtonClasses));

                dialog.showModal();
            });

            submitButton?.addEventListener('click', () => {
                if (!pendingForm) return;
                const form = pendingForm;
                pendingForm = null;
                dialog.close();
                form.submit();
            });

            cancelButton?.addEventListener('click', () => {
                pendingForm = null;
                dialog.close();
            });
            dialog?.addEventListener('click', (event) => {
                if (event.target === dialog) {
                    pendingForm = null;
                    dialog.close();
                }
            });
            dialog?.addEventListener('close', () => { pendingForm = null; });
        })();

        const adminToast = document.querySelector('[data-admin-toast]');

        if (adminToast) {
            let toastTimer;
            const dismissToast = () => {
                adminToast.classList.add('translate-y-2', 'opacity-0', 'pointer-events-none');
                window.setTimeout(() => adminToast.remove(), 300);
            };
            const startToastTimer = () => {
                window.clearTimeout(toastTimer);
                toastTimer = window.setTimeout(dismissToast, 4500);
            };

            adminToast.querySelector('[data-dismiss-admin-toast]')?.addEventListener('click', dismissToast);
            adminToast.addEventListener('mouseenter', () => window.clearTimeout(toastTimer));
            adminToast.addEventListener('mouseleave', startToastTimer);
            startToastTimer();
        }
    </script>
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
