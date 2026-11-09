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
    <style>
        /* ── Control Panel (hidden on print) ── */
        #ctrl-panel {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #1e293b;
            color: #f1f5f9;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            font-family: system-ui, sans-serif;
            font-size: 12px;
            z-index: 9999;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        #ctrl-panel label {
            font-weight: 700;
            color: #94a3b8;
            white-space: nowrap;
        }

        #ctrl-panel select,
        #ctrl-panel input[type=range] {
            background: #334155;
            color: #f1f5f9;
            border: 1px solid #475569;
            border-radius: 6px;
            padding: 3px 8px;
            font-size: 12px;
            cursor: pointer;
        }

        #ctrl-panel input[type=range] {
            width: 80px;
            padding: 4px 0;
        }

        #btn-print {
            margin-left: auto;
            background: #3b82f6;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 5px 14px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        #btn-print:hover {
            background: #2563eb;
        }

        /* ── Base Styles ── */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: {!! $fontFamily !!};
            font-size: {{ $fontSize }};
            color: #000;
            background: #f8fafc;
            display: flex;
            justify-content: center;
        }

        #chit-wrapper {
            background: #fff;
            padding: 10px 10px 20px 10px;
            width: {{ $paperWidth }};
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .text-bold {
            font-weight: 900;
            font-size: {{ $orderNoSize }};
            margin: 3px 0;
        }

        .text-small {
            font-weight: 900;
            font-size: 0.9em;
            margin: 3px 0;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .date {
            font-weight: 900;
            font-size: 0.9em;
            margin: 5px 0 10px;
            text-align: center;
            text-transform: uppercase;
        }

        .items {
            margin-top: 10px;
            margin-bottom: 15px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 0.72em;
            margin-bottom: 5px;
            padding-bottom: 2px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 0.78em;
            margin-top: 5px;
            border-bottom: 1px solid #000;
            padding: 5px 0;
            margin-bottom: 15px;
        }

        .process-list {
            margin-top: 15px;
            font-size: 0.78em;
            font-weight: bold;
        }

        .process-list div {
            margin-bottom: 15px;
        }

        .special-note {
            margin-top: 15px;
            font-size: 0.78em;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .note-line {
            border-bottom: 1px dotted #000;
            height: 18px;
            margin-top: 4px;
        }

        @media print {
            #ctrl-panel {
                display: none !important;
            }

            body {
                background: #fff;
            }

            #chit-wrapper {
                box-shadow: none;
                padding: 0;
                margin-top: 0 !important;
            }

            @page {
                margin: 0;
            }
        }
    </style>
</head>

<body>

    <!-- ── Size Control Panel ── -->
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

    <!-- ── Chit Content ── -->
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

        <div class="items">
            @php $total_qty = 0; @endphp
            @foreach($order->items as $item)
                @php
                    $catTrail = '';
                    if ($item->serviceType && $item->serviceType->category) {
                        $cat = $item->serviceType->category;
                        $catTrail = $cat->parent ? $cat->parent->name . ' > ' . $cat->name : $cat->name;
                    }
                    $itemName = $item->serviceType ? $item->serviceType->name : $item->item_description;
                    $total_qty += $item->quantity;
                @endphp
                <div style="margin-bottom: 8px;">
                    @if($catTrail)
                        <div style="font-size: 0.6em; font-weight: bold; text-transform: uppercase; margin-bottom: 1px;">&bull;
                            {{ $catTrail }}
                        </div>
                        <div class="item-row" style="margin-bottom: 2px;">
                            <span style="margin-left: 8px;">{{ $itemName }}</span>
                            <span>x{{ $item->quantity }}</span>
                        </div>
                    @else
                        <div class="item-row" style="margin-bottom: 2px;">
                            <span>&bull; {{ $itemName }}</span>
                            <span>x{{ $item->quantity }}</span>
                        </div>
                    @endif
                    @if($item->turnaroundType && !$item->turnaroundType->is_default)
                        <div style="font-size:0.65em; margin-left:12px; margin-bottom: 2px;">[{{ $item->turnaroundType->name }}]
                        </div>
                    @elseif((float) $item->custom_turnaround_percentage > 0)
                        <div style="font-size:0.65em; margin-left:12px; margin-bottom: 2px;">[Express
                            ({{ (float) $item->custom_turnaround_percentage }}%)]</div>
                    @endif
                    @if($item->extras->count() > 0)
                        @foreach($item->extras as $e)
                            @php $total_qty += $e->quantity; @endphp
                            <div style="font-size:0.65em; margin-left:12px; margin-bottom: 2px;">+ {{ $e->extra->name }}
                                (x{{ $e->quantity }})</div>
                        @endforeach
                    @endif
                </div>
            @endforeach

            <div class="total-row" style="border-top: 1px solid #000; margin-top: 8px; padding-top: 8px;">
                <span>Total Qty</span>
                <span>x{{ fmod($total_qty, 1) == 0 ? (int) $total_qty : number_format($total_qty, 2) }}</span>
            </div>
        </div>

        {{-- Payment Totals --}}
        @if($showTotals)
        <div style="border-top: 1px solid #000; margin-top: 15px; padding-top: 10px; padding-bottom: 15px;">
            @if($order->discount > 0)
            <div class="item-row" style="font-size: 0.72em;">
                <span>Subtotal</span>
                <span>Rs.{{ number_format($order->subtotal, 2) }}</span>
            </div>
            @php $discountPct = ($order->subtotal > 0) ? round(($order->discount / $order->subtotal) * 100, 1) : 0; @endphp
            <div class="item-row" style="font-size: 0.72em;">
                <span>Discount{{ $discountPct > 0 ? ' (' . $discountPct . '%)' : '' }}</span>
                <span>- Rs.{{ number_format($order->discount, 2) }}</span>
            </div>
            @endif
            @if($order->delivery_fee > 0)
            <div class="item-row" style="font-size: 0.72em;">
                <span>Delivery Fee</span>
                <span>Rs.{{ number_format($order->delivery_fee, 2) }}</span>
            </div>
            @endif
            <div class="total-row" style="border-top: 1px solid #000; border-bottom: none; margin-bottom: 5px; font-size: 0.82em;">
                <span>TOTAL</span>
                <span>Rs.{{ number_format($order->total, 2) }}</span>
            </div>
            <div class="item-row" style="font-size: 0.72em;">
                <span>Paid</span>
                <span>Rs.{{ number_format($order->paid_amount, 2) }}</span>
            </div>
            @php $balance = $order->total - $order->paid_amount; @endphp
            @if($balance > 0)
            <div class="item-row" style="font-size: 0.78em; font-weight: 900; border-top: 1px solid #000; padding-top: 4px; margin-top: 4px;">
                <span>Balance Due</span>
                <span>Rs.{{ number_format($balance, 2) }}</span>
            </div>
            @else
            <div class="item-row" style="font-size: 0.72em; font-weight: 900;">
                <span>✓ Fully Paid</span>
                <span>Rs.{{ number_format($order->paid_amount, 2) }}</span>
            </div>
            @endif
        </div>
        @endif

        @if($showSpecialNotes)
        <div class="special-note" style="border-top: 1px solid #000; padding-top: 15px;">
            <div>{{ $notesLabel }}</div>
            @for($i = 0; $i < $notesLineCount; $i++)
                <div class="note-line"></div>
            @endfor
        </div>
        @endif

        @if($showProcessList && !empty($processItems))
        <div class="process-list">
            @foreach($processItems as $proc)
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>{{ $proc['name'] ?? '' }} :</span>
                    <span style="font-family: monospace;">{{ $proc['suffix'] ?? '________' }}</span>
                </div>
            @endforeach
        </div>
        @endif

    </div><!-- /chit-wrapper -->

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
