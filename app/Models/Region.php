<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table(key: 'code', keyType: 'string', incrementing: false, timestamps: false)]
#[Fillable([
    'code',
    'parent_code',
    'name',
    'level',
    'postal_code',
    'latitude',
    'longitude',
    'has_path',
])]
class Region extends Model
{
    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'has_path' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_code', 'code');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_code', 'code');
    }
}
