@extends('layouts.store')

@section('title', 'Atur Password Baru - Sinergi Store')

@section('content')
    <section class="mx-auto flex min-h-[60vh] max-w-7xl items-center justify-center px-6 py-12 lg:px-8">
        <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Pemulihan akun</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Atur password baru</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Gunakan minimal 8 karakter tanpa spasi. Kombinasi huruf, angka, dan simbol bebas.</p>

            <form method="POST" action="{{ route('account.password.update') }}" class="mt-7 space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                    @error('email')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-slate-700">Password baru</label>
                    <input id="password" name="password" type="password" required minlength="8" pattern="\S+" title="Password minimal 8 karakter dan tidak boleh mengandung spasi." autocomplete="new-password" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                    @error('password')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-medium text-slate-700">Ulangi password baru</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" pattern="\S+" title="Password minimal 8 karakter dan tidak boleh mengandung spasi." autocomplete="new-password" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                </div>
                <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2">Simpan password baru</button>
            </form>
        </div>
    </section>
@endsection
