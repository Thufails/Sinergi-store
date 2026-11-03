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
                        <div class="relative">
                            <input id="password" name="password" type="password" required minlength="8" pattern="\S+" title="Password minimal 8 karakter dan tidak boleh mengandung spasi." autocomplete="new-password" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 pr-12 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200" placeholder="Buat password">
                            <button type="button" data-toggle-password="password" aria-label="Tampilkan password" class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 transition hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-amber-500">
                                <svg data-eye-icon class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                            </button>
                        </div>
                        <ul class="mt-2 space-y-1 text-xs leading-5 text-slate-500">
                            <li>• Minimal 8 karakter</li>
                            <li>• Tanpa spasi</li>
                            <li class="whitespace-nowrap">• Huruf, angka, simbol bebas</li>
                        </ul>
                        @error('password')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-medium text-slate-700">Ulangi password</label>
                        <div class="relative">
                            <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" pattern="\S+" title="Password minimal 8 karakter dan tidak boleh mengandung spasi." autocomplete="new-password" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 pr-12 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200" placeholder="Ulangi password">
                            <button type="button" data-toggle-password="password_confirmation" aria-label="Tampilkan password" class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 transition hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-amber-500">
                                <svg data-eye-icon class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                            </button>
                        </div>
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
