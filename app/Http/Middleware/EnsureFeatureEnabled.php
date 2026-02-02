<?php

namespace App\Http\Middleware;

use App\Models\Feature;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureEnabled
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $featureKey): Response
    {
        $departmentId = $request->session()->get('selected_department_id');

        if (!$departmentId) {
            return redirect()->route('departments.select');
        }

        $enabled = Feature::query()
            ->where('key', $featureKey)
            ->whereHas('departments', function ($query) use ($departmentId) {
                $query->where('departments.id', $departmentId)
                    ->where('department_feature.is_enabled', true);
            })
            ->exists();

        if (!$enabled) {
            abort(403, 'Feature not enabled for this department.');
        }

        return $next($request);
    }
}
