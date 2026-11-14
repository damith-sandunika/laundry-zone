<!-- Add Expense Modal -->
<div x-show="addModalOpen" x-transition.opacity class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" style="display:none;">
    <div class="bg-white rounded-[20px] border border-slate-200 w-full max-w-[480px] shadow-2xl overflow-hidden relative" @click.away="addModalOpen = false">
        <form action="{{ route('expenses.store') }}" method="POST" style="margin:0;">
            @csrf
            <div style="padding:24px 30px;">
                <h3 style="font-size:18px;font-weight:900;color:#0f172a;margin:0 0 20px;letter-spacing:-0.02em;">Record Petty Cash Expense</h3>
                <div style="display:flex;flex-direction:column;gap:16px;">
                    <div>
                        <label style="display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Petty Cash Category</label>
                        <select name="category" style="width:100%;padding:12px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13.5px;font-weight:500;color:#0f172a;background:#f8fafc;outline:none;">
                            @foreach($expenseCategories as $cat)
                                <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Description</label>
                        <input type="text" name="description" required placeholder="e.g. Bought 5L Detergent" 
                            style="width:100%;padding:12px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13.5px;font-weight:500;color:#0f172a;background:#f8fafc;outline:none;box-sizing:border-box;">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div>
                            <label style="display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Amount (Rs.)</label>
                            <input type="number" step="0.01" name="amount" required placeholder="e.g. 2500"
                                style="width:100%;padding:12px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13.5px;font-weight:500;color:#0f172a;background:#f8fafc;outline:none;box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Date</label>
                            <input type="date" name="expense_date" value="{{ now()->toDateString() }}" required
                                style="width:100%;padding:12px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13.5px;font-weight:500;color:#0f172a;background:#f8fafc;outline:none;box-sizing:border-box;">
                        </div>
                    </div>
                </div>
            </div>
            <div style="padding:16px 30px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" @click="addModalOpen = false" style="padding:10px 16px;background:white;color:#64748b;font-size:13px;font-weight:600;border-radius:10px;border:1.5px solid #e2e8f0;cursor:pointer;">Cancel</button>
                <button type="submit" style="padding:10px 18px;background:#1d4ed8;color:white;font-size:13px;font-weight:700;border-radius:10px;border:none;cursor:pointer;">Record Expense</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Top-up Modal -->
<div x-show="topupModalOpen" class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" style="display:none;" x-cloak>
    <div class="bg-white rounded-[20px] border border-slate-200 w-full max-w-[400px] shadow-2xl overflow-hidden relative" @click.away="topupModalOpen = false">
        <form action="{{ route('petty_cash.topup') }}" method="POST" style="margin:0;">
            @csrf
            <div style="padding:24px 30px;">
                <h3 style="font-size:18px;font-weight:900;color:#0f172a;margin:0 0 20px;letter-spacing:-0.02em;">Top-up Petty Cash</h3>
                <div style="display:flex;flex-direction:column;gap:16px;">
                    <div>
                        <label style="display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Top-up Amount (Rs.)</label>
                        <input type="number" step="0.01" name="amount" required placeholder="e.g. 5000"
                            style="width:100%;padding:12px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13.5px;font-weight:500;color:#0f172a;background:#f8fafc;outline:none;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Reference Note</label>
                        <input type="text" name="reference_note" required placeholder="e.g. Weekly Top-up from Bank" 
                            style="width:100%;padding:12px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13.5px;font-weight:500;color:#0f172a;background:#f8fafc;outline:none;box-sizing:border-box;">
                    </div>
                </div>
            </div>
            <div style="padding:16px 30px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" @click="topupModalOpen = false" style="padding:10px 16px;background:white;color:#64748b;font-size:13px;font-weight:600;border-radius:10px;border:1.5px solid #e2e8f0;cursor:pointer;">Cancel</button>
                <button type="submit" style="padding:10px 18px;background:#10b981;color:white;font-size:13px;font-weight:700;border-radius:10px;border:none;cursor:pointer;">Add Funds</button>
            </div>
        </form>
    </div>
</div>

