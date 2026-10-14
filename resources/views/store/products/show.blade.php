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
                <p class="mt-6 text-sm text-slate-600">Stok tersedia: <strong>{{ $product->stock }}</strong></p>

                <a href="https://wa.me/6282142612382?text={{ urlencode('Halo, saya tertarik dengan produk ' . $product->name) }}" target="_blank" rel="noopener noreferrer" class="mt-8 inline-flex rounded-xl bg-amber-500 px-5 py-3 font-semibold transition hover:bg-amber-400">Tanya produk via WhatsApp</a>
            </div>
        </div>
    </section>
@endsection
