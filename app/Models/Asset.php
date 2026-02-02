<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'room_id',
        'sub_category_id',
        'note',
        'count',
        'peered_asset_id',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function infos(): HasMany
    {
        return $this->hasMany(AssetInfo::class);
    }

    public function peeredAsset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'peered_asset_id');
    }

    public function peeredAssets(): HasMany
    {
        return $this->hasMany(Asset::class, 'peered_asset_id');
    }
}
