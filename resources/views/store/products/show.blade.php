@extends('layouts.store')

@section('title', $product->name . ' - Sinergi Store')
@php
    $productMetaSummary = trim(strip_tags($product->description ?? '')) ?: 'Pesan produk ini melalui katalog Sinergi Store.';
    $productMetaDescription = \Illuminate\Support\Str::limit($productMetaSummary, 105)
        . ' Harga Rp ' . number_format($product->final_price, 0, ',', '.') . ' di Sinergi Store.';
@endphp
@section('meta_description', $productMetaDescription)
@section('meta_image', $product->image ? asset('storage/' . $product->image) : asset('images/logo-sinergi.png'))
@section('meta_image_alt', $product->name)
@section('twitter_card', 'summary_large_image')

@section('content')
    <section class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <a href="{{ route('store.products.index') }}" class="text-sm text-slate-500 transition hover:text-amber-600">← Kembali ke produk</a>

        <div class="mt-8 grid gap-10 lg:grid-cols-2">
            <div class="overflow-hidden rounded-2xl bg-slate-100">
                @if ($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="max-h-[600px] w-full object-contain">
                @else
                    <div class="flex min-h-[400px] items-center justify-center text-slate-400">Tidak ada gambar</div>
                @endif
            </div>

            <div class="py-4">
                <p class="font-semibold text-amber-600">{{ $product->category?->name }}</p>
                <h1 class="mt-2 text-4xl font-semibold tracking-tight">{{ $product->name }}</h1>

                <div class="mt-6">
                    @if ((float) $product->discount_value > 0)
                        <p class="text-3xl font-bold text-amber-600">Rp {{ number_format($product->final_price, 0, ',', '.') }}</p>
                        <p class="mt-1 text-slate-400 line-through">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</p>
                    @else
                        <p class="text-3xl font-bold">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</p>
                    @endif
                </div>

                <p class="mt-6 leading-7 text-slate-600">{{ $product->description ?: 'Belum ada deskripsi produk.' }}</p>
                <div class="mt-8 max-w-md rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <h2 class="text-lg font-semibold text-slate-900">Tertarik dengan produk ini?</h2>
                    <p class="mt-1 text-sm text-slate-500">Isi detail pesanan dan lanjutkan konfirmasi melalui WhatsApp.</p>
                    <button type="button" data-open-order-dialog @disabled(! $hasAvailableStock) class="mt-4 inline-flex min-h-10 w-full items-center justify-center rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500">
                        {{ $hasAvailableStock ? 'Pesan produk' : 'Stok sedang habis' }}
                    </button>
                    <button type="button" data-share-product class="mt-2 inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-amber-400 hover:bg-amber-50 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="18" cy="5" r="3" />
                            <circle cx="6" cy="12" r="3" />
                            <circle cx="18" cy="19" r="3" />
                            <path d="m8.7 10.7 6.6-4.4M8.7 13.3l6.6 4.4" />
                        </svg>
                        Bagikan produk
                    </button>
                    <p data-share-feedback class="mt-1 hidden text-center text-xs text-slate-500" role="status" aria-live="polite"></p>
                    @if (! $hasAvailableStock)
                        <p class="mt-3 text-center text-xs text-slate-500">Produk ini sedang tidak tersedia.</p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <dialog id="order-dialog" aria-labelledby="order-dialog-title" class="m-auto max-h-[90vh] w-[calc(100%-2rem)] max-w-xl overflow-hidden rounded-2xl p-0 shadow-2xl backdrop:bg-slate-950/60 md:translate-x-12 lg:translate-x-24">
        <div class="max-h-[90vh] overflow-y-auto bg-white">
            <div class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-slate-200 bg-white px-5 py-4 sm:px-6">
                <div>
                    <p class="text-sm font-semibold text-amber-600">Pesanan produk</p>
                    <h2 id="order-dialog-title" class="mt-1 text-xl font-semibold text-slate-900">{{ $product->name }}</h2>
                    <p class="mt-1 text-sm text-slate-500">Lengkapi informasi berikut untuk melanjutkan.</p>
                </div>
                <button type="button" data-close-order-dialog aria-label="Tutup form pesanan" class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" /></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('store.products.order', $product) }}" class="space-y-4 p-5 sm:p-6">
                @csrf

                @if ($errors->any())
                    <div class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                        <p class="font-semibold">Periksa kembali data pesanan:</p>
                        <ul class="mt-1 list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($product->variants->isNotEmpty())
                    <div>
                        <label for="variant_id" class="mb-2 block text-sm font-medium text-slate-700">Ukuran / warna</label>
                        <select id="variant_id" name="variant_id" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                            <option value="">Pilih varian</option>
                            @foreach ($product->variants as $variant)
                                @php
                                    $variantName = collect([$variant->size, $variant->color])->filter()->implode(' · ');
                                @endphp
                                <option value="{{ $variant->id }}" data-stock="{{ $variant->available_stock }}" @selected((string) old('variant_id') === (string) $variant->id) @disabled($variant->available_stock < 1)>
                                    {{ $variantName }}{{ $variant->available_stock < 1 ? ' · Stok habis' : ' · Tersedia ' . $variant->available_stock }}
                                </option>
                            @endforeach
                        </select>
                        @error('variant_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div>
                    <label for="customer_name" class="mb-2 block text-sm font-medium text-slate-700">Nama pembeli</label>
                    <input id="customer_name" name="customer_name" type="text" value="{{ old('customer_name') }}" maxlength="150" autocomplete="name" required class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200" placeholder="Nama lengkap">
                    @error('customer_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="customer_phone" class="mb-2 block text-sm font-medium text-slate-700">Nomor WhatsApp</label>
                    <input id="customer_phone" name="customer_phone" type="tel" value="{{ old('customer_phone') }}" maxlength="30" autocomplete="tel" inputmode="tel" required class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200" placeholder="Contoh: 081234567890">
                    @error('customer_phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="quantity" class="mb-2 block text-sm font-medium text-slate-700">Jumlah</label>
                    <input id="quantity" name="quantity" type="number" min="1" max="{{ max($product->variants->isEmpty() ? $availableProductStock : 1, 1) }}" value="{{ old('quantity', 1) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                    <p data-available-stock class="mt-2 text-sm text-slate-500">
                        @if ($product->variants->isNotEmpty())
                            Pilih varian untuk melihat stok tersedia.
                        @else
                            Stok tersedia: <strong>{{ $availableProductStock }}</strong>
                        @endif
                    </p>
                    @error('quantity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <p class="rounded-xl bg-slate-50 px-4 py-3 text-xs leading-5 text-slate-600">Pesanan akan berstatus menunggu konfirmasi. Stok di-hold 24 jam setelah admin menyetujui; ongkir dan pembayaran dikonfirmasi melalui WhatsApp.</p>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:justify-end">
                    <button type="button" data-close-order-dialog class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Batal</button>
                    <button type="submit" data-order-button @disabled(! $hasAvailableStock || $product->variants->isNotEmpty()) class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-400 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500">
                        {{ ! $hasAvailableStock ? 'Stok sedang habis' : ($product->variants->isNotEmpty() ? 'Pilih varian untuk pesan' : 'Buat pesanan & lanjut ke WhatsApp') }}
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <script>
        const orderDialog = document.getElementById('order-dialog');
        const openOrderDialogButton = document.querySelector('[data-open-order-dialog]');
        const closeOrderDialogButtons = document.querySelectorAll('[data-close-order-dialog]');
        const orderVariantSelect = document.getElementById('variant_id');
        const orderQuantityInput = document.getElementById('quantity');
        const orderStockMessage = document.querySelector('[data-available-stock]');
        const orderButton = document.querySelector('[data-order-button]');
        const shareProductButton = document.querySelector('[data-share-product]');
        const shareFeedback = document.querySelector('[data-share-feedback]');
        const productStock = @json((int) $availableProductStock);
        const productShareData = {
            title: @json($product->name . ' - Sinergi Store'),
            text: 'Lihat produk ini di Sinergi Store.',
            url: document.querySelector('link[rel="canonical"]')?.href || window.location.href,
        };
        const setShareFeedback = (message) => {
            shareFeedback.textContent = message;
            shareFeedback.classList.remove('hidden');
        };

        openOrderDialogButton?.addEventListener('click', () => orderDialog?.showModal());
        closeOrderDialogButtons.forEach((button) => {
            button.addEventListener('click', () => orderDialog?.close());
        });
        orderDialog?.addEventListener('click', (event) => {
            if (event.target === orderDialog) orderDialog.close();
        });

        const updateOrderStock = () => {
            if (!orderQuantityInput || !orderButton) return;

            let availableStock = productStock;
            if (orderVariantSelect) {
                const selectedOption = orderVariantSelect.selectedOptions[0];
                if (!selectedOption?.value) {
                    orderStockMessage.textContent = 'Pilih varian untuk melihat stok tersedia.';
                    orderButton.disabled = true;
                    orderButton.textContent = 'Pilih varian untuk pesan';
                    orderQuantityInput.max = '1';
                    return;
                }
                availableStock = Number(selectedOption.dataset.stock || 0);
            }

            orderStockMessage.textContent = `Stok tersedia: ${availableStock}`;
            orderQuantityInput.max = String(Math.max(availableStock, 1));
            if (Number(orderQuantityInput.value) > availableStock) {
                orderQuantityInput.value = availableStock > 0 ? String(availableStock) : '';
            }
            orderButton.disabled = availableStock < 1;
            orderButton.textContent = availableStock > 0 ? 'Buat pesanan & lanjut ke WhatsApp' : 'Stok sedang habis';
        };

        orderVariantSelect?.addEventListener('change', updateOrderStock);
        updateOrderStock();

        const copyProductLink = async () => {
            try {
                if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(productShareData.url);
                } else {
                    const copyField = document.createElement('textarea');
                    copyField.value = productShareData.url;
                    copyField.setAttribute('readonly', '');
                    copyField.style.position = 'fixed';
                    copyField.style.opacity = '0';
                    document.body.appendChild(copyField);
                    copyField.select();
                    const copied = document.execCommand('copy');
                    copyField.remove();
                    if (!copied) throw new Error('Clipboard unavailable');
                }
                setShareFeedback('Tautan produk berhasil disalin.');
            } catch {
                window.prompt('Salin tautan produk ini:', productShareData.url);
                setShareFeedback('Salin tautan di atas untuk membagikan produk.');
            }
        };

        shareProductButton?.addEventListener('click', async () => {
            shareFeedback.textContent = '';
            shareFeedback.classList.add('hidden');

            if (navigator.share) {
                try {
                    await navigator.share(productShareData);
                    return;
                } catch (error) {
                    if (error.name === 'AbortError') return;
                }
            }

            await copyProductLink();
        });

        if (@json($errors->any())) orderDialog?.showModal();
    </script>
@endsection
