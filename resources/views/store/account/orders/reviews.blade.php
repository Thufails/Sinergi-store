@extends('layouts.store')

@section('title', 'Ulasan Pesanan ' . $order->order_number . ' - Sinergi Store')

@section('content')
    @php
        $itemsToReview = $order->items->whereNotNull('product_id')->filter(fn ($item) => ! $item->review);
    @endphp

    <section class="mx-auto max-w-4xl px-6 py-10 lg:px-8 lg:py-14">
        <a href="{{ route('account.orders.show', $order) }}" class="text-sm font-medium text-slate-600 transition hover:text-amber-700">← Kembali ke detail pesanan</a>

        <header class="mt-6">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">Ulasan pembeli</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Ceritakan pengalamanmu</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Pesanan {{ $order->order_number }} · Rating dan ulasan yang dikirim langsung tampil di halaman produk.</p>
        </header>

        @if (session('success'))
            <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        @if ($itemsToReview->isEmpty())
            <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="font-semibold text-slate-900">Semua produk sudah kamu ulas</h2>
                <p class="mt-1 text-sm text-slate-600">Terima kasih sudah berbagi pengalaman dengan pembeli lain.</p>
                <a href="{{ route('account.orders.show', $order) }}" class="mt-4 inline-flex min-h-10 items-center justify-center rounded-xl bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">Kembali ke pesanan</a>
            </div>
        @else
            <form method="POST" action="{{ route('account.orders.reviews.store', $order) }}" enctype="multipart/form-data" class="mt-6 space-y-5">
                @csrf
                @foreach ($order->items->whereNotNull('product_id') as $item)
                    <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 bg-slate-50 px-5 py-4 sm:px-6">
                            <h2 class="font-semibold text-slate-900">{{ $item->product?->name ?? $item->product_name }}</h2>
                            <p class="mt-1 text-sm text-slate-600">{{ $item->variant_name ?: 'Tanpa varian' }} · {{ $item->quantity }} unit</p>
                        </div>

                        @if ($item->review)
                            <div class="px-5 py-5 sm:px-6">
                                <p class="text-sm font-medium text-emerald-800">Produk ini sudah kamu ulas</p>
                                <div class="mt-2 flex gap-1 text-amber-500" aria-label="Rating {{ $item->review->rating }} dari 5">
                                    @for ($star = 1; $star <= 5; $star++)<span aria-hidden="true" class="text-lg">{{ $star <= $item->review->rating ? '★' : '☆' }}</span>@endfor
                                </div>
                                @if ($item->review->comment)<p class="mt-2 text-sm leading-6 text-slate-700">{{ $item->review->comment }}</p>@endif
                            </div>
                        @else
                            <div class="space-y-5 px-5 py-5 sm:px-6">
                                <fieldset>
                                    <legend class="text-sm font-semibold text-slate-800">Rating <span class="text-rose-600" aria-hidden="true">*</span></legend>
                                    <div class="mt-2 flex flex-row-reverse justify-end gap-1" data-star-rating role="radiogroup" aria-label="Pilih rating untuk {{ $item->product?->name ?? $item->product_name }}">
                                        @for ($star = 5; $star >= 1; $star--)
                                            <input id="rating-{{ $item->id }}-{{ $star }}" type="radio" name="reviews[{{ $item->id }}][rating]" value="{{ $star }}" @checked((int) old("reviews.{$item->id}.rating") === $star) required class="peer sr-only" data-star-input>
                                            <label for="rating-{{ $item->id }}-{{ $star }}" data-star-label="{{ $star }}" class="cursor-pointer text-3xl leading-none text-slate-300 transition hover:text-amber-400 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-amber-500" title="{{ $star }} bintang">★</label>
                                        @endfor
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">Pilih 1 sampai 5 bintang. Rating wajib, komentar dan media opsional.</p>
                                </fieldset>

                                <label class="block text-sm font-semibold text-slate-800" for="review-comment-{{ $item->id }}">Komentar <span class="font-normal text-slate-500">(opsional)</span>
                                    <textarea id="review-comment-{{ $item->id }}" name="reviews[{{ $item->id }}][comment]" rows="3" maxlength="2000" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-normal text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200" placeholder="Apa yang kamu suka dari produk ini?">{{ old("reviews.{$item->id}.comment") }}</textarea>
                                </label>

                                <label class="block text-sm font-semibold text-slate-800" for="review-media-{{ $item->id }}">Foto atau video <span class="font-normal text-slate-500">(opsional, maksimal 5 file)</span>
                                    <input id="review-media-{{ $item->id }}" type="file" name="reviews[{{ $item->id }}][media][]" accept=".jpg,.jpeg,.png,.webp,.mp4,.mov,.webm,image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm" multiple class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-amber-100 file:px-3 file:py-2 file:font-semibold file:text-amber-800">
                                </label>
                                <p class="text-xs text-slate-500">Format: JPG, PNG, WEBP, MP4, MOV, atau WEBM. Maksimal 20 MB per file.</p>
                            </div>
                        @endif
                    </article>
                @endforeach

                @if ($itemsToReview->isNotEmpty())
                    <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
                        <a href="{{ route('account.orders.show', $order) }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Nanti saja</a>
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">Kirim ulasan</button>
                    </div>
                @endif
            </form>
        @endif
    </section>

    <script>
        document.querySelectorAll('[data-star-rating]').forEach((ratingGroup) => {
            const inputs = [...ratingGroup.querySelectorAll('[data-star-input]')];
            const labels = [...ratingGroup.querySelectorAll('[data-star-label]')];
            const updateStars = () => {
                const selectedRating = Number(inputs.find((input) => input.checked)?.value ?? 0);

                labels.forEach((label) => {
                    const isHighlighted = Number(label.dataset.starLabel) <= selectedRating;
                    label.classList.toggle('text-amber-500', isHighlighted);
                    label.classList.toggle('text-slate-300', !isHighlighted);
                });
            };

            inputs.forEach((input) => input.addEventListener('change', updateStars));
            updateStars();
        });
    </script>
@endsection
