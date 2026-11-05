<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductConversation;
use App\Models\ProductConversationMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductConversationController extends Controller
{
    private const CUSTOMER_QUEUE_REPLY = 'Terima kasih, pesanmu sudah masuk antrean. Admin akan membalas lewat chat ini secepatnya.';

    public function index(Request $request): View
    {
        $this->closeIdleWaitingConversations();

        $filters = $request->validate([
            'view' => ['nullable', 'in:active,closed'],
        ]);
        $selectedView = $filters['view'] ?? 'active';

        $conversationQuery = ProductConversation::query()
            ->with(['customer:id,name,email,phone', 'product:id,name,slug,image', 'latestMessage'])
            ->addSelect([
                'latest_customer_message_body' => ProductConversationMessage::query()
                    ->select('body')
                    ->whereColumn('product_conversation_id', 'product_conversations.id')
                    ->where('sender_type', 'customer')
                    ->orderByDesc('id')
                    ->limit(1),
            ])
            ->withCount([
                'messages as unread_messages_count' => fn (Builder $query) => $query
                    ->where('sender_type', 'customer')
                    ->whereNull('read_at'),
            ]);

        if ($selectedView === 'closed') {
            $conversationQuery->where('status', 'closed');
        } else {
            $conversationQuery->where('status', '!=', 'closed');
        }

        $conversations = $conversationQuery
            ->orderByRaw("CASE status WHEN 'waiting_admin' THEN 0 WHEN 'waiting_customer' THEN 1 ELSE 2 END")
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate(15);

        $unreadConversationCount = $this->unreadConversationCount();

        return view('admin.chats.index', compact('conversations', 'unreadConversationCount', 'selectedView'));
    }

    public function show(ProductConversation $conversation): View
    {
        $this->closeIdleWaitingConversations();
        $conversation->refresh();

        $conversation->messages()
            ->where('sender_type', 'customer')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $conversation->load([
            'customer:id,name,email,phone',
            'product:id,name,slug,image',
            'productVariant:id,size,color,sku',
            'messages' => fn (HasMany $query) => $query
                ->where(fn (Builder $messageQuery) => $messageQuery
                    ->where('sender_type', '!=', 'system')
                    ->orWhere('body', '!=', self::CUSTOMER_QUEUE_REPLY))
                ->with('user:id,name')
                ->orderBy('id'),
        ]);
        $unreadConversationCount = $this->unreadConversationCount();

        return view('admin.chats.show', compact('conversation', 'unreadConversationCount'));
    }

    public function messages(Request $request, ProductConversation $conversation): JsonResponse
    {
        $this->closeIdleWaitingConversations();
        $conversation->refresh();

        $validated = $request->validate([
            'after_id' => ['nullable', 'integer', 'min:0'],
        ]);

        $messageQuery = $conversation->messages()
            ->where(fn (Builder $messageQuery) => $messageQuery
                ->where('sender_type', '!=', 'system')
                ->orWhere('body', '!=', self::CUSTOMER_QUEUE_REPLY))
            ->with('user:id,name')
            ->orderBy('id');

        if (! empty($validated['after_id'])) {
            $messageQuery->where('id', '>', (int) $validated['after_id']);
        }

        $messages = $messageQuery->get();

        $conversation->messages()
            ->where('sender_type', 'customer')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'messages' => $this->serializeMessages($messages),
            'status' => $conversation->status,
            'unread_conversations' => $this->unreadConversationCount(),
        ]);
    }

    public function send(Request $request, ProductConversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);
        $body = trim($validated['message']);

        if ($body === '') {
            return response()->json([
                'message' => 'Tulis pesan terlebih dahulu.',
            ], 422);
        }

        $message = DB::transaction(function () use ($request, $conversation, $body): ProductConversationMessage {
            $message = $conversation->messages()->create([
                'sender_type' => 'admin',
                'user_id' => $request->user()->id,
                'body' => $body,
            ]);

            $conversation->forceFill([
                'status' => 'waiting_customer',
                'last_message_at' => $message->created_at,
            ])->save();

            return $message;
        });

        return response()->json([
            'message' => $this->serializeMessages(collect([$message]))[0],
        ], 201);
    }

    public function close(ProductConversation $conversation): JsonResponse
    {
        if ($conversation->status === 'closed') {
            return response()->json([
                'status' => 'closed',
            ]);
        }

        DB::transaction(function () use ($conversation): void {
            $conversation->messages()
                ->where('sender_type', 'customer')
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            $conversation->forceFill(['status' => 'closed'])->save();
        });

        return response()->json([
            'status' => 'closed',
        ]);
    }

    public function unreadCount(): JsonResponse
    {
        $this->closeIdleWaitingConversations();

        return response()->json([
            'unread_conversations' => $this->unreadConversationCount(),
        ]);
    }

    private function unreadConversationCount(): int
    {
        return ProductConversation::query()
            ->where('status', '!=', 'closed')
            ->whereHas('messages', fn (Builder $query) => $query
                ->where('sender_type', 'customer')
                ->whereNull('read_at'))
            ->count();
    }

    private function closeIdleWaitingConversations(): void
    {
        ProductConversation::query()
            ->where('status', 'waiting_customer')
            ->whereNotNull('last_message_at')
            ->where('last_message_at', '<=', now()->subHours(48))
            ->update(['status' => 'closed']);
    }

    /**
     * @param  Collection<int, ProductConversationMessage>  $messages
     * @return list<array{id: int, sender_type: string, body: string, created_at: ?string, user_name: ?string}>
     */
    private function serializeMessages(Collection $messages): array
    {
        return $messages->map(fn (ProductConversationMessage $message): array => [
            'id' => (int) $message->id,
            'sender_type' => $message->sender_type,
            'body' => $message->body,
            'created_at' => $message->created_at?->toIso8601String(),
            'user_name' => $message->user?->name,
        ])->values()->all();
    }
}
