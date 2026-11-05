<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductConversation;
use App\Models\ProductConversationMessage;
use App\Services\ProductAvailabilityService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductConversationController extends Controller
{
    public function start(Request $request, Product $product, ProductAvailabilityService $productAvailability): JsonResponse
    {
        $product->loadMissing(['category', 'variants']);
        abort_unless($product->is_active && $product->category?->is_active, 404);

        $validated = $request->validate([
            'variant_id' => [
                'nullable',
                'integer',
                Rule::exists('product_variants', 'id')->where('product_id', $product->id),
            ],
        ]);

        $guestToken = $request->user('customer') ? null : $this->guestToken($request);
        $conversation = $this->conversationQuery($request, $guestToken)
            ->where('product_id', $product->id)
            ->where('status', '!=', 'closed')
            ->latest('last_message_at')
            ->latest('id')
            ->first();

        if (! $conversation) {
            $productAvailability->refresh($product);
            $variant = isset($validated['variant_id'])
                ? $product->variants->firstWhere('id', (int) $validated['variant_id'])
                : null;
            $variantLabel = $variant
                ? collect([$variant->size, $variant->color])->filter()->implode(' · ')
                : null;
            $initialReply = $this->initialReply($product);

            $conversation = DB::transaction(function () use ($request, $product, $guestToken, $variant, $variantLabel, $initialReply): ProductConversation {
                $conversation = ProductConversation::query()->create([
                    'customer_id' => $request->user('customer')?->id,
                    'guest_token' => $guestToken,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'product_name' => $product->name,
                    'variant_label' => $variantLabel !== '' ? $variantLabel : null,
                    'status' => 'waiting_admin',
                ]);

                $initialMessage = $conversation->messages()->create([
                    'sender_type' => 'system',
                    'body' => $initialReply,
                ]);

                $conversation->forceFill(['last_message_at' => $initialMessage->created_at])->save();

                return $conversation;
            });
        } else {
            $this->replyToPendingMessages($conversation, $productAvailability);
        }

        $messages = $conversation->messages()->orderBy('id')->get();

        return response()->json([
            'conversation_id' => $conversation->id,
            'status' => $conversation->status,
            'messages' => $this->serializeMessages($messages),
        ]);
    }

    public function messages(Request $request, ProductConversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'after_id' => ['nullable', 'integer', 'min:0'],
        ]);

        $conversation = $this->conversationQuery($request)->findOrFail($conversation->id);
        $messageQuery = $conversation->messages()->orderBy('id');

        if (! empty($validated['after_id'])) {
            $messageQuery->where('id', '>', (int) $validated['after_id']);
        }

        $messages = $messageQuery->get();

        $conversation->messages()
            ->whereIn('sender_type', ['system', 'admin'])
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'status' => $conversation->status,
            'messages' => $this->serializeMessages($messages),
        ]);
    }

    public function send(
        Request $request,
        ProductConversation $conversation,
        ProductAvailabilityService $productAvailability,
    ): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $conversation = $this->conversationQuery($request)->findOrFail($conversation->id);
        $body = trim($validated['message']);

        if ($body === '') {
            return response()->json([
                'message' => 'Tulis pesan terlebih dahulu.',
            ], 422);
        }

        $product = $conversation->product()->with('variants')->first();
        $automaticReply = $product
            ? $this->replyToCustomerMessage($conversation, $product, $body, $productAvailability)
            : 'Terima kasih, pesanmu sudah masuk antrean. Admin akan membalas lewat chat ini secepatnya.';

        $messages = DB::transaction(function () use ($conversation, $body, $automaticReply): array {
            $customerMessage = $conversation->messages()->create([
                'sender_type' => 'customer',
                'body' => $body,
            ]);

            $systemMessage = $conversation->messages()->create([
                'sender_type' => 'system',
                'body' => $automaticReply,
            ]);

            $conversation->forceFill([
                'status' => 'waiting_admin',
                'last_message_at' => $systemMessage->created_at,
            ])->save();

            return [$customerMessage, $systemMessage];
        });

        return response()->json([
            'messages' => $this->serializeMessages(collect($messages)),
            'status' => 'waiting_admin',
        ], 201);
    }

    private function conversationQuery(Request $request, ?string $guestToken = null): Builder
    {
        $customer = $request->user('customer');

        if ($customer) {
            return ProductConversation::query()->where('customer_id', $customer->id);
        }

        return ProductConversation::query()
            ->whereNull('customer_id')
            ->where('guest_token', $guestToken ?? $this->guestToken($request));
    }

    private function guestToken(Request $request): string
    {
        $guestToken = $request->session()->get('product_chat_guest_token');

        if (! is_string($guestToken) || ! Str::isUuid($guestToken)) {
            $guestToken = (string) Str::uuid();
            $request->session()->put('product_chat_guest_token', $guestToken);
        }

        return $guestToken;
    }

    private function initialReply(Product $product): string
    {
        if ($product->variants->isEmpty()) {
            $availableStock = (int) $product->getAttribute('available_stock');
            $stockSummary = $availableStock.' unit';
        } else {
            $stockSummary = $product->variants
                ->map(function ($variant): string {
                    $variantName = collect([$variant->size, $variant->color])
                        ->filter()
                        ->implode(' · ');

                    return ($variantName !== '' ? $variantName : 'Varian')
                        .': '.(int) $variant->available_stock.' unit';
                })
                ->implode(', ');
        }

        $hasAvailableStock = $product->variants->isEmpty()
            ? (int) $product->getAttribute('available_stock') > 0
            : $product->variants->contains(fn ($variant) => (int) $variant->available_stock > 0);

        $availabilityMessage = $hasAvailableStock
            ? 'Produk ini tersedia. Stok saat ini: '.$stockSummary.'.'
            : 'Maaf, stok produk ini sedang habis. Stok saat ini: '.$stockSummary.'.';

        return 'Halo! Ini chat mengenai '.$product->name.'. '.$availabilityMessage.' Kalau ingin berbicara dengan admin, pesanmu masuk ke antrean dan admin akan membalas lewat chat ini secepatnya.';
    }

    private function replyToCustomerMessage(
        ProductConversation $conversation,
        Product $product,
        string $message,
        ProductAvailabilityService $productAvailability,
    ): string {
        $productAvailability->refresh($product);
        $availability = $this->availabilityForConversation($conversation, $product);
        $normalizedMessage = mb_strtolower($message);

        if (preg_match('/\b(?:pesan|pesen|beli|order|ambil|jumlah)\b[^\d]{0,30}(\d+)\s*(?:pcs|buah|unit|item)?/u', $normalizedMessage, $matches) === 1
            || preg_match('/\b(\d+)\s*(?:pcs|buah|unit|item)\b/u', $normalizedMessage, $matches) === 1) {
            $requestedQuantity = (int) $matches[1];

            if ($requestedQuantity <= $availability['total']) {
                return 'Bisa, jumlah '.$requestedQuantity.' unit masih sesuai dengan total stok yang tersedia ('.$availability['total'].' unit). '.$availability['summary'].' Admin akan membantu memastikan variasi yang kamu pilih.';
            }

            return 'Stok yang tercatat saat ini '.$availability['total'].' unit, sedangkan kamu menanyakan '.$requestedQuantity.' unit. Jumlah itu melebihi stok yang tersedia. Pesanmu sudah masuk antrean dan admin akan membantu mengecek ketersediaan tambahan.';
        }

        if (preg_match('/\b(stok|tersedia|ready|sisa|ada barang|ada stok)\b/u', $normalizedMessage) === 1) {
            return 'Stok yang tersedia saat ini: '.$availability['summary'].'. Admin akan membantu memastikan stok saat pesanan diproses.';
        }

        return 'Terima kasih, pesanmu sudah masuk antrean. Admin akan membalas lewat chat ini secepatnya.';
    }

    /**
     * @return array{total: int, summary: string}
     */
    private function availabilityForConversation(ProductConversation $conversation, Product $product): array
    {
        if ($product->variants->isEmpty()) {
            $availableStock = (int) $product->getAttribute('available_stock');

            return [
                'total' => $availableStock,
                'summary' => $availableStock.' unit',
            ];
        }

        $variants = $conversation->product_variant_id
            ? $product->variants->where('id', $conversation->product_variant_id)
            : $product->variants;

        $totalAvailableStock = (int) $variants->sum('available_stock');
        $stockSummary = $variants
            ->map(function ($variant): string {
                $variantName = collect([$variant->size, $variant->color])
                    ->filter()
                    ->implode(' · ');

                return ($variantName !== '' ? $variantName : 'Varian')
                    .': '.(int) $variant->available_stock.' unit';
            })
            ->implode(', ');

        return [
            'total' => $totalAvailableStock,
            'summary' => $stockSummary !== '' ? $stockSummary : '0 unit',
        ];
    }

    private function replyToPendingMessages(
        ProductConversation $conversation,
        ProductAvailabilityService $productAvailability,
    ): void {
        DB::transaction(function () use ($conversation, $productAvailability): void {
            $lockedConversation = ProductConversation::query()
                ->lockForUpdate()
                ->findOrFail($conversation->id);

            $latestReplyId = (int) $lockedConversation->messages()
                ->whereIn('sender_type', ['system', 'admin'])
                ->max('id');
            $pendingCustomerMessages = $lockedConversation->messages()
                ->where('sender_type', 'customer')
                ->where('id', '>', $latestReplyId)
                ->orderBy('id')
                ->get();

            if ($pendingCustomerMessages->isEmpty()) {
                return;
            }

            $product = $lockedConversation->product()->with('variants')->first();

            foreach ($pendingCustomerMessages as $customerMessage) {
                $reply = $product
                    ? $this->replyToCustomerMessage($lockedConversation, $product, $customerMessage->body, $productAvailability)
                    : 'Terima kasih, pesanmu sudah masuk antrean. Admin akan membalas lewat chat ini secepatnya.';

                $systemMessage = $lockedConversation->messages()->create([
                    'sender_type' => 'system',
                    'body' => $reply,
                ]);

                $lockedConversation->forceFill([
                    'status' => 'waiting_admin',
                    'last_message_at' => $systemMessage->created_at,
                ])->save();
            }
        });
    }

    /**
     * @param  Collection<int, ProductConversationMessage>  $messages
     * @return list<array{id: int, sender_type: string, body: string, created_at: ?string}>
     */
    private function serializeMessages(Collection $messages): array
    {
        return $messages->map(fn (ProductConversationMessage $message): array => [
            'id' => (int) $message->id,
            'sender_type' => $message->sender_type,
            'body' => $message->body,
            'created_at' => $message->created_at?->toIso8601String(),
        ])->values()->all();
    }
}
