@extends('layouts.admin')

@section('title', 'Detail Pesanan - Sinergi Store')
@section('heading', 'Detail pesanan')

@section('content')
    @php
        $shippingWhatsappNumber = preg_replace('/\D+/', '', (string) $order->customer_phone) ?? '';
        if (str_starts_with($shippingWhatsappNumber, '0')) {
            $shippingWhatsappNumber = '62' . substr($shippingWhatsappNumber, 1);
        } elseif (str_starts_with($shippingWhatsappNumber, '8')) {
            $shippingWhatsappNumber = '62' . $shippingWhatsappNumber;
        }

        $shippingWhatsappMessage = implode("\n", [
            'Halo ' . $order->customer_name . ', pesananmu sudah dikirim dari Sinergi Store.',
            'Nomor pesanan: ' . $order->order_number,
            'Kurir: ' . ($order->courier_name ?: '-'),
            'Nomor resi: ' . ($order->tracking_number ?: '-'),
            '',
            'Terima kasih sudah berbelanja di Sinergi Store.',
        ]);

        $statusStyles = [
            'pending_confirmation' => 'bg-amber-100 text-amber-900 ring-1 ring-inset ring-amber-300',
            'awaiting_payment' => 'bg-sky-100 text-sky-900 ring-1 ring-inset ring-sky-300',
            'paid' => 'bg-emerald-100 text-emerald-900 ring-1 ring-inset ring-emerald-300',
            'packing' => 'bg-violet-100 text-violet-900 ring-1 ring-inset ring-violet-300',
            'shipped' => 'bg-cyan-100 text-cyan-900 ring-1 ring-inset ring-cyan-300',
            'cancelled' => 'bg-slate-200 text-slate-800 ring-1 ring-inset ring-slate-300',
            'expired' => 'bg-rose-100 text-rose-900 ring-1 ring-inset ring-rose-300',
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
                        @elseif ($order->status === 'packing')
                            Pesanan sedang dikemas; batas pembayaran sudah tidak berlaku.
                        @elseif ($order->status === 'shipped')
                            Pesanan sudah dikirim; batas pembayaran sudah tidak berlaku.
                        @elseif ($order->status === 'cancelled')
                            Pesanan dibatalkan; hold stok sudah dilepas.
                        @else
                            Batas pembayaran sudah tidak berlaku untuk status pesanan ini.
                        @endif
                    </p>
                </div>
            @endif
        </div>

        @if ($order->status === 'shipped' || $order->courier_name || $order->tracking_number)
            <div class="border-b border-slate-200 bg-sky-50/70 px-6 py-5">
                <h3 class="text-sm font-semibold text-slate-900">Informasi pengiriman</h3>
                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Kurir</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800">{{ $order->courier_name ?: 'Belum diisi' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Nomor resi</p>
                        <p class="mt-1 break-all text-sm font-semibold text-slate-800">{{ $order->tracking_number ?: 'Belum diisi' }}</p>
                    </div>
                    @if ($order->shipped_at)
                        <div class="sm:col-span-2">
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Dikirim pada</p>
                            <p class="mt-1 text-sm text-slate-700">{{ $order->shipped_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
                        </div>
                    @endif
                    @if ($order->status === 'shipped' && preg_match('/^62\d{8,13}$/', $shippingWhatsappNumber) === 1)
                        <div class="sm:col-span-2">
                            <a href="https://wa.me/{{ $shippingWhatsappNumber }}?text={{ rawurlencode($shippingWhatsappMessage) }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-10 items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 focus-visible:ring-offset-2">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.52 3.48A11.8 11.8 0 0 0 12.12 0C5.57 0 .23 5.33.23 11.89c0 2.1.55 4.15 1.6 5.96L.13 24l6.31-1.65a11.9 11.9 0 0 0 5.68 1.45h.01c6.55 0 11.89-5.33 11.89-11.89 0-3.17-1.24-6.15-3.5-8.43ZM12.13 21.8h-.01a9.9 9.9 0 0 1-5.04-1.38l-.36-.21-3.75.98 1-3.66-.24-.38a9.86 9.86 0 0 1-1.52-5.26c0-5.47 4.45-9.92 9.92-9.92 2.65 0 5.14 1.03 7.01 2.91a9.86 9.86 0 0 1 2.9 7.02c0 5.47-4.44 9.9-9.91 9.9Zm5.44-7.42c-.3-.15-1.77-.87-2.05-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.47-.89-.79-1.49-1.77-1.67-2.07-.17-.3-.02-.46.13-.61.14-.14.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.2 5.08 4.49.71.31 1.27.49 1.7.63.71.23 1.36.2 1.87.12.57-.08 1.77-.72 2.02-1.42.25-.7.25-1.3.18-1.42-.08-.13-.28-.2-.58-.35Z" /></svg>
                                Kirim info pengiriman via WhatsApp
                            </a>
                            <p class="mt-2 text-xs text-slate-500">Pesan akan terbuka di WhatsApp untuk ditinjau sebelum dikirim.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

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
                @elseif ($order->status === 'paid')
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="packing">
                        <button type="submit" class="rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">Mulai pengemasan</button>
                    </form>
                @elseif ($order->status === 'packing')
                    <button type="button" data-open-shipping-dialog class="rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">Input kurir &amp; nomor resi</button>
                    <p class="self-center text-sm text-slate-500">Simpan detail pengiriman untuk mengubah status menjadi Dikirim.</p>
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

    @if ($order->status === 'packing')
        <dialog data-shipping-dialog aria-labelledby="shipping-dialog-title" aria-describedby="shipping-dialog-description" class="m-auto w-[calc(100%-2rem)] max-w-lg overflow-hidden rounded-2xl p-0 shadow-2xl backdrop:bg-slate-950/60">
            <div class="bg-white p-6 sm:p-7">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-amber-700">Detail pengiriman</p>
                        <h2 id="shipping-dialog-title" class="mt-1 text-xl font-semibold text-slate-900">Pesanan siap dikirim?</h2>
                    </div>
                    <button type="button" data-close-shipping-dialog aria-label="Tutup form pengiriman" class="rounded-lg p-2 text-2xl leading-none text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">&times;</button>
                </div>
                <p id="shipping-dialog-description" class="mt-3 text-sm leading-6 text-slate-600">
                    Isi nama kurir dan nomor resi. Setelah disimpan, status pesanan berubah menjadi <span class="font-semibold text-cyan-700">Dikirim</span>.
                </p>

                <form id="shipping-form" method="POST" action="{{ route('admin.orders.shipping', $order) }}" class="mt-6 space-y-5">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="courier_name" class="mb-2 block text-sm font-medium text-slate-700">Nama kurir</label>
                        <input id="courier_name" name="courier_name" type="text" value="{{ old('courier_name', $order->courier_name) }}" maxlength="100" required autofocus placeholder="Contoh: JNE, J&T, SiCepat" @class(['w-full rounded-xl border px-4 py-3 text-sm outline-none transition focus:ring-2 focus:ring-amber-200', 'border-rose-400 focus:border-rose-500' => $errors->has('courier_name'), 'border-slate-300 focus:border-amber-500' => ! $errors->has('courier_name')]) @if ($errors->has('courier_name')) aria-invalid="true" aria-describedby="courier-name-error" @endif>
                        @error('courier_name')
                            <p id="courier-name-error" class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="tracking_number" class="mb-2 block text-sm font-medium text-slate-700">Nomor resi</label>
                        <input id="tracking_number" name="tracking_number" type="text" value="{{ old('tracking_number', $order->tracking_number) }}" maxlength="100" required placeholder="Masukkan nomor resi" @class(['w-full rounded-xl border px-4 py-3 text-sm outline-none transition focus:ring-2 focus:ring-amber-200', 'border-rose-400 focus:border-rose-500' => $errors->has('tracking_number'), 'border-slate-300 focus:border-amber-500' => ! $errors->has('tracking_number')]) @if ($errors->has('tracking_number')) aria-invalid="true" aria-describedby="tracking-number-error" @endif>
                        @error('tracking_number')
                            <p id="tracking-number-error" class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="rounded-xl bg-amber-50 px-4 py-3 text-xs leading-5 text-amber-900">
                        Pastikan nomor resi benar. Penyimpanan akan mencatat waktu pengiriman dan memperbarui riwayat pesanan.
                    </div>
                </form>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button" data-close-shipping-dialog class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Batal</button>
                    <button type="submit" form="shipping-form" class="rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">Simpan &amp; tandai dikirim</button>
                </div>
            </div>
        </dialog>

        <script>
            (() => {
                const shippingDialog = document.querySelector('[data-shipping-dialog]');
                const openShippingButton = document.querySelector('[data-open-shipping-dialog]');

                if (!shippingDialog) return;

                openShippingButton?.addEventListener('click', () => shippingDialog.showModal());
                document.querySelectorAll('[data-close-shipping-dialog]').forEach((button) => {
                    button.addEventListener('click', () => shippingDialog.close());
                });
                shippingDialog.addEventListener('click', (event) => {
                    if (event.target === shippingDialog) shippingDialog.close();
                });

                @if ($errors->has('courier_name') || $errors->has('tracking_number'))
                    shippingDialog.showModal();
                @endif
            })();
        </script>
    @endif
@endsection
