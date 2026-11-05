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
    public function index(): View
    {
        $conversations = ProductConversation::query()
            ->with(['customer:id,name,email,phone', 'product:id,name,slug,image', 'latestMessage'])
            ->withCount([
                'messages as unread_messages_count' => fn (Builder $query) => $query
                    ->where('sender_type', 'customer')
                    ->whereNull('read_at'),
            ])
            ->orderByRaw("CASE status WHEN 'waiting_admin' THEN 0 WHEN 'waiting_customer' THEN 1 ELSE 2 END")
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate(15);

        $unreadConversationCount = $this->unreadConversationCount();

        return view('admin.chats.index', compact('conversations', 'unreadConversationCount'));
    }

    public function show(ProductConversation $conversation): View
    {
        $conversation->messages()
            ->where('sender_type', 'customer')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $conversation->load([
            'customer:id,name,email,phone',
            'product:id,name,slug,image',
            'productVariant:id,size,color,sku',
            'messages' => fn (HasMany $query) => $query->with('user:id,name')->orderBy('id'),
        ]);
        $unreadConversationCount = $this->unreadConversationCount();

        return view('admin.chats.show', compact('conversation', 'unreadConversationCount'));
    }

    public function messages(Request $request, ProductConversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'after_id' => ['nullable', 'integer', 'min:0'],
        ]);

        $messageQuery = $conversation->messages()->with('user:id,name')->orderBy('id');

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

    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'unread_conversations' => $this->unreadConversationCount(),
        ]);
    }

    private function unreadConversationCount(): int
    {
        return ProductConversation::query()
            ->whereHas('messages', fn (Builder $query) => $query
                ->where('sender_type', 'customer')
                ->whereNull('read_at'))
            ->count();
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
