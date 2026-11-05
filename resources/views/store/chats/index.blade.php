@extends('layouts.store')

@section('title', 'Percakapan Saya - Sinergi Store')

@section('content')
    <section class="mx-auto max-w-5xl px-6 py-10 lg:px-8 lg:py-14">
        <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Bantuan pembeli</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Percakapan saya</h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">Lihat balasan admin dan lanjutkan pertanyaanmu tentang produk.</p>
            </div>
            <a href="{{ route('store.products.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-amber-400 hover:text-amber-800">Cari produk</a>
        </div>

        @if ($conversations->isEmpty())
            <div class="rounded-3xl border border-slate-200 bg-white px-6 py-14 text-center shadow-sm">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-800" aria-hidden="true">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20.5 11.5a8.5 8.5 0 0 1-12.7 7.4L3 20l1.2-4.3A8.5 8.5 0 1 1 20.5 11.5Z"/><path d="M8.5 11.5h.01M12 11.5h.01M15.5 11.5h.01"/></svg>
                </div>
                <h2 class="mt-5 text-lg font-semibold text-slate-900">Belum ada percakapan</h2>
                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600">Mulai chat dari halaman produk untuk bertanya tentang stok atau detail produk.</p>
                <a href="{{ route('store.products.index') }}" class="mt-6 inline-flex min-h-11 items-center justify-center rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">Jelajahi produk</a>
            </div>
        @else
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="divide-y divide-slate-100">
                    @foreach ($conversations as $conversation)
                        @php
                            $statusLabel = match ($conversation->status) {
                                'waiting_admin' => 'Menunggu admin',
                                'waiting_customer' => 'Menunggu balasanmu',
                                'closed' => 'Selesai',
                                default => 'Aktif',
                            };
                            $statusStyle = match ($conversation->status) {
                                'waiting_admin' => 'bg-amber-100 text-amber-800',
                                'waiting_customer' => 'bg-sky-100 text-sky-800',
                                'closed' => 'bg-slate-100 text-slate-600',
                                default => 'bg-emerald-100 text-emerald-800',
                            };
                        @endphp
                        <a href="{{ route('account.chats.show', $conversation) }}" class="flex items-center gap-4 px-5 py-4 transition hover:bg-slate-50 sm:px-6">
                            @if ($conversation->product?->image)
                                <img src="{{ asset('storage/' . $conversation->product->image) }}" alt="" class="h-14 w-14 shrink-0 rounded-2xl bg-slate-100 object-cover">
                            @else
                                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-sm font-bold text-amber-800" aria-hidden="true">SS</span>
                            @endif
                            <span class="min-w-0 flex-1">
                                <span class="flex flex-wrap items-center gap-2">
                                    <span class="truncate font-semibold text-slate-900">{{ $conversation->product?->name ?? $conversation->product_name }}</span>
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusStyle }}">{{ $statusLabel }}</span>
                                </span>
                                @if ($conversation->variant_label)
                                    <span class="mt-1 block truncate text-xs text-slate-500">{{ $conversation->variant_label }}</span>
                                @endif
                                <span class="mt-1 block truncate text-sm {{ $conversation->unread_messages_count > 0 ? 'font-semibold text-slate-900' : 'text-slate-500' }}">{{ $conversation->latestMessage?->body ?? 'Belum ada pesan.' }}</span>
                            </span>
                            <span class="flex shrink-0 flex-col items-end gap-2">
                                @if ($conversation->last_message_at)
                                    <span class="text-xs text-slate-400">{{ $conversation->last_message_at->timezone('Asia/Jakarta')->format('d M, H:i') }}</span>
                                @endif
                                @if ($conversation->unread_messages_count > 0)
                                    <span class="inline-flex min-w-6 items-center justify-center rounded-full bg-amber-500 px-2 py-0.5 text-xs font-bold text-slate-950" aria-label="{{ $conversation->unread_messages_count }} pesan baru">{{ $conversation->unread_messages_count }}</span>
                                @endif
                            </span>
                        </a>
                    @endforeach
                </div>
                <div class="border-t border-slate-200 px-5 py-4 sm:px-6">{{ $conversations->links() }}</div>
            </div>
        @endif
    </section>
@endsection
