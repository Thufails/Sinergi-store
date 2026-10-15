@extends('layouts.admin')

@section('title', 'Detail Pesanan - Sinergi Store')
@section('heading', 'Detail pesanan')

@section('content')
    @php
        $statusStyles = [
            'pending_confirmation' => 'bg-amber-100 text-amber-800',
            'awaiting_payment' => 'bg-sky-100 text-sky-800',
            'paid' => 'bg-emerald-100 text-emerald-800',
            'cancelled' => 'bg-slate-200 text-slate-700',
            'expired' => 'bg-rose-100 text-rose-700',
        ];
    @endphp

    <a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-slate-500 transition hover:text-amber-700">← Kembali ke daftar pesanan</a>

    <section class="mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 px-6 py-5">
            <div>
                <p class="text-sm font-medium text-amber-700">Nomor pesanan</p>
                <h2 class="mt-1 text-xl font-semibold text-slate-900">{{ $order->order_number }}</h2>
            </div>
            <span class="rounded-full px-3 py-1.5 text-sm font-semibold {{ $statusStyles[$order->status] ?? 'bg-slate-100 text-slate-600' }}">{{ $statusLabels[$order->status] ?? $order->status }}</span>
        </div>

        <div class="grid gap-6 border-b border-slate-200 px-6 py-6 md:grid-cols-2">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Informasi pembeli</h3>
                <p class="mt-3 text-sm text-slate-700">{{ $order->customer_name }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ $order->customer_phone }}</p>
            </div>
            <div class="md:text-right">
                <h3 class="text-sm font-semibold text-slate-900">Waktu pesanan</h3>
                <p class="mt-3 text-sm text-slate-700">Dibuat {{ $order->created_at->format('d M Y, H:i') }}</p>
                @if ($order->payment_due_at)
                    <p class="mt-1 text-sm {{ $order->payment_due_at->isPast() && $order->status === 'awaiting_payment' ? 'font-semibold text-rose-600' : 'text-slate-500' }}">
                        Batas pembayaran {{ $order->payment_due_at->format('d M Y, H:i') }}
                        @if ($order->status === 'awaiting_payment')
                            · {{ $order->payment_due_at->isPast() ? 'waktu habis, hold dilepas' : 'stok sedang di-hold' }}
                        @endif
                    </p>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Produk</th>
                        <th class="px-6 py-4 font-semibold">Varian</th>
                        <th class="px-6 py-4 text-right font-semibold">Harga</th>
                        <th class="px-6 py-4 text-right font-semibold">Jumlah</th>
                        <th class="px-6 py-4 text-right font-semibold">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($order->items as $item)
                        <tr>
                            <td class="px-6 py-4 font-medium text-slate-800">{{ $item->product_name }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ $item->variant_name ?: 'Tanpa varian' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-slate-600">Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right text-slate-600">{{ $item->quantity }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right font-medium text-slate-800">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50">
                    <tr>
                        <th colspan="4" class="px-6 py-4 text-right text-sm font-semibold text-slate-700">Total pesanan</th>
                        <th class="whitespace-nowrap px-6 py-4 text-right text-base font-bold text-slate-900">Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="border-t border-slate-200 bg-slate-50 px-6 py-5">
            <h3 class="text-sm font-semibold text-slate-900">Tindakan pesanan</h3>
            <p class="mt-1 text-sm text-slate-500">Stok baru dikurangi setelah pembayaran dikonfirmasi.</p>
            <div class="mt-4 flex flex-wrap gap-3">
                @if ($order->status === 'pending_confirmation')
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}" onsubmit="return confirm('Setujui pesanan dan tahan stok selama 24 jam?')">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="awaiting_payment">
                        <button type="submit" class="rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">Setujui &amp; mulai hold 24 jam</button>
                    </form>
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}" onsubmit="return confirm('Batalkan pesanan ini?')">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="rounded-xl border border-rose-200 px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">Batalkan pesanan</button>
                    </form>
                @elseif ($order->status === 'awaiting_payment' && $order->payment_due_at?->isFuture())
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}" onsubmit="return confirm('Pastikan pembayaran sudah diterima sebelum melanjutkan. Stok akan dikurangi.')">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="paid">
                        <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">Konfirmasi pembayaran diterima</button>
                    </form>
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}" onsubmit="return confirm('Batalkan pesanan dan lepaskan hold stok?')">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="rounded-xl border border-rose-200 px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">Batalkan &amp; lepas hold</button>
                    </form>
                @elseif ($order->status === 'awaiting_payment' && $order->payment_due_at?->isPast())
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}" onsubmit="return confirm('Tandai kedaluwarsa? Hold stok pesanan ini akan dilepas.')">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="expired">
                        <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700">Tandai kedaluwarsa</button>
                    </form>
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}" onsubmit="return confirm('Batalkan pesanan yang melewati batas pembayaran?')">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-white">Batalkan pesanan</button>
                    </form>
                @else
                    <p class="rounded-xl bg-white px-4 py-3 text-sm text-slate-500">Tidak ada tindakan lanjutan untuk status ini.</p>
                @endif
            </div>
        </div>
    </section>
@endsection
