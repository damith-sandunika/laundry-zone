<!-- Top-ups Filter Card -->
<div style="background:white;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 4px rgba(0,0,0,0.04);padding:16px 24px;">
    <form action="{{ route('expenses.index') }}" method="GET" style="display:flex;gap:12px;align-items:flex-end;margin:0;flex-wrap:wrap;">
        <input type="hidden" name="period" value="{{ $period }}">
        <input type="hidden" name="category" value="{{ $category }}">
        @if($startDate) <input type="hidden" name="start_date" value="{{ $startDate }}"> @endif
        @if($endDate) <input type="hidden" name="end_date" value="{{ $endDate }}"> @endif
        <input type="hidden" name="tab" value="topups">

        @if($isSuperAdmin)
        <div style="flex:1;min-width:180px;">
            <label style="display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Branch</label>
            <select name="branch_id" style="width:100%;padding:9px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;color:#0f172a;background:#f8fafc;outline:none;">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        @else
        @if($branchId) <input type="hidden" name="branch_id" value="{{ $branchId }}"> @endif
        @endif

        <div style="display:flex;align-items:center;gap:8px;padding:9px 14px;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:10px;">
            @if($currentBranch)
                <span style="font-size:12px;font-weight:700;color:#0369a1;">{{ $currentBranch->name }}</span>
                <span style="font-size:11px;color:#94a3b8;">|</span>
            @endif
            <span style="font-size:12px;font-weight:600;color:#374151;">
                Total Top-ups: <strong style="color:#10b981;">Rs.{{ number_format($topups->sum('amount'), 2) }}</strong>
            </span>
        </div>

        @if($isSuperAdmin)
        <button type="submit" style="padding:9px 18px;background:#1d4ed8;color:white;font-size:13px;font-weight:700;border-radius:10px;border:none;cursor:pointer;">
            Filter
        </button>
        @endif
    </form>
</div>

<div style="background:white;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 4px rgba(0,0,0,0.04);overflow:hidden;">
    <div style="padding:18px 24px;border-bottom:1px solid #f8fafc;display:flex;justify-content:space-between;align-items:center;">
        <span style="font-size:14px;font-weight:700;color:#0f172a;">Petty Cash Top-ups</span>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="padding:10px 24px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:left;">Date & Time</th>
                    @if($isSuperAdmin && !$branchId)
                    <th style="padding:10px 24px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:left;">Branch</th>
                    @endif
                    <th style="padding:10px 24px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:left;">Reference Note</th>
                    <th style="padding:10px 24px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:left;">Added By</th>
                    <th style="padding:10px 24px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topups as $topup)
                <tr style="border-top:1px solid #f8fafc;transition:background 0.1s;"
                    onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                    <td style="padding:14px 24px;font-size:12px;font-weight:500;color:#94a3b8;">
                        {{ \Carbon\Carbon::parse($topup->created_at)->format('M d, Y h:i A') }}
                    </td>
                    @if($isSuperAdmin && !$branchId)
                    <td style="padding:14px 24px;font-size:13px;font-weight:600;color:#374151;">
                        @php $topupBranchName = $branches->firstWhere('id', $topup->branch_id)?->name ?? 'N/A'; @endphp
                        {{ $topupBranchName }}
                    </td>
                    @endif
                    <td style="padding:14px 24px;font-size:13px;font-weight:600;color:#374151;">{{ $topup->reference_note }}</td>
                    <td style="padding:14px 24px;font-size:13px;font-weight:600;color:#374151;">
                        @php $topupUser = \App\Models\User::find($topup->user_id); @endphp
                        {{ $topupUser ? $topupUser->name : 'Unknown' }}
                    </td>
                    <td style="padding:14px 24px;text-align:right;font-size:14px;font-weight:800;color:#10b981;">
                        + Rs.{{ number_format($topup->amount, 2) }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="{{ ($isSuperAdmin && !$branchId) ? 5 : 4 }}" style="padding:40px;text-align:center;font-size:13px;color:#94a3b8;font-weight:500;">No top-ups recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@if($topups->hasPages())
<div style="padding:16px 24px;background:white;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 4px rgba(0,0,0,0.04);">
    {{ $topups->appends(request()->query())->links() }}
</div>
@endif

