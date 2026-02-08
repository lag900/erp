<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'name',
        'name_en',
        'name_ar',
        'code',
        'is_shared',
        'image',
    ];

    protected $appends = ['image_url', 'display_name'];

    protected $casts = [
        'is_shared' => 'boolean',
    ];

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function getDisplayNameAttribute()
    {
        return $this->name_ar ?: $this->name_en ?: $this->name;
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function levels(): HasMany
    {
        return $this->hasMany(Level::class);
    }

    public function rooms(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Room::class, Level::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Building $building) {
            $exists = self::where('location_id', $building->location_id)
                ->where('name_en', $building->name_en)
                ->exists();

            if ($exists) {
                throw new \Exception('Duplicate building in same location: ' . $building->name_en);
            }
        });

        static::updating(function (Building $building) {
            $exists = self::where('location_id', $building->location_id)
                ->where('name_en', $building->name_en)
                ->where('id', '!=', $building->id)
                ->exists();

            if ($exists) {
                throw new \Exception('Duplicate building in same location: ' . $building->name_en);
            }
        });
    }

    public static function checkDuplicate(int $locationId, string $nameEn): ?self
    {
        return self::where('location_id', $locationId)
            ->where('name_en', $nameEn)
            ->first();
    }
}
