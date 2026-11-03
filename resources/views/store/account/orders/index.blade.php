@extends('layouts.store')

@section('title', 'Pesanan Saya - Sinergi Store')

@section('content')
    @php
        $statusLabels = [
            'pending_confirmation' => 'Menunggu konfirmasi',
            'awaiting_payment' => 'Menunggu pembayaran',
            'paid' => 'Dibayar',
            'packing' => 'Sedang dikemas',
            'shipped' => 'Dikirim',
            'cancelled' => 'Dibatalkan',
            'expired' => 'Kedaluwarsa',
        ];
        $statusStyles = [
            'pending_confirmation' => 'bg-amber-100 text-amber-900 ring-1 ring-inset ring-amber-300',
            'awaiting_payment' => 'bg-sky-100 text-sky-900 ring-1 ring-inset ring-sky-300',
            'paid' => 'bg-emerald-100 text-emerald-900 ring-1 ring-inset ring-emerald-300',
            'packing' => 'bg-violet-100 text-violet-900 ring-1 ring-inset ring-violet-300',
            'shipped' => 'bg-cyan-100 text-cyan-900 ring-1 ring-inset ring-cyan-300',
            'cancelled' => 'bg-slate-200 text-slate-800 ring-1 ring-inset ring-slate-300',
            'expired' => 'bg-rose-100 text-rose-700 ring-1 ring-inset ring-rose-300',
        ];
    @endphp

    <section class="mx-auto max-w-7xl px-6 py-10 lg:px-8 lg:py-14">
        <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Akun pembeli</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Pesanan Saya</h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">Pantau status dan riwayat pesananmu di sini.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('account.addresses.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-amber-400 hover:text-amber-800">Alamat saya</a>
                <a href="{{ route('account.profile') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-amber-400 hover:text-amber-800">Profil saya</a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('success') }}</div>
        @endif

        @if ($orders->isEmpty())
            <div class="rounded-3xl border border-slate-200 bg-white px-6 py-14 text-center shadow-sm">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-800" aria-hidden="true">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.2 11.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L21 8H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                </div>
                <h2 class="mt-5 text-lg font-semibold text-slate-900">Belum ada pesanan</h2>
                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600">Pesanan yang dibuat saat kamu masuk ke akun akan muncul di sini.</p>
                <a href="{{ route('store.products.index') }}" class="mt-6 inline-flex min-h-11 items-center justify-center rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">Jelajahi produk</a>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($orders as $order)
                    <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-6">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Nomor pesanan</p>
                                <h2 class="mt-1 font-semibold text-slate-900">{{ $order->order_number }}</h2>
                                <p class="mt-1 text-xs text-slate-500">{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
                            </div>
                            <span class="rounded-full px-3 py-1.5 text-xs font-semibold {{ $statusStyles[$order->status] ?? 'bg-slate-100 text-slate-700' }}">{{ $statusLabels[$order->status] ?? $order->status }}</span>
                        </div>
                        <div class="flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div class="min-w-0">
                                <p class="truncate font-medium text-slate-900">{{ $order->items->pluck('product_name')->unique()->join(', ') }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $order->items->sum('quantity') }} unit · {{ $order->items->count() }} jenis produk</p>
                                @if ($order->tracking_number)
                                    <p class="mt-2 text-sm text-slate-600">Resi · <span class="font-semibold text-slate-900">{{ $order->tracking_number }}</span></p>
                                @endif
                            </div>
                            <div class="flex items-center justify-between gap-4 sm:justify-end">
                                <p class="whitespace-nowrap font-semibold text-slate-900">Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</p>
                                <a href="{{ route('account.orders.show', $order) }}" class="inline-flex min-h-10 shrink-0 items-center justify-center rounded-lg border border-amber-400 px-3 py-2 text-sm font-semibold text-amber-800 transition hover:bg-amber-50">Lihat detail</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-6">{{ $orders->links() }}</div>
        @endif
    </section>
@endsection
