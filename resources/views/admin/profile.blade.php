@extends('layouts.admin')

@section('title', 'Profil Admin - Sinergi Store')
@section('heading', 'Profil Admin')

@section('content')
    <div class="max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        @if (session('success'))
            <div class="mb-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="mb-2 block text-sm font-medium text-slate-700">Nama</label>
                <input id="name" name="name" type="text" value="{{ old('name', auth()->user()->name) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
            </div>
            <div>
                <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', auth()->user()->email) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
            </div>
            <div class="border-t border-slate-200 pt-5">
                <p class="mb-4 text-sm text-slate-500">Kosongkan password jika tidak ingin mengubahnya.</p>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-slate-700">Password baru</label>
                        <div class="relative"><input id="password" name="password" type="password" class="w-full rounded-xl border border-slate-300 px-4 py-3 pr-12 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200"><button type="button" data-toggle-password="password" aria-label="Tampilkan password" class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 transition hover:text-slate-700"><svg data-eye-icon class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg></button></div>
                    </div>
                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-medium text-slate-700">Konfirmasi password</label>
                        <div class="relative"><input id="password_confirmation" name="password_confirmation" type="password" class="w-full rounded-xl border border-slate-300 px-4 py-3 pr-12 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200"><button type="button" data-toggle-password="password_confirmation" aria-label="Tampilkan password" class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 transition hover:text-slate-700"><svg data-eye-icon class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg></button></div>
                    </div>
                </div>
            </div>
            <button type="submit" class="rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">Simpan perubahan</button>
        </form>
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
