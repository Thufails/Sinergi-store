@extends('layouts.admin')

@section('title', 'Pesanan - Sinergi Store')
@section('heading', 'Pesanan')

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

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="text-xl font-semibold text-slate-900">Daftar pesanan</h2>
            <p class="mt-1 text-sm text-slate-500">Tinjau pesanan dan kelola konfirmasi pembayaran.</p>
        </div>

        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid items-end gap-4 border-b border-slate-200 bg-slate-50 p-5 md:grid-cols-2 2xl:grid-cols-12">
            <label class="flex min-w-0 flex-col gap-1 text-xs font-medium text-slate-500 2xl:col-span-4">
                Cari pesanan
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Nomor, nama, atau WhatsApp" class="h-11 min-w-0 rounded-xl border border-slate-300 bg-white px-4 text-sm font-normal text-slate-800 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
            </label>
            <label class="flex min-w-0 flex-col gap-1 text-xs font-medium text-slate-500 2xl:col-span-2">
                Status
                <select name="status" class="h-11 min-w-0 rounded-xl border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                    <option value="">Semua status</option>
                    @foreach ($statusLabels as $status => $label)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="flex min-w-0 flex-col gap-1 text-xs font-medium text-slate-500 2xl:col-span-2">
                Dari tanggal
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="h-11 min-w-0 rounded-xl border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
            </label>
            <label class="flex min-w-0 flex-col gap-1 text-xs font-medium text-slate-500 2xl:col-span-2">
                Sampai tanggal
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="h-11 min-w-0 rounded-xl border border-slate-300 bg-white px-3 text-sm font-normal text-slate-800 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
            </label>
            <button type="submit" class="h-11 rounded-xl bg-slate-900 px-4 text-sm font-semibold text-white transition hover:bg-slate-700 2xl:col-span-1">Terapkan</button>
            <a href="{{ route('admin.orders.index') }}" class="flex h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-medium text-slate-600 transition hover:bg-slate-100 2xl:col-span-1">Reset</a>
            @if ($errors->has('date_from') || $errors->has('date_to'))
                <p class="text-sm text-rose-600 md:col-span-2 xl:col-span-12" role="alert">{{ $errors->first('date_to') ?: $errors->first('date_from') }}</p>
            @endif
        </form>

        @if ($orders->isEmpty())
            <div class="px-6 py-16 text-center">
                <p class="font-medium text-slate-700">Belum ada pesanan.</p>
                <p class="mt-1 text-sm text-slate-500">Pesanan dari halaman publik akan muncul di sini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Nomor pesanan</th>
                            <th class="px-6 py-4 font-semibold">Pembeli</th>
                            <th class="px-6 py-4 font-semibold">Item</th>
                            <th class="px-6 py-4 font-semibold">Total</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 font-semibold">Dibuat</th>
                            <th class="px-6 py-4 text-right font-semibold">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($orders as $order)
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-900">{{ $order->order_number }}</td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <p class="font-medium text-slate-800">{{ $order->customer_name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $order->customer_phone }}</p>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-600">{{ $order->items->sum('quantity') }} unit · {{ $order->items->count() }} jenis</td>
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-800">Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-6 py-4"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusStyles[$order->status] ?? 'bg-slate-100 text-slate-600' }}">{{ $statusLabels[$order->status] ?? $order->status }}</span></td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right"><a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-amber-700 hover:text-amber-800">Lihat detail</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 px-6 py-4">{{ $orders->links() }}</div>
        @endif
    </section>
@endsection
