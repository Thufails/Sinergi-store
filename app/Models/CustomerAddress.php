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

    public function provinceRegion(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'province_code', 'code');
    }

    public function regencyRegion(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'regency_code', 'code');
    }

    public function districtRegion(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'district_code', 'code');
    }

    public function villageRegion(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'village_code', 'code');
    }
}
