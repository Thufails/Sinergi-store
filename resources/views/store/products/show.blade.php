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
                    <p class="mt-1 text-sm text-slate-500">Pilih alamat tujuan dan jumlah produk. Ongkir akan dihitung admin sebelum pembayaran.</p>
                    @auth('customer')
                        <a href="{{ route('store.products.checkout', $product) }}" @class(['mt-4 inline-flex min-h-10 w-full items-center justify-center rounded-lg px-4 py-2.5 text-sm font-semibold transition', 'bg-amber-500 text-slate-950 hover:bg-amber-400' => $hasAvailableStock, 'pointer-events-none cursor-not-allowed bg-slate-300 text-slate-500' => ! $hasAvailableStock]) aria-disabled="{{ $hasAvailableStock ? 'false' : 'true' }}">
                            {{ $hasAvailableStock ? 'Pesan produk' : 'Stok sedang habis' }}
                        </a>
                    @else
                        <a href="{{ route('account.login') }}" class="mt-4 inline-flex min-h-10 w-full items-center justify-center rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">Masuk untuk memesan</a>
                    @endauth
                    <button type="button" data-share-product class="mt-2 inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-amber-400 hover:bg-amber-50 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.7 10.7 6.6-4.4M8.7 13.3l6.6 4.4"/></svg>
                        Bagikan produk
                    </button>
                    <p data-share-feedback class="mt-1 hidden text-center text-xs text-slate-500" role="status" aria-live="polite"></p>
                    @if (! $hasAvailableStock)
                        <p class="mt-3 text-center text-xs text-slate-500">Produk ini sedang tidak tersedia.</p>
                    @endif
                </div>
            </div>
        </div>

        <section class="mt-12 border-t border-slate-200 pt-10" aria-labelledby="product-reviews-heading">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">Pengalaman pembeli</p>
                    <h2 id="product-reviews-heading" class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Ulasan produk</h2>
                </div>
                @if ($reviewCount > 0)
                    <div class="text-right">
                        <p class="text-2xl font-bold text-slate-950">{{ number_format($averageRating, 1) }} <span class="text-amber-500">★</span></p>
                        <p class="text-sm text-slate-500">{{ $reviewCount }} ulasan</p>
                    </div>
                @endif
            </div>

            @if ($productReviews->isEmpty())
                <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center">
                    <p class="font-medium text-slate-800">Belum ada ulasan untuk produk ini.</p>
                    <p class="mt-1 text-sm text-slate-500">Ulasan pembeli akan muncul di sini setelah pesanan diterima.</p>
                </div>
            @else
                <div class="mt-5 divide-y divide-slate-200 rounded-2xl border border-slate-200 bg-white px-5 sm:px-6">
                    @foreach ($productReviews as $review)
                        <article class="py-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $review->customer?->name ?? 'Pembeli Sinergi Store' }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">Pembeli terverifikasi · {{ $review->created_at->timezone('Asia/Jakarta')->format('d M Y') }}</p>
                                </div>
                                <div class="flex gap-0.5 text-amber-500" aria-label="Rating {{ $review->rating }} dari 5">
                                    @for ($star = 1; $star <= 5; $star++)<span aria-hidden="true">{{ $star <= $review->rating ? '★' : '☆' }}</span>@endfor
                                </div>
                            </div>
                            @if ($review->comment)<p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $review->comment }}</p>@endif
                            @if ($review->media_paths)
                                <div class="mt-4 flex flex-wrap gap-3">
                                    @foreach ($review->media_paths as $mediaPath)
                                        @php($mediaExtension = strtolower(pathinfo($mediaPath, PATHINFO_EXTENSION)))
                                        @if (in_array($mediaExtension, ['mp4', 'mov', 'webm'], true))
                                            <video controls preload="metadata" class="max-h-72 max-w-full rounded-xl border border-slate-200" aria-label="Video ulasan pembeli"><source src="{{ asset('storage/' . $mediaPath) }}"></video>
                                        @else
                                            <a href="{{ asset('storage/' . $mediaPath) }}" target="_blank" rel="noopener noreferrer" class="block">
                                                <img src="{{ asset('storage/' . $mediaPath) }}" alt="Foto ulasan dari {{ $review->customer?->name ?? 'pembeli' }}" loading="lazy" class="h-24 w-24 rounded-xl border border-slate-200 object-cover sm:h-28 sm:w-28">
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </section>

    <script>
        const shareProductButton = document.querySelector('[data-share-product]');
        const shareFeedback = document.querySelector('[data-share-feedback]');
        const productShareData = {
            title: @json($product->name . ' - Sinergi Store'),
            text: 'Lihat produk ini di Sinergi Store.',
            url: document.querySelector('link[rel="canonical"]')?.href || window.location.href,
        };
        const setShareFeedback = (message) => {
            shareFeedback.textContent = message;
            shareFeedback.classList.remove('hidden');
        };

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
    </script>
@endsection
