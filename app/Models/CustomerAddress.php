<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'label',
    'recipient_name',
    'recipient_phone',
    'province',
    'province_code',
    'city',
    'regency_code',
    'district',
    'district_code',
    'subdistrict',
    'village_code',
    'postal_code',
    'address_line',
    'notes',
    'latitude',
    'longitude',
    'is_default',
])]
class CustomerAddress extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_default' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
