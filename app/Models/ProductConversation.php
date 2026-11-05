<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'guest_token',
        'product_id',
        'product_variant_id',
        'product_name',
        'variant_label',
        'status',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ProductConversationMessage::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(ProductConversationMessage::class)->latestOfMany();
    }
}
