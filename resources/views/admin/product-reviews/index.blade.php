@extends('layouts.admin')

@section('title', 'Ulasan Produk - Sinergi Store')
@section('heading', 'Ulasan Produk')

@section('content')
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="text-xl font-semibold text-slate-900">Ulasan dari pembeli</h2>
            <p class="mt-1 text-sm text-slate-500">Ulasan produk yang dikirim setelah pembeli mengonfirmasi pesanan diterima.</p>
        </div>

        <form method="GET" action="{{ route('admin.product-reviews.index') }}" class="flex flex-col gap-3 border-b border-slate-200 bg-slate-50 p-5 sm:flex-row">
            @if (request()->filled('order'))
                <input type="hidden" name="order" value="{{ request('order') }}">
            @endif
            <input
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari produk, pembeli, nomor pesanan, atau komentar..."
                class="min-w-0 flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200"
            >
            <div class="flex gap-2">
                <button type="submit" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Cari</button>
                <a href="{{ route('admin.product-reviews.index', request()->filled('order') ? ['order' => request('order')] : []) }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Reset</a>
            </div>
        </form>

        @if (request()->filled('order'))
            <div class="flex flex-col gap-2 border-b border-slate-200 bg-amber-50 px-6 py-3 text-sm text-amber-900 sm:flex-row sm:items-center sm:justify-between">
                <span>Menampilkan ulasan untuk pesanan terkait.</span>
                <a href="{{ route('admin.product-reviews.index', request()->filled('search') ? ['search' => request('search')] : []) }}" class="font-semibold hover:underline">Tampilkan semua ulasan</a>
            </div>
        @endif

        @if ($reviews->isEmpty())
            <div class="px-6 py-16 text-center">
                <p class="font-medium text-slate-700">{{ request()->filled('order') ? 'Belum ada ulasan untuk pesanan ini.' : (request()->filled('search') ? 'Ulasan tidak ditemukan.' : 'Belum ada ulasan produk.') }}</p>
                <p class="mt-1 text-sm text-slate-500">Ulasan akan tercatat di sini setelah pembeli mengirimkannya.</p>
            </div>
        @else
            <div class="divide-y divide-slate-200">
                @foreach ($reviews as $review)
                    <article class="px-6 py-5">
                        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                            <div class="min-w-0">
                                <h3 class="font-semibold text-slate-900">{{ $review->product?->name ?? 'Produk telah dihapus' }}</h3>
                                <p class="mt-1 text-sm text-slate-500">
                                    Oleh {{ $review->customer?->name ?? 'Pembeli' }}
                                    <span aria-hidden="true">·</span>
                                    {{ $review->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                </p>
                            </div>
                            <div class="flex shrink-0 items-center gap-1 text-amber-500" aria-label="Rating {{ $review->rating }} dari 5">
                                @for ($star = 1; $star <= 5; $star++)
                                    <span class="text-lg leading-none {{ $star <= $review->rating ? 'text-amber-500' : 'text-slate-300' }}" aria-hidden="true">★</span>
                                @endfor
                                <span class="ml-1 text-sm font-semibold text-slate-700">{{ $review->rating }}/5</span>
                            </div>
                        </div>

                        @if ($review->comment)
                            <p class="mt-4 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $review->comment }}</p>
                        @endif

                        @if ($review->media_paths)
                            <div class="mt-4 flex flex-wrap gap-3">
                                @foreach ($review->media_paths as $mediaPath)
                                    @php($mediaUrl = asset('storage/' . $mediaPath))
                                    @php($mediaExtension = strtolower(pathinfo($mediaPath, PATHINFO_EXTENSION)))
                                    @if (in_array($mediaExtension, ['mp4', 'mov', 'webm'], true))
                                        <video controls preload="metadata" class="max-h-56 max-w-full rounded-xl border border-slate-200 bg-slate-900 sm:max-w-xs">
                                            <source src="{{ $mediaUrl }}">
                                            Browser tidak mendukung pemutaran video.
                                        </video>
                                    @else
                                        <a href="{{ $mediaUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Buka foto ulasan produk">
                                            <img src="{{ $mediaUrl }}" alt="Foto ulasan {{ $review->product?->name ?? 'produk' }}" loading="lazy" class="h-32 w-32 rounded-xl border border-slate-200 object-cover transition hover:opacity-90">
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-slate-500">
                            @if ($review->orderItem?->order)
                                <a href="{{ route('admin.orders.show', $review->orderItem->order) }}" class="font-semibold text-amber-700 hover:text-amber-800 hover:underline">
                                    Pesanan {{ $review->orderItem->order->order_number }}
                                </a>
                            @endif
                            <span>Ulasan tampil otomatis setelah dikirim pembeli.</span>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="border-t border-slate-200 px-6 py-4">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
@endsection
