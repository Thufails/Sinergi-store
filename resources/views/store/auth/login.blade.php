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
                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" autofocus class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200" placeholder="nama@email.com">
                    @error('email')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-slate-700">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200" placeholder="Masukkan password">
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
                <a href="{{ route('account.register') }}" class="font-semibold text-amber-700 hover:text-amber-800">Daftar / Sign up</a>
            </p>
        </div>
    </section>
@endsection
