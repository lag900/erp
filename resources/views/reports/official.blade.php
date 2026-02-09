@php
    $bodyTextColor = $settings->body_text_color ?? '#1a1a1a';
    $headerTextColor = $settings->header_text_color ?? '#000000';
@endphp
<!DOCTYPE html>
<html dir="rtl" lang="ar" style="--body-color: {{ $bodyTextColor }}; --header-color: {{ $headerTextColor }};">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            text-align: right;
            color: var(--body-color, #1a1a1a);
        }
        .header {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
            color: var(--header-color, #000000);
        }
        .header-content {
            display: table;
            width: 100%;
        }
        .logo-container {
            display: table-cell;
            vertical-align: middle;
            width: 20%;
        }
        .header-text {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            width: 60%;
        }
        .logo-img {
            max-width: 80px;
            max-height: 80px;
        }
        .report-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            background-color: #f8f9fa;
            padding: 10px;
            border: 1px solid #ddd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: right;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .signatures {
            margin-top: 50px;
            width: 100%;
            page-break-inside: avoid;
        }
        .signature-box {
            float: right;
            width: 30%;
            text-align: center;
            margin: 0 1.5%;
        }
        .stamp-box {
            clear: both;
            margin-top: 20px;
            text-align: center;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
        @page {
            margin: 100px 25px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <!-- Right Logo -->
            <div class="logo-container" style="text-align: right;">
                @if(!$settings->remove_right_logo && $settings->right_logo_path)
                    <img src="{{ public_path('storage/' . $settings->right_logo_path) }}" class="logo-img" alt="Right Logo">
                @endif
            </div>

            <!-- Center Text -->
            <div class="header-text">
                @if($settings->header_line_1)
                    <div style="font-weight: bold; font-size: 14px;">{{ $settings->header_line_1 }}</div>
                @endif
                @if($settings->header_line_2)
                    <div style="font-size: 12px;">{{ $settings->header_line_2 }}</div>
                @endif
                @if($settings->header_line_3)
                    <div style="font-size: 12px;">{{ $settings->header_line_3 }}</div>
                @endif
            </div>

            <!-- Left Logo -->
            <div class="logo-container" style="text-align: left;">
                @if(!$settings->remove_left_logo && $settings->left_logo_path)
                    <img src="{{ public_path('storage/' . $settings->left_logo_path) }}" class="logo-img" alt="Left Logo">
                @endif
            </div>
        </div>
        
        @if(!$settings->remove_center_logo && $settings->center_logo_path)
            <div style="text-align: center; margin-top: 10px;">
                <img src="{{ public_path('storage/' . $settings->center_logo_path) }}" class="logo-img" alt="Center Logo" style="max-height: 60px;">
            </div>
        @endif
    </div>

    <div class="report-title">
        {{ $settings->custom_header_title ?: $title }}
    </div>

    <div class="content">
        <!-- Report Content Table -->
        @if(isset($assets) && count($assets) > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 25%">الوصف</th>
                        <th style="width: 15%">التصنيف</th>
                        <th style="width: 15%">المبنى</th>
                        <th style="width: 10%">الغرفة</th>
                        <th style="width: 15%">العهدة</th>
                        <th style="width: 15%">الحالة</th>
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
                            <td>{{ $asset->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="text-align: center; padding: 20px;">
                لا توجد بيانات للعرض
            </div>
        @endif
    </div>

    <!-- Signatures Section -->
    @if(!empty($settings->signatures))
        <div class="signatures">
            @foreach($settings->signatures as $signature)
                <div class="signature-box">
                    <div style="font-weight: bold; margin-bottom: 40px;">{{ $signature['title'] }}</div>
                    <div>{{ $signature['name'] }}</div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Stamp Section -->
    @if($settings->show_stamp)
        <div class="stamp-box">
            <div style="border: 2px dashed #ccc; display: inline-block; padding: 20px; border-radius: 10px;">
                {{ $settings->stamp_label ?: 'ختم الإعتماد' }}
            </div>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        {{ $settings->footer_text ?? 'Generated by Batu ERB System' }} - {{ date('Y-m-d H:i') }}
    </div>
</body>
</html>
