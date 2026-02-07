<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory, \App\Traits\BelongsToDepartment;

    protected $fillable = [
        'department_id', // Owner Department
        'room_id',
        'sub_category_id',
        'note',
        'count',
        'peered_asset_id',
        'serial_number',
        'condition',
        'is_shared',
        'created_by_id',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function sharedDepartments(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'asset_department');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(AssetMovement::class);
    }

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
