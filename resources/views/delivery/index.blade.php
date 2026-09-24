<x-app-layout>
    @php
    if (!function_exists('formatCartForPreview')) {
        function formatCartForPreview($cart_json) {
            if (!$cart_json) return null;
            $cartArr = is_string($cart_json) ? json_decode($cart_json, true) : $cart_json;
            if (!is_array($cartArr)) return null;
            
            $processed = [];
            foreach ($cartArr as $ci) {
                $svc = \App\Models\ServiceType::with('category')->find($ci['service_type_id'] ?? $ci['id'] ?? null);
                $qty = (float)($ci['qty'] ?? 1);
                $base_price = (float)($ci['base_price'] ?? 0);
                
                $ttId = $ci['turnaround_type_id'] ?? null;
                $ttCharge = 0;
                if ($ttId) {
                    $tt = \App\Models\TurnaroundType::find($ttId);
                    if ($tt && !$tt->is_default) {
                        $ttCharge = $tt->markup_type === 'percentage' ? ($base_price * $qty) * ($tt->markup_value / 100) : $tt->markup_value * $qty;
                    }
                }
                
                $extras = [];
                foreach ($ci['selected_extras'] ?? [] as $se) {
                    $exId = is_array($se) ? ($se['id'] ?? null) : $se;
                    $exQty = is_array($se) ? ($se['qty'] ?? 1) : 1;
                    $ex = \App\Models\Extra::find($exId);
                    if ($ex) {
                        $extras[] = ['name' => $ex->name, 'qty' => $exQty, 'price' => $ex->price];
                    }
                }
                
                $processed[] = [
                    'name' => $ci['name'] ?? ($svc ? $svc->name : 'Service'),
                    'service' => $svc ? ['category' => $svc->category ? ['name' => $svc->category->name] : null] : null,
                    'qty' => $qty,
                    'base_price' => $base_price,
                    'turnaround_extra_charge' => $ttCharge,
                    'extras' => $extras,
                ];
            }
            return $processed;
        }
    }
    @endphp
    <x-slot name="header">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div>
                <h2 class="ph-title">Delivery & Logistics</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Manage incoming pickups and outgoing return deliveries</p>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div style="margin-bottom:16px;padding:12px 18px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;font-size:13px;font-weight:700;color:#16a34a;">
            ✓ {{ session('success') }}
        </div>
    @endif

    <div x-data="deliveryManager()">
        @include('delivery.partials.stat-cards')

        <!-- TABLE SECTION -->
        <div style="background:white;border-radius:16px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);overflow:hidden;">
            <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;background:#f8fafc;">
                <h3 style="font-size:16px;font-weight:900;color:#0f172a;margin:0;">{{ $currentTab === 'pickups' ? 'Incoming Pickups List' : 'Return Deliveries List' }}</h3>
            </div>

            <div style="overflow-x:auto;">
                @include('delivery.partials.pickups-table')
                @include('delivery.partials.returns-table')

            <!-- Pagination -->
            @if($appointments->hasPages())
                <div style="padding:16px 24px;border-top:1px solid #f1f5f9;">
                    {{ $appointments->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

        @include('delivery.partials.modals')
        @include('delivery.partials.scripts')
</x-app-layout>


