<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Card - {{ $order->order_number }}</title>
    @php
        $layout = $layout ?? \App\Models\Central\TenantPrintLayout::getForTenant(tenant('id'), 'receipt');
        $cfg = $layout->custom_fields ?? [];
        $paperWidth = $cfg['paper_width'] ?? '80mm';
        $orderNoSize = $cfg['order_no_size'] ?? '20px';
        $showLogo = $layout->show_logo;
        $logoSize = $cfg['logo_size'] ?? '45';
        $showCustomerPhone = ($cfg['show_customer_phone'] ?? '1') == '1';
        $showDueDate = ($cfg['show_due_date'] ?? '1') == '1';
        $showTotals = ($cfg['show_totals'] ?? '1') == '1';
        $showSpecialNotes = ($cfg['show_special_notes'] ?? '1') == '1';
        $notesLabel = $cfg['notes_label'] ?? 'Special Note :';
        $notesLineCount = (int)($cfg['notes_line_count'] ?? 2);
        $showProcessList = ($cfg['show_process_list'] ?? '1') == '1';
        
        $processItems = $cfg['process_items'] ?? null;
        if (!is_array($processItems) || empty($processItems)) {
            $processItems = [
                ['name' => 'Collected', 'suffix' => 'Kamal'],
                ['name' => 'Wash', 'suffix' => '________'],
                ['name' => 'Iron', 'suffix' => '________'],
                ['name' => 'Quality check', 'suffix' => '________'],
                ['name' => 'Final inspection', 'suffix' => '________'],
            ];
        }

        $fontFamily = $layout->font_family ?: "'Helvetica Neue', Helvetica, Arial, sans-serif";
        $fontSize = $layout->font_size ?: '12px';
        $headerSubtitle = $layout->header_subtitle ?: '';
    @endphp
    @include('orders.partials.chit.styles')
</head>
<body>
    <!-- Size Control Panel -->
    <div id="ctrl-panel">
        <label>&#128207; Paper Width:</label>
        <select id="sel-width" onchange="applySize()">
            <option value="58mm" {{ $paperWidth === '260px' || $paperWidth === '58mm' ? 'selected' : '' }}>58mm (Narrow)</option>
            <option value="72mm" {{ $paperWidth === '72mm' ? 'selected' : '' }}>72mm</option>
            <option value="80mm" {{ $paperWidth === '300px' || $paperWidth === '80mm' ? 'selected' : '' }}>80mm (Wide)</option>
            <option value="100mm" {{ $paperWidth === '360px' || $paperWidth === '100mm' ? 'selected' : '' }}>100mm</option>
            <option value="148mm">A6 (148mm)</option>
            <option value="210mm">A5 (210mm)</option>
        </select>

        <label>&#128288; Font Size:</label>
        <input type="range" id="sl-font" min="8" max="22" value="{{ (int)$fontSize }}" step="1" oninput="applySize()">
        <span id="scale-val">{{ $fontSize }}</span>

        <button id="btn-print" onclick="window.print()">&#128438; Print</button>
    </div>

    <!-- Chit Content -->
    <div id="chit-wrapper" style="margin-top: 56px;">
        <div class="header">
            @if($showLogo)
                @php
                    $tenant = \App\Services\Tenancy\TenantManager::getTenant();
                    $logoUrl = $tenant && $tenant->logo_path ? asset($tenant->logo_path) : asset('images/laundry_main_logo.png');
                @endphp
                <div style="margin-bottom: 6px;">
                    <img src="{{ $logoUrl }}" style="max-height: {{ $logoSize }}px; max-width: {{ $logoSize }}px; object-fit: contain;" onerror="this.style.display='none'">
                </div>
            @endif
            <div class="text-bold text-center">{{ $order->order_number }}</div>
            <div class="text-small">
                @if($order->customer && $order->customer->customer_mark)
                    {{ $order->customer->customer_mark }} -
                @endif
                {{ strtoupper(optional($order->customer)->title ? optional($order->customer)->title . ' ' : '') }}{{ strtoupper(optional($order->customer)->name ?? 'Walk-in') }}
                @if($showCustomerPhone && $order->customer && $order->customer->phone)
                    <br>{{ $order->customer->phone }}
                @endif
            </div>
            @if($headerSubtitle)
                <div style="font-size: 0.7em; color: #475569; font-weight: bold; margin-top: 2px;">{{ $headerSubtitle }}</div>
            @endif
        </div>

        @if($showDueDate)
            <div class="date">{{ $order->due_date ? $order->due_date->format('d M Y h:i A') : 'N/A' }}</div>
        @endif

        @include('orders.partials.chit.items_totals')
    </div>

    <script>
        const STORE_KEY = 'chit_print_settings';
        function applySize() {
            const width = document.getElementById('sel-width').value;
            const fontSize = parseInt(document.getElementById('sl-font').value);
            document.getElementById('scale-val').textContent = fontSize + 'px';
            const wrapper = document.getElementById('chit-wrapper');
            wrapper.style.width = width;
            wrapper.style.fontSize = fontSize + 'px';
            localStorage.setItem(STORE_KEY, JSON.stringify({ width, fontSize }));
        }

        window.onload = function () {
            try {
                const saved = JSON.parse(localStorage.getItem(STORE_KEY));
                if (saved) {
                    if (saved.width) document.getElementById('sel-width').value = saved.width;
                    if (saved.fontSize) document.getElementById('sl-font').value = saved.fontSize;
                }
            } catch (e) { }
            applySize();

            if (window.location.search.includes('auto_print=1')) {
                setTimeout(function () {
                    window.print();
                }, 500);
            }
        };
    </script>
</body>
</html>
