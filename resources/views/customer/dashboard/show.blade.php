<x-customer-layout>
    @php
        $hasPendingSlipGlobally = \App\Models\PaymentTransaction::where('order_id', $order->id)->where('status', 'pending')->exists();
    @endphp
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('customer.orders') }}" class="text-slate-400 hover:text-blue-600 transition p-2 bg-white rounded-lg shadow-sm border border-slate-100"><i class="fa-solid fa-arrow-left"></i></a>
                <div>
                    <h2 class="font-black text-2xl text-slate-800 leading-tight">Order #{{ $order->order_number }}</h2>
                    <p class="text-sm font-bold text-slate-500">Placed on {{ $order->created_at->format('M d, Y h:i A') }}</p>
                </div>
            </div>
            @if($order->payment_status === 'unpaid' || $order->payment_status === 'partial')
                @if(!$hasPendingSlipGlobally)
                <button type="button" x-data @click="$dispatch('open-payment-modal')" class="bg-slate-800 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-slate-900 hover:shadow-lg transition-all text-sm flex items-center gap-2">
                    <i class="fa-solid fa-credit-card"></i> Pay Now
                </button>
                @endif
            @else
                <span class="bg-green-100 text-green-700 px-4 py-2 rounded-xl font-bold text-sm flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i> Paid
                </span>
            @endif
        </div>
    </x-slot>

    <!-- Horizontal Status Timeline -->
    @if($order->payment_approved || $order->requested_advance == 0)
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 lg:p-8 mb-6 overflow-hidden">
        <h3 class="font-black text-slate-800 mb-8 flex items-center gap-2"><i class="fa-solid fa-bars-progress text-blue-500"></i> Order Progress</h3>
        
        @php
            if ($order->is_delivery_order) {
                $statuses = [
                    'received'         => ['label' => 'Order Placed', 'icon' => 'fa-clipboard-list', 'color' => 'yellow'],
                    'picked_up'        => ['label' => 'Picked Up', 'icon' => 'fa-truck-pickup', 'color' => 'blue'],
                    'processing'       => ['label' => 'Processing', 'icon' => 'fa-spinner', 'color' => 'blue'],
                    'ready'            => ['label' => 'Ready to Deliver', 'icon' => 'fa-box-open', 'color' => 'blue'],
                    'out_for_delivery' => ['label' => 'Out for Delivery', 'icon' => 'fa-motorcycle', 'color' => 'blue'],
                    'delivered'        => ['label' => 'Delivered', 'icon' => 'fa-truck-fast', 'color' => 'purple'],
                    'completed'        => ['label' => 'Completed', 'icon' => 'fa-check-double', 'color' => 'green']
                ];
            } else {
                $statuses = [
                    'received'   => ['label' => 'Order Placed', 'icon' => 'fa-clipboard-list', 'color' => 'yellow'],
                    'processing' => ['label' => 'Processing', 'icon' => 'fa-spinner', 'color' => 'blue'],
                    'ready'      => ['label' => 'Job Done', 'icon' => 'fa-box-open', 'color' => 'blue'],
                    'completed'  => ['label' => 'Completed', 'icon' => 'fa-check-double', 'color' => 'green']
                ];
            }
            
            $actualStatus = $order->status;
            
            if ($order->is_delivery_order) {
                if (in_array($actualStatus, ['pending', 'received', 'dispatched_to_workshop', 'received_at_workshop'])) {
                    $actualStatus = 'picked_up';
                }
                if (in_array($actualStatus, ['washing', 'drying', 'ironing']) || ($order->payment_approved && in_array($order->status, ['dispatched_to_workshop', 'received_at_workshop']))) {
                    $actualStatus = 'processing';
                }
                if (in_array($actualStatus, ['returned_from_workshop', 'done'])) {
                    $actualStatus = 'ready';
                }
            } else {
                if (in_array($actualStatus, ['pending', 'dispatched_to_workshop', 'received_at_workshop'])) {
                    $actualStatus = 'received';
                }
                if (in_array($actualStatus, ['washing', 'drying', 'ironing']) || ($order->payment_approved && in_array($order->status, ['dispatched_to_workshop', 'received_at_workshop']))) {
                    $actualStatus = 'processing';
                }
                if (in_array($actualStatus, ['returned_from_workshop', 'done'])) {
                    $actualStatus = 'ready';
                }
            }
            
            if ($actualStatus === 'delivered' && $order->payment_status === 'paid') {
                $actualStatus = 'completed';
            } elseif ($actualStatus === 'delivered' && $order->payment_status !== 'paid' && !$order->is_delivery_order) {
                $actualStatus = 'ready'; 
            }

            if ($order->status === 'cancelled') {
                $statuses = ['cancelled' => ['label' => 'Order Cancelled', 'icon' => 'fa-xmark', 'color' => 'red']];
                $actualStatus = 'cancelled';
            }
            
            $currentStatusIndex = array_search($actualStatus, array_keys($statuses));
            if ($currentStatusIndex === false) $currentStatusIndex = 0;
            
            $totalSteps = count($statuses);
            $currentIndex = 0;
        @endphp

        <div class="relative mt-4">
            <div class="absolute left-0 top-6 w-full h-1 bg-slate-100 rounded-full z-0 hidden sm:block"></div>
            
            <div class="absolute left-0 top-6 h-1 bg-blue-500 rounded-full z-0 transition-all duration-500 hidden sm:block" 
                 style="width: {{ $totalSteps > 1 ? ($currentStatusIndex / ($totalSteps - 1)) * 100 : 100 }}%"></div>

            <div class="flex flex-col sm:flex-row justify-between relative z-10 gap-6 sm:gap-0">
                @foreach($statuses as $key => $details)
                    @php
                        $isCompleted = $currentIndex <= $currentStatusIndex;
                        $isCurrent = $currentIndex === $currentStatusIndex;
                        
                        $colorClasses = [
                            'yellow' => ['bg' => 'bg-yellow-500', 'text' => 'text-yellow-600', 'border' => 'border-yellow-200'],
                            'blue'   => ['bg' => 'bg-blue-500', 'text' => 'text-blue-600', 'border' => 'border-blue-200'],
                            'green'  => ['bg' => 'bg-green-500', 'text' => 'text-green-600', 'border' => 'border-green-200'],
                            'purple' => ['bg' => 'bg-purple-500', 'text' => 'text-purple-600', 'border' => 'border-purple-200'],
                            'red'    => ['bg' => 'bg-red-500', 'text' => 'text-red-600', 'border' => 'border-red-200'],
                        ];
                        $color = $colorClasses[$details['color']];
                    @endphp
                    
                    <div class="flex flex-row sm:flex-col items-center sm:text-center gap-4 sm:gap-3 flex-1 relative">
                        @if($currentIndex < $totalSteps - 1)
                            <div class="absolute left-6 top-12 bottom-[-24px] w-0.5 bg-slate-100 sm:hidden z-0 {{ $isCompleted && $currentIndex < $currentStatusIndex ? '!bg-blue-500' : '' }}"></div>
                        @endif

                        <div class="w-12 h-12 rounded-full flex items-center justify-center border-4 border-white shadow-sm shrink-0 relative z-10 transition-colors duration-300
                            {{ $isCompleted ? $color['bg'] . ' text-white' : 'bg-slate-100 text-slate-400' }}
                            {{ $isCurrent ? 'ring-4 ring-blue-50/50 scale-110' : '' }}">
                            <i class="fa-solid {{ $details['icon'] }} text-lg"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-black {{ $isCompleted ? 'text-slate-800' : 'text-slate-400' }}">{{ $details['label'] }}</h4>
                            @if($isCurrent)
                                <p class="text-[10px] font-bold uppercase tracking-wider text-blue-600 mt-1 bg-blue-50 inline-block px-2 py-0.5 rounded-full hidden sm:inline-block">Current</p>
                            @endif
                        </div>
                    </div>
                    
                    @php $currentIndex++; @endphp
                @endforeach
            </div>
        </div>
        </div>
    </div>
    @endif

    <!-- Action Required: Payment Banner -->
    @php
        $latestSlip = \App\Models\PaymentTransaction::where('order_id', $order->id)->whereNotNull('receipt_path')->latest()->first();
        $isLatestSlipRejected = $latestSlip && $latestSlip->status === 'rejected';
        
        $hasStartedWashing = !in_array($order->status, ['pending', 'received', 'dispatched_to_workshop', 'received_at_workshop']);
    @endphp

    @if(!$hasStartedWashing)
        @if($isLatestSlipRejected)
        <div class="bg-red-50 rounded-3xl border border-red-200 p-6 mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 text-red-800 text-center sm:text-left">
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center text-3xl shrink-0">
                    <i class="fa-solid fa-circle-xmark text-red-500"></i>
                </div>
                <div>
                    <h3 class="font-black text-lg mb-1">Payment Slip Rejected</h3>
                    <p class="font-bold text-sm text-red-700 opacity-90 max-w-xl">
                        Unfortunately, your recent payment slip was not accepted by our team.
                    </p>
                    <div class="mt-3 bg-white p-3 rounded-xl border border-red-100 text-sm font-bold shadow-sm">
                        <span class="text-red-900 uppercase text-[10px] tracking-wider block mb-1">Reason for Rejection:</span>
                        {{ $latestSlip->reject_reason ?? 'No reason provided' }}
                    </div>
                </div>
            </div>
            
            <div class="relative z-10 shrink-0 w-full sm:w-auto mt-2 sm:mt-0">
                <button type="button" x-data @click="$dispatch('open-payment-modal')" class="w-full sm:w-auto bg-red-500 text-white hover:bg-red-600 px-6 py-3 rounded-xl font-black shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-up-from-bracket"></i> Upload New Slip
                </button>
            </div>
        </div>
        @elseif(!$order->payment_approved && ($order->requested_advance - $order->paid_amount) > 0 && !$hasPendingSlipGlobally)
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-3xl shadow-lg p-8 mb-6 text-white relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -mt-20 -mr-20 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-white opacity-5 rounded-full -mb-10 -ml-10 pointer-events-none"></div>
            
            <div class="relative z-10 text-center md:text-left">
                <h3 class="text-2xl font-black mb-2"><i class="fa-solid fa-circle-exclamation mr-2 text-yellow-300 animate-pulse"></i> Action Required: Advance Payment</h3>
                <p class="text-blue-100 font-medium text-sm md:text-base max-w-2xl">
                    We have inspected your laundry! To start the washing process, please pay the requested advance amount of 
                    <strong class="text-white bg-blue-800/50 px-2 py-1 rounded-lg">LKR {{ number_format($order->requested_advance - $order->paid_amount, 2) }}</strong>. 
                    Once paid, our workshop will immediately begin processing your order.
                </p>
            </div>
            
            <div class="relative z-10 shrink-0 w-full md:w-auto">
                <button type="button" x-data @click="$dispatch('open-payment-modal')" class="w-full md:w-auto bg-yellow-400 text-yellow-900 hover:bg-yellow-300 px-8 py-4 rounded-2xl font-black shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all text-lg flex items-center justify-center gap-3">
                    <i class="fa-solid fa-bolt"></i> PAY NOW
                </button>
            </div>
        </div>
        @elseif($hasPendingSlipGlobally)
        <div class="bg-yellow-50 rounded-3xl border border-yellow-200 p-6 mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4 text-yellow-800">
                <div class="w-12 h-12 rounded-full bg-yellow-200 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-hourglass-half fa-spin"></i>
                </div>
                <div>
                    <h3 class="font-black text-lg">Payment Under Review</h3>
                    <p class="font-bold text-sm text-yellow-700 opacity-90 mt-1">Your payment slip has been submitted and is currently being reviewed by our workshop admins.</p>
                </div>
            </div>
        </div>
        @endif
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Details & Items -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Order Items -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="font-black text-slate-800"><i class="fa-solid fa-shirt text-blue-500 mr-2"></i> Order Items</h3>
                    <span class="text-sm font-bold text-slate-500">{{ $order->items->count() }} Items</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach($order->items as $item)
                        <div class="p-6 flex items-center justify-between hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                                    <i class="fa-solid fa-tshirt"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-sm">{{ $item->serviceType->name ?? 'Service' }}</h4>
                                    <p class="text-xs font-bold text-slate-500 mt-1">Qty: {{ $item->quantity }} &times; LKR {{ number_format($item->unit_price, 2) }}</p>
                                </div>
                            </div>
                            <div class="font-black text-slate-800">
                                LKR {{ number_format($item->total_price, 2) }}
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="bg-slate-50 p-6 border-t border-slate-100">
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm font-bold text-slate-500">
                            <span>Subtotal</span>
                            <span>LKR {{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        @if($order->discount > 0)
                            <div class="flex justify-between text-sm font-bold text-green-600">
                                <span>Discount</span>
                                <span>- LKR {{ number_format($order->discount, 2) }}</span>
                            </div>
                        @endif
                        @if($order->additional > 0)
                            <div class="flex justify-between text-sm font-bold text-red-500">
                                <span>Additional Charges</span>
                                <span>+ LKR {{ number_format($order->additional, 2) }}</span>
                            </div>
                        @endif
                        @if($order->delivery_fee > 0)
                            <div class="flex justify-between text-sm font-bold text-slate-700">
                                <span>Delivery Fee</span>
                                <span>+ LKR {{ number_format($order->delivery_fee, 2) }}</span>
                            </div>
                        @endif
                        <div class="pt-3 border-t border-slate-200 flex justify-between items-center">
                            <span class="font-black text-slate-800">Total Amount</span>
                            <span class="font-black text-lg text-slate-800">LKR {{ number_format($order->total, 2) }}</span>
                        </div>
                        @if($order->paid_amount > 0)
                            <div class="flex justify-between text-sm font-bold text-green-600 mt-2">
                                <span>Paid Amount</span>
                                <span>- LKR {{ number_format($order->paid_amount, 2) }}</span>
                            </div>
                        @endif
                        
                        @if($order->total - $order->paid_amount > 0)
                            <div class="pt-3 border-t border-slate-200 flex flex-col sm:flex-row justify-between sm:items-center mt-2 bg-blue-50 -mx-6 px-6 py-4 border-b border-blue-100 gap-3">
                                <div>
                                    <span class="block font-black text-blue-900">Balance to Pay</span>
                                    <span class="font-black text-2xl text-blue-700">LKR {{ number_format(max(0, $order->total - $order->paid_amount), 2) }}</span>
                                </div>
                                @if(!$hasPendingSlipGlobally)
                                <button type="button" x-data @click="$dispatch('open-payment-modal')" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-md transition-colors text-sm flex items-center justify-center gap-2 w-full sm:w-auto">
                                    <i class="fa-solid fa-credit-card"></i> Pay Now
                                </button>
                                @endif
                            </div>
                        @else
                            <div class="pt-3 border-t border-slate-200 flex justify-between items-center mt-2 bg-green-50 -mx-6 px-6 py-3 border-b border-green-100">
                                <span class="font-black text-green-900">Payment Status</span>
                                <span class="font-black text-lg text-green-700"><i class="fa-solid fa-check-circle mr-1"></i> Fully Paid</span>
                            </div>
                        @endif
                        
                        @php
                            $slips = \App\Models\PaymentTransaction::where('order_id', $order->id)->whereNotNull('receipt_path')->orderBy('created_at', 'desc')->get();
                            $hasPendingSlip = $slips->where('status', 'pending')->count() > 0;
                        @endphp
                        @if($slips->count() > 0)
                            <div class="mt-4 pt-4 border-t border-slate-200 space-y-3">
                                <span class="text-sm font-black text-slate-800 mb-1 block">Payment Slips Uploaded</span>
                                
                                @if($hasPendingSlip)
                                    <div class="bg-yellow-50 text-yellow-700 p-3 rounded-xl border border-yellow-200 text-xs font-bold flex items-center gap-2">
                                        <i class="fa-solid fa-hourglass-half text-yellow-500 text-lg"></i>
                                        <span>Your uploaded bank slip is currently being reviewed by our workshop admin. Please wait until it is approved. The paid amount will be updated shortly!</span>
                                    </div>
                                @endif

                                @foreach($slips as $slip)
                                    <div class="bg-white p-3 rounded-xl border {{ $slip->status === 'rejected' ? 'border-red-200' : ($slip->status === 'pending' ? 'border-yellow-200' : 'border-green-200') }} shadow-sm">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <span class="text-sm font-black text-slate-800">LKR {{ number_format($slip->amount, 2) }}</span>
                                                <span class="text-[10px] text-slate-400 font-bold block">{{ $slip->created_at->format('M d, Y - g:i A') }}</span>
                                            </div>
                                            <div class="flex flex-col items-end gap-2">
                                                @if($slip->status === 'pending')
                                                    <span class="bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wide">Pending Review</span>
                                                @elseif($slip->status === 'completed')
                                                    <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wide">Approved</span>
                                                @elseif($slip->status === 'rejected')
                                                    <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wide">Rejected</span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        @if($slip->status === 'rejected' && $slip->reject_reason)
                                            <div class="bg-red-50 text-red-800 p-2 rounded-lg text-xs font-bold mb-2 border border-red-100">
                                                <i class="fa-solid fa-circle-exclamation mr-1"></i> Reason: {{ $slip->reject_reason }}
                                            </div>
                                        @endif
                                        
                                        <div class="mt-2 text-right">
                                            <a href="{{ \Illuminate\Support\Str::startsWith($slip->receipt_path, 'http') ? $slip->receipt_path : asset('storage/' . $slip->receipt_path) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-xs font-bold hover:bg-slate-200 transition-colors">
                                                <i class="fa-solid fa-file-invoice mr-1.5"></i> View Slip
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($order->notes)
                <div class="bg-yellow-50 rounded-3xl border border-yellow-100 p-6">
                    <h3 class="font-black text-yellow-800 text-sm mb-2"><i class="fa-solid fa-note-sticky mr-1"></i> Order Notes</h3>
                    <p class="text-sm text-yellow-700 font-medium">{{ $order->notes }}</p>
                </div>
            @endif
        </div>

        <!-- Right Column: Timeline & Branch -->
        <div class="space-y-6">
            <!-- Branch Info -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-bl from-blue-100 to-transparent rounded-bl-[40px] -z-0"></div>
                <h3 class="font-black text-slate-800 mb-4 relative z-10"><i class="fa-solid fa-shop text-blue-500 mr-2"></i> Branch Details</h3>
                <div class="relative z-10">
                    <p class="font-bold text-slate-800 text-sm">{{ $order->branch->name }}</p>
                    <p class="text-xs font-bold text-slate-500 mt-1"><i class="fa-solid fa-location-dot mr-1"></i> {{ $order->branch->address }}</p>
                    @if($order->branch->phone)
                        <p class="text-xs font-bold text-slate-500 mt-1"><i class="fa-solid fa-phone mr-1"></i> {{ $order->branch->phone }}</p>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Payment Modal -->
    <div x-data="{ 
            showPaymentModal: false, 
            amountToPay: {{ ($order->requested_advance - $order->paid_amount > 0) ? ($order->requested_advance - $order->paid_amount) : max(0, $order->total - $order->paid_amount) }},
            paymentMethod: 'bank',
            isSubmitting: false
         }" 
         @open-payment-modal.window="showPaymentModal = true"
         class="relative z-50" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
         
        <div x-show="showPaymentModal" x-transition.opacity class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>

        <div x-show="showPaymentModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="fixed inset-0 z-10 w-screen overflow-y-auto" style="display: none;">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md">
                    <form action="{{ route('customer.orders.submitPayment', $order->order_number) }}" method="POST" enctype="multipart/form-data" @submit="isSubmitting = true">
                        @csrf
                        <div class="bg-white px-6 pb-6 pt-8 sm:p-8">
                            <div class="sm:flex sm:items-start mb-6">
                                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:h-12 sm:w-12 text-blue-600 text-xl">
                                    <i class="fa-solid fa-credit-card"></i>
                                </div>
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                    <h3 class="text-xl font-black leading-6 text-slate-800" id="modal-title">Make Payment</h3>
                                    <div class="mt-1">
                                        <p class="text-sm text-slate-500 font-medium">Please enter the amount you wish to pay.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1">Amount (LKR)</label>
                                    <input type="number" name="amount" x-model="amountToPay" step="0.01" max="{{ $order->total }}" required class="w-full px-4 py-3 text-lg font-black text-blue-700 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 text-center">
                                    <div class="flex justify-between mt-1 px-1">
                                        <span class="text-[10px] text-slate-400 font-bold">Requested: {{ number_format($order->requested_advance > 0 ? $order->requested_advance : $order->total, 2) }}</span>
                                        <span class="text-[10px] text-slate-400 font-bold">Total: {{ number_format($order->total, 2) }}</span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Payment Method</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        @forelse($paymentMethods as $method)
                                        <label class="relative cursor-pointer">
                                            <input type="radio" name="payment_method" value="{{ $method->type === 'bank_transfer' ? 'bank' : 'gateway' }}" x-model="paymentMethod" class="peer sr-only">
                                            <div class="p-3 border-2 border-slate-200 rounded-xl text-center peer-checked:border-blue-600 peer-checked:bg-blue-50 hover:bg-slate-50 transition-all h-full flex flex-col justify-center">
                                                <i class="fa-solid {{ $method->type === 'bank_transfer' ? 'fa-building-columns' : 'fa-credit-card' }} text-slate-400 peer-checked:text-blue-600 mb-1 text-lg"></i>
                                                <div class="text-xs font-black text-slate-700 peer-checked:text-blue-700">{{ $method->name }}</div>
                                            </div>
                                        </label>
                                        @empty
                                        <div class="col-span-2 text-sm text-red-500 font-bold p-3 bg-red-50 rounded-xl border border-red-100">
                                            No online payment methods are currently available.
                                        </div>
                                        @endforelse
                                    </div>
                                </div>
                                
                                <div x-show="paymentMethod === 'bank'" x-collapse>
                                    @if(!empty($bankDetails))
                                    <div class="mb-4 p-4 bg-blue-50 rounded-xl border border-blue-100">
                                        <h4 class="text-sm font-black text-blue-800 mb-2">Our Bank Details</h4>
                                        <div class="text-xs text-blue-700 whitespace-pre-wrap font-medium">{!! nl2br(e($bankDetails)) !!}</div>
                                    </div>
                                    @endif
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Upload Bank Slip</label>
                                    <input type="file" name="receipt_image" accept="image/*" :required="paymentMethod === 'bank'" class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all border border-slate-200 rounded-xl p-1 bg-slate-50">
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100 gap-3">
                            <button type="submit" :disabled="isSubmitting" class="inline-flex w-full justify-center rounded-xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-md hover:bg-blue-500 sm:ml-3 sm:w-auto transition-colors disabled:opacity-70 disabled:cursor-not-allowed">
                                <span x-show="!isSubmitting">Proceed to Pay</span>
                                <span x-show="isSubmitting"><i class="fa-solid fa-spinner fa-spin mr-2"></i> Processing...</span>
                            </button>
                            <button type="button" @click="showPaymentModal = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-6 py-3 text-sm font-black text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-customer-layout>
