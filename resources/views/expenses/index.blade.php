<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h2 style="font-size:24px;font-weight:900;color:#0f172a;letter-spacing:-0.03em;margin:0 0 4px;">Expenses</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Log and track daily shop expenditures</p>
            </div>
            <button @click="$dispatch('open-add-modal')" style="display:flex;align-items:center;gap:7px;padding:10px 18px;background:#1d4ed8;color:white;font-size:13px;font-weight:700;border-radius:10px;border:none;cursor:pointer;box-shadow:0 2px 10px rgba(29,78,216,0.3);transition:all 0.15s;"
                onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='#1d4ed8'">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                Record Expense
            </button>
        </div>
    </x-slot>

    @if(session('success'))
        <div style="display:flex;align-items:center;gap:10px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;padding:14px 18px;margin-bottom:24px;">
            <svg width="16" height="16" fill="none" stroke="#16a34a" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span style="font-size:13px;font-weight:600;color:#15803d;">{{ session('success') }}</span>
        </div>
    @endif

    <div x-data="{ addModalOpen: false }" @open-add-modal.window="addModalOpen = true" style="display:flex;flex-direction:column;gap:20px;">

        <!-- Expenses Table Card -->
        <div style="background:white;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 4px rgba(0,0,0,0.04);overflow:hidden;">
            <div style="padding:18px 24px;border-bottom:1px solid #f8fafc;display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:14px;font-weight:700;color:#0f172a;">All Recorded Expenditures</span>
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
                        <tr><td colspan="5" style="padding:40px;text-align:center;font-size:13px;color:#94a3b8;font-weight:500;">No expenses recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Expense Modal -->
        <div x-show="addModalOpen" x-transition.opacity class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" style="display:none;">
            <div class="bg-white rounded-[20px] border border-slate-200 w-full max-w-[480px] shadow-2xl overflow-hidden relative" @click.away="addModalOpen = false">
                <form action="{{ route('expenses.store') }}" method="POST" style="margin:0;">
                    @csrf
                    <div style="padding:24px 30px;">
                        <h3 style="font-size:18px;font-weight:900;color:#0f172a;margin:0 0 20px;letter-spacing:-0.02em;">Record New Expense</h3>
                        
                        <div style="display:flex;flex-direction:column;gap:16px;">
                            <div>
                                <label style="display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Expense Category</label>
                                <select name="category" style="width:100%;padding:12px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13.5px;font-weight:500;color:#0f172a;background:#f8fafc;outline:none;">
                                    <option value="utilities">Utilities (Water, Electricity)</option>
                                    <option value="supplies">Laundry Supplies (Detergent etc)</option>
                                    <option value="rent">Shop Rent</option>
                                    <option value="other">Other Miscellaneous</option>
                                </select>
                            </div>
                            
                            <div>
                                <label style="display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Description</label>
                                <input type="text" name="description" required placeholder="e.g. Bought 5L Detergent" 
                                    style="width:100%;padding:12px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13.5px;font-weight:500;color:#0f172a;background:#f8fafc;outline:none;box-sizing:border-box;"
                                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                            </div>

                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                                <div>
                                    <label style="display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Amount (Rs.)</label>
                                    <input type="number" step="0.01" name="amount" required placeholder="e.g. 2500"
                                        style="width:100%;padding:12px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13.5px;font-weight:500;color:#0f172a;background:#f8fafc;outline:none;box-sizing:border-box;"
                                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                                </div>
                                <div>
                                    <label style="display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Date</label>
                                    <input type="date" name="expense_date" value="{{ now()->toDateString() }}" required
                                        style="width:100%;padding:12px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13.5px;font-weight:500;color:#0f172a;background:#f8fafc;outline:none;box-sizing:border-box;"
                                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="padding:16px 30px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:10px;">
                        <button type="button" @click="addModalOpen = false" style="padding:10px 16px;background:white;color:#64748b;font-size:13px;font-weight:600;border-radius:10px;border:1.5px solid #e2e8f0;cursor:pointer;">
                            Cancel
                        </button>
                        <button type="submit" style="padding:10px 18px;background:#1d4ed8;color:white;font-size:13px;font-weight:700;border-radius:10px;border:none;cursor:pointer;box-shadow:0 2px 8px rgba(29,78,216,0.25);">
                            Record Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
