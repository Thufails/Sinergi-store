@extends('layouts.admin')

@section('title', 'Dashboard Admin - Sinergi Store')
@section('heading', 'Dashboard')

@section('content')
    <section class="rounded-2xl bg-amber-500 p-6 shadow-sm sm:p-8">
        <p class="text-sm font-semibold text-amber-950">
            Selamat datang kembali
        </p>

        <h2 class="mt-2 max-w-2xl text-3xl font-semibold tracking-tight text-slate-950">
            Siapkan katalog Sinergi Store dari sini.
        </h2>

        <p class="mt-3 max-w-xl text-sm leading-6 text-amber-950/75">
            Kelola produk dan kategori dengan rapi. Modul katalog akan kita tambahkan pada langkah berikutnya.
        </p>
    </section>

    <section class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total produk</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $totalProducts }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ $totalProducts ? 'Produk tersimpan' : 'Belum ada data' }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Kategori</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $totalCategories }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ $totalCategories ? 'Kategori tersimpan' : 'Belum ada data' }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Produk aktif</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $activeProducts }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ $activeProducts ? 'Produk siap ditampilkan' : 'Belum ada data' }}</p>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Aksi cepat</h2>
        <p class="mt-1 text-sm text-slate-500">
            Menu katalog akan aktif setelah modul CRUD dibuat.
        </p>
    </section>
@endsection
