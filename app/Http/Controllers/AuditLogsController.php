<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Gate;

class AuditLogsController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', AuditLog::class);

        $query = AuditLog::query()
            ->with('user')
            ->orderBy('created_at', 'desc');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%")
                  ->orWhere('action_type', 'like', "%{$search}%")
                  ->orWhere('auditable_type', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(20)->withQueryString();

        // Stats for Dashboard
        $stats = [
            'total_today' => AuditLog::whereDate('created_at', today())->count(),
            'warnings_today' => AuditLog::whereDate('created_at', today())->where('severity', 'warning')->count(),
            'critical_today' => AuditLog::whereDate('created_at', today())->where('severity', 'critical')->count(),
            'modules' => AuditLog::select('module', DB::raw('count(*) as total'))
                ->groupBy('module')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get(),
        ];

        return Inertia::render('Audit/Index', [
            'logs' => $logs,
            'filters' => $request->all(['search', 'user_id', 'module', 'action_type', 'severity', 'date_from', 'date_to']),
            'users' => User::select('id', 'name')->get(),
            'stats' => $stats,
            'modules' => AuditLog::distinct()->pluck('module'),
            'actionTypes' => AuditLog::distinct()->pluck('action_type'),
        ]);
    }

    public function show(AuditLog $log)
    {
        Gate::authorize('view', $log);
        return Inertia::render('Audit/Show', [
            'log' => $log->load('user')
        ]);
    }

    public function securityAlerts()
    {
        Gate::authorize('viewAny', AuditLog::class);

        $alerts = AuditLog::whereIn('severity', ['warning', 'critical'])
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Audit/SecurityAlerts', [
            'alerts' => $alerts
        ]);
    }

    public function export(Request $request)
    {
        Gate::authorize('manage-audit-logs');
        
        // In a real app, use Maatwebsite/Laravel-Excel or similar
        // For now, return a simple JSON/CSV response or placeholder logic
        
        $logs = AuditLog::orderBy('created_at', 'desc')->limit(1000)->get();
        
        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'User', 'Role', 'Action', 'Module', 'Status', 'Severity', 'IP', 'Timestamp']);
            
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user_name,
                    $log->role,
                    $log->action_type,
                    $log->module,
                    $log->status,
                    $log->severity,
                    $log->ip_address,
                    $log->created_at
                ]);
            }
            fclose($file);
        };

        return response()->streamDownload($callback, 'audit_logs_' . date('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function cleanup(Request $request)
    {
        Gate::authorize('manage-audit-logs');
        Gate::authorize('delete', AuditLog::class);
        
        $months = $request->input('months', 6);
        $date = now()->subMonths($months);
        
        $count = AuditLog::where('created_at', '<', $date)->delete();
        
        return back()->with('success', "Successfully cleaned up {$count} logs older than {$months} months.");
    }
}
