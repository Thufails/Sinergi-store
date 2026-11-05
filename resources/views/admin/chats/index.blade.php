@extends('layouts.admin')

@section('title', 'Chat Pembeli - Sinergi Store')
@section('heading', 'Chat pembeli')
@section('header-eyebrow', 'Layanan pelanggan')

@section('content')
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 px-6 py-5">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Inbox percakapan</h2>
                <p class="mt-1 text-sm text-slate-500">Pertanyaan pembeli tentang produk akan muncul di sini.</p>
            </div>
            <span class="rounded-full bg-amber-100 px-3 py-1.5 text-sm font-semibold text-amber-900" data-admin-unread-count>{{ $unreadConversationCount }} belum dibaca</span>
        </div>

        <nav class="flex gap-2 border-b border-slate-200 px-5 py-3 sm:px-6" aria-label="Filter percakapan">
            <a href="{{ route('admin.chats.index', ['view' => 'active']) }}" @class(['rounded-lg px-3 py-2 text-sm font-semibold transition', 'bg-amber-100 text-amber-900' => $selectedView === 'active', 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => $selectedView !== 'active']) @if ($selectedView === 'active') aria-current="page" @endif>
                Percakapan aktif
            </a>
            <a href="{{ route('admin.chats.index', ['view' => 'closed']) }}" @class(['rounded-lg px-3 py-2 text-sm font-semibold transition', 'bg-amber-100 text-amber-900' => $selectedView === 'closed', 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => $selectedView !== 'closed']) @if ($selectedView === 'closed') aria-current="page" @endif>
                Selesai
            </a>
        </nav>

        @if ($conversations->isEmpty())
            <div class="px-6 py-16 text-center">
                @if ($selectedView === 'closed')
                    <p class="font-medium text-slate-700">Belum ada percakapan yang selesai.</p>
                    <p class="mt-1 text-sm text-slate-500">Percakapan yang ditutup akan tersimpan di sini.</p>
                @else
                    <p class="font-medium text-slate-700">Belum ada chat dari pembeli.</p>
                    <p class="mt-1 text-sm text-slate-500">Percakapan baru dari halaman produk akan muncul di inbox ini.</p>
                @endif
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach ($conversations as $conversation)
                    @php
                        $statusLabel = match ($conversation->status) {
                            'waiting_admin' => 'Perlu dibalas',
                            'waiting_customer' => 'Menunggu pembeli',
                            'closed' => 'Selesai',
                            default => 'Percakapan aktif',
                        };
                        $statusStyle = match ($conversation->status) {
                            'waiting_admin' => 'bg-rose-100 text-rose-800',
                            'waiting_customer' => 'bg-sky-100 text-sky-800',
                            'closed' => 'bg-slate-100 text-slate-600',
                            default => 'bg-amber-100 text-amber-800',
                        };
                    @endphp
                    <a href="{{ route('admin.chats.show', $conversation) }}" class="flex flex-wrap items-center gap-4 px-5 py-4 transition hover:bg-slate-50 sm:px-6">
                        @if ($conversation->product?->image)
                            <img src="{{ asset('storage/' . $conversation->product->image) }}" alt="" class="h-12 w-12 shrink-0 rounded-xl bg-slate-100 object-cover">
                        @else
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-sm font-bold text-amber-800" aria-hidden="true">SS</span>
                        @endif
                        <span class="min-w-0 flex-1">
                            <span class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold text-slate-900">{{ $conversation->customer?->name ?? 'Tamu' }}</span>
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusStyle }}">{{ $statusLabel }}</span>
                            </span>
                            <span class="mt-1 block truncate text-sm text-slate-600">{{ $conversation->product?->name ?? $conversation->product_name }}</span>
                            <span class="mt-1 block truncate text-sm {{ $conversation->unread_messages_count > 0 ? 'font-semibold text-slate-900' : 'text-slate-500' }}">
                                {{ $conversation->latestMessage?->body ?? 'Belum ada pesan.' }}
                            </span>
                        </span>
                        <span class="flex shrink-0 items-center gap-3 sm:flex-col sm:items-end sm:gap-1">
                            @if ($conversation->last_message_at)
                                <span class="text-xs text-slate-400">{{ $conversation->last_message_at->timezone('Asia/Jakarta')->format('d M, H:i') }}</span>
                            @endif
                            @if ($conversation->unread_messages_count > 0)
                                <span class="inline-flex min-w-6 items-center justify-center rounded-full bg-rose-500 px-2 py-0.5 text-xs font-bold text-white" aria-label="{{ $conversation->unread_messages_count }} pesan belum dibaca">{{ $conversation->unread_messages_count }}</span>
                            @endif
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="border-t border-slate-200 px-6 py-4">{{ $conversations->links() }}</div>
        @endif
    </section>
@endsection
