@extends('layouts.store')

@section('title', $product->name . ' - Sinergi Store')

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
                <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="text-lg font-semibold text-slate-900">Pesan produk</h2>
                    <p class="mt-1 text-sm text-slate-500">Pilih opsi dan jumlah, lalu lanjutkan konfirmasi melalui WhatsApp.</p>

                    @if ($errors->any())
                        <div class="mt-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                            <ul class="list-inside list-disc space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('store.products.order', $product) }}" class="mt-5 space-y-4">
                        @csrf

                        @if ($product->variants->isNotEmpty())
                            <div>
                                <label for="variant_id" class="mb-2 block text-sm font-medium text-slate-700">Ukuran / warna</label>
                                <select id="variant_id" name="variant_id" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                                    <option value="">Pilih varian</option>
                                    @foreach ($product->variants as $variant)
                                        @php
                                            $variantName = collect([$variant->size, $variant->color])->filter()->implode(' · ');
                                        @endphp
                                        <option value="{{ $variant->id }}" data-stock="{{ $variant->stock }}" @selected((string) old('variant_id') === (string) $variant->id) @disabled($variant->stock < 1)>
                                            {{ $variantName }}{{ $variant->stock < 1 ? ' · Stok habis' : ' · Stok ' . $variant->stock }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('variant_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <div>
                            <label for="quantity" class="mb-2 block text-sm font-medium text-slate-700">Jumlah</label>
                            <input id="quantity" name="quantity" type="number" min="1" max="{{ $product->variants->isEmpty() ? $product->stock : 1 }}" value="{{ old('quantity', 1) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                            <p data-available-stock class="mt-2 text-sm text-slate-500">
                                @if ($product->variants->isNotEmpty())
                                    Pilih varian untuk melihat stok tersedia.
                                @else
                                    Stok tersedia: <strong>{{ $product->stock }}</strong>
                                @endif
                            </p>
                            @error('quantity')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" data-order-button @disabled(! $hasAvailableStock || $product->variants->isNotEmpty()) class="inline-flex w-full items-center justify-center rounded-xl bg-amber-500 px-5 py-3 font-semibold text-slate-950 transition hover:bg-amber-400 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500">
                            {{ ! $hasAvailableStock ? 'Stok sedang habis' : ($product->variants->isNotEmpty() ? 'Pilih varian untuk pesan' : 'Pesan via WhatsApp') }}
                        </button>
                        <p class="text-xs leading-5 text-slate-500">Pesanan belum dibayar. Admin akan membantu konfirmasi stok, ongkir, dan pembayaran melalui WhatsApp.</p>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script>
        const orderVariantSelect = document.getElementById('variant_id');
        const orderQuantityInput = document.getElementById('quantity');
        const orderStockMessage = document.querySelector('[data-available-stock]');
        const orderButton = document.querySelector('[data-order-button]');
        const productStock = @json((int) $product->stock);

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
            orderButton.textContent = availableStock > 0 ? 'Pesan via WhatsApp' : 'Stok sedang habis';
        };

        orderVariantSelect?.addEventListener('change', updateOrderStock);
        updateOrderStock();
    </script>
@endsection
