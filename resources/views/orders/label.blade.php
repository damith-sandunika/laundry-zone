<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Label - {{ $order->order_number }}</title>
    @include('orders.partials.label.styles')
</head>
<body>

    <!-- ── Control Panel ── -->
    <div id="ctrl-panel">
        <label>Paper Width:</label>
        <select id="sel-width" onchange="applySize()">
            <option value="54mm">54mm</option>
            <option value="58mm">58mm (Narrow)</option>
            <option value="72mm">72mm</option>
            <option value="80mm" selected>80mm (Wide)</option>
            <option value="100mm">100mm</option>
            <option value="148mm">A6 (148mm)</option>
            <option value="210mm">A5 (210mm)</option>
        </select>

        <label>Font Size:</label>
        <input type="range" id="sl-font" min="8" max="22" value="12" step="1" oninput="applySize()">
        <span id="scale-val">12px</span>

        <button id="btn-print" onclick="window.print()">Print</button>
    </div>

    <!-- ── Label Content ── -->
    <div id="label-wrapper" style="margin-top: 56px;">

        <!-- Brand -->
        <div class="center" style="padding-bottom: 5px; border-bottom: 2px solid #000; margin-bottom: 6px;">
            <div class="brand-name">{{ \App\Models\Setting::get('company_name', 'Laundry Zone') }}</div>
            @if($branch)
                <div class="brand-sub" style="font-weight:800;">{{ $branch->name }}</div>
                @if($branch->address)
                    <div class="brand-sub">{{ $branch->address }}</div>
                @endif
                @if($branch->phone)
                    <div class="brand-sub">📞 {{ $branch->phone }}</div>
                @endif
            @endif
        </div>

        <!-- Invoice Number -->
        <div class="center" style="margin: 5px 0;">
            <div class="inv-label">Invoice No.</div>
            <div class="inv-number">{{ $order->order_number }}</div>
            <div class="inv-date">{{ $order->created_at->format('d M Y  h:i A') }}</div>
        </div>

        <hr class="divider-solid">

        <!-- Customer -->
        <div class="row"><span>Customer:</span><span class="bold">{{ $order->customer->name ?? 'Walk-in' }}</span></div>
        @if($order->customer?->phone)
            <div class="row"><span>Phone:</span><span class="bold">{{ $order->customer->phone }}</span></div>
        @endif
        @if($order->due_date)
            <div class="row"><span>Delivery Date:</span><span class="bold">{{ \Carbon\Carbon::parse($order->due_date)->format('d M Y') }}</span></div>
        @endif

        <hr class="divider">

        <!-- Payment -->
        @php $balance = max(0, $order->total - $order->paid_amount); @endphp
        @if($balance > 0)
        <div class="balance-row">
            <span>Pending Amount</span>
            <span>Rs. {{ number_format($balance, 2) }}</span>
        </div>
        @endif

        <!-- Delivery Address -->
        @if($order->is_delivery_order && $order->deliveryAppointment?->delivery_address)
            <hr class="divider">
            <div class="section-label">Delivery Address</div>
            <div class="address-text">{{ $order->deliveryAppointment->delivery_address }}</div>
        @endif

        <!-- Instruction Box -->
        @if($order->is_delivery_order)
            <div class="instruction">
                <div class="instruction-main">** FOR DELIVERY **</div>
                <div class="instruction-sub">Rider will deliver to customer</div>
            </div>
        @else
            <div class="instruction">
                <div class="instruction-main">COLLECT AT BRANCH</div>
                <div class="instruction-sub">{{ $branch->name ?? '' }}</div>
            </div>
        @endif

        <hr class="divider-solid" style="margin-top: 8px;">

        <!-- Footer -->
        <div class="footer-text">Thank you for choosing {{ \App\Models\Setting::get('company_name', $mainBranch->name ?? 'us') }}!</div>

    </div><!-- /label-wrapper -->

    <script>
        const STORE_KEY = 'label_print_settings';

        function applySize() {
            const width    = document.getElementById('sel-width').value;
            const fontSize = parseInt(document.getElementById('sl-font').value);
            document.getElementById('scale-val').textContent = fontSize + 'px';

            const wrapper = document.getElementById('label-wrapper');
            wrapper.style.width    = width;
            wrapper.style.fontSize = fontSize + 'px';

            localStorage.setItem(STORE_KEY, JSON.stringify({ width, fontSize }));
        }

        window.onload = function () {
            try {
                const saved = JSON.parse(localStorage.getItem(STORE_KEY));
                if (saved) {
                    if (saved.width)    document.getElementById('sel-width').value = saved.width;
                    if (saved.fontSize) document.getElementById('sl-font').value   = saved.fontSize;
                }
            } catch(e) {}
            applySize();

            if (window.location.search.includes('auto_print=1')) {
                setTimeout(function() { window.print(); }, 500);
            }
        };
    </script>
</body>
</html>
