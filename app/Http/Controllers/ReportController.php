<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Department;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Reports/Index', [
            'reportTypes' => [
                ['id' => 'inventory', 'name' => 'Asset Inventory Report', 'arabic_name' => 'تقرير جرد الأصول', 'description' => 'Complete list of all assets in the department.'],
                ['id' => 'custody', 'name' => 'Custody Report', 'arabic_name' => 'تقرير العهدة', 'description' => 'Assets assigned to specific users or locations.'],
                ['id' => 'movement', 'name' => 'Asset Movement Report', 'arabic_name' => 'تقرير حركة الأصول', 'description' => 'History of asset transfers and assignments.'],
                ['id' => 'added', 'name' => 'Added Assets Report', 'arabic_name' => 'تقرير الأصول المضافة', 'description' => 'New assets registered within a date range.'],
                ['id' => 'damaged', 'name' => 'Damaged/Scrap Report', 'arabic_name' => 'تقرير التالف والكهنة', 'description' => 'Assets marked as damaged or out of service.'],
                ['id' => 'summary', 'name' => 'Assets by Category', 'arabic_name' => 'ملخص الأصول حسب التصنيف', 'description' => 'Numerical summary of assets grouped by type.'],
            ]
        ]);
    }

    public function view(Request $request, string $type)
    {
        $departmentId = session('selected_department_id');
        $department = Department::findOrFail($departmentId);
        
        $query = Asset::where('department_id', $departmentId)
            ->with(['category', 'subCategory', 'building', 'level', 'room', 'creator']);

        // Apply Common Filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Specific Report Logic
        switch ($type) {
            case 'damaged':
                $query->whereIn('status', ['damaged', 'discarded', 'repair', 'needs_maintenance']);
                break;
            case 'movement':
                // For movement, we show assets that have been transferred at least once
                // This is a simplified version; a full history would need a different view
                $query->whereHas('activityLogs', function($q) {
                    $q->where('action', 'transfer');
                });
                break;
            case 'custody':
                if ($request->filled('user_id')) {
                    $query->where('created_by', $request->user_id);
                }
                break;
        }

        if ($type === 'summary') {
            $assets = Asset::where('assets.department_id', $departmentId)
                ->join('sub_categories', 'assets.sub_category_id', '=', 'sub_categories.id')
                ->select('sub_categories.category_id', DB::raw('count(*) as total'))
                ->groupBy('sub_categories.category_id')
                ->get()
                ->map(function($item) {
                    $item->category = Category::find($item->category_id);
                    return $item;
                });
        } else {
            $assets = $query->latest()->get();
        }

        if ($request->has('export')) {
            return $this->export($type, $assets, $department, $request->export);
        }

        return Inertia::render('Reports/View', [
            'type' => $type,
            'assets' => $assets,
            'department' => $department,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['date_from', 'date_to', 'category_id', 'status', 'user_id']),
        ]);
    }

    private function export($type, $assets, $department, $format)
    {
        $data = [
            'type' => $type,
            'assets' => $assets,
            'department' => $department,
            'generated_at' => Carbon::now()->format('Y-m-d H:i'),
            'generated_by' => auth()->user()->name,
            'title' => $this->getArabicTitle($type),
        ];

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.official', $data);
            return $pdf->download("Report_{$type}_" . date('Ymd_His') . ".pdf");
        }

        return Excel::download(new \App\Exports\AssetExport($assets), "Report_{$type}_" . date('Ymd_His') . ".xlsx");
    }

    private function getArabicTitle($type)
    {
        $titles = [
            'inventory' => 'تقرير جرد الأصول',
            'custody' => 'تقرير العهدة الشخصية',
            'movement' => 'تقرير حركة الأصول',
            'added' => 'تقرير الأصول المضافة حديثاً',
            'damaged' => 'تقرير الأصول التالفة والمستبعدة',
            'summary' => 'ملخص الأصول حسب التصنيف',
        ];
        return $titles[$type] ?? 'تقرير أصول';
    }
}
