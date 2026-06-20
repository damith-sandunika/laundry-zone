<x-app-layout>
    <style>
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .half-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .dash-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        @media(max-width:1280px) {
            .stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media(max-width:900px) {

            .stat-grid,
            .half-grid,
            .main-grid,
            .bottom-grid {
                display: flex !important;
                flex-direction: column !important;
                width: 100% !important;
                gap: 16px !important;
            }
            .hide-on-mobile { display: none !important; }
            
            .dash-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            .header-actions {
                width: 100%;
                justify-content: space-between;
            }
            .open-shift-form, .close-shift-form {
                width: 100%;
                flex-direction: column;
            }
            .open-shift-form input, .open-shift-form button, .close-shift-form button, .shift-active-indicator {
                width: 100% !important;
                justify-content: center !important;
            }
        }

        @keyframes ping {

            75%,
            100% {
                transform: scale(2);
                opacity: 0;
            }
        }
    </style>
    <x-slot name="header">
        <div class="dash-header">
            <div>
                <h2 class="ph-title">
                    Dashboard</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Today's updates and key metrics for
                    <strong style="color:#64748b;">{{ $branchName }}</strong></p>
            </div>
            @if($activeSession)
                <div class="header-actions">
                    <div class="shift-active-indicator"
                        style="display:flex;align-items:center;justify-content:center;gap:8px;padding:10px 18px;background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:10px;height:38px;box-sizing:border-box;">
                        <span
                            style="width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;animation:ping 1.5s infinite;"></span>
                        <span style="font-size:12px;font-weight:700;color:#16a34a;">Shift Active</span>
                    </div>
                    <a href="{{ route('pos.index') }}"
                        class="hide-on-mobile"
                        style="display:flex;align-items:center;gap:7px;padding:10px 18px;background:#1d4ed8;color:white;font-size:13px;font-weight:700;border-radius:10px;text-decoration:none;transition:all 0.2s;box-shadow:0 2px 10px rgba(29,78,216,0.3);"
                        onmouseover="this.style.background='#1e40af';this.style.transform='translateY(-1px)'"
                        onmouseout="this.style.background='#1d4ed8';this.style.transform='translateY(0)'">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                        </svg>
                        New Order
                    </a>
                    <form action="{{ route('shifts.close') }}" method="POST"
                        onsubmit="return confirm('Close today\'s shift?')" class="close-shift-form">
                        @csrf
                        <button type="submit"
                            style="padding:10px 16px;background:white;color:#64748b;font-size:13px;font-weight:600;border-radius:10px;border:1.5px solid #e2e8f0;cursor:pointer;transition:all 0.15s;height:38px;box-sizing:border-box;display:flex;align-items:center;justify-content:center;"
                            onmouseover="this.style.borderColor='#cbd5e1';this.style.color='#374151'"
                            onmouseout="this.style.borderColor='#e2e8f0';this.style.color='#64748b'">
                            Close Shift
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </x-slot>

    @if(session('success'))
        <div
            style="display:flex;align-items:center;gap:10px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;padding:14px 18px;margin-bottom:24px;">
            <svg width="18" height="18" fill="none" stroke="#16a34a" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span style="font-size:13px;font-weight:600;color:#15803d;">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div
            style="display:flex;align-items:center;gap:10px;background:#fef2f2;border:1.5px solid #fecaca;border-radius:12px;padding:14px 18px;margin-bottom:24px;">
            <svg width="18" height="18" fill="none" stroke="#dc2626" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span style="font-size:13px;font-weight:600;color:#b91c1c;">{{ session('error') }}</span>
        </div>
    @endif

    @if(!$activeSession)
        {{-- ── Shift Closed Card ── --}}
        <div style="display:flex;justify-content:center;padding:40px 0;">
            <div
                style="background:white;border-radius:20px;border:1px solid #e2e8f0;padding:48px 40px;width:100%;max-width:420px;text-align:center;box-shadow:0 4px 24px rgba(0,0,0,0.05);">
                <div
                    style="width:64px;height:64px;border-radius:16px;background:#eff6ff;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                    <svg width="28" height="28" fill="none" stroke="#3b82f6" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h3 style="font-size:20px;font-weight:800;color:#0f172a;margin:0 0 8px;">Shift is Closed</h3>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0 0 28px;line-height:1.6;">Open the branch
                    shift register with your starting cash to begin processing orders today.</p>
                <form action="{{ route('shifts.open') }}" method="POST" class="open-shift-form">
                    @csrf
                    <div style="text-align:left;margin-bottom:14px;">
                        <label
                            style="display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:8px;">Opening
                            Cash (Rs.)</label>
                        <input type="number" step="0.01" name="opening_cash" placeholder="e.g. 5000" required
                            style="width:100%;padding:13px 16px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;font-weight:500;color:#0f172a;outline:none;box-sizing:border-box;background:#f8fafc;"
                            onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)'"
                            onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                    </div>
                    <button type="submit"
                        style="width:100%;padding:13px;background:#1d4ed8;color:white;font-size:14px;font-weight:700;border-radius:10px;border:none;cursor:pointer;box-shadow:0 4px 12px rgba(29,78,216,0.3);">
                        Open Shift Register
                    </button>
                </form>
            </div>
        </div>
    @else

        {{-- ── STAT CARDS ── --}}
        <div class="stat-grid">
            @php
                $cards = [
                    ['label' => 'Active Orders', 'value' => $stats['active_orders'], 'sub' => 'Processing now', 'color' => '#3b82f6', 'bg' => '#eff6ff', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                    ['label' => 'Pending Pickup', 'value' => $stats['pending_deliveries'], 'sub' => 'Awaiting customer', 'color' => '#f59e0b', 'bg' => '#fffbeb', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['label' => 'Completed Today', 'value' => $stats['completed_today'], 'sub' => 'Delivered orders', 'color' => '#10b981', 'bg' => '#ecfdf5', 'icon' => 'M5 13l4 4L19 7'],
                    ['label' => 'Total Balance', 'value' => 'Rs.' . number_format($stats['current_balance'], 0), 'sub' => null, 'color' => '#8b5cf6', 'bg' => '#f5f3ff', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ];
            @endphp
            @foreach($cards as $card)
                <div style="background:white;border-radius:16px;border:1px solid #f1f5f9;padding:20px 22px;box-shadow:0 1px 4px rgba(0,0,0,0.04);display:flex;justify-content:space-between;align-items:center;transition:all 0.2s;cursor:default;"
                    onmouseover="this.style.boxShadow='0 6px 20px rgba(0,0,0,0.08)';this.style.transform='translateY(-2px)'"
                    onmouseout="this.style.boxShadow='0 1px 4px rgba(0,0,0,0.04)';this.style.transform='translateY(0)'">
                    <div>
                        <p
                            style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;margin:0 0 6px;">
                            {{ $card['label'] }}</p>
                        <p
                            style="font-size:28px;font-weight:900;color:#0f172a;letter-spacing:-0.03em;margin:0 0 6px;line-height:1;">
                            {{ $card['value'] }}</p>
                        @if($card['label'] === 'Total Balance')
                            <p style="font-size:12px;font-weight:700;color:#16a34a;margin:0 0 4px;">
                                Cash In Drawer: Rs.{{ number_format($stats['cash_balance'], 0) }}
                            </p>
                            <p style="font-size:12px;font-weight:700;color:#2563eb;margin:0 0 6px;">
                                Card Payments: {{ $stats['card_payments'] }} (Rs.{{ number_format($stats['card_payments_amount'], 0) }})
                            </p>
                        @endif
                        @if($card['sub'])
                        <p
                            style="font-size:11px;font-weight:600;color:{{ $card['color'] }};background:{{ $card['bg'] }};display:inline-block;padding:3px 8px;border-radius:6px;margin:0;">
                            {{ $card['sub'] }}</p>
                        @endif
                    </div>
                    <div
                        style="width:46px;height:46px;border-radius:12px;background:{{ $card['bg'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="22" height="22" fill="none" stroke="{{ $card['color'] }}" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}" />
                        </svg>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ── MAIN GRID ── --}}
        <div class="main-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">

            {{-- Recent Orders --}}
            <div
                style="background:white;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 4px rgba(0,0,0,0.04);overflow:hidden;">
                <div
                    style="padding:16px 20px;border-bottom:1px solid #f8fafc;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                    <div>
                        <h3 style="font-size:15px;font-weight:800;color:#0f172a;margin:0 0 2px;letter-spacing:-0.01em;">
                            Recent Orders</h3>
                        <p style="font-size:12px;color:#94a3b8;font-weight:500;margin:0;">Currently processing</p>
                    </div>
                    <a href="{{ route('pos.index') }}"
                        style="display:flex;align-items:center;gap:6px;padding:8px 14px;background:#eff6ff;color:#2563eb;font-size:12px;font-weight:700;border-radius:8px;text-decoration:none;transition:background 0.15s;"
                        onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                        </svg>
                        New Order
                    </a>
                </div>
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f8fafc;">
                            <th
                                style="padding:10px 16px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:left;">
                                Order</th>
                            <th
                                style="padding:10px 16px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:left;">
                                Customer</th>
                            <th
                                style="padding:10px 16px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:left;">
                                Due Date</th>
                            <th
                                style="padding:10px 16px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:center;">
                                Status</th>
                            <th
                                style="padding:10px 16px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:right;">
                                </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            @php
                                $sc = ['received' => ['#6b7280', '#f9fafb'], 'washing' => ['#3b82f6', '#eff6ff'], 'drying' => ['#8b5cf6', '#f5f3ff'], 'ready' => ['#10b981', '#ecfdf5'], 'delivered' => ['#059669', '#d1fae5'], 'cancelled' => ['#ef4444', '#fef2f2']];
                                $c = $sc[$order->status] ?? ['#6b7280', '#f9fafb'];
                            @endphp
                            <tr style="border-top:1px solid #f8fafc;transition:background 0.1s;"
                                onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                <td style="padding:14px 16px;"><a href="{{ route('orders.show', $order) }}"
                                        style="font-size:13px;font-weight:800;color:#2563eb;text-decoration:none;">{{ $order->order_number }}</a>
                                </td>
                                <td style="padding:14px 16px;font-size:13px;font-weight:600;color:#374151;">
                                    {{ $order->customer->name }}</td>
                                <td style="padding:14px 16px;">
                                    @if($order->due_date)
                                        <div style="font-size:12px;font-weight:700;color:#0f172a;">{{ \Carbon\Carbon::parse($order->due_date)->format('M d, Y') }}</div>
                                        <div style="font-size:11px;font-weight:600;color:#64748b;margin-top:2px;">{{ \Carbon\Carbon::parse($order->due_date)->format('h:i A') }}</div>
                                    @else
                                        <span style="font-size:12px;font-weight:600;color:#94a3b8;">N/A</span>
                                    @endif
                                </td>
                                <td style="padding:14px 16px;text-align:center;">
                                    <span
                                        style="font-size:10px;font-weight:800;color:{{ $c[0] }};background:{{ $c[1] }};padding:4px 10px;border-radius:6px;text-transform:uppercase;letter-spacing:0.06em;">{{ $order->status }}</span>
                                </td>
                                <td style="padding:14px 16px;text-align:right;">
                                    <a href="{{ route('orders.show', $order) }}" style="padding:6px 12px;background:#f8fafc;color:#2563eb;border-radius:8px;font-size:11px;font-weight:800;text-decoration:none;border:1px solid #e2e8f0;transition:all 0.15s;" onmouseover="this.style.background='#eff6ff';this.style.borderColor='#bfdbfe';" onmouseout="this.style.background='#f8fafc';this.style.borderColor='#e2e8f0';">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5"
                                    style="padding:32px;text-align:center;font-size:13px;color:#94a3b8;font-weight:500;">No
                                    active orders</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Chart --}}
            <div style="background:white;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 4px rgba(0,0,0,0.04);padding:18px 20px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:12px;">
                    <div>
                        <h3 style="font-size:15px;font-weight:800;color:#0f172a;margin:0 0 2px;letter-spacing:-0.01em;">
                            Order Performance</h3>
                        <p style="font-size:12px;color:#94a3b8;font-weight:500;margin:0;">Pending vs Delivered</p>
                    </div>
                    <div style="display:flex;background:#f1f5f9;border-radius:14px;padding:4px;gap:3px;">
                        <button data-chart-tab="today" class="chart-tab-btn active-tab"
                            style="padding:8px 22px;font-size:12px;font-weight:700;border:none;border-radius:11px;cursor:pointer;transition:all 0.2s ease;letter-spacing:0.01em;background:#1d4ed8;color:white;box-shadow:0 2px 8px rgba(29,78,216,0.4);">Today</button>
                        <button data-chart-tab="weekly" class="chart-tab-btn"
                            style="padding:8px 22px;font-size:12px;font-weight:700;border:none;border-radius:11px;cursor:pointer;transition:all 0.2s ease;letter-spacing:0.01em;background:transparent;color:#64748b;">Weekly</button>
                        <button data-chart-tab="monthly" class="chart-tab-btn"
                            style="padding:8px 22px;font-size:12px;font-weight:700;border:none;border-radius:11px;cursor:pointer;transition:all 0.2s ease;letter-spacing:0.01em;background:transparent;color:#64748b;">Monthly</button>
                    </div>
                </div>
                <div style="position:relative;height:220px;"><canvas id="orderChart"></canvas></div>
                <div
                    style="display:flex;justify-content:center;gap:24px;margin-top:16px;padding-top:14px;border-top:1px solid #f8fafc;">
                    <div style="display:flex;align-items:center;gap:7px;"><span
                            style="width:10px;height:10px;border-radius:3px;background:#f59e0b;display:inline-block;"></span><span
                            style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.06em;">Pending</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:7px;"><span
                            style="width:10px;height:10px;border-radius:3px;background:#10b981;display:inline-block;"></span><span
                            style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.06em;">Delivered</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── BOTTOM GRID ── --}}
        <div class="bottom-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

            {{-- Inventory Alerts --}}
            <div x-data="{ showInvModal: false }"
                style="background:white;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 4px rgba(0,0,0,0.04);overflow:hidden;">
                <div
                    style="padding:20px 24px;border-bottom:1px solid #f8fafc;display:flex;align-items:center;justify-content:space-between;gap:12px;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div
                            style="width:36px;height:36px;border-radius:10px;background:#fef2f2;display:flex;align-items:center;justify-content:center;">
                            <svg width="18" height="18" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h3 style="font-size:15px;font-weight:800;color:#0f172a;margin:0 0 1px;letter-spacing:-0.01em;">
                                Inventory Alerts</h3>
                            <p style="font-size:12px;color:#94a3b8;font-weight:500;margin:0;">Low stock warnings</p>
                        </div>
                    </div>
                    <button @click="showInvModal = true"
                        style="padding:8px 14px;background:#eff6ff;color:#2563eb;font-size:12px;font-weight:700;border:1.5px solid #bfdbfe;border-radius:8px;cursor:pointer;transition:background 0.15s;"
                        onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                        View Full Inventory
                    </button>
                </div>
                <div style="padding:16px 20px;display:flex;flex-direction:column;gap:10px;">
                    @forelse($lowStockItems as $item)
                        <div
                            style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:12px;">
                            <div>
                                <p style="font-size:13px;font-weight:700;color:#0f172a;margin:0 0 2px;">{{ $item->name }}</p>
                                <p
                                    style="font-size:11px;font-weight:600;color:#ef4444;margin:0;text-transform:uppercase;letter-spacing:0.06em;">
                                    Min: {{ $item->min_stock }}</p>
                            </div>
                            <div
                                style="text-align:center;background:white;border:1px solid #fecaca;border-radius:10px;padding:8px 14px;">
                                <p style="font-size:22px;font-weight:900;color:#dc2626;margin:0;line-height:1;">
                                    {{ $item->current_stock }}</p>
                                <p style="font-size:10px;font-weight:700;color:#f87171;margin:0;text-transform:uppercase;">
                                    {{ $item->unit }}</p>
                            </div>
                        </div>
                    @empty
                        <div
                            style="display:flex;flex-direction:column;align-items:center;padding:28px;background:#f0fdf4;border-radius:12px;text-align:center;">
                            <div
                                style="width:44px;height:44px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;margin-bottom:10px;">
                                <svg width="20" height="20" fill="none" stroke="#16a34a" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <p style="font-size:14px;font-weight:800;color:#16a34a;margin:0 0 4px;">Stock Levels Good</p>
                            <p style="font-size:12px;color:#15803d;font-weight:500;margin:0;">No items are currently running
                                low.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Inventory Modal -->
                <div x-show="showInvModal"
                    style="position:fixed;inset:0;z-index:99;display:flex;align-items:center;justify-content:center;background:rgba(15,23,42,0.5);backdrop-filter:blur(4px);display:none;">
                    <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:550px;overflow:hidden;max-height:85vh;display:flex;flex-direction:column;"
                        @click.away="showInvModal = false">
                        <div
                            style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#f8fafc;">
                            <h3 style="font-size:18px;font-weight:900;color:#0f172a;margin:0;">Current Inventory Status</h3>
                            <button @click="showInvModal = false"
                                style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:16px;">✕</button>
                        </div>
                        <div style="padding:0;overflow-y:auto;flex:1;">
                            <table style="width:100%;border-collapse:collapse;text-align:left;">
                                <thead>
                                    <tr style="background:#f1f5f9;border-bottom:1px solid #e2e8f0;">
                                        <th
                                            style="padding:12px 24px;font-size:11px;font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:0.05em;">
                                            Item Name</th>
                                        <th
                                            style="padding:12px 24px;font-size:11px;font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:0.05em;">
                                            Stock Level</th>
                                        <th
                                            style="padding:12px 24px;font-size:11px;font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:0.05em;">
                                            Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allInventory as $inv)
                                        <tr style="border-bottom:1px solid #f1f5f9;">
                                            <td style="padding:14px 24px;font-size:13px;font-weight:700;color:#0f172a;">
                                                {{ $inv->name }}</td>
                                            <td style="padding:14px 24px;">
                                                <span
                                                    style="font-size:14px;font-weight:800;color:#1d4ed8;">{{ $inv->current_stock }}</span>
                                                <span
                                                    style="font-size:11px;font-weight:600;color:#64748b;">{{ $inv->unit }}</span>
                                            </td>
                                            <td style="padding:14px 24px;">
                                                @if($inv->current_stock <= $inv->min_stock)
                                                    <span
                                                        style="padding:4px 8px;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:6px;font-size:11px;font-weight:800;">Low
                                                        Stock</span>
                                                @else
                                                    <span
                                                        style="padding:4px 8px;background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;border-radius:6px;font-size:11px;font-weight:800;">Good</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Shift Register --}}
            <div style="background:#0f172a;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.15);">
                <div
                    style="padding:20px 24px;border-bottom:1px solid rgba(255,255,255,0.06);display:flex;align-items:center;gap:12px;">
                    <div
                        style="width:36px;height:36px;border-radius:10px;background:rgba(59,130,246,0.15);border:1px solid rgba(59,130,246,0.25);display:flex;align-items:center;justify-content:center;">
                        <svg width="18" height="18" fill="none" stroke="#60a5fa" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 style="font-size:15px;font-weight:800;color:#f8fafc;margin:0 0 1px;letter-spacing:-0.01em;">
                            Shift Register</h3>
                        <p style="font-size:12px;color:#475569;font-weight:500;margin:0;">Current session metrics</p>
                    </div>
                </div>
                <div style="padding:20px 24px;display:flex;flex-direction:column;gap:0;">
                    <div
                        style="display:flex;justify-content:space-between;align-items:center;padding:16px 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                        <span style="font-size:13px;font-weight:500;color:#64748b;">Opening Balance</span>
                        <span
                            style="font-size:20px;font-weight:900;color:#f8fafc;letter-spacing:-0.02em;">Rs.{{ number_format($activeSession->opening_cash, 0) }}</span>
                    </div>
                    <div
                        style="display:flex;justify-content:space-between;align-items:center;padding:16px 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                        <span style="font-size:13px;font-weight:500;color:#64748b;">Opened By</span>
                        <span
                            style="font-size:12px;font-weight:700;color:#93c5fd;background:rgba(59,130,246,0.15);border:1px solid rgba(59,130,246,0.2);padding:4px 12px;border-radius:6px;">{{ $activeSession->user->name }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 0;">
                        <span style="font-size:13px;font-weight:500;color:#64748b;">Time Started</span>
                        <span
                            style="font-size:13px;font-weight:700;color:#cbd5e1;">{{ \Carbon\Carbon::parse($activeSession->opened_at)->format('h:i A') }}</span>
                    </div>
                </div>
            </div>
        </div>

    @endif

    <x-slot name="scripts">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const chartData = {
                today: { labels: {!! json_encode($chartData['today']['labels']) !!}, pending: {!! json_encode($chartData['today']['pending']) !!}, delivered: {!! json_encode($chartData['today']['delivered']) !!} },
                weekly: { labels: {!! json_encode($chartData['weekly']['labels']) !!}, pending: {!! json_encode($chartData['weekly']['pending']) !!}, delivered: {!! json_encode($chartData['weekly']['delivered']) !!} },
                monthly: { labels: {!! json_encode($chartData['monthly']['labels']) !!}, pending: {!! json_encode($chartData['monthly']['pending']) !!}, delivered: {!! json_encode($chartData['monthly']['delivered']) !!} },
            };

            const ctx = document.getElementById('orderChart');
            let chart = null;

            function renderChart(tab) {
                const d = chartData[tab];
                if (chart) chart.destroy();
                chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: d.labels,
                        datasets: [
                            { label: 'Pending', data: d.pending, backgroundColor: '#f59e0b', borderRadius: 6, barPercentage: 0.55 },
                            { label: 'Delivered', data: d.delivered, backgroundColor: '#10b981', borderRadius: 6, barPercentage: 0.55 }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { backgroundColor: '#0f172a', padding: 12, cornerRadius: 8 } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 11, weight: '600' } } },
                            x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 12, weight: '700' } } }
                        }
                    }
                });
            }

            if (ctx) renderChart('weekly');

            // Tab click wiring
            const tabBtns = document.querySelectorAll('.chart-tab-btn');
            tabBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    // Update active styles
                    tabBtns.forEach(b => {
                        b.style.background = 'transparent';
                        b.style.color = '#64748b';
                        b.style.boxShadow = 'none';
                        b.classList.remove('active-tab');
                    });
                    btn.style.background = '#1d4ed8';
                    btn.style.color = 'white';
                    btn.style.boxShadow = '0 2px 8px rgba(29,78,216,0.4)';
                    btn.classList.add('active-tab');
                    
                    // Render chart
                    renderChart(btn.dataset.chartTab);
                });
            });
        </script>
    </x-slot>


</x-app-layout>