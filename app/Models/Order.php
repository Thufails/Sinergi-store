<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING_CONFIRMATION = 'pending_confirmation';

    public const STATUS_AWAITING_PAYMENT = 'awaiting_payment';

    public const STATUS_PAYMENT_REVIEW = 'payment_review';

    public const STATUS_PAID = 'paid';

    public const STATUS_PACKING = 'packing';

    public const STATUS_PROCESSING = self::STATUS_PACKING;

    public const STATUS_SHIPPED = 'shipped';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'order_number',
        'submission_token',
        'customer_id',
        'customer_address_id',
        'customer_name',
        'customer_phone',
        'status',
        'total_amount',
        'subtotal_amount',
        'shipping_fee',
        'shipping_weight_grams',
        'shipping_recipient_name',
        'shipping_recipient_phone',
        'shipping_province',
        'shipping_city',
        'shipping_district',
        'shipping_village',
        'shipping_postal_code',
        'shipping_address_line',
        'shipping_notes',
        'shipping_latitude',
        'shipping_longitude',
        'paid_at',
        'payment_due_at',
        'payment_proof_path',
        'payment_proof_uploaded_at',
        'payment_review_note',
        'courier_name',
        'courier_service',
        'shipping_estimated_delivery',
        'tracking_number',
        'shipped_at',
        'receipt_confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'subtotal_amount' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'shipping_weight_grams' => 'integer',
            'shipping_latitude' => 'decimal:7',
            'shipping_longitude' => 'decimal:7',
            'paid_at' => 'datetime',
            'payment_due_at' => 'datetime',
            'payment_proof_uploaded_at' => 'datetime',
            'shipped_at' => 'datetime',
            'receipt_confirmed_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function customerAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->oldest('created_at')->oldest('id');
    }
}
