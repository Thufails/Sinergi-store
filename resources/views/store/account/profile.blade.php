@extends('layouts.store')

@section('title', 'Profil Saya - Sinergi Store')

@section('content')
    <section class="mx-auto max-w-5xl px-6 py-10 lg:px-8 lg:py-14">
        <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Akun pembeli</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Profil Saya</h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">Perbarui informasi kontak dan keamanan akunmu.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('account.addresses.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-amber-400 hover:text-amber-800">Alamat saya</a>
                <a href="{{ route('account.orders') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-amber-400 hover:text-amber-800">Pesanan saya</a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('success') }}</div>
        @endif

        <div class="space-y-5">
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                <h2 class="text-lg font-semibold text-slate-900">Informasi akun</h2>
                <p class="mt-1 text-sm text-slate-500">Email digunakan untuk login dan pemulihan password.</p>
                <form method="POST" action="{{ route('account.profile.update') }}" class="mt-6 grid gap-5 sm:grid-cols-2">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="name" class="mb-2 block text-sm font-medium text-slate-700">Nama</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $customer->name) }}" maxlength="150" required autocomplete="name" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                        @error('name')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                        <input id="email" type="email" value="{{ $customer->email }}" readonly class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
                        <p class="mt-2 text-xs text-slate-500">Perubahan email memerlukan proses verifikasi, jadi belum bisa diubah di sini.</p>
                    </div>
                    <div>
                        <label for="phone" class="mb-2 block text-sm font-medium text-slate-700">Nomor WhatsApp</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone', $customer->phone) }}" maxlength="30" autocomplete="tel" inputmode="tel" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200" placeholder="Contoh: 081234567890">
                        @error('phone')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400 sm:w-auto">Simpan profil</button>
                    </div>
                </form>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                <h2 class="text-lg font-semibold text-slate-900">Ubah password</h2>
                <p class="mt-1 text-sm text-slate-500">Gunakan minimal 8 karakter tanpa spasi. Kombinasi huruf, angka, dan simbol bebas.</p>
                <form method="POST" action="{{ route('account.profile.password.update') }}" class="mt-6 grid gap-5 sm:grid-cols-2">
                    @csrf
                    @method('PUT')
                    <div class="sm:col-span-2">
                        <label for="current_password" class="mb-2 block text-sm font-medium text-slate-700">Password saat ini</label>
                        <input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                        @error('current_password')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
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
                    <div class="sm:col-span-2">
                        <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-amber-400 hover:bg-amber-50 hover:text-slate-900">Perbarui password</button>
                    </div>
                </form>
            </section>

            <form method="POST" action="{{ route('account.logout') }}" class="flex justify-end">
                @csrf
                <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">Keluar dari akun</button>
            </form>
        </div>
    </section>
@endsection
