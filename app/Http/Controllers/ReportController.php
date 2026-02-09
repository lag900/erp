<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Department;
use App\Models\ReportSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'permission:report-access']);
    }

    public function index()
    {
        return Inertia::render('Reports/Index');
    }

    public function view(Request $request, $type)
    {
        $user = Auth::user();
        $department = $user->department;

        if (!$department) {
            return redirect()->route('dashboard')
                ->with('error', 'You must be assigned to a department to access reports.');
        }

        // Build query based on report type
        $query = Asset::with(['category', 'subCategory', 'building', 'room', 'custodian'])
            ->where('department_id', $department->id);

        // Apply report type filters
        switch ($type) {
            case 'inventory':
                // All assets
                break;
            case 'custody':
                $query->whereNotNull('custodian_id');
                break;
            case 'movement':
                $query->whereNotNull('last_transfer_date');
                break;
            case 'added':
                $query->where('created_at', '>=', Carbon::now()->subMonths(3));
                break;
            case 'damaged':
                $query->whereIn('status', ['damaged', 'retired']);
                break;
            case 'summary':
                // Group by category
                $assets = Category::withCount(['assets' => function ($q) use ($department) {
                    $q->where('department_id', $department->id);
                }])
                ->having('assets_count', '>', 0)
                ->get()
                ->map(function ($category) {
                    return [
                        'category' => $category,
                        'category_id' => $category->id,
                        'total' => $category->assets_count,
                    ];
                });

                return $this->renderView($type, $assets, $department, $request);
        }

        // Apply additional filters
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $assets = $query->get();

        return $this->renderView($type, $assets, $department, $request);
    }

    private function renderView($type, $assets, $department, Request $request)
    {
        // Check for export request
        if ($request->has('export')) {
            return $this->export($type, $assets, $department, $request->export);
        }

        // Return Inertia view
        return Inertia::render('Reports/View', [
            'type' => $type,
            'assets' => $assets,
            'department' => $department,
            'categories' => Category::all(),
            'filters' => $request->only(['date_from', 'date_to', 'category_id', 'status']),
        ]);
    }

    private function export($type, $assets, $department, $format)
    {
        $settings = ReportSetting::forDepartment($department->id);
        
        $data = [
            'type' => $type,
            'assets' => $assets,
            'department' => $department,
            'generated_at' => Carbon::now()->format('Y-m-d H:i'),
            'generated_by' => Auth::check() ? Auth::user()->name : 'System',
            'title' => $this->getArabicTitle($type),
            'settings' => $settings,
            'reference_number' => 'REF-' . date('Ymd') . '-' . strtoupper(substr($type, 0, 3)) . '-' . rand(1000, 9999),
        ];

        // HTML-first approach for perfect Arabic rendering
        if ($format === 'html' || $format === 'print') {
            return view('reports.print', $data);
        }

        // Excel export for data analysis
        if ($format === 'excel') {
            return Excel::download(new \App\Exports\AssetExport($assets), "Report_{$type}_" . date('Ymd_His') . ".xlsx");
        }

        // Legacy PDF support (fallback)
        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.official', $data);
            $pdf->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);
            return $pdf->download("Report_{$type}_" . date('Ymd_His') . ".pdf");
        }

        // Default to HTML print view
        return view('reports.print', $data);
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
