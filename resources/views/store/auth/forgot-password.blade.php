@extends('layouts.store')

@section('title', 'Lupa Password - Sinergi Store')

@section('content')
    <section class="mx-auto flex min-h-[60vh] max-w-7xl items-center justify-center px-6 py-12 lg:px-8">
        <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <a href="{{ route('account.login') }}" class="text-sm font-medium text-slate-500 transition hover:text-amber-700">← Kembali ke login</a>
            <p class="mt-7 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Pemulihan akun</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Lupa password?</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Masukkan email akun. Jika terdaftar, tautan untuk membuat password baru akan dikirim ke email tersebut.</p>

            @if (session('status'))
                <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('account.password.email') }}" class="mt-7 space-y-5">
                @csrf
                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" autofocus class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200" placeholder="nama@email.com">
                    @error('email')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2">Kirim tautan reset</button>
            </form>
        </div>
    </section>
@endsection
