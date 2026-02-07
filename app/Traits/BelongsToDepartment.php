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

        if (session()->has('selected_department_id')) {
            $selectedId = session('selected_department_id');
            static::addGlobalScope('department', function (Builder $builder) use ($selectedId) {
                // Bypass department scoping for SuperAdmins
                $user = \Illuminate\Support\Facades\Auth::user();
                if ($user && method_exists($user, 'hasRole') && $user->hasRole('SuperAdmin')) {
                    return;
                }

                $tableName = $builder->getModel()->getTable();
                $column = $builder->getModel()->qualifyColumn('department_id');

                // Special handling for Assets to include shared ones
                if ($builder->getModel() instanceof \App\Models\Asset) {
                    $builder->where(function ($query) use ($selectedId, $column) {
                        $query->where($column, $selectedId)
                              ->orWhereExists(function ($q) use ($selectedId) {
                                  $q->select(\Illuminate\Support\Facades\DB::raw(1))
                                    ->from('asset_department')
                                    ->whereColumn('asset_department.asset_id', 'assets.id')
                                    ->where('asset_department.department_id', $selectedId);
                              });
                    });
                } else {
                    $builder->where($column, $selectedId);
                }
            });
        }
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
