<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <h2 class="ph-title">Workshop Orders</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Log of all orders dispatched to this workshop</p>
            </div>
            <a href="{{ route('workshop.dashboard') }}" class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition-colors">
                Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="mb-6 flex flex-wrap gap-4 items-center justify-between bg-white p-4 rounded-xl shadow-sm border border-slate-100">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <div>
                <input type="text" name="date_range" id="date_range"
                    value="{{ request('date_range') }}" placeholder="Filter by Date"
                    class="border-slate-200 rounded-lg text-sm font-bold w-[250px] shadow-sm">
            </div>
            <button type="submit"
                class="px-4 py-2 bg-slate-900 text-white rounded-lg text-sm font-bold shadow-sm hover:bg-slate-800 transition-colors">
                Filter
            </button>
            @if (request('date_range'))
                <a href="{{ route('history') }}"
                    class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-bold hover:bg-slate-200 transition-colors">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex flex-col justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Order Value</span>
            <span class="text-2xl font-black text-slate-800 mt-2">Rs.{{ number_format($stats['total_order_value'], 2) }}</span>
            <div class="mt-4 flex flex-col gap-2 pt-3 border-t border-slate-50">
                <div class="flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-600">🚶 Walk-in</span>
                    <span class="text-xs font-black text-slate-800">Rs.{{ number_format($stats['walk_in'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-600">🚚 Delivery</span>
                    <span class="text-xs font-black text-slate-800">Rs.{{ number_format($stats['delivery'], 2) }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex flex-col justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pending / Due</span>
            <span class="text-2xl font-black text-amber-600 mt-2">Rs.{{ number_format($stats['pending_due'], 2) }}</span>
            <div class="mt-4 flex flex-col gap-2 pt-3 border-t border-slate-50">
                <div class="flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-600">Regular Pending</span>
                    <span class="text-xs font-black text-slate-800">Rs.{{ number_format($stats['regular_pending'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-600">Credit Orders</span>
                    <span class="text-xs font-black text-slate-800">Rs.{{ number_format($stats['credit_orders'], 2) }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex flex-col justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Collected Payments</span>
            <span class="text-2xl font-black text-emerald-600 mt-2">Rs.{{ number_format($stats['collected_payments'], 2) }}</span>
            <div class="mt-4 flex flex-col gap-2 pt-3 border-t border-slate-50">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-[10px] font-black text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full uppercase tracking-wider">From These Orders</span>
                    <span class="text-[10px] font-black text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full uppercase tracking-wider">Rs.{{ number_format($stats['collected_payments'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-600">💵 Cash</span>
                    <span class="text-xs font-black text-slate-800">{{ number_format($stats['cash'], 0) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-600">💳 Card</span>
                    <span class="text-xs font-black text-slate-800">{{ number_format($stats['card'], 0) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-600">🏦 Bank</span>
                    <span class="text-xs font-black text-slate-800">{{ number_format($stats['bank'], 0) }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex flex-col justify-start gap-4">
            <div>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Total Order Qty</span>
                <span class="text-3xl font-black text-indigo-600">{{ $stats['order_qty'] }}</span>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Total Customers</span>
                <span class="text-3xl font-black text-indigo-600">{{ $stats['total_customer_count'] }}</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-md border border-gray-100 shadow-sm overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left" style="border-collapse:collapse;">
                <thead style="background:#f8fafc;border-bottom:1.5px solid #e2e8f0;">
                    <tr>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Order Info</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Customer</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Branch</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Total</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Dispatched</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Status</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dispatches as $dispatch)
                    @php $isDeleted = $dispatch->order && $dispatch->order->trashed(); @endphp
                    <tr style="background:{{ $isDeleted ? '#fef2f2' : 'white' }};border-bottom:1px solid #f1f5f9;transition:all 0.1s; {{ $isDeleted ? 'opacity: 0.8;' : '' }}" onmouseover="this.style.background='{{ $isDeleted ? '#fee2e2' : '#f8fafc' }}'" onmouseout="this.style.background='{{ $isDeleted ? '#fef2f2' : 'white' }}'">
                        <td style="padding:12px 20px;">
                            <a href="{{ route('orders.show', $dispatch->order->order_number ?? $dispatch->order->id) }}" style="font-size:13px;font-weight:800;color:#1d4ed8;text-decoration:{{ $isDeleted ? 'line-through' : 'underline' }};">
                                #{{ $dispatch->order->order_number ?? $dispatch->order->id }}
                            </a>
                        </td>
                        <td style="padding:12px 20px;font-weight:700;color:#0f172a;">
                            {{ $dispatch->order->customer->name ?? 'Unknown' }}
                        </td>
                        <td style="padding:12px 20px;font-weight:700;color:#4f46e5;">
                            {{ $dispatch->branch->name ?? '—' }}
                        </td>
                        <td style="padding:12px 20px;font-weight:800;color:#0f172a;">
                            Rs. {{ number_format($dispatch->order->total ?? 0, 2) }}
                        </td>
                        <td style="padding:12px 20px;font-weight:600;color:#475569;">
                            <span style="display:block;">{{ $dispatch->dispatched_at?->format('M d, Y') }}</span>
                            <span style="font-size:11px;color:#94a3b8;font-weight:500;">{{ $dispatch->dispatched_at?->format('h:i A') }}</span>
                        </td>
                        <td style="padding:12px 20px;font-weight:600;color:#475569;">
                            @php
                                $statusColors = [
                                    'dispatched_to_workshop' => ['bg' => '#fef3c7', 'text' => '#b45309', 'label' => 'Pending receipt'],
                                    'received_at_workshop' => ['bg' => '#dbeafe', 'text' => '#1d4ed8', 'label' => 'Received'],
                                    'processing' => ['bg' => '#e0e7ff', 'text' => '#4338ca', 'label' => 'Processing'],
                                    'completed_at_workshop' => ['bg' => '#dcfce7', 'text' => '#15803d', 'label' => 'Completed'],
                                    'handed_over_to_delivery' => ['bg' => '#f3e8ff', 'text' => '#7e22ce', 'label' => 'With Rider'],
                                    'received_at_branch' => ['bg' => '#f1f5f9', 'text' => '#475569', 'label' => 'At Branch'],
                                    'returned_to_branch' => ['bg' => '#fee2e2', 'text' => '#b91c1c', 'label' => 'Returned'],
                                ];
                                $st = $statusColors[$dispatch->status] ?? ['bg' => '#f1f5f9', 'text' => '#475569', 'label' => ucfirst(str_replace('_', ' ', $dispatch->status))];
                            @endphp
                            <span style="display:inline-block;padding:4px 10px;background:{{ $st['bg'] }};color:{{ $st['text'] }};border-radius:9999px;font-size:11px;font-weight:700;">
                                @if($isDeleted) DELETED @else {{ $st['label'] }} @endif
                            </span>
                        </td>
                        <td style="padding:12px 20px;text-align:center;">
                            @if($isDeleted && $dispatch->order->deletedBy)
                                <div style="font-size:10px;color:#ef4444;font-weight:700;">By: {{ $dispatch->order->deletedBy->name }} <br> <span style="color:#b91c1c;">{{ $dispatch->order->deleted_at?->format('M d, Y h:i A') }}</span></div>
                            @endif
                            @if(!$isDeleted)
                            <a href="{{ route('orders.show', $dispatch->order->order_number) }}"
                               style="display:inline-block;padding:5px 12px;background:#f1f5f9;color:#334155;border:1px solid #e2e8f0;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;transition:all 0.1s;"
                               onmouseover="this.style.background='#e2e8f0';this.style.color='#0f172a';" onmouseout="this.style.background='#f1f5f9';this.style.color='#334155';">
                                View
                            </a>
                            @endif
                            @if(!$isDeleted && auth()->user() && in_array(auth()->user()->role, ['super_admin', 'branch_admin', 'workshop_admin']))
                                <button onclick="if(confirm('Are you sure you want to delete this order? It will be crossed out and removed from reports.')){ fetch('{{ route('orders.destroy', $dispatch->order->id) }}', {method:'DELETE', headers:{'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'}}).then(r=>r.json()).then(d=>{if(d.success) location.reload(); else alert(d.message);}); }"
                                        style="display:inline-block;padding:5px 12px;background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;transition:all 0.1s;margin-left:4px;"
                                        onmouseover="this.style.background='#fecaca';" onmouseout="this.style.background='#fee2e2';">
                                    Delete
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding:40px;text-align:center;color:#94a3b8;font-size:13px;font-weight:500;">
                            No workshop orders found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($dispatches->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $dispatches->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

    @if($cancelledBookings->count() > 0)
    <div class="mb-4 mt-8">
        <h3 class="font-black text-lg text-slate-800">Rejected Online Bookings</h3>
        <p class="text-sm text-slate-500 font-medium">Log of all bookings rejected by the branch/workshop</p>
    </div>
    
    <div class="bg-white rounded-md border border-red-100 shadow-sm overflow-hidden mb-8" x-data="{ openModal: null }">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left" style="border-collapse:collapse;">
                <thead style="background:#fef2f2;border-bottom:1.5px solid #fecaca;">
                    <tr>
                        <th style="padding:14px 20px;font-weight:800;color:#991b1b;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Booking ID</th>
                        <th style="padding:14px 20px;font-weight:800;color:#991b1b;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Customer</th>
                        <th style="padding:14px 20px;font-weight:800;color:#991b1b;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Rejected Time</th>
                        <th style="padding:14px 20px;font-weight:800;color:#991b1b;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Rejection Details</th>
                        <th style="padding:14px 20px;font-weight:800;color:#991b1b;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cancelledBookings as $booking)
                    <tr style="background:#fff5f5;border-bottom:1px solid #fee2e2;">
                        <td style="padding:12px 20px;">
                            <span style="font-size:13px;font-weight:800;color:#dc2626;text-decoration:line-through;">#{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td style="padding:12px 20px;font-weight:700;color:#7f1d1d;opacity:0.8;text-decoration:line-through;">
                            {{ $booking->customer->name ?? 'Unknown' }}
                            <div style="font-size:11px;color:#991b1b;font-weight:600;text-decoration:none;">{{ $booking->contact_phone }}</div>
                        </td>
                        <td style="padding:12px 20px;font-weight:600;color:#991b1b;opacity:0.8;">
                            <span style="display:block;">{{ $booking->updated_at?->format('M d, Y') }}</span>
                            <span style="font-size:11px;font-weight:500;">{{ $booking->updated_at?->format('h:i A') }}</span>
                        </td>
                        <td style="padding:12px 20px;">
                            <div style="font-size:11.5px;font-weight:700;color:#dc2626;">
                                Reason: {{ $booking->cancel_reason ?? 'Not provided' }}
                            </div>
                            <div style="font-size:10px;color:#991b1b;margin-top:2px;font-weight:600;">
                                By: {{ $booking->cancelledBy->name ?? 'System' }}
                            </div>
                        </td>
                        <td style="padding:12px 20px;text-align:center;" x-data="{ open: false }">
                            <button type="button" @click="open = true" style="display:inline-block;padding:5px 12px;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;transition:all 0.1s;" onmouseover="this.style.background='#fecaca';this.style.color='#7f1d1d';" onmouseout="this.style.background='#fef2f2';this.style.color='#991b1b';">
                                View Details
                            </button>
                            
                            <!-- Alpine Modal for this Booking -->
                            <template x-teleport="body">
                                <div x-show="open" style="display:none;">
                                    <div style="position:fixed;inset:0;background:rgba(15,23,42,0.4);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;z-index:9999;">
                                        <div @click.outside="open = false" style="background:white;border-radius:16px;padding:24px;width:100%;max-width:400px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.1),0 8px 10px -6px rgba(0,0,0,0.1);text-align:left;">
                                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                                                <h3 style="font-size:16px;font-weight:800;color:#0f172a;margin:0;">{{ $booking->order_number ?? 'Booking #' . str_pad($booking->id, 5, '0', STR_PAD_LEFT) }} Details</h3>
                                                <button @click="open = false" style="background:none;border:none;color:#94a3b8;font-size:16px;cursor:pointer;padding:4px;"><i class="fa-solid fa-xmark"></i></button>
                                            </div>
                                            <div style="display:flex;flex-direction:column;gap:12px;font-size:13px;color:#334155;">
                                                <div><strong>Customer:</strong> {{ $booking->customer->name ?? 'Unknown' }} ({{ $booking->contact_phone }})</div>
                                                <div><strong>Pickup Address:</strong> <span style="color:#64748b;">{{ $booking->pickup_address ?? 'N/A' }}</span></div>
                                                <div><strong>Scheduled For:</strong> <span style="color:#64748b;">{{ $booking->pickup_scheduled_at?->format('M d, Y h:i A') }}</span></div>
                                                <hr style="border:0;border-top:1px solid #f1f5f9;margin:0;">
                                                <div><strong>Rejection Reason:</strong> <span style="color:#dc2626;font-weight:700;">{{ $booking->cancel_reason ?? 'Not provided' }}</span></div>
                                                <div><strong>Rejected By:</strong> <span style="color:#991b1b;">{{ $booking->cancelledBy->name ?? 'System' }}</span></div>
                                                <hr style="border:0;border-top:1px solid #f1f5f9;margin:0;">
                                                <div><strong>Notes/Description:</strong><br><span style="white-space:pre-wrap;color:#64748b;">{{ $booking->item_description ?: ($booking->notes ?: 'No description provided') }}</span></div>
                                            </div>
                                            <div style="margin-top:24px;text-align:right;">
                                                <button @click="open = false" style="background:#f1f5f9;color:#334155;border:none;padding:8px 16px;border-radius:8px;font-weight:700;font-size:13px;cursor:pointer;">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($cancelledBookings->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $cancelledBookings->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
    @endif
    <x-slot name="scripts">
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if(typeof flatpickr !== 'undefined') {
                    flatpickr("#date_range", {
                        mode: "range",
                        dateFormat: "Y-m-d",
                        maxDate: "today"
                    });
                }
            });
        </script>
    </x-slot>
</x-app-layout>


