@extends('layouts.store')

@section('title', 'Chat ' . ($conversation->product?->name ?? $conversation->product_name) . ' - Sinergi Store')

@section('content')
    @php
        $statusLabel = match ($conversation->status) {
            'waiting_admin' => 'Menunggu admin',
            'waiting_customer' => 'Menunggu balasanmu',
            'closed' => 'Selesai',
            default => 'Percakapan aktif',
        };
        $statusStyle = match ($conversation->status) {
            'waiting_admin' => 'bg-amber-100 text-amber-800',
            'waiting_customer' => 'bg-sky-100 text-sky-800',
            'closed' => 'bg-slate-100 text-slate-600',
            default => 'bg-emerald-100 text-emerald-800',
        };
    @endphp

    <section class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8 lg:py-10">
        <a href="{{ route('account.chats.index') }}" class="mb-4 inline-flex items-center gap-2 text-sm font-medium text-slate-600 transition hover:text-amber-700">← Kembali ke percakapan</a>

        <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_18rem]">
            <section class="flex h-[min(74vh,48rem)] min-h-[30rem] flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <header class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-4 sm:px-5">
                    <div class="flex min-w-0 items-center gap-3">
                        @if ($conversation->product?->image)
                            <img src="{{ asset('storage/' . $conversation->product->image) }}" alt="" class="h-11 w-11 shrink-0 rounded-xl bg-slate-100 object-cover">
                        @else
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-sm font-bold text-amber-800" aria-hidden="true">SS</span>
                        @endif
                        <div class="min-w-0">
                            <h1 class="truncate font-semibold text-slate-900">{{ $conversation->product?->name ?? $conversation->product_name }}</h1>
                            <p class="truncate text-xs text-slate-500">Chat Sinergi Store</p>
                        </div>
                    </div>
                    <span class="shrink-0 rounded-full px-3 py-1 text-[11px] font-semibold {{ $statusStyle }}" data-customer-chat-status>{{ $statusLabel }}</span>
                </header>

                <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50 px-4 py-3 sm:px-5">
                    @if ($conversation->product?->slug)
                        <a href="{{ route('store.products.show', $conversation->product) }}" class="text-sm font-medium text-amber-800 hover:underline">Lihat produk</a>
                    @endif
                    <p class="min-w-0 truncate text-xs text-slate-500">
                        {{ $conversation->product?->name ?? $conversation->product_name }}
                        @if ($conversation->variant_label) · {{ $conversation->variant_label }} @endif
                    </p>
                </div>

                <div class="min-h-0 flex-1 space-y-3 overflow-y-auto bg-slate-50 px-4 py-5 sm:px-5" data-customer-chat-messages aria-live="polite" aria-relevant="additions text">
                    @forelse ($conversation->messages as $message)
                        @php
                            $isCustomerMessage = $message->sender_type === 'customer';
                            $isSystemMessage = $message->sender_type === 'system';
                        @endphp
                        <div class="{{ $isCustomerMessage ? 'ml-auto max-w-[88%]' : 'max-w-[88%]' }}" data-customer-chat-entry="{{ $message->id }}">
                            <p class="mb-1 text-[11px] font-medium {{ $isCustomerMessage ? 'text-right text-slate-500' : 'text-slate-500' }}">{{ $isSystemMessage ? 'Balasan otomatis' : ($isCustomerMessage ? 'Kamu' : (($message->user?->name ? $message->user->name.' - Admin Sinergi' : 'Admin Sinergi'))) }}</p>
                            <p class="whitespace-pre-wrap break-words rounded-2xl px-3.5 py-2.5 text-sm leading-6 {{ $isSystemMessage ? 'border border-amber-200 bg-amber-50 text-slate-700' : ($isCustomerMessage ? 'rounded-tr-sm bg-amber-500 text-slate-950' : 'rounded-tl-sm border border-slate-200 bg-white text-slate-700') }}">{{ $message->body }}</p>
                            <time class="mt-1 block text-[10px] {{ $isCustomerMessage ? 'text-right' : '' }} text-slate-400">{{ $message->created_at->timezone('Asia/Jakarta')->format('H:i') }}</time>
                        </div>
                    @empty
                        <p class="py-10 text-center text-sm text-slate-500" data-customer-chat-empty>Belum ada pesan dalam percakapan ini.</p>
                    @endforelse
                </div>

                <form class="border-t border-slate-200 bg-white p-3 sm:p-4" data-customer-chat-form data-messages-url="{{ route('store.product-conversations.messages', $conversation) }}" data-send-url="{{ route('store.product-conversations.messages.store', $conversation) }}">
                    @csrf
                    <div class="flex items-end gap-2">
                        <label for="customer-chat-message" class="sr-only">Tulis pesan</label>
                        <textarea id="customer-chat-message" rows="1" maxlength="2000" required placeholder="Tulis pesanmu..." class="max-h-32 min-h-11 flex-1 resize-y rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20" data-customer-chat-input></textarea>
                        <button type="submit" class="inline-flex h-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 px-4 text-sm font-semibold text-slate-950 transition hover:bg-amber-400 disabled:cursor-wait disabled:opacity-50" data-customer-chat-submit>Kirim</button>
                    </div>
                    <p class="mt-2 text-xs text-rose-600" data-customer-chat-error role="alert"></p>
                </form>
            </section>

            <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Produk yang ditanyakan</p>
                <h2 class="mt-2 font-semibold text-slate-900">{{ $conversation->product?->name ?? $conversation->product_name }}</h2>
                @if ($conversation->variant_label)
                    <p class="mt-1 text-sm text-slate-500">{{ $conversation->variant_label }}</p>
                @endif
                @if ($conversation->product)
                    <a href="{{ route('store.products.show', $conversation->product) }}" class="mt-4 inline-flex min-h-10 w-full items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-amber-400 hover:text-amber-800">Buka halaman produk</a>
                @endif
                <p class="mt-4 border-t border-slate-100 pt-4 text-xs leading-5 text-slate-500">Balasan admin dan informasi stok akan muncul di percakapan ini.</p>
            </aside>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.querySelector('[data-customer-chat-form]');
            if (!form) return;

            const messagesContainer = document.querySelector('[data-customer-chat-messages]');
            const input = form.querySelector('[data-customer-chat-input]');
            const submitButton = form.querySelector('[data-customer-chat-submit]');
            const errorMessage = form.querySelector('[data-customer-chat-error]');
            const statusBadge = document.querySelector('[data-customer-chat-status]');
            const csrfToken = form.querySelector('input[name="_token"]').value;
            let lastMessageId = Math.max(0, ...Array.from(messagesContainer.querySelectorAll('[data-customer-chat-entry]'), (entry) => Number(entry.dataset.customerChatEntry) || 0));
            let isPolling = false;
            let isSending = false;

            const updateStatusBadge = (status) => {
                if (!statusBadge) return;

                const labels = {
                    waiting_admin: ['Menunggu admin', 'shrink-0 rounded-full bg-amber-100 px-3 py-1 text-[11px] font-semibold text-amber-800'],
                    waiting_customer: ['Menunggu balasanmu', 'shrink-0 rounded-full bg-sky-100 px-3 py-1 text-[11px] font-semibold text-sky-800'],
                    closed: ['Selesai', 'shrink-0 rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold text-slate-600'],
                };
                const [label, classes] = labels[status] || ['Percakapan aktif', 'shrink-0 rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-semibold text-emerald-800'];
                statusBadge.textContent = label;
                statusBadge.className = classes;
            };

            const appendMessage = (message) => {
                const messageId = Number(message.id) || 0;
                if (messagesContainer.querySelector(`[data-customer-chat-entry="${messageId}"]`)) return;

                messagesContainer.querySelector('[data-customer-chat-empty]')?.remove();
                const isCustomerMessage = message.sender_type === 'customer';
                const isSystemMessage = message.sender_type === 'system';
                const wrapper = document.createElement('div');
                wrapper.dataset.customerChatEntry = String(messageId);
                wrapper.className = isCustomerMessage ? 'ml-auto max-w-[88%]' : 'max-w-[88%]';

                const sender = document.createElement('p');
                sender.className = `mb-1 text-[11px] font-medium ${isCustomerMessage ? 'text-right text-slate-500' : 'text-slate-500'}`;
                sender.textContent = isSystemMessage
                    ? 'Balasan otomatis'
                    : (isCustomerMessage ? 'Kamu' : (message.sender_name ? `${message.sender_name} - Admin Sinergi` : 'Admin Sinergi'));

                const bubble = document.createElement('p');
                bubble.className = `whitespace-pre-wrap break-words rounded-2xl px-3.5 py-2.5 text-sm leading-6 ${isSystemMessage ? 'border border-amber-200 bg-amber-50 text-slate-700' : (isCustomerMessage ? 'rounded-tr-sm bg-amber-500 text-slate-950' : 'rounded-tl-sm border border-slate-200 bg-white text-slate-700')}`;
                bubble.textContent = message.body;

                const timestamp = document.createElement('time');
                timestamp.className = `mt-1 block text-[10px] text-slate-400 ${isCustomerMessage ? 'text-right' : ''}`;
                const createdAt = new Date(message.created_at);
                timestamp.textContent = Number.isNaN(createdAt.getTime())
                    ? ''
                    : createdAt.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                wrapper.append(sender, bubble, timestamp);
                messagesContainer.append(wrapper);
                lastMessageId = Math.max(lastMessageId, messageId);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            };

            const pollMessages = async () => {
                if (isPolling || document.visibilityState === 'hidden') return;

                isPolling = true;
                try {
                    const url = new URL(form.dataset.messagesUrl, window.location.origin);
                    url.searchParams.set('after_id', String(lastMessageId));
                    const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    const payload = await response.json();
                    if (!response.ok) return;

                    (payload.messages || []).forEach(appendMessage);
                    updateStatusBadge(payload.status);
                } catch {
                    // Polling retries automatically on the next interval.
                } finally {
                    isPolling = false;
                }
            };

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const body = input.value.trim();
                if (!body || isSending) return;

                isSending = true;
                submitButton.disabled = true;
                errorMessage.textContent = '';

                try {
                    const response = await fetch(form.dataset.sendUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ message: body }),
                    });
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok) throw new Error(payload.message || 'Pesan belum terkirim. Coba lagi.');

                    input.value = '';
                    (payload.messages || []).forEach(appendMessage);
                    updateStatusBadge(payload.status);
                } catch (error) {
                    errorMessage.textContent = error.message || 'Pesan belum terkirim. Coba lagi.';
                } finally {
                    isSending = false;
                    submitButton.disabled = false;
                    input.focus();
                }
            });

            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            window.setInterval(pollMessages, 5000);
        })();
    </script>
@endpush
