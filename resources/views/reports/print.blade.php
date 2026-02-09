@php
    $bodyTextColor = $settings->body_text_color ?? '#1a1a1a';
@endphp
<!DOCTYPE html>
<html dir="rtl" lang="ar" style="--body-color: {{ $bodyTextColor }};">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            text-align: right;
            color: var(--body-color, #1a1a1a);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header-center {
            text-align: center;
            flex-grow: 1;
        }
        .logo-img {
            max-height: 80px;
        }
        .report-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: right;
        }
        th {
            background-color: #f8f9fa;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .signatures-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 50px;
        }
        .signature-box {
            text-align: center;
            padding: 20px;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
            table { font-size: 12px; }
            th, td { padding: 6px; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: left; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Print Report</button>
    </div>

    <!-- Header Section -->
    <div class="header">
        <div class="logo-left">
            @if(!$settings->remove_right_logo && $settings->right_logo_path)
                <img src="{{ asset('storage/' . $settings->right_logo_path) }}" class="logo-img">
            @endif
        </div>
        
        <div class="header-center">
            @if(!$settings->remove_center_logo && $settings->center_logo_path)
                <img src="{{ asset('storage/' . $settings->center_logo_path) }}" class="logo-img" style="margin-bottom: 10px;">
            @endif
            @if($settings->header_line_1) <h3>{{ $settings->header_line_1 }}</h3> @endif
            @if($settings->header_line_2) <div>{{ $settings->header_line_2 }}</div> @endif
            @if($settings->header_line_3) <div>{{ $settings->header_line_3 }}</div> @endif
        </div>

        <div class="logo-right">
            @if(!$settings->remove_left_logo && $settings->left_logo_path)
                <img src="{{ asset('storage/' . $settings->left_logo_path) }}" class="logo-img">
            @endif
        </div>
    </div>

    <h1 class="report-title">{{ $settings->custom_header_title ?: $title }}</h1>

    <!-- Data Table -->
    @if(isset($assets) && count($assets) > 0)
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>الوصف</th>
                    <th>التصنيف</th>
                    <th>المبنى</th>
                    <th>الغرفة</th>
                    <th>العهدة</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assets as $index => $asset)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $asset->description }}</td>
                        <td>{{ optional($asset->category)->name }}</td>
                        <td>{{ optional($asset->building)->name }}</td>
                        <td>{{ optional($asset->room)->number }}</td>
                        <td>{{ $asset->custodian ? $asset->custodian->name : '-' }}</td>
                        <td>
                            <span style="padding: 2px 8px; border-radius: 4px; background: #eee;">
                                {{ $asset->status }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 40px; color: #666;">
            لا توجد بيانات للعرض
        </div>
    @endif

    <!-- Signatures -->
    @if(!empty($settings->signatures))
        <div class="signatures-grid">
            @foreach($settings->signatures as $signature)
                <div class="signature-box">
                    <p style="font-weight: bold; margin-bottom: 40px;">{{ $signature['title'] }}</p>
                    <p>{{ $signature['name'] }}</p>
                </div>
            @endforeach
        </div>
    @endif

    <div style="margin-top: 40px; text-align: center; color: #666; font-size: 12px; border-top: 1px solid #eee; padding-top: 10px;">
        {{ $settings->footer_text ?? 'Generated by Batu ERB System' }} - {{ date('Y-m-d H:i') }}
    </div>
</body>
</html>
