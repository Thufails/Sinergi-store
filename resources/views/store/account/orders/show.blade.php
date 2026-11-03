@extends('layouts.store')

@section('title', 'Detail Pesanan ' . $order->order_number . ' - Sinergi Store')

@section('content')
    @php
        $statusLabels = [
            'pending_confirmation' => 'Menunggu konfirmasi',
            'awaiting_payment' => 'Menunggu pembayaran',
            'payment_review' => 'Menunggu verifikasi pembayaran',
            'paid' => 'Dibayar',
            'packing' => 'Sedang dikemas',
            'shipped' => 'Dikirim',
            'cancelled' => 'Dibatalkan',
            'expired' => 'Kedaluwarsa',
        ];
        $statusStyles = [
            'pending_confirmation' => 'bg-amber-100 text-amber-900 ring-1 ring-inset ring-amber-300',
            'awaiting_payment' => 'bg-sky-100 text-sky-900 ring-1 ring-inset ring-sky-300',
            'payment_review' => 'bg-rose-100 text-rose-900 ring-1 ring-inset ring-rose-300',
            'paid' => 'bg-emerald-100 text-emerald-900 ring-1 ring-inset ring-emerald-300',
            'packing' => 'bg-violet-100 text-violet-900 ring-1 ring-inset ring-violet-300',
            'shipped' => 'bg-cyan-100 text-cyan-900 ring-1 ring-inset ring-cyan-300',
            'cancelled' => 'bg-slate-200 text-slate-800 ring-1 ring-inset ring-slate-300',
            'expired' => 'bg-rose-100 text-rose-700 ring-1 ring-inset ring-rose-300',
        ];
        $paymentProofCanBeUploaded = $order->status === 'awaiting_payment'
            && $order->payment_due_at
            && $order->payment_due_at->isFuture();
    @endphp

    <section class="mx-auto max-w-7xl px-6 py-10 lg:px-8 lg:py-14">
        <a href="{{ route('account.orders') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 transition hover:text-amber-700">← Kembali ke pesanan saya</a>
        @if (session('success'))<div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('success') }}</div>@endif
        @if ($errors->any())<div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif

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
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Alamat pengiriman</p>
                    <p class="mt-3 font-medium text-slate-900">{{ $order->shipping_recipient_name ?: $order->customer_name }} · {{ $order->shipping_recipient_phone ?: $order->customer_phone }}</p>
                    <p class="mt-1 text-sm leading-6 text-slate-600">{{ collect([$order->shipping_address_line, $order->shipping_village, $order->shipping_district, $order->shipping_city, $order->shipping_province, $order->shipping_postal_code])->filter()->implode(', ') ?: 'Alamat tidak tersedia untuk pesanan lama.' }}</p>
                    @if ($order->shipping_notes)<p class="mt-2 text-sm text-slate-500">Catatan kurir: {{ $order->shipping_notes }}</p>@endif
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Informasi pengiriman</p>
                    @if ($order->courier_name || $order->tracking_number)
                        <p class="mt-3 font-medium text-slate-900">{{ $order->courier_name ?: 'Kurir belum diisi' }}{{ $order->courier_service ? ' · ' . $order->courier_service : '' }}</p>
                        <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-slate-600">
                            <p class="break-all">Resi · {{ $order->tracking_number ?: 'Belum tersedia' }}</p>
                            @if ($order->tracking_number)
                                <button type="button" data-copy-tracking-number="{{ $order->tracking_number }}" class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-slate-500 transition hover:bg-white hover:text-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-400" aria-label="Salin nomor resi" title="Salin nomor resi">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="7" y="6" width="9" height="11" rx="1.5"/><path d="M13 6V4.5A1.5 1.5 0 0 0 11.5 3h-7A1.5 1.5 0 0 0 3 4.5v9A1.5 1.5 0 0 0 4.5 15H7"/></svg>
                                </button>
                                <span data-copy-tracking-feedback role="status" aria-live="polite" class="text-xs text-emerald-700"></span>
                            @endif
                        </div>
                        @if ($order->shipping_estimated_delivery)<p class="mt-1 text-sm text-slate-600">Estimasi tiba · {{ $order->shipping_estimated_delivery }}</p>@endif
                        @if ($order->shipped_at)<p class="mt-2 text-xs text-slate-500">Dikirim {{ $order->shipped_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>@endif
                    @else
                        <p class="mt-3 text-sm text-slate-600">Detail kurir dan resi akan muncul setelah pesanan dikirim.</p>
                    @endif
                </div>
                <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-800">Status pembayaran</p>
                    @if ($order->payment_due_at)
                        <p class="mt-2 text-sm font-semibold text-slate-900">Batas pembayaran {{ $order->payment_due_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
                    @endif
                    @if ($order->status === 'pending_confirmation')
                        <p class="mt-1 text-sm text-slate-700">Admin sedang memeriksa alamat dan ongkir. Total final akan ditampilkan setelah ongkir ditambahkan.</p>
                    @elseif ($order->status === 'payment_review')
                        <p class="mt-1 text-sm text-slate-700">Bukti pembayaran sudah dikirim dan menunggu verifikasi admin.</p>
                    @elseif ($order->status === 'awaiting_payment')
                        <p class="mt-1 text-sm text-slate-700">Silakan bayar sesuai total pesanan sebelum batas waktu.</p>
                        @if ($paymentProofCanBeUploaded)
                            <div class="mt-3 rounded-xl border border-rose-300 bg-rose-50 p-3" role="note">
                                <p class="text-xs font-bold uppercase tracking-wide text-rose-800">Rekening simulasi — jangan transfer</p>
                                <dl class="mt-2 grid gap-2 text-sm sm:grid-cols-2">
                                    <div>
                                        <dt class="text-xs text-slate-600">Bank</dt>
                                        <dd class="font-semibold text-slate-900">Bank Contoh</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-slate-600">Nomor rekening</dt>
                                        <dd class="font-semibold tracking-wide text-slate-900">0000 0000 0000</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-slate-600">Atas nama</dt>
                                        <dd class="font-semibold text-slate-900">Sinergi Store (Simulasi)</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-slate-600">Total transfer</dt>
                                        <dd class="font-bold text-slate-950">Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</dd>
                                    </div>
                                </dl>
                                <p class="mt-2 text-xs leading-5 text-rose-800">Gunakan hanya untuk melihat tampilan. Ganti dengan rekening resmi sebelum menerima pembayaran sungguhan.</p>
                            </div>
                        @endif
                    @elseif ($order->status === 'expired')
                        <p class="mt-1 text-sm text-slate-700">Batas pembayaran telah terlewati dan pesanan kedaluwarsa.</p>
                    @endif
                    @if ($order->payment_proof_path)
                        <a href="{{ route('account.orders.payment-proof', $order) }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex text-sm font-semibold text-amber-800 underline">Lihat bukti pembayaran yang dikirim</a>
                    @endif
                </div>
            </div>

            @if ($paymentProofCanBeUploaded)
                <form method="POST" action="{{ route('account.orders.payment-proof.upload', $order) }}" enctype="multipart/form-data" class="border-b border-slate-200 bg-sky-50/60 px-5 py-5 sm:px-7">
                    @csrf
                    @if ($order->payment_review_note)
                        <div class="mb-4 rounded-xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-900" role="alert">
                            <p class="font-semibold">Bukti pembayaran ditolak</p>
                            <p class="mt-1">Silakan unggah ulang bukti pembayaran yang jelas dan sesuai dengan total pembayaran.</p>
                            <p class="mt-2"><span class="font-semibold">Catatan admin:</span> {{ $order->payment_review_note }}</p>
                        </div>
                    @endif
                    <h2 class="font-semibold text-slate-900">Kirim bukti pembayaran</h2>
                    <p class="mt-1 text-sm text-slate-600">Pilih JPG, JPEG, PNG, WEBP, atau PDF dengan ukuran maksimal 5 MB.</p>
                    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                        <label class="block flex-1 text-sm font-medium text-slate-700">File bukti pembayaran
                            <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf" required class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-amber-100 file:px-3 file:py-2 file:font-semibold file:text-amber-800">
                        </label>
                        <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">Kirim bukti</button>
                    </div>
                </form>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3 font-semibold sm:px-7">Produk</th><th class="px-5 py-3 font-semibold">Varian</th><th class="px-5 py-3 text-right font-semibold">Jumlah</th><th class="px-5 py-3 text-right font-semibold sm:px-7">Subtotal</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($order->items as $item)
                            <tr><td class="px-5 py-4 font-medium text-slate-900 sm:px-7">{{ $item->product_name }}</td><td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $item->variant_name ?: 'Tanpa varian' }}</td><td class="px-5 py-4 text-right text-slate-600">{{ $item->quantity }}</td><td class="whitespace-nowrap px-5 py-4 text-right font-medium text-slate-900 sm:px-7">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td></tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50">
                        <tr><th colspan="3" class="px-5 pt-4 text-right font-medium text-slate-600 sm:px-7">Subtotal produk</th><td class="whitespace-nowrap px-5 pt-4 text-right font-medium text-slate-800 sm:px-7">Rp {{ number_format((float) ($order->subtotal_amount ?? $order->total_amount), 0, ',', '.') }}</td></tr>
                        <tr><th colspan="3" class="px-5 py-2 text-right font-medium text-slate-600 sm:px-7">Ongkir{{ $order->courier_name ? ' · ' . $order->courier_name : '' }}</th><td class="whitespace-nowrap px-5 py-2 text-right font-medium text-slate-800 sm:px-7">{{ (float) $order->shipping_fee > 0 ? 'Rp ' . number_format((float) $order->shipping_fee, 0, ',', '.') : ($order->status === 'pending_confirmation' ? 'Menunggu konfirmasi' : 'Rp 0') }}</td></tr>
                        <tr><th colspan="3" class="px-5 pb-4 pt-2 text-right font-semibold text-slate-900 sm:px-7">Total pembayaran</th><td class="whitespace-nowrap px-5 pb-4 pt-2 text-right text-base font-bold text-slate-950 sm:px-7">Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</td></tr>
                    </tfoot>
                </table>
            </div>

            <div class="border-t border-slate-200 px-5 py-5 sm:px-7">
                <h2 class="font-semibold text-slate-900">Riwayat status</h2>
                @php
                    $allHistories = $order->statusHistories->values();
                    $recentHistoryCount = min(3, $allHistories->count());
                    $previousHistories = $allHistories->take($allHistories->count() - $recentHistoryCount);
                    $recentHistories = $allHistories->slice($allHistories->count() - $recentHistoryCount);
                @endphp
                @if ($previousHistories->isNotEmpty())
                    <details class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <summary class="cursor-pointer text-sm font-semibold text-amber-800 marker:text-amber-600">
                            Lihat riwayat sebelumnya ({{ $previousHistories->count() }})
                        </summary>
                        <ol class="mt-4 space-y-4 border-t border-slate-200 pt-4">
                            @foreach ($previousHistories as $history)
                                @php($isPaymentProofRejected = $history->from_status === 'payment_review' && $history->to_status === 'awaiting_payment')
                                <li class="flex gap-3">
                                    <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $isPaymentProofRejected ? 'bg-rose-600' : 'bg-amber-500' }}" aria-hidden="true"></span>
                                    <div>
                                        <p class="text-sm font-medium {{ $isPaymentProofRejected ? 'text-rose-800' : 'text-slate-900' }}">{{ $isPaymentProofRejected ? 'Bukti pembayaran ditolak' : ($statusLabels[$history->to_status] ?? $history->to_status) }}</p>
                                        @if ($history->message)
                                            <p class="mt-1 text-sm text-slate-700">{{ $history->message }}</p>
                                        @elseif ($isPaymentProofRejected)
                                            <p class="mt-1 text-sm text-slate-700">Silakan unggah ulang bukti pembayaran yang jelas dan sesuai dengan total pembayaran.</p>
                                        @endif
                                        <p class="mt-1 text-xs text-slate-500">{{ $history->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </details>
                @endif
                <ol class="mt-4 space-y-4">
                    @forelse ($recentHistories as $history)
                        @php($isPaymentProofRejected = $history->from_status === 'payment_review' && $history->to_status === 'awaiting_payment')
                        <li class="flex gap-3">
                            <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $isPaymentProofRejected ? 'bg-rose-600' : 'bg-amber-500' }}" aria-hidden="true"></span>
                            <div>
                                <p class="text-sm font-medium {{ $isPaymentProofRejected ? 'text-rose-800' : 'text-slate-900' }}">{{ $isPaymentProofRejected ? 'Bukti pembayaran ditolak' : ($statusLabels[$history->to_status] ?? $history->to_status) }}</p>
                                @if ($history->message)
                                    <p class="mt-1 text-sm text-slate-700">{{ $history->message }}</p>
                                @elseif ($isPaymentProofRejected)
                                    <p class="mt-1 text-sm text-slate-700">Silakan unggah ulang bukti pembayaran yang jelas dan sesuai dengan total pembayaran.</p>
                                @endif
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

    <script>
        document.querySelectorAll('[data-copy-tracking-number]').forEach((button) => {
            button.addEventListener('click', async () => {
                const trackingNumber = button.dataset.copyTrackingNumber;
                const feedback = button.parentElement.querySelector('[data-copy-tracking-feedback]');

                try {
                    if (navigator.clipboard?.writeText) {
                        await navigator.clipboard.writeText(trackingNumber);
                    } else {
                        const copyField = document.createElement('textarea');
                        copyField.value = trackingNumber;
                        copyField.setAttribute('readonly', '');
                        copyField.style.position = 'fixed';
                        copyField.style.opacity = '0';
                        document.body.appendChild(copyField);
                        copyField.select();
                        const copied = document.execCommand('copy');
                        copyField.remove();
                        if (!copied) throw new Error('Clipboard unavailable');
                    }

                    feedback.textContent = 'Tersalin';
                    window.setTimeout(() => feedback.textContent = '', 2000);
                } catch {
                    feedback.textContent = 'Gagal menyalin';
                    window.setTimeout(() => feedback.textContent = '', 2500);
                }
            });
        });
    </script>
@endsection
