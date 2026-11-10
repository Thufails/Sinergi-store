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
                    <button type="button" data-product-chat-open aria-expanded="false" class="mt-2 inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-lg border border-amber-500 px-4 py-2.5 text-sm font-semibold text-amber-800 transition hover:bg-amber-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.4 8.4 0 0 1-12.5 7.3L3 20l1.2-4.2A8.5 8.5 0 1 1 21 11.5Z"/><path d="M8 12h.01M12 12h.01M16 12h.01"/></svg>
                        Tanya produk lewat chat
                    </button>
                    @if (! $hasAvailableStock)
                        <p class="mt-3 text-center text-xs text-slate-500">Produk ini sedang tidak tersedia.</p>
                    @endif
                </div>
            </div>
        </div>

        <div
            class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="product-chat-title"
            aria-hidden="true"
            data-product-chat
            data-start-url="{{ route('store.products.conversations.start', $product) }}"
            data-messages-url-template="{{ route('store.product-conversations.messages', ['conversation' => '__conversation__']) }}"
            data-send-url-template="{{ route('store.product-conversations.messages.store', ['conversation' => '__conversation__']) }}"
        >
            <section class="flex h-[min(82vh,42rem)] w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
                <header class="flex items-center justify-between gap-4 border-b border-slate-200 bg-slate-50 px-5 py-4">
                    <div class="flex min-w-0 items-center gap-3">
                        @if ($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="" class="h-11 w-11 shrink-0 rounded-xl bg-white object-cover">
                        @else
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-sm font-bold text-amber-800" aria-hidden="true">SS</span>
                        @endif
                        <div class="min-w-0">
                            <h2 id="product-chat-title" class="font-semibold text-slate-900">Chat tentang produk</h2>
                            <p class="truncate text-xs text-slate-500">{{ $product->name }}</p>
                        </div>
                    </div>
                    <button type="button" data-product-chat-close class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-200 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500" aria-label="Tutup chat">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/></svg>
                    </button>
                </header>

                <div class="border-b border-slate-100 px-5 py-3">
                    <p class="text-sm font-medium text-slate-800">{{ $product->name }}</p>
                    <p class="mt-0.5 text-xs text-slate-500">Rp {{ number_format($product->final_price, 0, ',', '.') }} · Stok ditampilkan sesuai data terbaru.</p>
                </div>

                <div class="min-h-0 flex-1 space-y-3 overflow-y-auto bg-slate-50 px-4 py-4" data-product-chat-messages aria-live="polite" aria-relevant="additions text">
                    <p class="text-center text-sm text-slate-500" data-product-chat-status>Memulai chat...</p>
                    <div class="space-y-3" data-product-chat-thread></div>
                </div>

                <form class="border-t border-slate-200 bg-white p-3" data-product-chat-form>
                    @csrf
                    <div class="flex items-end gap-2">
                        <label for="product-chat-message" class="sr-only">Tulis pesan tentang produk</label>
                        <textarea id="product-chat-message" rows="1" maxlength="2000" required disabled placeholder="Tulis pertanyaanmu..." class="max-h-28 min-h-11 flex-1 resize-y rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 disabled:bg-slate-100" data-product-chat-input></textarea>
                        <button type="submit" disabled class="inline-flex h-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 px-4 text-sm font-semibold text-slate-950 transition hover:bg-amber-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" data-product-chat-submit>Kirim</button>
                    </div>
                    <p class="mt-2 px-1 text-[11px] text-slate-500">Balasan otomatis memberi info stok. Admin akan membalas pesanmu di chat ini.</p>
                </form>
            </section>
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

        const productChat = document.querySelector('[data-product-chat]');
        if (productChat) {
            const chatOpenButton = document.querySelector('[data-product-chat-open]');
            const chatCloseButton = productChat.querySelector('[data-product-chat-close]');
            const chatMessages = productChat.querySelector('[data-product-chat-messages]');
            const chatThread = productChat.querySelector('[data-product-chat-thread]');
            const chatForm = productChat.querySelector('[data-product-chat-form]');
            const chatInput = productChat.querySelector('[data-product-chat-input]');
            const chatSubmit = productChat.querySelector('[data-product-chat-submit]');
            const chatStatus = productChat.querySelector('[data-product-chat-status]');
            const csrfToken = chatForm.querySelector('input[name="_token"]').value;
            const conversationUrl = (template, id) => template.replace('__conversation__', String(id));
            let conversationId = null;
            let lastMessageId = 0;
            let pollTimer = null;
            let isPolling = false;
            let isSending = false;
            let renderedMessageIds = new Set();

            const setChatStatus = (message) => {
                chatStatus.textContent = message;
                chatStatus.classList.remove('hidden');
            };

            const appendChatMessage = (message) => {
                const messageId = Number(message.id) || 0;
                if (renderedMessageIds.has(messageId)) return;

                if (chatStatus.isConnected) chatStatus.classList.add('hidden');

                const isCustomerMessage = message.sender_type === 'customer';
                const wrapper = document.createElement('div');
                wrapper.className = isCustomerMessage ? 'ml-auto max-w-[88%]' : 'max-w-[88%]';
                wrapper.dataset.chatEntry = '';

                const sender = document.createElement('p');
                sender.className = isCustomerMessage ? 'mb-1 text-right text-[11px] font-medium text-slate-500' : 'mb-1 text-[11px] font-medium text-slate-500';
                sender.textContent = isCustomerMessage
                    ? 'Kamu'
                    : (message.sender_type === 'admin'
                        ? (message.sender_name ? `${message.sender_name} - Admin Sinergi` : 'Admin Sinergi')
                        : 'Sinergi Store');

                const bubble = document.createElement('p');
                bubble.className = isCustomerMessage
                    ? 'whitespace-pre-wrap break-words rounded-2xl rounded-tr-sm bg-amber-500 px-3.5 py-2.5 text-sm leading-6 text-slate-950'
                    : 'whitespace-pre-wrap break-words rounded-2xl rounded-tl-sm border border-slate-200 bg-white px-3.5 py-2.5 text-sm leading-6 text-slate-700';
                bubble.textContent = message.body;

                const timestamp = document.createElement('time');
                timestamp.className = isCustomerMessage ? 'mt-1 block text-right text-[10px] text-slate-400' : 'mt-1 block text-[10px] text-slate-400';
                const createdAt = new Date(message.created_at);
                timestamp.textContent = Number.isNaN(createdAt.getTime())
                    ? ''
                    : createdAt.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                wrapper.append(sender, bubble, timestamp);
                chatThread.append(wrapper);
                renderedMessageIds.add(messageId);
                lastMessageId = Math.max(lastMessageId, messageId);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            };

            const updateMessages = (items) => {
                items.forEach(appendChatMessage);
            };

            const parseResponse = async (response) => {
                const payload = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(payload.message || 'Chat belum bisa diproses. Coba lagi, ya.');
                }

                return payload;
            };

            const pollMessages = async () => {
                if (!conversationId || isPolling || document.visibilityState === 'hidden') return;

                isPolling = true;
                try {
                    const url = new URL(conversationUrl(productChat.dataset.messagesUrlTemplate, conversationId), window.location.origin);
                    url.searchParams.set('after_id', String(lastMessageId));
                    const response = await fetch(url, {
                        headers: { 'Accept': 'application/json' },
                    });
                    const payload = await parseResponse(response);
                    updateMessages(payload.messages || []);
                } catch {
                    // Polling will retry automatically on its next interval.
                } finally {
                    isPolling = false;
                }
            };

            const openProductChat = async () => {
                productChat.classList.remove('hidden');
                productChat.classList.add('flex');
                productChat.setAttribute('aria-hidden', 'false');
                chatOpenButton.setAttribute('aria-expanded', 'true');
                setChatStatus('Membuka chat dan memeriksa stok...');
                chatCloseButton.focus();

                try {
                    const response = await fetch(productChat.dataset.startUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({}),
                    });
                    const payload = await parseResponse(response);
                    conversationId = payload.conversation_id;
                    lastMessageId = 0;
                    renderedMessageIds = new Set();
                    chatThread.replaceChildren();
                    updateMessages(payload.messages || []);
                    chatInput.disabled = false;
                    chatSubmit.disabled = false;
                    chatInput.focus();

                    if (pollTimer) window.clearInterval(pollTimer);
                    pollTimer = window.setInterval(pollMessages, 5000);
                } catch (error) {
                    setChatStatus(error.message || 'Chat belum bisa dibuka. Coba tutup lalu buka lagi.');
                }
            };

            chatOpenButton.addEventListener('click', openProductChat);
            chatCloseButton.addEventListener('click', () => {
                productChat.classList.add('hidden');
                productChat.classList.remove('flex');
                productChat.setAttribute('aria-hidden', 'true');
                chatOpenButton.setAttribute('aria-expanded', 'false');
                chatOpenButton.focus();
                if (pollTimer) window.clearInterval(pollTimer);
                pollTimer = null;
            });

            productChat.addEventListener('click', (event) => {
                if (event.target === productChat) chatCloseButton.click();
            });

            productChat.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') chatCloseButton.click();
            });

            chatForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                const body = chatInput.value.trim();
                if (!conversationId || !body || isSending) return;

                isSending = true;
                chatInput.disabled = true;
                chatSubmit.disabled = true;

                try {
                    const response = await fetch(conversationUrl(productChat.dataset.sendUrlTemplate, conversationId), {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ message: body }),
                    });
                    const payload = await parseResponse(response);
                    chatInput.value = '';
                    updateMessages(payload.messages || []);
                } catch (error) {
                    setChatStatus(error.message || 'Pesan belum terkirim. Coba lagi, ya.');
                } finally {
                    isSending = false;
                    chatInput.disabled = false;
                    chatSubmit.disabled = false;
                    chatInput.focus();
                }
            });
        }
    </script>
@endsection
