<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 */
class Asset extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\BelongsToDepartment;

    protected $fillable = [
        'department_id', // Owner Department
        'category_id',   // Main Classification
        'room_id',
        'sub_category_id',
        'note',
        'count',
        'asset_code',
        'series_no',
        'sequence_number',
        'peered_asset_id',
        'parent_id',
        'is_parent',
        'is_bundle_parent',
        'component_no',
        'status',
        'is_shared',
        'group_type_id',
        'group_name',
        'created_by_id',
        'bundle_serial',
        'bundle_group_number',
        'category_prefix',
        'full_serial',
    ];

    public function groupType(): BelongsTo
    {
        return $this->belongsTo(AssetGroupType::class, 'group_type_id');
    }

    protected $appends = ['image_url', 'short_code'];

    /**
     * Get the short visual identifier (Sxx-nnn)
     * Rule: Main visual identifier for staff and inventory.
     */
    public function getShortCodeAttribute(): string
    {
        if ($this->series_no !== null && $this->component_no !== null) {
            return 'S' . str_pad($this->series_no, 2, '0', STR_PAD_LEFT) . 
                   '-' . str_pad($this->component_no, 2, '0', STR_PAD_LEFT);
        }

        // Fallback for standalone assets
        return 'ID-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get the asset image URL from its subcategory or category.
     */
    public function getImageUrlAttribute()
    {
        // 1. Try Subcategory Image
        if ($this->subCategory && $this->subCategory->image) {
            return asset('storage/' . $this->subCategory->image);
        }

        // 2. Try Category Image (if subcategory doesn't have one)
        if ($this->subCategory && $this->subCategory->category && $this->subCategory->category->image) {
            return asset('storage/' . $this->subCategory->category->image);
        }

        // 3. Fallback to a professional system default
        return asset('images/default-asset.png');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Asset::class, 'parent_id');
    }

    /**
     * Calculate Bundle Status
     * Logic:
     * - Complete: All expected components (CPU, Monitor, Keyboard, Mouse) are active.
     * - Partially Working: Some components are damaged/maintenance.
     * - Missing Components: Some expected components are not registered as children.
     */
    public function getBundleStatusAttribute(): string
    {
        if (!$this->is_parent) return $this->status;

        $children = $this->children()->with('subCategory')->get();
        if ($children->isEmpty()) return 'Pending Components';

        $statuses = $children->pluck('status')->unique();
        
        if ($statuses->contains('damaged') || $statuses->contains('maintenance')) {
            return 'Partially Working';
        }

        // Logic for expected components could be more complex, 
        // but for now let's check if we have enough components
        if ($children->count() < 4) {
             return 'Missing Components';
        }

        return 'Complete';
    }

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

    public function level()
    {
        return $this->hasOneThrough(Level::class, Room::class, 'id', 'id', 'room_id', 'level_id');
    }

    public function building()
    {
        return $this->hasOneThrough(Building::class, Room::class, 'id', 'id', 'room_id', 'level_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'subject');
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

    /**
     * Scope for advanced filtering
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('asset_code', 'like', '%' . $search . '%')
                  ->orWhere('note', 'like', '%' . $search . '%')
                  ->orWhereHas('room', fn($q) => $q->where('name', 'like', '%' . $search . '%'))
                  ->orWhereHas('subCategory', fn($q) => 
                      $q->where('name', 'like', '%' . $search . '%')
                        ->orWhereHas('category', fn($q) => $q->where('name', 'like', '%' . $search . '%'))
                  );
            });
        });

        $query->when($filters['statuses'] ?? null, function ($query, $statuses) {
            $statuses = is_array($statuses) ? $statuses : [$statuses];
            // Filter out empty or 'all' values
            $cleanStatuses = array_filter($statuses, fn($s) => !empty($s) && $s !== 'all');
            
            if (!empty($cleanStatuses)) {
                $query->whereIn('status', $cleanStatuses);
            }
        });

        $query->when($filters['department_id'] ?? null, function ($query, $deptId) {
            $query->where('department_id', $deptId);
        });

        $query->when($filters['building_id'] ?? null, function ($query, $buildingId) {
            $query->whereHas('room.level', fn($q) => $q->where('building_id', $buildingId));
        });

        $query->when($filters['room_id'] ?? null, function ($query, $roomId) {
            $query->where('room_id', $roomId);
        });

        $query->when($filters['category_id'] ?? null, function ($query, $catId) {
            $query->whereHas('subCategory', fn($q) => $q->where('category_id', $catId));
        });

        $query->when($filters['sub_category_id'] ?? null, function ($query, $subId) {
            $query->where('sub_category_id', $subId);
        });

        $query->when($filters['created_by'] ?? null, function ($query, $userId) {
            $query->where('created_by_id', $userId);
        });

        $query->when($filters['date_from'] ?? null, function ($query, $from) {
            $query->whereDate('created_at', '>=', $from);
        });

        $query->when($filters['date_to'] ?? null, function ($query, $to) {
            $query->whereDate('created_at', '<=', $to);
        });

        $query->when($filters['asset_type'] ?? null, function ($query, $type) {
            if ($type === 'individual') {
                $query->where('is_parent', false)->whereNull('parent_id');
            } elseif ($type === 'bundle') {
                $query->where('is_parent', true);
            } elseif ($type === 'component') {
                $query->whereNotNull('parent_id');
            }
        });
    }
}
