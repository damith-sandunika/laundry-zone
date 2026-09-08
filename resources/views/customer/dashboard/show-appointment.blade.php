<x-customer-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('customer.orders') }}" class="text-slate-400 hover:text-blue-600 transition p-2 bg-white rounded-lg shadow-sm border border-slate-100"><i class="fa-solid fa-arrow-left"></i></a>
                <div>
                    <h2 class="font-black text-xl md:text-2xl text-slate-800 leading-tight">#{{ $appointment->order->order_number ?? $appointment->id }}</h2>
                    <p class="text-sm font-bold text-slate-500">Requested on {{ $appointment->created_at->format('M d, Y h:i A') }}</p>
                </div>
            </div>
            
            @if($appointment->status === 'cancelled')
                <span class="bg-red-100 text-red-700 px-4 py-2 rounded-xl font-bold text-sm flex items-center gap-2 border border-red-200">
                    <i class="fa-solid fa-xmark"></i> Rejected
                </span>
            @else
                <span class="bg-yellow-100 text-yellow-700 px-4 py-2 rounded-xl font-bold text-sm flex items-center gap-2">
                    <i class="fa-solid fa-clock"></i> Pending Review
                </span>
            @endif
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Left Column: Details -->
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
                <h3 class="font-black text-slate-800 mb-6 flex items-center gap-2"><i class="fa-solid fa-list-check text-blue-500"></i> Request Details</h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between border-b border-slate-100 pb-3">
                        <span class="text-slate-500 font-bold text-sm">Status</span>
                        <span class="text-slate-800 font-black text-sm uppercase {{ $appointment->status === 'cancelled' ? 'text-red-600' : '' }}">{{ ucfirst($appointment->status) }}</span>
                    </div>
                    @if($appointment->status === 'cancelled')
                        <div class="flex justify-between border-b border-slate-100 pb-3">
                            <span class="text-slate-500 font-bold text-sm">Rejection Reason</span>
                            <span class="text-red-600 font-black text-sm">{{ $appointment->cancel_reason ?: 'No specific reason provided' }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between border-b border-slate-100 pb-3">
                        <span class="text-slate-500 font-bold text-sm">Scheduled Pickup</span>
                        <span class="text-slate-800 font-black text-sm">{{ \Carbon\Carbon::parse($appointment->pickup_scheduled_at)->format('M d, Y h:i A') }}</span>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-3">
                        <span class="text-slate-500 font-bold text-sm">Payment Method</span>
                        <span class="text-slate-800 font-black text-sm">{{ ucfirst(str_replace('_', ' ', $appointment->payment_method ?? 'Cash')) }}</span>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-3">
                        <span class="text-slate-500 font-bold text-sm">Delivery Fee (Estimate)</span>
                        <span class="text-slate-800 font-black text-sm">Rs. {{ number_format($appointment->delivery_cost, 2) }}</span>
                    </div>
                </div>

                @if($appointment->status === 'cancelled')
                    <div class="mt-6 p-4 bg-red-50 rounded-xl border border-red-100">
                        <h4 class="font-bold text-red-800 text-sm mb-2"><i class="fa-solid fa-circle-exclamation mr-1"></i> Order Rejected</h4>
                        <p class="text-xs text-red-700 font-medium leading-relaxed">
                            Unfortunately, we are unable to process your request at this time. If you have any questions, please contact our support team.
                        </p>
                    </div>
                @else
                    <div class="mt-6 p-4 bg-blue-50 rounded-xl border border-blue-100">
                        <h4 class="font-bold text-blue-800 text-sm mb-2"><i class="fa-solid fa-circle-info mr-1"></i> What happens next?</h4>
                        <p class="text-xs text-blue-700 font-medium leading-relaxed">
                            Your pickup request has been received. Our rider will arrive at the scheduled time to collect your laundry. 
                            Once weighed and inspected at our branch, a final order will be generated and you will be able to make the payment here.
                        </p>
                    </div>
                    
                    <div class="mt-6 text-center">
                        <button type="button" onclick="alert('Payment will be activated once the laundry is weighed and the final bill is generated.');" class="bg-slate-800 text-white px-8 py-3 rounded-xl font-bold hover:bg-slate-900 transition-all text-sm w-full md:w-auto shadow-md">
                            <i class="fa-solid fa-credit-card mr-2"></i> Pay Now (Total Pending)
                        </button>
                    </div>
                @endif
            </div>

            @if($appointment->item_description)
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="font-black text-slate-800"><i class="fa-solid fa-shirt text-blue-500 mr-2"></i> Requested Items & Notes</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-slate-700 font-medium whitespace-pre-line">{{ $appointment->item_description }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column: Branch & Location -->
        <div class="space-y-6">
            <!-- Branch Info -->
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-bl from-blue-100 to-transparent rounded-bl-[40px] -z-0"></div>
                <h3 class="font-black text-slate-800 mb-4 relative z-10"><i class="fa-solid fa-shop text-blue-500 mr-2"></i> Assigned Branch</h3>
                <div class="relative z-10">
                    <p class="font-bold text-slate-800 text-sm">{{ $appointment->branch->name ?? 'Pending Assignment' }}</p>
                    @if($appointment->branch)
                        <p class="text-xs font-bold text-slate-500 mt-1"><i class="fa-solid fa-location-dot mr-1"></i> {{ $appointment->branch->address }}</p>
                        @if($appointment->branch->phone)
                            <p class="text-xs font-bold text-slate-500 mt-1"><i class="fa-solid fa-phone mr-1"></i> {{ $appointment->branch->phone }}</p>
                        @endif
                    @endif
                </div>
            </div>
            
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
                <h3 class="font-black text-slate-800 mb-4"><i class="fa-solid fa-truck-pickup text-blue-500 mr-2"></i> Pickup Address</h3>
                <p class="text-sm font-bold text-slate-700 leading-relaxed">{{ $appointment->pickup_address }}</p>
            </div>
        </div>
    </div>
</x-customer-layout>


