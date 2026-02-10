<?php

namespace App\Traits;

use App\Models\Department;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Building;
use App\Models\Category;

trait HasDepartmentVisibility
{
    public static function bootHasDepartmentVisibility(): void
    {
        static::addGlobalScope('department_visibility', function (Builder $builder) {
            // Only apply scope if we have a selected department and it's NOT the admin department
            if (!session()->has('selected_department_id') || session('is_admin_department')) {
                return;
            }

            $selectedId = session('selected_department_id');
            $model = $builder->getModel();
            $tableName = $model->getTable();
            
            if ($model instanceof Building) {
                $pivotTable = 'building_department';
                $foreignKey = 'building_id';
            } elseif ($model instanceof Category) {
                $pivotTable = 'category_department';
                $foreignKey = 'category_id';
            } else {
                return;
            }

            $builder->whereExists(function ($q) use ($selectedId, $pivotTable, $foreignKey, $tableName) {
                $q->select(DB::raw(1))
                    ->from($pivotTable)
                    ->whereColumn("$pivotTable.$foreignKey", "$tableName.id")
                    ->where("$pivotTable.department_id", $selectedId);
            });
        });
    }
}
