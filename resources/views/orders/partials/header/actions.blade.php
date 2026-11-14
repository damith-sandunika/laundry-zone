                {{-- 💰 Pay Now --}}
                @if(!in_array($order->status, ['delivered', 'completed']) && $order->total - $order->paid_amount > 0 && auth()->user()->branch_id == $order->branch_id)
                    <div class="ord-btn-wrap" x-data="{}">
                        <button type="button" @click="$dispatch('open-pay-modal')" class="ord-btn--pay-full" title="">
                            💰
                        </button>
                        <span class="ord-btn-label">Pay Now</span>
                    </div>
                @endif

                {{-- 💸 Refund Excess --}}
                @if(!in_array($order->status, ['delivered', 'completed']) && $order->paid_amount - $order->total > 0 && auth()->user()->branch_id == $order->branch_id)
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
                @if(config('app.delivery_enabled') && !$order->is_delivery_order && !in_array($order->status, ['delivered', 'completed']) && $order->branch?->is_delivery_enabled)
                <div class="ord-btn-wrap" x-data="{}">
                    <button type="button" @click="$dispatch('open-convert-delivery-modal')" class="ord-btn" style="color: #ea580c; border-color: #fed7aa;" title="Convert to Delivery" onmouseover="this.style.background='#fff7ed'" onmouseout="this.style.background='white'">
                        🚚
                    </button>
                    <span class="ord-btn-label">Convert to Delivery</span>
                </div>
                @endif

                {{-- 🏠 Convert to Customer Pickup --}}
                @if(config('app.delivery_enabled') && $order->is_delivery_order && !in_array($order->status, ['delivered', 'completed']) && $order->deliveryAppointment && $order->deliveryAppointment->delivery_preference !== 'pickup_from_branch')
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
