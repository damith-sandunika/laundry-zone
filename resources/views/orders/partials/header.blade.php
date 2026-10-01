<x-slot name="header">
        <style>
            .ord-btn-wrap {
                position: relative;
                display: inline-flex;
                align-items: center;
            }

            .ord-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 36px;
                height: 36px;
                border-radius: 10px;
                border: 1.5px solid;
                background: white;
                cursor: pointer;
                font-size: 16px;
                line-height: 1;
                transition: all 0.15s;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
                text-decoration: none;
                flex-shrink: 0;
            }

            .ord-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
            }

            .ord-btn-label {
                position: absolute;
                bottom: calc(100% + 7px);
                left: 50%;
                transform: translateX(-50%);
                background: #0f172a;
                color: white;
                font-size: 11px;
                font-weight: 700;
                padding: 4px 8px;
                border-radius: 6px;
                white-space: nowrap;
                pointer-events: none;
                opacity: 0;
                transition: opacity 0.15s;
                z-index: 50;
            }

            .ord-btn-label::after {
                content: '';
                position: absolute;
                top: 100%;
                left: 50%;
                transform: translateX(-50%);
                border: 4px solid transparent;
                border-top-color: #0f172a;
            }

            .ord-btn-wrap:hover .ord-btn-label {
                opacity: 1;
            }

            /* Pay Now - green gradient button (larger, pill) */
            .ord-btn--pay-full {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 36px;
                height: 36px;
                border-radius: 10px;
                border: none;
                background: linear-gradient(135deg, #10b981, #059669);
                cursor: pointer;
                font-size: 16px;
                transition: all 0.15s;
                box-shadow: 0 4px 10px rgba(16, 185, 129, 0.35);
            }

            .ord-btn--pay-full:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(16, 185, 129, 0.45);
            }

            /* Individual color accents */
            .ord-btn--edit {
                color: #1d4ed8;
                border-color: #bfdbfe;
            }

            .ord-btn--edit:hover {
                background: #eff6ff;
                border-color: #93c5fd;
            }

            .ord-btn--pdf {
                color: #dc2626;
                border-color: #fecaca;
            }

            .ord-btn--pdf:hover {
                background: #fef2f2;
                border-color: #fca5a5;
            }

            .ord-btn--print {
                color: #334155;
                border-color: #e2e8f0;
            }

            .ord-btn--print:hover {
                background: #f8fafc;
                border-color: #cbd5e1;
            }

            .ord-btn--chit {
                color: #7c3aed;
                border-color: #ddd6fe;
            }

            .ord-btn--chit:hover {
                background: #f5f3ff;
                border-color: #c4b5fd;
            }

            .ord-btn--label {
                color: #0f766e;
                border-color: #99f6e4;
            }

            .ord-btn--label:hover {
                background: #f0fdfa;
                border-color: #5eead4;
            }
        </style>

        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div>
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                    <a href="{{ route('orders.index') }}"
                        style="font-size:12px;color:#94a3b8;text-decoration:none;font-weight:700;"
                        onmouseover="this.style.color='#1d4ed8'" onmouseout="this.style.color='#94a3b8'">← Orders</a>
                    <span style="color:#cbd5e1;font-size:11px;">/</span>
                    <span style="font-size:12px;color:#64748b;font-weight:700;">{{ $order->order_number }}</span>
                </div>
                <h2 class="ph-title">Order Details</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">
                    Customer: <span
                        style="font-weight:700;color:#334155;">{{ $order->customer->name ?? 'Unknown Customer' }}</span>
                    &bull; {{ $order->created_at->format('M d, Y h:i A') }}
                </p>
            </div>

            {{-- ── Icon Buttons with Hover Tooltip ── --}}
            <div style="display:flex;align-items:center;gap:8px;">

                {{-- 💰 Pay Now --}}
                @if(!in_array($order->status, ['delivered', 'completed']) && $order->total - $order->paid_amount > 0 && (auth()->user()->isSuperAdmin() || auth()->user()->branch_id == $order->branch_id))
                    <div class="ord-btn-wrap" x-data="{}">
                        <button type="button" @click="$dispatch('open-pay-modal')" class="ord-btn--pay-full" title="">
                            💰
                        </button>
                        <span class="ord-btn-label">Pay Now</span>
                    </div>
                @endif

                {{-- 💸 Refund Excess --}}
                @if(!in_array($order->status, ['delivered', 'completed']) && $order->paid_amount - $order->total > 0 && (auth()->user()->isSuperAdmin() || auth()->user()->branch_id == $order->branch_id))
                    <div class="ord-btn-wrap" x-data="{}">
                        <button type="button" @click="$dispatch('open-overpayment-modal', {{ $order->paid_amount - $order->total }})" class="ord-btn" style="color: #ef4444; border-color: #fca5a5;" title="" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='white'">
                            💸
                        </button>
                        <span class="ord-btn-label">Refund Excess</span>
                    </div>
                @endif

                {{-- 🏷️ Print Label --}}
                <div class="ord-btn-wrap">
                    <a href="{{ route('orders.printLabel', $order->id) }}" target="_blank"
                        class="ord-btn ord-btn--label" title="">
                        🏷️
                    </a>
                    <span class="ord-btn-label">Print Label</span>
                </div>

                {{-- ✏️ Edit Order --}}
                @if(!in_array($order->status, ['delivered', 'completed']))
                <div class="ord-btn-wrap">
                    <a href="{{ route('orders.edit', $order->order_number) }}" class="ord-btn ord-btn--edit" title="">
                        ✏️
                    </a>
                    <span class="ord-btn-label">Edit Order</span>
                </div>
                @endif

                {{-- 🚚 Convert to Delivery --}}
                @if(!$order->is_delivery_order && !in_array($order->status, ['delivered', 'completed']) && $order->branch?->is_delivery_enabled)
                <div class="ord-btn-wrap" x-data="{}">
                    <button type="button" @click="$dispatch('open-convert-delivery-modal')" class="ord-btn" style="color: #ea580c; border-color: #fed7aa;" title="Convert to Delivery" onmouseover="this.style.background='#fff7ed'" onmouseout="this.style.background='white'">
                        🚚
                    </button>
                    <span class="ord-btn-label">Convert to Delivery</span>
                </div>
                @endif

                {{-- 🏠 Convert to Customer Pickup --}}
                @if($order->is_delivery_order && !in_array($order->status, ['delivered', 'completed']) && $order->deliveryAppointment && $order->deliveryAppointment->delivery_preference !== 'pickup_from_branch')
                <div class="ord-btn-wrap" x-data="{}">
                    <button type="button"
                        @click="$dispatch('open-convert-pickup-modal')"
                        class="ord-btn" style="color:#d97706;border-color:#fde68a;" onmouseover="this.style.background='#fffbeb'" onmouseout="this.style.background='white'" title="Convert to Customer Pickup">
                        🏠
                    </button>
                    <span class="ord-btn-label">Cust Will Pickup</span>
                </div>
                @endif
                <div class="ord-btn-wrap" x-data="{ openPrintModal: false, showMain: true, showSub: true, showService: true }">
                    <button type="button" @click="openPrintModal = true" class="ord-btn ord-btn--pdf" title="Print Invoice Options">
                        📄
                    </button>
                    <span class="ord-btn-label">Download PDF</span>

                    {{-- Alpine Modal --}}
                    <div x-show="openPrintModal" class="fixed inset-0 flex items-center justify-center overflow-y-auto text-left" style="display: none; margin: 0; padding: 0; z-index: 99999;">
                        <div class="fixed inset-0 bg-black bg-opacity-60 transition-opacity" @click="openPrintModal = false"></div>
                        <div class="relative bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm mx-4 transform transition-all" style="z-index: 999999;">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Invoice Print Settings</h3>
                            <div class="space-y-3 mb-6">
                                <label class="flex items-center space-x-3 cursor-pointer">
                                    <input type="checkbox" x-model="showMain" class="form-checkbox h-5 w-5 text-blue-600 rounded">
                                    <span class="text-gray-700 font-medium">Print Main Category</span>
                                </label>
                                <label class="flex items-center space-x-3 cursor-pointer">
                                    <input type="checkbox" x-model="showSub" class="form-checkbox h-5 w-5 text-blue-600 rounded">
                                    <span class="text-gray-700 font-medium">Print Sub Category</span>
                                </label>
                                <label class="flex items-center space-x-3 cursor-pointer">
                                    <input type="checkbox" x-model="showService" class="form-checkbox h-5 w-5 text-blue-600 rounded">
                                    <span class="text-gray-700 font-medium">Print Service Name</span>
                                </label>
                            </div>
                            <div class="flex justify-end space-x-3">
                                <button type="button" @click="openPrintModal = false" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-semibold transition">Cancel</button>
                                <button type="button" @click="window.open(`{{ route('orders.invoice', $order->order_number) }}?show_main=${showMain}&show_sub=${showSub}&show_service=${showService}`, '_blank'); openPrintModal = false" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold shadow-md transition flex items-center gap-2" style="color: white; outline: none; border: none;">
                                    Print Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 🖨️ Print Bill --}}
                <div class="ord-btn-wrap">
                    <a href="{{ route('orders.print', $order->order_number) }}" target="_blank"
                        class="ord-btn ord-btn--print" title="">
                        🖨️
                    </a>
                    <span class="ord-btn-label">Print Bill</span>
                </div>

                {{-- 📋 Print Daw Chit --}}
                <div class="ord-btn-wrap">
                    <a href="{{ route('orders.printChit', $order->id) }}"
                        onclick="window.open(this.href,'_blank','width=320,height=480'); return false;"
                        class="ord-btn ord-btn--chit" title="">
                        📋
                    </a>
                    <span class="ord-btn-label">Print Daw Chit</span>
                </div>

            </div>
        </div>
    </x-slot>
