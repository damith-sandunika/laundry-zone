<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Job Card - {{ $order->order_number }}</title>
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
            color: white;
            border: none;
            padding: 6px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            letter-spacing: 0.03em;
            transition: background 0.15s;
        }

        #btn-print:hover {
            background: #2563eb;
        }

        #scale-val {
            font-weight: 800;
            color: #60a5fa;
            min-width: 36px;
        }

        /* ── Chit Body ── */
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 10px;
            color: #000;
            box-sizing: border-box;
            background: #f1f5f9;
        }

        #chit-wrapper {
            background: #fff;
            margin: 0 auto;
            padding: 10px;
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }

        .text-bold {
            font-weight: 900;
            font-size: 1.4em;
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
        }

        .note-line {
            border-bottom: 1px solid #000;
            height: 18px;
            margin-top: 5px;
        }

        @media print {
            #ctrl-panel {
                display: none !important;
            }

            body {
                margin: 0;
                padding: 0;
                background: #fff;
            }

            #chit-wrapper {
                padding: 3mm;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>

    <!-- ── Size Control Panel ── -->
    <div id="ctrl-panel">
        <label>&#128207; Paper Width:</label>
        <select id="sel-width" onchange="applySize()">
            <option value="58mm">58mm (Narrow)</option>
            <option value="72mm">72mm</option>
            <option value="80mm">80mm (Wide)</option>
            <option value="100mm">100mm</option>
            <option value="148mm">A6 (148mm)</option>
            <option value="210mm">A5 (210mm)</option>
        </select>

        <label>&#128288; Font Size:</label>
        <input type="range" id="sl-font" min="8" max="22" value="12" step="1" oninput="applySize()">
        <span id="scale-val">12px</span>

        <button id="btn-print" onclick="window.print()">&#128438; Print</button>
    </div>

    <!-- ── Chit Content ── -->
    <div id="chit-wrapper" style="margin-top: 56px;">

        <div class="header">
            <div class="text-bold text-center">{{ $order->order_number }}</div>
            <div class="text-small">
                @if($order->customer && $order->customer->customer_mark)
                    {{ $order->customer->customer_mark }} -
                @endif
                {{ strtoupper(optional($order->customer)->title ? optional($order->customer)->title . ' ' : '') }}{{ strtoupper(optional($order->customer)->name ?? 'Walk-in') }}
                @if($order->customer && $order->customer->phone)
                    <br>{{ $order->customer->phone }}
                @endif
            </div>
        </div>

        <div class="date">{{ $order->due_date ? $order->due_date->format('d M Y h:i A') : 'N/A' }}</div>

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

        {{-- Payment Totals — controlled by General Setting: chit_show_totals --}}
        @if(\App\Models\Setting::get('chit_show_totals', '0') == '1')
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

        <div class="special-note" style="border-top: 1px solid #000; padding-top: 15px;">
            <div>Special Note :</div>
            <div class="note-line"></div>
            <div class="note-line"></div>
        </div>

        <div class="process-list">
            <div>Collected :</div>
            <div>Wash :</div>
            <div>Iron :</div>
            <div>Quality check :</div>
            <div>Final inspection :</div>
        </div>



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

            // Save to localStorage so next print remembers
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

            // Auto-print if requested via URL
            if (window.location.search.includes('auto_print=1')) {
                setTimeout(function () {
                    window.print();
                }, 500);
            }
        };
    </script>
</body>

</html>
