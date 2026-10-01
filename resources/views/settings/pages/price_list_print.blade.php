<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Price List - {{ $mainBranch->name ?? 'Laundry Zone' }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            padding: 30px;
            color: #1e293b;
            margin: 0 auto;
            max-width: 900px;
            background: #fff;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Header Section */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
        }

        .logo-section h1 {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 5px 0;
            letter-spacing: -0.02em;
        }

        .logo-section p {
            margin: 0;
            color: #475569;
            font-size: 13px;
            line-height: 1.5;
        }

        .title-section {
            text-align: right;
        }

        .title-section h2 {
            font-size: 28px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin: 0 0 8px 0;
        }

        .title-section p {
            margin: 0;
            color: #64748b;
            font-size: 12px;
            font-weight: 500;
        }

        /* Category Section */
        .main-category-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .main-category-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 10px;
            text-transform: uppercase;
            background: #f1f5f9;
            padding: 8px 12px;
            border-left: 4px solid #3b82f6;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 10px;
        }

        thead th {
            background: #f8fafc;
            color: #475569;
            text-align: left;
            padding: 8px 10px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-top: 1px solid #e2e8f0;
            border-bottom: 2px solid #cbd5e1;
        }

        tbody td {
            padding: 6px 10px;
            border-bottom: 1px dashed #e2e8f0;
            vertical-align: middle;
            color: #334155;
        }

        tbody tr:last-child td {
            border-bottom: 1px solid #e2e8f0;
        }

        /* Typographic Utilities */
        .code {
            font-family: 'Inter', monospace;
            font-weight: 600;
            color: #64748b;
            font-size: 11px;
        }

        .subcat-name {
            font-weight: 500;
            color: #475569;
        }

        .service-name {
            font-weight: 500;
            color: #0f172a;
        }

        .price {
            text-align: right;
            font-weight: 600;
            white-space: nowrap;
            color: #0f172a;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            padding-top: 15px;
        }

        @media print {
            body {
                padding: 10px;
                max-width: 100%;
            }

            .no-print {
                display: none;
            }

            thead {
                display: table-header-group;
            }

            .main-category-section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()"
            style="padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px;">🖨
            Print / Save as PDF</button>
        <button onclick="window.close()"
            style="padding: 8px 16px; background: #e5e7eb; color: #374151; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px; margin-left: 8px;">Close</button>
    </div>

    <div class="header">
        <div class="logo-section">
            @if($mainBranch && $mainBranch->logo_path)
                <img src="{{ asset($mainBranch->logo_path) }}" alt="Logo"
                    style="max-width: 120px; height: auto; margin-bottom: 10px; display: block;"
                    onerror="this.style.display='none'">
            @endif
            <h1>{{ \App\Models\Setting::get('company_name', 'Laundry Zone') }}</h1>
            <div style="font-size: 13px; color: #475569; line-height: 1.5;">
                @if(\App\Models\Setting::get('company_address'))
                    {{ \App\Models\Setting::get('company_address') }}<br>
                @endif
                @if(\App\Models\Setting::get('company_phone'))
                    Tel: {{ \App\Models\Setting::get('company_phone') }}
                @endif
                @if(\App\Models\Setting::get('company_email'))
                    | Email: {{ \App\Models\Setting::get('company_email') }}
                @endif
            </div>
        </div>
        <div class="title-section">
            <h2>PRICE LIST</h2>
            <p>Generated: {{ date('M d, Y') }}</p>
        </div>
    </div>

    @if($mainCategories->isEmpty())
        <p style="text-align: center; color: #64748b;">No services available.</p>
    @else
        @foreach($mainCategories as $category)
            @php
                $hasServices = $category->services->count() > 0 || $category->children->filter(fn($c) => $c->services->count() > 0)->count() > 0;
            @endphp

            @if($hasServices)
                <div class="main-category-section">
                    <div class="main-category-title">{{ $category->name }}</div>

                    <table>
                        <thead>
                            <tr>
                                <th style="width: 15%;">Code</th>
                                <th style="width: 25%;">Category</th>
                                <th style="width: 45%;">Service</th>
                                <th style="width: 15%; text-align: right;">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Services directly under main category --}}
                            @foreach($category->services as $service)
                                <tr>
                                    <td class="code">{{ $service->item_code ?? '-' }}</td>
                                    <td class="subcat-name">-</td>
                                    <td class="service-name">{{ $service->icon }} {{ $service->name }}</td>
                                    <td class="price">Rs.{{ number_format($service->base_price, 2) }}</td>
                                </tr>
                            @endforeach

                            {{-- Sub-categories --}}
                            @foreach($category->children as $subCategory)
                                @foreach($subCategory->services as $service)
                                    <tr>
                                        <td class="code">{{ $service->item_code ?? '-' }}</td>
                                        <td class="subcat-name">{{ $subCategory->name }}</td>
                                        <td class="service-name">{{ $service->icon }} {{ $service->name }}</td>
                                        <td class="price">Rs.{{ number_format($service->base_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endforeach
    @endif

    <div class="footer">
        Prices and services are subject to change without prior notice.
    </div>

    <script>
        window.onload = function () {
            setTimeout(function () {
                window.print();
            }, 500);
        }
    </script>
</body>

</html>