@extends('layouts.store')

@section('title', 'Checkout ' . $product->name . ' - Sinergi Store')

@section('content')
    @php
        $selectedAddressId = old('address_id', $addresses->firstWhere('is_default', true)?->id ?? $addresses->first()?->id);
        $initialStock = $product->variants->isEmpty()
            ? (int) $product->available_stock
            : (int) ($product->variants->firstWhere('id', old('variant_id'))?->available_stock ?? 0);
    @endphp

    <section class="mx-auto max-w-6xl px-6 py-10 lg:px-8 lg:py-14">
        <a href="{{ route('store.products.show', $product) }}" class="text-sm font-medium text-slate-600 transition hover:text-amber-700">← Kembali ke produk</a>
        <div class="mt-5 mb-7">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Checkout</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Buat pesanan</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Pilih alamat penerima. Admin akan menghitung ongkir dan memperbarui total sebelum pembayaran.</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                <p class="font-semibold">Periksa kembali pesananmu:</p>
                <ul class="mt-1 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('store.products.order', $product) }}" class="grid gap-6 lg:grid-cols-[1fr_360px]">
            @csrf
            <input type="hidden" name="submission_token" value="{{ old('submission_token', $submissionToken) }}">

            <div class="space-y-6">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="address-heading">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 id="address-heading" class="text-lg font-semibold text-slate-900">Alamat penerima</h2>
                            <p class="mt-1 text-sm text-slate-500">Alamat ini akan disimpan sebagai snapshot pesanan.</p>
                        </div>
                        <a href="{{ route('account.addresses.create') }}" class="text-sm font-semibold text-amber-700 hover:text-amber-800">+ Tambah alamat</a>
                    </div>

                    @if ($addresses->isEmpty())
                        <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <p class="text-sm font-semibold text-amber-900">Belum ada alamat tersimpan</p>
                            <p class="mt-1 text-sm text-amber-800">Tambahkan alamat tujuan terlebih dahulu untuk melanjutkan pesanan.</p>
                            <a href="{{ route('account.addresses.create') }}" class="mt-3 inline-flex min-h-10 items-center rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-400">Tambah alamat</a>
                        </div>
                    @else
                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            @foreach ($addresses as $address)
                                <label class="relative flex cursor-pointer gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-amber-300 hover:bg-amber-50/40">
                                    <input type="radio" name="address_id" value="{{ $address->id }}" @checked((string) $selectedAddressId === (string) $address->id) required class="mt-1 h-4 w-4 shrink-0 border-slate-300 text-amber-600 focus:ring-amber-500">
                                    <span class="min-w-0 text-sm">
                                        <span class="flex flex-wrap items-center gap-2 font-semibold text-slate-900">{{ $address->label }} @if ($address->is_default)<span class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800">Utama</span>@endif</span>
                                        <span class="mt-1 block font-medium text-slate-800">{{ $address->recipient_name }} · {{ $address->recipient_phone }}</span>
                                        <span class="mt-1 block leading-5 text-slate-600">{{ $address->address_line }}, {{ $address->district }}, {{ $address->city }}, {{ $address->province }} {{ $address->postal_code }}</span>
                                        @if ($address->notes)<span class="mt-1 block text-xs text-slate-500">Catatan: {{ $address->notes }}</span>@endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('address_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    @endif
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="product-heading">
                    <h2 id="product-heading" class="text-lg font-semibold text-slate-900">Produk yang dipesan</h2>
                    <div class="mt-4 flex gap-4">
                        @if ($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-20 w-20 rounded-xl bg-slate-100 object-cover">
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-slate-900">{{ $product->name }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $product->category?->name }}</p>
                            <p class="mt-2 font-semibold text-amber-700">Rp {{ number_format($product->final_price, 0, ',', '.') }} <span class="font-normal text-slate-500">/ unit</span></p>
                        </div>
                    </div>

                    @if ($product->variants->isNotEmpty())
                        <div class="mt-5">
                            <label for="variant_id" class="mb-2 block text-sm font-medium text-slate-700">Ukuran / warna</label>
                            <select id="variant_id" name="variant_id" required data-variant-select class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                                <option value="">Pilih varian</option>
                                @foreach ($product->variants as $variant)
                                    @php($variantName = collect([$variant->size, $variant->color])->filter()->implode(' · '))
                                    <option value="{{ $variant->id }}" data-stock="{{ $variant->available_stock }}" @selected((string) old('variant_id') === (string) $variant->id) @disabled($variant->available_stock < 1)>{{ $variantName }}{{ $variant->available_stock < 1 ? ' · Stok habis' : ' · Tersedia ' . $variant->available_stock }}</option>
                                @endforeach
                            </select>
                            @error('variant_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    @endif

                    <div class="mt-5 max-w-xs">
                        <label for="quantity" class="mb-2 block text-sm font-medium text-slate-700">Jumlah</label>
                        <input id="quantity" name="quantity" type="number" min="1" max="{{ max($initialStock, 1) }}" value="{{ old('quantity', 1) }}" required data-quantity-input class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                        <p data-stock-message class="mt-2 text-sm text-slate-500">{{ $product->variants->isEmpty() ? 'Stok tersedia: ' . $product->available_stock : 'Pilih varian untuk melihat stok.' }}</p>
                        @error('quantity')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </section>
            </div>

            <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 lg:sticky lg:top-24">
                <h2 class="text-lg font-semibold text-slate-900">Ringkasan pesanan</h2>
                <div class="mt-5 flex items-center justify-between gap-4 text-sm text-slate-600"><span>Subtotal produk</span><span data-subtotal data-unit-price="{{ $product->final_price }}" class="font-semibold text-slate-900">Rp {{ number_format($product->final_price, 0, ',', '.') }}</span></div>
                <div class="mt-3 flex items-center justify-between gap-4 text-sm text-slate-600"><span>Ongkir</span><span class="font-medium text-slate-700">Dihitung admin</span></div>
                <div class="mt-4 border-t border-slate-200 pt-4">
                    <p class="text-xs leading-5 text-slate-500">Admin akan memeriksa alamat, menghitung ongkir, lalu menampilkan total pembayaran final di Pesanan Saya. Kamu dapat membayar dan mengunggah bukti setelah total dikonfirmasi.</p>
                </div>
                <button type="submit" data-submit-order @disabled($addresses->isEmpty() || ! $product->variants->isEmpty() || $initialStock < 1) class="mt-5 inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-400 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500">
                    {{ $addresses->isEmpty() ? 'Tambahkan alamat dahulu' : ($initialStock < 1 ? 'Stok sedang habis' : 'Buat pesanan') }}
                </button>
                <p class="mt-3 text-center text-xs text-slate-500">Setelah dibuat, pesanan dapat dipantau di halaman Pesanan Saya.</p>
            </aside>
        </form>
    </section>

    <script>
        (() => {
            const variantSelect = document.querySelector('[data-variant-select]');
            const quantityInput = document.querySelector('[data-quantity-input]');
            const stockMessage = document.querySelector('[data-stock-message]');
            const subtotal = document.querySelector('[data-subtotal]');
            const submitButton = document.querySelector('[data-submit-order]');
            if (!quantityInput || !stockMessage || !subtotal || !submitButton) return;

            const unitPrice = Number(subtotal.dataset.unitPrice || 0);
            const baseStock = {{ (int) ($product->available_stock ?? 0) }};
            const formatRupiah = (amount) => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(amount);
            const updateSummary = () => {
                let available = baseStock;
                if (variantSelect) {
                    const option = variantSelect.selectedOptions[0];
                    if (!option?.value) {
                        stockMessage.textContent = 'Pilih varian untuk melihat stok.';
                        submitButton.disabled = true;
                        submitButton.textContent = 'Pilih varian dahulu';
                        quantityInput.max = '1';
                        subtotal.textContent = `Rp ${formatRupiah(unitPrice * Math.max(1, Number(quantityInput.value) || 1))}`;
                        return;
                    }
                    available = Number(option.dataset.stock || 0);
                }

                stockMessage.textContent = `Stok tersedia: ${available}`;
                quantityInput.max = String(Math.max(available, 1));
                if (Number(quantityInput.value) > available && available > 0) quantityInput.value = String(available);
                submitButton.disabled = available < 1;
                submitButton.textContent = available < 1 ? 'Stok sedang habis' : 'Buat pesanan';
                subtotal.textContent = `Rp ${formatRupiah(unitPrice * Math.max(1, Number(quantityInput.value) || 1))}`;
            };

            variantSelect?.addEventListener('change', updateSummary);
            quantityInput.addEventListener('input', updateSummary);
            updateSummary();
        })();
    </script>
@endsection
