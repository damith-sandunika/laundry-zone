<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <h2 class="ph-title">Petty Cash</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Log and track petty cash expenditures</p>
                @if($currentBranch)
                <div style="margin-top:8px; display:inline-block; background:#f0f9ff; border:1px solid #bae6fd; padding:4px 12px; border-radius:8px;">
                    <span style="font-size:12px; font-weight:700; color:#0369a1;">{{ $currentBranch->name }} &mdash; Balance: Rs. {{ number_format($currentBranch->petty_cash_balance ?? 0, 2) }}</span>
                </div>
                @elseif($isSuperAdmin)
                <div style="margin-top:8px; display:inline-block; background:#fefce8; border:1px solid #fde68a; padding:4px 12px; border-radius:8px;">
                    <span style="font-size:12px; font-weight:700; color:#b45309;">Super Admin View &mdash; Filter by branch below</span>
                </div>
                @endif
            </div>
            <div style="display:flex; gap:10px;">
                <button @click="$dispatch('open-topup-modal')" class="px-4 py-2 bg-emerald-600 text-white text-xs font-semibold rounded hover:bg-emerald-700 transition-colors flex items-center gap-2">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                    Top-up Cash
                </button>
                <button @click="$dispatch('open-add-modal')" class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition-colors flex items-center gap-2">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                    Record Expense
                </button>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div style="display:flex;align-items:center;gap:10px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;padding:14px 18px;margin-bottom:24px;">
            <svg width="16" height="16" fill="none" stroke="#16a34a" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span style="font-size:13px;font-weight:600;color:#15803d;">{{ session('success') }}</span>
        </div>
    @endif

    <div x-data="{ addModalOpen: false, topupModalOpen: false, activeTab: 'expenses' }" @open-add-modal.window="addModalOpen = true" @open-topup-modal.window="topupModalOpen = true" style="display:flex;flex-direction:column;gap:20px;">

        <!-- Tabs -->
        <div style="display:flex; gap:10px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
            <button @click="activeTab = 'expenses'" :class="activeTab === 'expenses' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600'" style="padding:8px 16px; font-size:13px; font-weight:700; border-radius:8px; border:1px solid #e2e8f0; cursor:pointer;">
                Petty Cash Log
            </button>
            <button @click="activeTab = 'topups'" :class="activeTab === 'topups' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600'" style="padding:8px 16px; font-size:13px; font-weight:700; border-radius:8px; border:1px solid #e2e8f0; cursor:pointer;">
                Top-ups Log
            </button>
        </div>

        <div x-show="activeTab === 'expenses'" x-transition style="display:flex;flex-direction:column;gap:20px;">

        <!-- Filter Bar -->
        <div style="background:white;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 4px rgba(0,0,0,0.04);padding:16px 24px;">
            <form x-data="{ selectedPeriod: '{{ $period }}' }" action="{{ route('expenses.index') }}" method="GET" style="display:flex;gap:16px;align-items:flex-end;margin:0;flex-wrap:wrap;">

                @if($isSuperAdmin)
                <div style="flex:1;min-width:200px;">
                    <label style="display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Branch</label>
                    <select name="branch_id" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;color:#0f172a;background:#f8fafc;outline:none;">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div style="flex:1;min-width:200px;">
                    <label style="display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Time Period</label>
                    <select name="period" x-model="selectedPeriod" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;color:#0f172a;background:#f8fafc;outline:none;">
                        @if(!$isSuperAdmin)
                        <option value="shift">Current Open Shift</option>
                        @endif
                        <option value="today">Today</option>
                        <option value="this_week">This Week</option>
                        <option value="this_month">This Month</option>
                        <option value="custom">Custom Date Range</option>
                        <option value="all_time">All Time</option>
                    </select>
                </div>
                
                <div x-show="selectedPeriod === 'custom'" x-transition style="display:flex;gap:16px;flex:1;min-width:280px;" x-cloak>
                    <div style="flex:1;">
                        <label style="display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Date From</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" style="width:100%;padding:9px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;color:#0f172a;background:#f8fafc;outline:none;">
                    </div>
                    <div style="flex:1;">
                        <label style="display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Date To</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" style="width:100%;padding:9px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;color:#0f172a;background:#f8fafc;outline:none;">
                    </div>
                </div>

                <div style="flex:1;min-width:200px;">
                    <label style="display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Category</label>
                    <select name="category" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;color:#0f172a;background:#f8fafc;outline:none;">
                        <option value="">All Categories</option>
                        @foreach($expenseCategories as $cat)
                            <option value="{{ $cat->name }}" {{ $category === $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" style="padding:10px 20px;background:#1d4ed8;color:white;font-size:13px;font-weight:700;border-radius:10px;border:none;cursor:pointer;box-shadow:0 2px 8px rgba(29,78,216,0.25);">
                        Apply Filter
                    </button>
                    @if($period !== 'shift' || $category !== '' || $branchId)
                        <a href="{{ route('expenses.index') }}" style="display:inline-block;padding:10px 20px;background:#f1f5f9;color:#475569;font-size:13px;font-weight:700;border-radius:10px;border:none;text-decoration:none;margin-left:8px;">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Expenses Table Card -->
        <div style="background:white;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 4px rgba(0,0,0,0.04);overflow:hidden;">
            <div style="padding:18px 24px;border-bottom:1px solid #f8fafc;display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:14px;font-weight:700;color:#0f172a;">
                    @if($period === 'shift')
                        {{ $activeSession ? 'Current Shift Petty Cash' : 'No Open Shift' }}
                    @else
                        Filtered Petty Cash
                    @endif
                </span>
            </div>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f8fafc;">
                            <th style="padding:10px 24px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:left;">Date</th>
                            <th style="padding:10px 24px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:left;">Category</th>
                            <th style="padding:10px 24px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:left;">Description</th>
                            <th style="padding:10px 24px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:right;">Amount</th>
                            <th style="padding:10px 24px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                        <tr style="border-top:1px solid #f8fafc;transition:background 0.1s;"
                            onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                            <td style="padding:14px 24px;font-size:12px;font-weight:500;color:#94a3b8;">
                                {{ \Carbon\Carbon::parse($expense->expense_date)->format('M d, Y') }}
                            </td>
                            <td style="padding:14px 24px;">
                                @php
                                    $isSalary = $expense->category === 'salary';
                                    $bgColor = $isSalary ? '#f3e8ff' : '#fee2e2';
                                    $textColor = $isSalary ? '#7e22ce' : '#b91c1c';
                                @endphp
                                <span style="font-size:11px;font-weight:700;color:{{ $textColor }};background:{{ $bgColor }};padding:3px 8px;border-radius:6px;text-transform:uppercase;letter-spacing:0.06em;">
                                    {{ ucfirst($expense->category) }}
                                </span>
                            </td>
                            <td style="padding:14px 24px;font-size:13px;font-weight:600;color:#374151;">{{ $expense->description }}</td>
                            <td style="padding:14px 24px;text-align:right;font-size:14px;font-weight:800;color:#0f172a;">
                                Rs.{{ number_format($expense->amount, 2) }}
                            </td>
                            <td style="padding:14px 24px;text-align:center;font-size:12px;font-weight:600;">
                                <a href="{{ route('expenses.edit', $expense) }}" style="color:#2563eb;text-decoration:none;margin-right:12px;">Edit</a>
                                <form action="{{ route('expenses.destroy', $expense) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background:none;border:none;padding:0;color:#dc2626;font-size:12px;font-weight:600;cursor:pointer;">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="padding:40px;text-align:center;font-size:13px;color:#94a3b8;font-weight:500;">No petty cash recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($expenses->hasPages())
        <div style="padding:16px 24px;background:white;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 4px rgba(0,0,0,0.04);">
            {{ $expenses->appends(request()->query())->links() }}
        </div>
        @endif
        
        </div> <!-- End of Expenses Tab -->

        <!-- Top-ups Tab -->
        @include('expenses.partials.topups_tab')

        <!-- Modals -->
        @include('expenses.partials.modals')

    </div>
</x-app-layout>


