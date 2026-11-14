@if($order->correctionRequests->where('type', 'payment_change')->count() > 0)
    <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.02);padding:16px;">
        <h3 style="font-size:12px;font-weight:800;color:#0f172a;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.05em;">
            Payment Edit History</h3>
        <div style="display:flex;flex-direction:column;gap:10px;">
            @foreach($order->correctionRequests->where('type', 'payment_change')->sortByDesc('created_at') as $cr)
                <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px;padding:12px;font-size:12px;display:flex;flex-direction:column;gap:6px;
                    {{ $cr->status === 'pending' ? 'border-left:4px solid #f59e0b;' : ($cr->status === 'approved' ? 'border-left:4px solid #10b981;' : 'border-left:4px solid #ef4444;') }}">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <strong style="font-size:10px;text-transform:uppercase;letter-spacing:0.05em;color:#475569;">
                            {{ ucfirst($cr->status) }} Request
                        </strong>
                        <span style="font-size:10px;color:#64748b;font-weight:600;">{{ $cr->created_at->format('M d, h:i A') }}</span>
                    </div>
                    @php
                        $oldMethod = $cr->old_data['payment_method'] ?? 'unknown';
                        $newMethod = $cr->new_data['payment_method'] ?? 'unknown';
                    @endphp
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="text-decoration:line-through;color:#ef4444;font-weight:700;">{{ ucfirst($oldMethod) }}</span>
                        <i class="fa-solid fa-arrow-right" style="color:#94a3b8;font-size:10px;"></i>
                        <span style="color:#10b981;font-weight:800;">{{ ucfirst($newMethod) }}</span>
                    </div>
                    @if($cr->reason)
                        <div style="background:white;padding:6px 8px;border-radius:6px;border:1px solid #e2e8f0;color:#334155;font-style:italic;font-size:11px;">
                            "{{ $cr->reason }}"
                        </div>
                    @endif
                    <div style="font-size:10px;color:#64748b;margin-top:2px;">
                        By <strong>{{ $cr->requestedBy->name ?? 'Unknown' }}</strong>
                        @if($cr->status !== 'pending' && $cr->approvedBy)
                            &bull; {{ ucfirst($cr->status) }} by <strong>{{ $cr->approvedBy->name }}</strong>
                        @endif
                    </div>
                    @if($cr->status === 'pending' && in_array(auth()->user()->role, ['admin', 'branch_admin', 'workshop_admin', 'super_admin']))
                        <div style="display:flex;gap:6px;margin-top:6px;">
                            <form action="{{ route('corrections.approve', $cr->id) }}" method="POST" style="flex:1;" onsubmit="return confirm('Approve payment change?')">
                                @csrf
                                <button type="submit" style="width:100%;padding:6px;background:#10b981;color:white;border:none;border-radius:6px;font-size:10px;font-weight:800;cursor:pointer;">Approve</button>
                            </form>
                            <form action="{{ route('corrections.reject', $cr->id) }}" method="POST" style="flex:1;" onsubmit="return confirm('Reject payment change?')">
                                @csrf
                                <button type="submit" style="width:100%;padding:6px;background:#fee2e2;color:#ef4444;border:none;border-radius:6px;font-size:10px;font-weight:800;cursor:pointer;">Reject</button>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif

