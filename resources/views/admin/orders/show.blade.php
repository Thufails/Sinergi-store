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

    <section class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 px-6 py-5">
            <div>
                <a href="{{ route('admin.orders.index') }}" class="mb-4 inline-flex min-h-9 items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 transition hover:border-amber-400 hover:bg-amber-50 hover:text-slate-950 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.56l3.22 3.22a.75.75 0 1 1-1.06 1.06l-4.5-4.5a.75.75 0 0 1 0-1.06l4.5-4.5a.75.75 0 0 1 1.06 1.06l-3.22 3.22h10.69A.75.75 0 0 1 17 10Z" clip-rule="evenodd" /></svg>
                    <span>Kembali ke daftar pesanan</span>
                </a>
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
                <p class="mt-3 text-sm text-slate-700">Dibuat {{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
            </div>

            @if ($order->payment_due_at)
                @php
                    $isAwaitingPayment = $order->status === 'awaiting_payment';
                    $paymentDeadlinePassed = $isAwaitingPayment && $order->payment_due_at->isPast();
                    $paymentDeadlineNear = $isAwaitingPayment
                        && $order->payment_due_at->isFuture()
                        && $order->payment_due_at->lessThanOrEqualTo(now()->addHours(6));
                    $paymentDeadlineUrgent = $paymentDeadlinePassed || $paymentDeadlineNear || $order->status === 'expired';
                    $paymentDeadlineStyles = $paymentDeadlineUrgent
                        ? 'border-rose-200 bg-rose-50 text-rose-800'
                        : ($isAwaitingPayment ? 'border-amber-200 bg-amber-50 text-amber-900' : 'border-slate-200 bg-slate-50 text-slate-700');
                @endphp
                <div class="flex flex-col gap-3 rounded-xl border px-4 py-3 text-left sm:flex-row sm:items-center md:col-span-2 {{ $paymentDeadlineStyles }}">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" />
                    </svg>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide">Batas pembayaran</p>
                        <p class="mt-1 text-sm font-bold">{{ $order->payment_due_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
                    </div>
                    <p class="text-xs leading-5 sm:ml-auto sm:text-right">
                        @if ($order->status === 'expired')
                            Waktu pembayaran terlewati; pesanan sudah kedaluwarsa.
                        @elseif ($paymentDeadlinePassed)
                            Waktu pembayaran habis; hold stok sudah dilepas.
                        @elseif ($paymentDeadlineNear)
                            Segera berakhir. Stok masih di-hold.
                        @elseif ($isAwaitingPayment)
                            Stok sedang di-hold sampai batas waktu ini.
                        @elseif ($order->status === 'paid')
                            Pembayaran sudah dikonfirmasi.
                        @else
                            Pesanan dibatalkan; hold stok sudah dilepas.
                        @endif
                    </p>
                </div>
            @endif
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
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}" data-approve-order-form>
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="awaiting_payment">
                        <button type="submit" class="rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">Setujui &amp; mulai hold 24 jam</button>
                    </form>
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}" data-confirm-title="Batalkan pesanan ini?" data-confirm-description="Pesanan {{ $order->order_number }} akan dibatalkan. Tindakan ini tidak bisa dibatalkan." data-confirm-button="Ya, batalkan pesanan" data-confirm-tone="danger">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="rounded-xl border border-rose-200 px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">Batalkan pesanan</button>
                    </form>
                @elseif ($order->status === 'awaiting_payment' && $order->payment_due_at?->isFuture())
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}" data-confirm-payment-form>
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="paid">
                        <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">Konfirmasi pembayaran diterima</button>
                    </form>
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}" data-confirm-title="Batalkan pesanan &amp; lepas hold?" data-confirm-description="Pesanan {{ $order->order_number }} akan dibatalkan dan stok yang sedang di-hold akan tersedia kembali." data-confirm-button="Ya, batalkan &amp; lepas hold" data-confirm-tone="danger">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="rounded-xl border border-rose-200 px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">Batalkan &amp; lepas hold</button>
                    </form>
                @elseif ($order->status === 'awaiting_payment' && $order->payment_due_at?->isPast())
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}" data-confirm-title="Tandai pesanan kedaluwarsa?" data-confirm-description="Pesanan {{ $order->order_number }} akan ditandai kedaluwarsa dan hold stoknya dilepas." data-confirm-button="Ya, tandai kedaluwarsa">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="expired">
                        <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700">Tandai kedaluwarsa</button>
                    </form>
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}" data-confirm-title="Batalkan pesanan ini?" data-confirm-description="Pesanan {{ $order->order_number }} akan dibatalkan meskipun batas pembayarannya sudah lewat." data-confirm-button="Ya, batalkan pesanan" data-confirm-tone="danger">
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

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="order-history-heading">
        <div class="border-b border-slate-200 px-6 py-5">
            <h2 id="order-history-heading" class="text-lg font-semibold text-slate-900">Riwayat pesanan</h2>
            <p class="mt-1 text-sm text-slate-500">Catatan perubahan status dan pihak yang melakukannya.</p>
        </div>
        <div class="p-6">
            @if ($order->statusHistories->isNotEmpty())
                <ol class="space-y-6">
                    @foreach ($order->statusHistories as $history)
                        <li class="relative flex gap-4">
                            <span class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $statusStyles[$history->to_status] ?? 'bg-slate-100 text-slate-600' }}" aria-hidden="true">
                                @if ($history->from_status === null)
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 0 1 .75.75v5.5h5.5a.75.75 0 0 1 0 1.5h-5.5v5.5a.75.75 0 0 1-1.5 0v-5.5h-5.5a.75.75 0 0 1 0-1.5h5.5v-5.5A.75.75 0 0 1 10 3Z" clip-rule="evenodd" /></svg>
                                @else
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M15.78 6.22a.75.75 0 0 1 0 1.06l-6.25 6.25a.75.75 0 0 1-1.06 0l-3.25-3.25a.75.75 0 1 1 1.06-1.06L9 11.94l5.72-5.72a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" /></svg>
                                @endif
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-semibold text-slate-900">
                                        {{ $history->from_status === null ? 'Pesanan dibuat' : 'Status diperbarui' }}
                                    </h3>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusStyles[$history->to_status] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $statusLabels[$history->to_status] ?? $history->to_status }}
                                    </span>
                                </div>
                                @if ($history->from_status !== null)
                                    <p class="mt-1 text-sm text-slate-600">
                                        Dari {{ $statusLabels[$history->from_status] ?? $history->from_status }} menjadi {{ $statusLabels[$history->to_status] ?? $history->to_status }}.
                                    </p>
                                @else
                                    <p class="mt-1 text-sm text-slate-600">Pesanan diterima dari formulir publik.</p>
                                @endif
                                <p class="mt-2 text-xs text-slate-500">
                                    {{ $history->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                    <span aria-hidden="true">·</span>
                                    @if ($history->from_status === null)
                                        Oleh Pelanggan
                                    @else
                                        Oleh Admin ({{ $history->actor_name ?? 'Tidak diketahui' }})
                                    @endif
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="text-sm text-slate-500">Riwayat belum tersedia untuk pesanan yang tercatat sebelum fitur ini.</p>
            @endif
        </div>
    </section>

    @if ($order->status === 'pending_confirmation')
        <dialog data-approve-order-dialog aria-labelledby="approve-order-title" aria-describedby="approve-order-description" class="m-auto w-[calc(100%-2rem)] max-w-md overflow-hidden rounded-2xl p-0 shadow-2xl backdrop:bg-slate-950/60">
            <div class="bg-white p-6 sm:p-7">
                <div class="flex items-start gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-700" aria-hidden="true">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="9" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-amber-700">Konfirmasi pesanan</p>
                        <h2 id="approve-order-title" class="mt-1 text-xl font-semibold text-slate-900">Setujui pesanan ini?</h2>
                    </div>
                </div>

                <p id="approve-order-description" class="mt-5 text-sm leading-6 text-slate-600">
                    Stok akan di-hold selama 24 jam dan status pesanan berubah menjadi <span class="font-semibold text-sky-700">Menunggu pembayaran</span>. Stok fisik baru dikurangi setelah pembayaran dikonfirmasi.
                </p>
                <p class="mt-3 rounded-xl bg-amber-50 px-4 py-3 text-xs leading-5 text-amber-900">
                    Jika pembayaran belum dikonfirmasi sampai batas waktu, hold stok perlu dilepas dan pesanan ditandai kedaluwarsa.
                </p>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button" data-cancel-approve-order class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Periksa lagi</button>
                    <button type="button" data-confirm-approve-order class="rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">Ya, setujui &amp; hold stok</button>
                </div>
            </div>
        </dialog>

        <script>
            (() => {
                const approvalForm = document.querySelector('[data-approve-order-form]');
                const approvalDialog = document.querySelector('[data-approve-order-dialog]');
                const confirmApprovalButton = document.querySelector('[data-confirm-approve-order]');
                const cancelApprovalButton = document.querySelector('[data-cancel-approve-order]');
                let approvalConfirmed = false;

                if (!approvalForm || !approvalDialog) return;

                approvalForm.addEventListener('submit', (event) => {
                    if (approvalConfirmed) {
                        approvalConfirmed = false;
                        return;
                    }

                    event.preventDefault();
                    approvalDialog.showModal();
                });

                confirmApprovalButton?.addEventListener('click', () => {
                    approvalConfirmed = true;
                    approvalDialog.close();
                    approvalForm.requestSubmit();
                });

                cancelApprovalButton?.addEventListener('click', () => approvalDialog.close());
                approvalDialog.addEventListener('click', (event) => {
                    if (event.target === approvalDialog) approvalDialog.close();
                });
            })();
        </script>
    @endif

    @if ($order->status === 'awaiting_payment' && $order->payment_due_at?->isFuture())
        <dialog data-confirm-payment-dialog aria-labelledby="confirm-payment-title" aria-describedby="confirm-payment-description" class="m-auto w-[calc(100%-2rem)] max-w-md overflow-hidden rounded-2xl p-0 shadow-2xl backdrop:bg-slate-950/60">
            <div class="bg-white p-6 sm:p-7">
                <div class="flex items-start gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700" aria-hidden="true">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                            <circle cx="12" cy="12" r="9" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-emerald-700">Konfirmasi pembayaran</p>
                        <h2 id="confirm-payment-title" class="mt-1 text-xl font-semibold text-slate-900">Pembayaran sudah diterima?</h2>
                    </div>
                </div>

                <p id="confirm-payment-description" class="mt-5 text-sm leading-6 text-slate-600">
                    Pastikan pembayaran pesanan <span class="font-semibold text-slate-900">{{ $order->order_number }}</span> benar-benar sudah diterima sebelum melanjutkan.
                </p>
                <p class="mt-3 rounded-xl bg-emerald-50 px-4 py-3 text-xs leading-5 text-emerald-900">
                    Setelah dikonfirmasi, status menjadi <span class="font-semibold">Dibayar</span> dan stok produk akan langsung dikurangi.
                </p>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button" data-cancel-confirm-payment class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Belum, periksa lagi</button>
                    <button type="button" data-submit-confirm-payment class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">Ya, pembayaran diterima</button>
                </div>
            </div>
        </dialog>

        <script>
            (() => {
                const paymentForm = document.querySelector('[data-confirm-payment-form]');
                const paymentDialog = document.querySelector('[data-confirm-payment-dialog]');
                const submitPaymentButton = document.querySelector('[data-submit-confirm-payment]');
                const cancelPaymentButton = document.querySelector('[data-cancel-confirm-payment]');
                let paymentConfirmed = false;

                if (!paymentForm || !paymentDialog) return;

                paymentForm.addEventListener('submit', (event) => {
                    if (paymentConfirmed) {
                        paymentConfirmed = false;
                        return;
                    }

                    event.preventDefault();
                    paymentDialog.showModal();
                });

                submitPaymentButton?.addEventListener('click', () => {
                    paymentConfirmed = true;
                    paymentDialog.close();
                    paymentForm.requestSubmit();
                });

                cancelPaymentButton?.addEventListener('click', () => paymentDialog.close());
                paymentDialog.addEventListener('click', (event) => {
                    if (event.target === paymentDialog) paymentDialog.close();
                });
            })();
        </script>
    @endif
@endsection
