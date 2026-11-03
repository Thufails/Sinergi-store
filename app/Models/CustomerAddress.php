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
    'rajaongkir_province_id',
    'city',
    'rajaongkir_city_id',
    'district',
    'rajaongkir_district_id',
    'subdistrict',
    'rajaongkir_subdistrict_id',
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
