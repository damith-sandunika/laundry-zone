<x-app-layout>
    @include('orders.partials.header')

    @if(session('success'))
        <div
            style="margin-bottom:12px;padding:10px 14px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;font-size:12px;font-weight:700;color:#16a34a;">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div
            style="margin-bottom:12px;padding:10px 14px;background:#fef2f2;border:1.5px solid #fecaca;border-radius:10px;font-size:12px;font-weight:700;color:#dc2626;">
            <div style="display:flex;align-items:center;gap:6px;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div
            style="margin-bottom:12px;padding:10px 14px;background:#fef2f2;border:1.5px solid #fecaca;border-radius:10px;font-size:12px;font-weight:700;color:#dc2626;">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>Please fix the following errors:</span>
            </div>
            <ul style="margin:0;padding-left:20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('print_bill_id'))
        <script>
            window.onload = function () {
                window.open("{{ route('orders.print', session('print_bill_id')) }}", "_blank");
            };
        </script>
    @endif

    @include('orders.partials.workflow_actions')

    @include('orders.partials.correction_requests')

    <div class="flex flex-col gap-4 items-start">
        <div class="w-full flex flex-col gap-4">
            @include('orders.partials.main_column')

            <div class="max-w-sm flex flex-col gap-4 mt-4">
                @include('orders.partials.payment_slips')

            </div>
        </div>

        @include('orders.partials.maps')



        <!-- Convert to Delivery Modal -->
        <div x-data="convertDeliveryModal()" @open-convert-delivery-modal.window="openModal()">
            <div x-cloak x-show="isOpen"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
                <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:440px;overflow:hidden;"
                    @click.away="isOpen = false">
                    <div
                        style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#fff7ed;">
                        <div>
                            <h3 style="font-size:18px;font-weight:900;color:#ea580c;margin:0;">🚚 Convert to Delivery
                            </h3>
                            <p style="font-size:12px;color:#c2410c;margin:2px 0 0;font-weight:600;">Order
                                #{{ $order->order_number }}</p>
                        </div>
                        <button @click="isOpen = false"
                            style="background:none;border:none;cursor:pointer;color:#ea580c;font-size:20px;">✕</button>
                    </div>
                    <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                        <div>
                            <label
                                style="display:block;font-size:11.5px;font-weight:800;color:#334155;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">Delivery
                                Address *</label>
                            <textarea x-model="address" rows="2"
                                style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:13px;font-weight:600;outline:none;"
                                onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'"
                                placeholder="Enter full delivery address..."></textarea>
                        </div>

                        <div style="display:flex;gap:16px;">
                            <div style="flex:1;">
                                <label
                                    style="display:block;font-size:11.5px;font-weight:800;color:#334155;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">Delivery
                                    Fee (Rs.)</label>
                                <input type="number" step="0.01" x-model="fee"
                                    style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:13px;font-weight:600;outline:none;"
                                    onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'"
                                    placeholder="0.00">
                            </div>
                            <div style="flex:1;">
                                <label
                                    style="display:block;font-size:11.5px;font-weight:800;color:#334155;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">Phone</label>
                                <input type="text" x-model="phone"
                                    style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:13px;font-weight:600;outline:none;"
                                    onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'"
                                    placeholder="Phone number">
                            </div>
                        </div>

                        <div style="display:flex;gap:12px;margin-top:8px;">
                            <button type="button" @click="isOpen = false"
                                style="flex:1;padding:14px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:14px;cursor:pointer;">Cancel</button>
                            <button type="button" @click="submitConvert()" :disabled="isSubmitting"
                                style="flex:1;padding:14px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;font-size:14px;font-weight:900;border:none;border-radius:14px;cursor:pointer;box-shadow:0 4px 12px rgba(234,88,12,0.3);">
                                <span x-text="isSubmitting ? 'Converting...' : 'Confirm Delivery'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function convertDeliveryModal() {
                return {
                    isOpen: false,
                    isSubmitting: false,
                    address: `{{ addslashes($order->customer->address ?? '') }}`,
                    phone: `{{ addslashes($order->customer->phone ?? '') }}`,
                    fee: 0,
                    openModal() {
                        this.isOpen = true;
                    },
                    async submitConvert() {
                        if (!this.address.trim()) {
                            alert('Delivery address is required.');
                            return;
                        }
                        this.isSubmitting = true;
                        try {
                            const res = await fetch(`/orders/{{ $order->id }}/convert-to-delivery`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    delivery_address: this.address,
                                    delivery_fee: this.fee,
                                    contact_phone: this.phone
                                })
                            });
                            const data = await res.json();
                            if (data.success) {
                                window.location.reload();
                            } else {
                                alert(data.message || 'Error converting order.');
                            }
                        } catch (e) {
                            alert('Connection error.');
                        }
                        this.isSubmitting = false;
                    }
                }
            }

            function convertPickupModal() {
                return {
                    showConvertPickupModal: false,
                    isConvertingPickup: false,
                    convertPickupDeliveryFee: {{ $order->delivery_fee ?? 0 }},
                    convertPickupDeductAmount: {{ $order->delivery_fee ?? 0 }},
                    // Overpayment modal
                    showOverpaymentModal: false,
                    overpaymentAmount: 0,
                    isProcessingRefund: false,
                    openModal() {
                        this.showConvertPickupModal = true;
                        this.convertPickupDeductAmount = this.convertPickupDeliveryFee;
                    },
                    async submitConvertPickup() {
                        this.isConvertingPickup = true;
                        try {
                            const res = await fetch(`/orders/{{ $order->id }}/convert-to-pickup`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    deduct_amount: this.convertPickupDeductAmount
                                })
                            });
                            const data = await res.json();
                            if (data.success) {
                                this.showConvertPickupModal = false;
                                if (data.overpayment > 0) {
                                    // Show overpayment modal instead of reloading
                                    this.overpaymentAmount = data.overpayment;
                                    this.showOverpaymentModal = true;
                                } else {
                                    window.location.reload();
                                }
                            } else {
                                alert(data.message || 'Failed to convert.');
                            }
                        } catch (err) {
                            alert('Connection error');
                        }
                        this.isConvertingPickup = false;
                    },
                    async handleOverpayment(action) {
                        this.isProcessingRefund = true;
                        try {
                            const res = await fetch(`/orders/{{ $order->id }}/refund-overpayment`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({ action: action })
                            });
                            const data = await res.json();
                            if (data.success) {
                                window.location.reload();
                            } else {
                                alert(data.message || 'Failed to process.');
                            }
                        } catch (err) {
                            alert('Connection error');
                        }
                        this.isProcessingRefund = false;
                    }
                }
            }
        </script>

    <!-- Pay Now Modal -->
    <div x-data="{ openPayModal: false, payAmount: '{{ number_format(max(0, $order->total - $order->paid_amount), 2, '.', '') }}', payMethod: 'cash' }"
         x-on:open-pay-modal.window="openPayModal = true"
         x-show="openPayModal" x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">

        <div class="bg-white rounded-[24px] shadow-2xl w-full max-w-sm overflow-hidden" @click.away="openPayModal = false" x-show="openPayModal" x-transition.scale.origin.bottom>
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="font-black text-lg text-slate-800">Collect Payment</h3>
                <button @click="openPayModal = false" class="text-slate-400 hover:text-red-500 font-bold text-xl leading-none">&times;</button>
            </div>

            <form action="{{ route('orders.collectCreditPayment', $order->id) }}" method="POST" class="p-5 flex flex-col gap-4">
                @csrf

                <div>
                    <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Amount (Rs.)</label>
                    <input type="number" name="amount" x-model="payAmount" step="0.01" min="0.01" required
                        class="w-full bg-slate-50 border-2 border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 rounded-xl px-4 py-3 text-[15px] font-black text-slate-800 outline-none transition-all">
                    <p class="text-[10px] font-bold text-slate-400 mt-1">Balance Due: Rs.{{ number_format(max(0, $order->total - $order->paid_amount), 2) }}</p>
                </div>

                <div>
                    <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Payment Method</label>
                    <select name="payment_method" x-model="payMethod" required
                        class="w-full bg-slate-50 border-2 border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 rounded-xl px-4 py-3 text-[14px] font-black text-slate-800 outline-none transition-all cursor-pointer">
                        <option value="cash">💵 Cash</option>
                        <option value="card">💳 Card</option>
                        <option value="bank">🏦 Bank Transfer</option>
                    </select>
                </div>

                <div class="flex gap-2 mt-2">
                    <button type="button" @click="openPayModal = false" class="flex-1 py-3 bg-slate-100 text-slate-600 font-bold rounded-xl transition-colors hover:bg-slate-200 text-[13px]">Cancel</button>
                    <button type="submit" class="flex-1 py-3 bg-emerald-500 text-white font-black rounded-xl transition-all hover:bg-emerald-600 hover:shadow-lg hover:shadow-emerald-500/30 text-[13px]">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>