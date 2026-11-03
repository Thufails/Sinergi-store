@extends('layouts.store')

@section('title', 'Daftar Akun - Sinergi Store')

@section('content')
    <section class="mx-auto flex min-h-[60vh] max-w-7xl items-center justify-center px-6 py-12 lg:px-8">
        <div class="w-full max-w-lg rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <a href="{{ route('store.home') }}" class="text-sm font-medium text-slate-500 transition hover:text-amber-700">← Kembali ke toko</a>
            <p class="mt-7 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Akun pembeli</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Daftar / Sign up</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Buat akun agar pesananmu tersimpan dan mudah dipantau.</p>

            <form method="POST" action="{{ route('account.register.submit') }}" class="mt-7 space-y-5">
                @csrf
                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-slate-700">Nama</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" maxlength="150" required autocomplete="name" autofocus class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200" placeholder="Nama lengkap">
                    @error('name')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" maxlength="255" required autocomplete="email" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200" placeholder="nama@email.com">
                    @error('email')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone" class="mb-2 block text-sm font-medium text-slate-700">Nomor WhatsApp <span class="font-normal text-slate-500">(opsional)</span></label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" maxlength="30" autocomplete="tel" inputmode="tel" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200" placeholder="Contoh: 081234567890">
                    @error('phone')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-slate-700">Password</label>
                        <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200" placeholder="Minimal 8 karakter">
                        @error('password')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-medium text-slate-700">Ulangi password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200" placeholder="Ulangi password">
                    </div>
                </div>
                <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2">Buat akun</button>
            </form>

            <p class="mt-6 border-t border-slate-200 pt-5 text-center text-sm text-slate-600">
                Sudah punya akun?
                <a href="{{ route('account.login') }}" class="font-semibold text-amber-700 hover:text-amber-800">Masuk / Sign in</a>
            </p>
        </div>
    </section>
@endsection
