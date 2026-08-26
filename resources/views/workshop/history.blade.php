<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <h2 class="ph-title">Workshop History</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Log of all completed and returned workshop orders</p>
            </div>
            <a href="{{ route('workshop.dashboard') }}" class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition-colors">
                Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="bg-white rounded-md border border-gray-100 shadow-sm overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left" style="border-collapse:collapse;">
                <thead style="background:#f8fafc;border-bottom:1.5px solid #e2e8f0;">
                    <tr>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Order Info</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Customer</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Branch</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Dispatched</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Returned</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dispatches as $dispatch)
                    <tr style="background:white;border-bottom:1px solid #f1f5f9;transition:all 0.1s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                        <td style="padding:12px 20px;">
                            <span style="font-size:13px;font-weight:800;color:#1d4ed8;">#{{ $dispatch->order->order_number ?? $dispatch->order->id }}</span>
                        </td>
                        <td style="padding:12px 20px;font-weight:700;color:#0f172a;">
                            {{ $dispatch->order->customer->name ?? 'Unknown' }}
                        </td>
                        <td style="padding:12px 20px;font-weight:700;color:#4f46e5;">
                            {{ $dispatch->branch->name ?? '—' }}
                        </td>
                        <td style="padding:12px 20px;font-weight:600;color:#475569;">
                            <span style="display:block;">{{ $dispatch->dispatched_at?->format('M d, Y') }}</span>
                            <span style="font-size:11px;color:#94a3b8;font-weight:500;">{{ $dispatch->dispatched_at?->format('h:i A') }}</span>
                        </td>
                        <td style="padding:12px 20px;font-weight:600;color:#475569;">
                            @php
                                $completedDate = $dispatch->received_at_branch_at ?? $dispatch->returned_at;
                            @endphp
                            <span style="display:block;">{{ $completedDate?->format('M d, Y') }}</span>
                            <span style="font-size:11px;color:#94a3b8;font-weight:500;">{{ $completedDate?->format('h:i A') }}</span>
                        </td>
                        <td style="padding:12px 20px;text-align:center;">
                            <a href="{{ route('orders.show', $dispatch->order) }}"
                               style="display:inline-block;padding:5px 12px;background:#f1f5f9;color:#334155;border:1px solid #e2e8f0;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;transition:all 0.1s;"
                               onmouseover="this.style.background='#e2e8f0';this.style.color='#0f172a';" onmouseout="this.style.background='#f1f5f9';this.style.color='#334155';">
                                View Timeline
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding:40px;text-align:center;color:#94a3b8;font-size:13px;font-weight:500;">
                            No completed workshop orders found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($dispatches->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $dispatches->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
