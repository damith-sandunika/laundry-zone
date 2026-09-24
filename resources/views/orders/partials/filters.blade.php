            <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:24px;background:#f8fafc;">
                <div>
                    <h3 style="font-size:16px;font-weight:900;color:#0f172a;margin:0;">Order List</h3>
                </div>
                <!-- Search and Bulk Action button -->
                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                    @if(auth()->user()->role === 'super_admin' && isset($allBranches) && $allBranches->count() > 0)
                        <form action="{{ route('orders.index') }}" method="GET" style="margin:0;display:flex;gap:8px;">
                            @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
                            <input type="date" name="start_date" value="{{ request('start_date') }}"
                                onchange="this.form.submit()"
                                style="padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13px;font-weight:600;outline:none;background:white;cursor:pointer;"
                                title="Start Date">
                            <input type="date" name="end_date" value="{{ request('end_date') }}"
                                onchange="this.form.submit()"
                                style="padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13px;font-weight:600;outline:none;background:white;cursor:pointer;"
                                title="End Date">
                            <select name="branch_id" onchange="this.form.submit()"
                                style="padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13px;font-weight:600;outline:none;background:white;cursor:pointer;">
                                <option value="">All Branches</option>
                                @foreach($allBranches as $branch)
                                    <option value="{{ $branch->id }}" {{ (isset($filterBranchId) && $filterBranchId == $branch->id) ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            <noscript><button type="submit">Filter</button></noscript>
                        </form>
                    @endif

                    <form action="{{ route('orders.index') }}" method="GET" style="display:flex;gap:8px;margin:0;">
                        @if(request('branch_id')) <input type="hidden" name="branch_id"
                        value="{{ request('branch_id') }}"> @endif
                        @if(request('start_date')) <input type="hidden" name="start_date"
                        value="{{ request('start_date') }}"> @endif
                        @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                        @endif
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search ID or phone…"
                            style="padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13px;font-weight:600;outline:none;"
                            onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        <button type="submit"
                            style="padding:8px 14px;background:white;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13px;font-weight:700;color:#334155;cursor:pointer;">Search</button>
                    </form>
                </div>
            </div>