@extends('layouts.store')

@section('title', 'Detail Pesanan ' . $order->order_number . ' - Sinergi Store')

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
        <a href="{{ route('account.orders') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 transition hover:text-amber-700">← Kembali ke pesanan saya</a>

        <div class="mt-5 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 px-5 py-5 sm:px-7">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Nomor pesanan</p>
                    <h1 class="mt-1 text-xl font-semibold text-slate-950 sm:text-2xl">{{ $order->order_number }}</h1>
                    <p class="mt-2 text-sm text-slate-500">Dibuat {{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
                </div>
                <span class="rounded-full px-3 py-1.5 text-sm font-semibold {{ $statusStyles[$order->status] ?? 'bg-slate-100 text-slate-700' }}">{{ $statusLabels[$order->status] ?? $order->status }}</span>
            </div>

            <div class="grid gap-5 border-b border-slate-200 p-5 sm:grid-cols-2 sm:p-7">
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Informasi pembeli</p>
                    <p class="mt-3 font-medium text-slate-900">{{ $order->customer_name }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ $order->customer_phone }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Informasi pengiriman</p>
                    @if ($order->courier_name || $order->tracking_number)
                        <p class="mt-3 font-medium text-slate-900">{{ $order->courier_name ?: 'Kurir belum diisi' }}</p>
                        <p class="mt-1 break-all text-sm text-slate-600">Resi · {{ $order->tracking_number ?: 'Belum tersedia' }}</p>
                        @if ($order->shipped_at)
                            <p class="mt-2 text-xs text-slate-500">Dikirim {{ $order->shipped_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
                        @endif
                    @else
                        <p class="mt-3 text-sm text-slate-600">Informasi pengiriman akan muncul setelah pesanan dikirim.</p>
                    @endif
                </div>
                @if ($order->payment_due_at)
                    <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4 sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-amber-800">Batas pembayaran</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $order->payment_due_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
                        @if ($order->status === 'awaiting_payment')
                            <p class="mt-1 text-sm text-slate-600">Selesaikan pembayaran sebelum batas waktu agar pesanan tidak kedaluwarsa.</p>
                        @elseif ($order->status === 'expired')
                            <p class="mt-1 text-sm text-slate-600">Batas pembayaran telah terlewati dan pesanan kedaluwarsa.</p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-semibold sm:px-7">Produk</th>
                            <th class="px-5 py-3 font-semibold">Varian</th>
                            <th class="px-5 py-3 text-right font-semibold">Jumlah</th>
                            <th class="px-5 py-3 text-right font-semibold sm:px-7">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="px-5 py-4 font-medium text-slate-900 sm:px-7">{{ $item->product_name }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $item->variant_name ?: 'Tanpa varian' }}</td>
                                <td class="px-5 py-4 text-right text-slate-600">{{ $item->quantity }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-right font-medium text-slate-900 sm:px-7">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50">
                        <tr>
                            <th colspan="3" class="px-5 py-4 text-right font-semibold text-slate-700 sm:px-7">Total pesanan</th>
                            <td class="whitespace-nowrap px-5 py-4 text-right font-bold text-slate-950 sm:px-7">Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="border-t border-slate-200 px-5 py-5 sm:px-7">
                <h2 class="font-semibold text-slate-900">Riwayat status</h2>
                <ol class="mt-4 space-y-4">
                    @forelse ($order->statusHistories as $history)
                        <li class="flex gap-3">
                            <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-amber-500" aria-hidden="true"></span>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $statusLabels[$history->to_status] ?? $history->to_status }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $history->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-slate-500">Belum ada riwayat status.</li>
                    @endforelse
                </ol>
            </div>
        </div>
    </section>
@endsection
