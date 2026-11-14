<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Price List - {{ $mainBranch->name ?? 'Laundry Zone' }}</title>
    @include('settings.pages.price_list_styles')
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
