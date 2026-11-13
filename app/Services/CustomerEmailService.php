<?php

namespace App\Services;

use App\Mail\CustomerActivityMail;
use App\Models\Order;
use App\Models\ProductConversation;
use App\Models\ProductConversationMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class CustomerEmailService
{
    /**
     * @param  array<string, string>  $details
     */
    public function sendOrderUpdate(
        Order $order,
        string $subject,
        string $heading,
        string $message,
        array $details = [],
    ): void {
        $customer = $order->customer;

        if (! $customer || blank($customer->email)) {
            return;
        }

        try {
            Mail::to($customer->email)->send(new CustomerActivityMail(
                subjectLine: $subject,
                heading: $heading,
                messageBody: $message,
                details: $details,
                actionUrl: route('account.orders.show', $order),
                actionLabel: 'Lihat pesanan',
                customerName: $customer->name,
            ));
        } catch (Throwable $exception) {
            Log::warning('Email pembaruan pesanan gagal dikirim.', [
                'order_number' => $order->order_number,
                'customer_id' => $customer->id,
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    public function sendChatReply(ProductConversation $conversation, ProductConversationMessage $message): void
    {
        $conversation->loadMissing(['customer', 'product']);
        $customer = $conversation->customer;

        if (! $customer || blank($customer->email)) {
            return;
        }

        try {
            Mail::to($customer->email)->send(new CustomerActivityMail(
                subjectLine: 'Balasan admin untuk percakapan produkmu',
                heading: 'Ada balasan baru dari admin',
                messageBody: $message->body,
                details: [
                    'Produk' => $conversation->product?->name ?? $conversation->product_name,
                ],
                actionUrl: route('account.chats.show', $conversation),
                actionLabel: 'Buka percakapan',
                customerName: $customer->name,
            ));
        } catch (Throwable $exception) {
            Log::warning('Email balasan chat pembeli gagal dikirim.', [
                'conversation_id' => $conversation->id,
                'customer_id' => $customer->id,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
