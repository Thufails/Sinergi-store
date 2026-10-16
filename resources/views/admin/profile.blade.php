@extends('layouts.admin')

@section('title', 'Profil Admin - Sinergi Store')
@section('heading', 'Profil Admin')

@section('content')
    @php
        $admin = auth()->user();
        $initials = collect(explode(' ', trim($admin->name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    @endphp

    <div class="max-w-3xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-200 bg-gradient-to-r from-amber-50 via-white to-slate-50 px-6 py-6 sm:flex-row sm:items-center sm:px-8">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-slate-950 text-xl font-bold tracking-wide text-amber-400 shadow-sm ring-4 ring-white">
                {{ $initials }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Akun administrator</p>
                <h2 class="mt-1 truncate text-xl font-semibold text-slate-900">{{ $admin->name }}</h2>
                <p class="mt-1 truncate text-sm text-slate-500">{{ $admin->email }}</p>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Admin aktif
            </span>
        </div>

        <div class="p-6 sm:p-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-7">
                @csrf
                @method('PUT')

                <section aria-labelledby="profile-information-heading" class="space-y-4">
                    <div>
                        <h3 id="profile-information-heading" class="text-base font-semibold text-slate-900">Informasi akun</h3>
                        <p class="mt-1 text-sm text-slate-500">Perbarui nama dan email yang digunakan untuk akun admin.</p>
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="name" class="mb-2 block text-sm font-medium text-slate-700">Nama</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $admin->name) }}" required autocomplete="name" class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                        </div>
                        <div>
                            <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $admin->email) }}" required autocomplete="email" class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                        </div>
                    </div>
                </section>

                <section aria-labelledby="profile-security-heading" class="space-y-4 border-t border-slate-200 pt-6">
                    <div>
                        <h3 id="profile-security-heading" class="text-base font-semibold text-slate-900">Keamanan</h3>
                        <p class="mt-1 text-sm text-slate-500">Kosongkan kedua kolom jika tidak ingin mengubah password.</p>
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="password" class="mb-2 block text-sm font-medium text-slate-700">Password baru</label>
                            <div class="relative"><input id="password" name="password" type="password" autocomplete="new-password" class="w-full rounded-xl border border-slate-300 px-4 py-3 pr-12 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200"><button type="button" data-toggle-password="password" aria-label="Tampilkan password" class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 transition hover:text-slate-700"><svg data-eye-icon class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg></button></div>
                        </div>
                        <div>
                            <label for="password_confirmation" class="mb-2 block text-sm font-medium text-slate-700">Konfirmasi password</label>
                            <div class="relative"><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="w-full rounded-xl border border-slate-300 px-4 py-3 pr-12 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200"><button type="button" data-toggle-password="password_confirmation" aria-label="Tampilkan password" class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 transition hover:text-slate-700"><svg data-eye-icon class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg></button></div>
                        </div>
                    </div>
                </section>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-slate-500">Pastikan email akun tetap bisa kamu akses.</p>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-slate-950 shadow-sm transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-300 focus:ring-offset-2">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                        Simpan perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
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
@endpush
