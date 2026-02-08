<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'level_id',
        'name',
        'code',
    ];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($room) {
            // Force Delete associated assets to clean up rows immediately
            // This prevents FK integrity issues if cascade is missing (though we fixed that too)
            $room->assets->each->forceDelete();
        });
    }
}
