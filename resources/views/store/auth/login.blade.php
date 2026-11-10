@extends('layouts.store')

@section('title', 'Masuk - Sinergi Store')

@section('content')
    <section class="mx-auto flex min-h-[60vh] max-w-7xl items-center justify-center px-6 py-12 lg:px-8">
        <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <a href="{{ route('store.home') }}" class="text-sm font-medium text-slate-500 transition hover:text-amber-700">← Kembali ke toko</a>
            <p class="mt-7 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Akun pembeli</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Masuk / Sign in</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Masuk untuk melihat pesanan dan mengelola profilmu.</p>

            @if (session('status'))
                <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('status') }}</div>
            @endif
            @if (session('success'))
                <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('account.login.submit') }}" class="mt-7 space-y-5">
                @csrf
                @if (old('redirect_to', $redirectTo))
                    <input type="hidden" name="redirect_to" value="{{ old('redirect_to', $redirectTo) }}">
                @endif
                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" autofocus class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200" placeholder="nama@email.com">
                    @error('email')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-slate-700">Password</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" required autocomplete="current-password" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 pr-12 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200" placeholder="Masukkan password">
                        <button type="button" data-toggle-password="password" aria-label="Tampilkan password" class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 transition hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-amber-500">
                            <svg data-eye-icon class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                        </button>
                    </div>
                    @error('password')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
                    <label class="inline-flex items-center gap-2 text-slate-600"><input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-amber-600 focus:ring-amber-500"> Ingat saya</label>
                    <a href="{{ route('account.password.request') }}" class="font-medium text-amber-700 hover:text-amber-800">Lupa password?</a>
                </div>
                <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2">Masuk</button>
            </form>

            <p class="mt-6 border-t border-slate-200 pt-5 text-center text-sm text-slate-600">
                Belum punya akun?
                <a href="{{ route('account.register', $redirectTo ? ['redirect_to' => $redirectTo] : []) }}" class="font-semibold text-amber-700 hover:text-amber-800">Daftar / Sign up</a>
            </p>
        </div>
    </section>
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
@endsection
