@if($order->cubbies->count())
    <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.02);padding:16px;">
        <h3 style="font-size:12px;font-weight:800;color:#0f172a;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.05em;">
            Storage</h3>
        @foreach($order->cubbies->sortByDesc('assigned_at') as $oc)
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;{{ $oc->released_at ? 'opacity:0.5;' : '' }}">
                <div style="width:30px;height:30px;background:#eef2ff;color:#4f46e5;border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:12px;">
                    {{ $oc->cubby->name ?? '?' }}
                </div>
                <div style="flex:1;">
                    <p style="font-size:12px;font-weight:800;color:#0f172a;margin:0;">Cubby:
                        {{ $oc->cubby->name ?? 'N/A' }}
                    </p>
                    <p style="font-size:10px;color:#94a3b8;margin:2px 0 0;font-weight:600;">
                        Assigned: {{ \Carbon\Carbon::parse($oc->assigned_at)->format('M d, h:i A') }}
                        &bull; Loc: {{ $oc->cubby->branch->name ?? 'Unknown' }}
                    </p>
                </div>
                @if($oc->released_at)
                    <span style="font-size:9px;font-weight:700;color:#94a3b8;background:#f1f5f9;padding:2px 6px;border-radius:4px;text-transform:uppercase;">Released</span>
                @else
                    <span style="font-size:9px;font-weight:800;color:#16a34a;background:#f0fdf4;padding:2px 6px;border-radius:4px;text-transform:uppercase;">Active</span>
                @endif
            </div>
        @endforeach
    </div>
@endif

<!-- Order Notes Card -->
@if($order->notes)
    <div style="background:#fffbeb;border:1.5px solid #fef3c7;border-radius:12px;padding:12px;">
        <span style="font-size:10px;font-weight:800;color:#d97706;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:2px;">Order Notes</span>
        <p style="font-size:12px;color:#b45309;margin:0;font-weight:600;line-height:1.4;">
            {{ $order->notes }}
        </p>
    </div>
@endif

