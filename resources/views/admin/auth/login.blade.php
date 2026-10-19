<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Sinergi Store</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-sinergi.png') }}?v=20261015">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-sinergi.png') }}?v=20261015">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-stone-950 text-slate-900 antialiased">
    <main class="grid min-h-screen lg:grid-cols-2">
        <section class="relative hidden overflow-hidden bg-amber-500 p-12 lg:flex lg:flex-col lg:justify-between">
            <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-amber-300/50"></div>
            <div class="absolute -bottom-32 -left-24 h-96 w-96 rounded-full bg-amber-600/30"></div>
            <div class="relative">
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/90 p-2 shadow-lg shadow-amber-950/10">
                        <img src="{{ asset('images/logo-sinergi.png') }}" alt="" class="h-full w-full object-contain">
                    </span>
                    <div>
                        <p class="text-sm font-bold uppercase tracking-[0.25em] text-amber-950">Sinergi Store</p>
                        <p class="mt-1 text-xs font-medium text-amber-950/70">Admin workspace</p>
                    </div>
                </div>
                <h1 class="mt-14 max-w-lg text-5xl font-semibold leading-tight tracking-tight text-stone-950">Kelola katalog dengan lebih sederhana.</h1>
                <p class="mt-6 max-w-md text-lg leading-8 text-amber-950/80">Atur produk, kategori, dan konten toko dari satu ruang kerja yang rapi.</p>
            </div>
            <p class="relative text-sm text-amber-950/70">Admin workspace · Sinergi Store</p>
        </section>

        <section class="relative isolate flex items-center justify-center overflow-hidden bg-stone-950 px-6 py-12 sm:px-10">
            <div class="pointer-events-none absolute -right-24 -top-28 h-80 w-80 rounded-full bg-amber-500/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-40 -left-24 h-96 w-96 rounded-full bg-orange-700/10 blur-3xl"></div>
            <div class="relative w-full max-w-md rounded-3xl border border-white/10 bg-white/[0.035] p-6 shadow-2xl shadow-black/20 sm:p-9">
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/90 p-1.5">
                        <img src="{{ asset('images/logo-sinergi.png') }}" alt="" class="h-full w-full object-contain">
                    </span>
                    <div>
                        <p class="text-sm font-bold uppercase tracking-[0.2em] text-white">Sinergi Store</p>
                        <p class="mt-1 text-xs text-slate-400">Admin workspace</p>
                    </div>
                </div>
                <div class="mb-8">
                    <p class="text-sm font-semibold text-amber-400">Admin workspace</p>
                    <h2 class="mt-2 text-3xl font-semibold tracking-tight text-white">Selamat datang kembali</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-400">Masuk untuk mengelola katalog Sinergi Store.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-5 rounded-xl border border-red-400/30 bg-red-400/10 px-4 py-3 text-sm text-red-200">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-5" autocomplete="off">
        @csrf

        <div>
            <label for="email" class="mb-2 block text-sm font-medium text-slate-200">Email address</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                autocomplete="username"
                class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20"
                required
            >
        </div>

        <div>
            <label for="password" class="mb-2 block text-sm font-medium text-slate-200">Password</label>
            <div class="relative">
                <input id="password" type="password" name="password" autocomplete="current-password" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 pr-12 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20" required>
                <button type="button" data-toggle-password="password" aria-label="Tampilkan password" class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 transition hover:text-slate-200">
                    <svg data-eye-icon class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                </button>
            </div>
        </div>

        <button type="submit" class="w-full rounded-xl bg-amber-500 px-4 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-300 focus:ring-offset-2 focus:ring-offset-stone-950">Masuk ke dashboard</button>
    </form>
                <p class="mt-8 text-center text-xs text-slate-500">Gunakan akun admin yang terdaftar di database.</p>
            </div>
        </section>
    </main>
</body>
<script>
    document.querySelectorAll('[data-toggle-password]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.togglePassword);
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            button.setAttribute('aria-label', visible ? 'Tampilkan password' : 'Sembunyikan password');
            button.querySelector('[data-eye-icon]').innerHTML = visible
                ? '<path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/>'
                : '<path d="m3 3 18 18"/><path d="M10.6 6.2A10.7 10.7 0 0 1 12 6c6 0 9.5 6 9.5 6a17.5 17.5 0 0 1-3.2 3.8M6.2 6.8C3.8 8.2 2.5 12 2.5 12a17.5 17.5 0 0 0 5.4 4.8A10.7 10.7 0 0 0 12 18c.5 0 1-.1 1.4-.1"/>';
        });
    });
</script>
</html>
