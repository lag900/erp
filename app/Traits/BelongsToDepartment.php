<?php

namespace App\Traits;

use App\Models\Department;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToDepartment
{
    public static function bootBelongsToDepartment(): void
    {
        static::creating(function ($model) {
            if (!$model->department_id && session()->has('selected_department_id')) {
                $model->department_id = session('selected_department_id');
            }
        });

        static::addGlobalScope('department', function (Builder $builder) {
            // Only apply scope if we have a selected department and it's NOT the admin department
            if (!session()->has('selected_department_id') || session('is_admin_department')) {
                return;
            }

            $selectedId = session('selected_department_id');
            $tableName = $builder->getModel()->getTable();
            $column = $builder->getModel()->qualifyColumn('department_id');

            // Special handling for Assets to include shared ones AND filter by category visibility
            if ($builder->getModel() instanceof \App\Models\Asset) {
                $builder->where(function ($query) use ($selectedId, $column) {
                    // 1. Assets owned or shared directly with this department
                    $query->where($column, $selectedId)
                          ->orWhereExists(function ($q) use ($selectedId) {
                              $q->select(\Illuminate\Support\Facades\DB::raw(1))
                                ->from('asset_department')
                                ->whereColumn('asset_department.asset_id', 'assets.id')
                                ->where('asset_department.department_id', $selectedId);
                          });
                    
                    // 2. OR Assets whose category is visible to this department
                    $query->orWhereExists(function ($cq) use ($selectedId) {
                        $cq->select(\Illuminate\Support\Facades\DB::raw(1))
                           ->from('category_department')
                           ->whereColumn('category_department.category_id', 'assets.category_id')
                           ->where('category_department.department_id', $selectedId);
                    });
                });

                // 3. AND Assets must be in a visible building (Infrastructure constraint)
                $builder->whereExists(function ($bq) use ($selectedId) {
                    $bq->select(\Illuminate\Support\Facades\DB::raw(1))
                       ->from('rooms')
                       ->join('levels', 'rooms.level_id', '=', 'levels.id')
                       ->join('buildings', 'levels.building_id', '=', 'buildings.id')
                       ->join('building_department', 'buildings.id', '=', 'building_department.building_id')
                       ->whereColumn('rooms.id', 'assets.room_id')
                       ->where('building_department.department_id', $selectedId);
                });
            } else {
                $builder->where($column, $selectedId);
            }
        });
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
