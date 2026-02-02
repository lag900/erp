<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'key',
        'value',
        'image',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the image URL attribute.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $attributes['image'] 
                ? (str_starts_with($attributes['image'], 'http') 
                    ? $attributes['image'] 
                    : asset('storage/' . $attributes['image']))
                : null,
        );
    }

    /**
     * Get the image value attribute (for form compatibility).
     */
    protected function imageValue(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $attributes['image'] ?? null,
        );
    }
}
