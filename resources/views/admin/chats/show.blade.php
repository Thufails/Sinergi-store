@extends('layouts.admin')

@section('title', 'Percakapan Chat - Sinergi Store')
@section('heading', 'Percakapan pembeli')
@section('header-eyebrow', 'Layanan pelanggan')

@section('content')
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

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_19rem]">
        <section class="flex h-[min(75vh,52rem)] min-h-[32rem] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <a href="{{ route('admin.chats.index', $conversation->status === 'closed' ? ['view' => 'closed'] : ['view' => 'active']) }}" class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900" aria-label="Kembali ke inbox chat">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 18 9 12l6-6"/></svg>
                    </a>
                    <div class="min-w-0">
                        <h2 class="truncate font-semibold text-slate-900">{{ $conversation->customer?->name ?? 'Tamu' }}</h2>
                        <p class="truncate text-xs text-slate-500">{{ $conversation->customer?->email ?? 'Pembeli tanpa akun' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusStyle }}" data-admin-chat-status>{{ $statusLabel }}</span>
                </div>
            </header>

            <div class="border-b border-slate-100 bg-slate-50 px-5 py-3 sm:px-6">
                <p class="text-sm font-semibold text-slate-800">{{ $conversation->product?->name ?? $conversation->product_name }}</p>
                @if ($conversation->variant_label)
                    <p class="mt-0.5 text-xs text-slate-500">Variasi yang ditanyakan: {{ $conversation->variant_label }}</p>
                @else
                    <p class="mt-0.5 text-xs text-slate-500">Produk yang ditanyakan pembeli</p>
                @endif
            </div>

            <div class="min-h-0 flex-1 space-y-3 overflow-y-auto bg-slate-50 px-4 py-5 sm:px-6" data-admin-chat-messages aria-live="polite" aria-relevant="additions text">
                @forelse ($conversation->messages as $message)
                    @php
                        $isAdminMessage = $message->sender_type === 'admin';
                        $isSystemMessage = $message->sender_type === 'system';
                    @endphp
                    <div class="{{ $isSystemMessage ? 'mx-auto max-w-[90%] text-center' : ($isAdminMessage ? 'ml-auto max-w-[88%]' : 'max-w-[88%]') }}" data-admin-chat-entry="{{ $message->id }}">
                        @unless ($isSystemMessage)
                            <p class="mb-1 text-[11px] font-medium {{ $isAdminMessage ? 'text-right text-slate-500' : 'text-slate-500' }}">{{ $isAdminMessage ? ($message->user?->name ?? 'Admin') : ($conversation->customer?->name ?? 'Tamu') }}</p>
                        @else
                            <p class="mb-1 text-[11px] font-medium text-slate-500">Balasan otomatis</p>
                        @endunless
                        <p class="whitespace-pre-wrap break-words rounded-2xl px-3.5 py-2.5 text-sm leading-6 {{ $isSystemMessage ? 'border border-amber-200 bg-amber-50 text-slate-700' : ($isAdminMessage ? 'rounded-tr-sm bg-amber-500 text-slate-950' : 'rounded-tl-sm border border-slate-200 bg-white text-slate-700') }}">{{ $message->body }}</p>
                        <time class="mt-1 block text-[10px] {{ $isAdminMessage ? 'text-right' : ($isSystemMessage ? 'text-center' : '') }} text-slate-400">{{ $message->created_at->timezone('Asia/Jakarta')->format('H:i') }}</time>
                    </div>
                @empty
                    <p class="py-10 text-center text-sm text-slate-500" data-admin-chat-empty>Belum ada pesan dalam percakapan ini.</p>
                @endforelse
            </div>

            @if ($conversation->status === 'closed')
                <p class="border-t border-slate-200 bg-slate-50 px-5 py-4 text-center text-sm text-slate-600" data-admin-chat-closed-notice>
                    Percakapan ini sudah selesai. Jika pembeli mengirim pesan baru, percakapan akan aktif kembali.
                </p>
            @else
                <p class="hidden border-t border-slate-200 bg-slate-50 px-5 py-4 text-center text-sm text-slate-600" data-admin-chat-closed-notice>
                    Percakapan ini sudah selesai. Jika pembeli mengirim pesan baru, percakapan akan aktif kembali.
                </p>
                <form class="border-t border-slate-200 bg-white p-3 sm:p-4" data-admin-chat-form data-customer-label="{{ $conversation->customer?->name ?? 'Tamu' }}" data-messages-url="{{ route('admin.chats.messages', $conversation) }}" data-send-url="{{ route('admin.chats.messages.store', $conversation) }}">
                    @csrf
                    <div class="flex flex-wrap items-end gap-2">
                        <label for="admin-chat-message" class="sr-only">Tulis balasan kepada pembeli</label>
                        <textarea id="admin-chat-message" rows="1" maxlength="2000" required placeholder="Tulis balasan..." class="max-h-32 min-h-11 min-w-[12rem] flex-1 resize-y rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20" data-admin-chat-input></textarea>
                    <button type="submit" class="inline-flex h-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 px-4 text-sm font-semibold text-slate-950 transition hover:bg-amber-400 disabled:cursor-wait disabled:opacity-50" data-admin-chat-submit>Kirim</button>
                    <button type="button" class="inline-flex h-11 shrink-0 items-center justify-center whitespace-nowrap rounded-xl border border-amber-300 px-3 text-xs font-semibold text-amber-900 transition hover:bg-amber-50 disabled:cursor-wait disabled:opacity-50 sm:px-4 sm:text-sm" data-admin-chat-close data-close-url="{{ route('admin.chats.close', $conversation) }}">Selesaikan percakapan</button>
                    </div>
                    <p class="mt-2 text-xs text-rose-600" data-admin-chat-error role="alert"></p>
                </form>
            @endif
        </section>

        <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="font-semibold text-slate-900">Detail pembeli</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div>
                    <dt class="text-xs text-slate-500">Nama</dt>
                    <dd class="mt-0.5 font-medium text-slate-800">{{ $conversation->customer?->name ?? 'Tamu' }}</dd>
                </div>
                @if ($conversation->customer?->email)
                    <div>
                        <dt class="text-xs text-slate-500">Email</dt>
                        <dd class="mt-0.5 break-all text-slate-700">{{ $conversation->customer->email }}</dd>
                    </div>
                @endif
                @if ($conversation->customer?->phone)
                    <div>
                        <dt class="text-xs text-slate-500">WhatsApp</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $conversation->customer->phone }}</dd>
                    </div>
                @endif
                <div class="border-t border-slate-100 pt-3">
                    <dt class="text-xs text-slate-500">Percakapan dimulai</dt>
                    <dd class="mt-0.5 text-slate-700">{{ $conversation->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</dd>
                </div>
            </dl>
            <a href="{{ $conversation->product ? route('store.products.show', $conversation->product) : '#' }}" @class(['mt-5 inline-flex w-full items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50' => $conversation->product, 'pointer-events-none mt-5 inline-flex w-full items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-400' => ! $conversation->product]) @if (! $conversation->product) aria-disabled="true" @endif>
                Lihat halaman produk
            </a>
        </aside>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.querySelector('[data-admin-chat-form]');
            if (!form) return;

            const messagesContainer = document.querySelector('[data-admin-chat-messages]');
            const input = form.querySelector('[data-admin-chat-input]');
            const submitButton = form.querySelector('[data-admin-chat-submit]');
            const errorMessage = form.querySelector('[data-admin-chat-error]');
            const statusBadge = document.querySelector('[data-admin-chat-status]');
            const closeButton = document.querySelector('[data-admin-chat-close]');
            const closedNotice = document.querySelector('[data-admin-chat-closed-notice]');
            const unreadBadge = document.querySelector('[data-admin-chat-unread-badge]');
            const csrfToken = form.querySelector('input[name="_token"]').value;
            let lastMessageId = Math.max(0, ...Array.from(messagesContainer.querySelectorAll('[data-admin-chat-entry]'), (entry) => Number(entry.dataset.adminChatEntry) || 0));
            let isPolling = false;
            let isSending = false;

            const updateStatusBadge = (status) => {
                if (!statusBadge) return;

                const labels = {
                    waiting_admin: ['Perlu dibalas', 'rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-800'],
                    waiting_customer: ['Menunggu pembeli', 'rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-800'],
                    closed: ['Selesai', 'rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600'],
                };
                const [label, classes] = labels[status] || ['Percakapan aktif', 'rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800'];
                statusBadge.textContent = label;
                statusBadge.className = classes;

                const isClosed = status === 'closed';
                if (closeButton) closeButton.classList.toggle('hidden', isClosed);
                if (closedNotice) closedNotice.classList.toggle('hidden', !isClosed);
                form.classList.toggle('hidden', isClosed);
            };

            const appendMessage = (message) => {
                const messageId = Number(message.id) || 0;
                if (messagesContainer.querySelector(`[data-admin-chat-entry="${messageId}"]`)) return;

                messagesContainer.querySelector('[data-admin-chat-empty]')?.remove();
                const isAdminMessage = message.sender_type === 'admin';
                const isSystemMessage = message.sender_type === 'system';
                const wrapper = document.createElement('div');
                wrapper.dataset.adminChatEntry = String(messageId);
                wrapper.className = isSystemMessage ? 'mx-auto max-w-[90%] text-center' : (isAdminMessage ? 'ml-auto max-w-[88%]' : 'max-w-[88%]');

                const sender = document.createElement('p');
                sender.className = `mb-1 text-[11px] font-medium ${isAdminMessage ? 'text-right text-slate-500' : 'text-slate-500'}`;
                sender.textContent = isSystemMessage ? 'Balasan otomatis' : (isAdminMessage ? (message.user_name || 'Admin') : form.dataset.customerLabel);

                const bubble = document.createElement('p');
                bubble.className = `whitespace-pre-wrap break-words rounded-2xl px-3.5 py-2.5 text-sm leading-6 ${isSystemMessage ? 'border border-amber-200 bg-amber-50 text-slate-700' : (isAdminMessage ? 'rounded-tr-sm bg-amber-500 text-slate-950' : 'rounded-tl-sm border border-slate-200 bg-white text-slate-700')}`;
                bubble.textContent = message.body;

                const timestamp = document.createElement('time');
                timestamp.className = `mt-1 block text-[10px] text-slate-400 ${isAdminMessage ? 'text-right' : (isSystemMessage ? 'text-center' : '')}`;
                const createdAt = new Date(message.created_at);
                timestamp.textContent = Number.isNaN(createdAt.getTime())
                    ? ''
                    : createdAt.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                wrapper.append(sender, bubble, timestamp);
                messagesContainer.append(wrapper);
                lastMessageId = Math.max(lastMessageId, messageId);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            };

            const updateUnreadBadge = (count) => {
                const unreadCount = Number(count) || 0;
                if (unreadBadge) {
                    unreadBadge.textContent = unreadCount > 0 ? String(unreadCount) : '';
                    unreadBadge.classList.toggle('hidden', unreadCount < 1);
                    unreadBadge.classList.toggle('inline-flex', unreadCount > 0);
                    unreadBadge.setAttribute('aria-label', `${unreadCount} percakapan belum dibaca`);
                }

                document.querySelectorAll('[data-admin-unread-count]').forEach((element) => {
                    element.textContent = `${unreadCount} belum dibaca`;
                });
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
                    updateUnreadBadge(payload.unread_conversations);
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
                    if (!response.ok) throw new Error(payload.message || 'Balasan belum terkirim. Coba lagi.');

                    input.value = '';
                    appendMessage(payload.message);
                    updateStatusBadge('waiting_customer');
                } catch (error) {
                    errorMessage.textContent = error.message || 'Balasan belum terkirim. Coba lagi.';
                } finally {
                    isSending = false;
                    submitButton.disabled = false;
                    input.focus();
                }
            });

            closeButton?.addEventListener('click', async () => {
                if (!window.confirm('Selesaikan percakapan ini? Riwayat chat tetap tersimpan dan pembeli bisa mengaktifkannya lagi dengan mengirim pesan baru.')) return;

                closeButton.disabled = true;
                errorMessage.textContent = '';

                try {
                    const response = await fetch(closeButton.dataset.closeUrl, {
                        method: 'PATCH',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok) throw new Error(payload.message || 'Percakapan belum bisa diselesaikan. Coba lagi.');

                    if (payload.message) appendMessage(payload.message);
                    updateStatusBadge(payload.status);
                } catch (error) {
                    errorMessage.textContent = error.message || 'Percakapan belum bisa diselesaikan. Coba lagi.';
                    closeButton.disabled = false;
                }
            });

            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            window.setInterval(pollMessages, 5000);
        })();
    </script>
@endpush
