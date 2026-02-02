<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDepartmentSelected
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $selectedDepartmentId = $request->session()->get('selected_department_id');

        if ($selectedDepartmentId) {
            $hasDepartment = $user->departments()
                ->where('departments.id', $selectedDepartmentId)
                ->exists();

            if ($hasDepartment) {
                return $next($request);
            }
        }

        $defaultDepartment = $user->defaultDepartment();

        if ($defaultDepartment) {
            $request->session()->put('selected_department_id', $defaultDepartment->id);

            return $next($request);
        }

        $departmentIds = $user->departments()->pluck('departments.id');

        if ($departmentIds->count() === 1) {
            $request->session()->put('selected_department_id', $departmentIds->first());

            return $next($request);
        }

        return redirect()->route('departments.select');
    }
}
