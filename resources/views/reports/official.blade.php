<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page {
            margin: 1.5cm;
            footer: page-footer;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #1a1a1a;
            direction: rtl;
            text-align: right;
            line-height: 1.5;
        }
        .header-table {
            width: 100%;
            border-bottom: 3px solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            max-height: 100px;
            max-width: 120px;
        }
        .header-center {
            text-align: center;
        }
        .header-center h1 {
            font-size: 20pt;
            margin: 0;
            font-weight: bold;
            color: #000;
        }
        .header-center h2 {
            font-size: 14pt;
            margin: 5px 0 0;
            font-weight: normal;
        }
        .report-title-box {
            margin-top: 15px;
        }
        .report-title {
            font-size: 16pt;
            font-weight: bold;
            text-decoration: underline;
        }
        
        .info-grid {
            width: 100%;
            margin-bottom: 30px;
            border: 1px solid #eee;
            background: #fcfcfc;
            border-radius: 8px;
        }
        .info-grid td {
            padding: 12px;
            border: 1px solid #f0f0f0;
        }
        .info-label {
            font-size: 9pt;
            color: #666;
            margin-bottom: 3px;
            display: block;
        }
        .info-value {
            font-size: 11pt;
            font-weight: bold;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        table.data-table th {
            background-color: #f8f9fa;
            border: 1px solid #222;
            padding: 10px 8px;
            font-size: 9pt;
            font-weight: bold;
            text-align: center;
        }
        table.data-table td {
            border: 1px solid #ccc;
            padding: 8px;
            font-size: 9pt;
            text-align: center;
        }
        .status-badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: bold;
        }

        .signatures-section {
            margin-top: 60px;
            width: 100%;
        }
        .signature-box {
            width: 33%;
            text-align: center;
            vertical-align: top;
        }
        .signature-label {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 50px;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 80%;
            margin: 0 auto 5px;
        }
        .signature-name {
            font-size: 9pt;
            color: #444;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            font-size: 8pt;
            color: #999;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 20%; text-align: right;">
                @if($department->university_logo)
                    <img src="{{ public_path('storage/' . $department->university_logo) }}" class="logo">
                @else
                    <div style="width: 100px; height: 100px; border: 1px dashed #ccc; text-align: center; line-height: 100px; font-size: 8pt;">Logo</div>
                @endif
            </td>
            <td class="header-center" style="width: 60%;">
                <h1>{{ config('app.org_name', 'جامعة الملك خالد') }}</h1>
                <h2>{{ $department->arabic_name ?? $department->name }}</h2>
                <div class="report-title-box">
                    <span class="report-title">{{ $title }}</span>
                </div>
            </td>
            <td style="width: 20%; text-align: left;">
                @if($department->department_logo)
                    <img src="{{ public_path('storage/' . $department->department_logo) }}" class="logo">
                @endif
            </td>
        </tr>
    </table>

    <table class="info-grid">
        <tr>
            <td>
                <span class="info-label">تاريخ التقرير / Report Date</span>
                <span class="info-value">{{ $generated_at }}</span>
            </td>
            <td>
                <span class="info-label">القسم المختص / Department</span>
                <span class="info-value">{{ $department->arabic_name ?? $department->name }}</span>
            </td>
            <td>
                <span class="info-label">إجمالي الأصول / Total Assets</span>
                <span class="info-value">{{ count($assets) }}</span>
            </td>
            <td>
                <span class="info-label">تم الاستخراج بواسطة / Generated By</span>
                <span class="info-value">{{ $generated_by }}</span>
            </td>
        </tr>
    </table>

    @if($type === 'summary')
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th style="text-align: right;">تصنيف الأصول / Asset Category</th>
                <th>إجمالي العدد / Total Count</th>
                <th>النسبة المئوية / Percentage</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = $assets->sum('total'); @endphp
            @foreach($assets as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td style="text-align: right; font-weight: bold;">{{ $item->category->name ?? 'Uncategorized' }}</td>
                <td style="font-weight: bold;">{{ $item->total }}</td>
                <td>{{ $grandTotal > 0 ? round(($item->total / $grandTotal) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #eee;">
                <td colspan="2" style="text-align: right; font-weight: bold;">الإجمالي / TOTAL</td>
                <td style="font-weight: bold;">{{ $grandTotal }}</td>
                <td style="font-weight: bold;">100%</td>
            </tr>
        </tfoot>
    </table>
    @else
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th>كود الأصل / Asset Code</th>
                <th>اسم المادة / Asset Name</th>
                <th>التصنيف / Category</th>
                <th>الموقع / Location</th>
                <th>الحالة / Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assets as $index => $asset)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td style="font-family: 'monospace'; font-weight: bold;">{{ $asset->asset_code ?? $asset->code }}</td>
                <td style="font-weight: bold;">{{ $asset->name }}</td>
                <td>{{ $asset->category->name ?? '-' }}</td>
                <td>
                    {{ $asset->building->name ?? '' }} 
                    {{ $asset->room ? ' - ' . $asset->room->name : '' }}
                </td>
                <td>
                    @switch($asset->status)
                        @case('new') جديد @break
                        @case('active') مفعل @break
                        @case('good') جيد @break
                        @case('damaged') تالف @break
                        @case('maintenance') صيانة @break
                        @case('retired') مستبعد @break
                        @default {{ $asset->status }}
                    @endswitch
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <table class="signatures-section">
        <tr>
            <td class="signature-box">
                <div class="signature-label">إعداد بواسطة / Prepared By</div>
                <div style="height: 60px;"></div>
                <div class="signature-line"></div>
                <div class="signature-name">الاسم: ........................................</div>
            </td>
            <td class="signature-box">
                <div class="signature-label">مراجعة بواسطة / Reviewed By</div>
                <div style="height: 60px;"></div>
                <div class="signature-line"></div>
                <div class="signature-name">الاسم: ........................................</div>
            </td>
            <td class="signature-box">
                <div class="signature-label">اعتماد بواسطة / Approved By</div>
                <div style="height: 60px;"></div>
                <div class="signature-line"></div>
                <div class="signature-name">الاسم: ........................................</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        هذا التقرير تم استخراجه آلياً من نظام إدارة الأصول (ERB) - {{ date('Y-m-d H:i') }}
    </div>
</body>
</html>
