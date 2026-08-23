<x-customer-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div>
                <h2 class="font-black text-2xl text-slate-800 tracking-tight">Welcome, {{ $customer->name }}!</h2>
                <p class="text-sm text-slate-500 font-medium mt-1">Manage your laundry orders and track deliveries.</p>
            </div>
            <a href="{{ route('customer.booking') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-md shadow-blue-200 transition-all flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> New Booking
            </a>
        </div>
    </x-slot>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-box-open"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Active Orders</p>
                <h3 class="text-2xl font-black text-slate-800">{{ $activeCount }}</h3>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-check-double"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Orders</p>
                <h3 class="text-2xl font-black text-slate-800">{{ $totalOrders }}</h3>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4 col-span-2 md:col-span-1">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Spent</p>
                <h3 class="text-2xl font-black text-slate-800">LKR {{ number_format($totalSpent, 2) }}</h3>
            </div>
        </div>
    </div>

    <!-- Active Orders -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-slate-800">Active Orders</h3>
            @if($activeCount > 0)
                <a href="{{ route('customer.orders') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">View All</a>
            @endif
        </div>

        @if($activeCount > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Appointments (Pending Pickups) -->
                @foreach($appointments as $appointment)
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition-shadow relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-bl from-purple-100 to-transparent rounded-bl-3xl -z-0"></div>
                        <div class="relative z-10 flex justify-between items-start mb-4">
                            <div>
                                <span class="text-xs font-bold bg-purple-100 text-purple-700 px-2 py-1 rounded-md mb-2 inline-block">
                                    <i class="fa-solid fa-truck-pickup"></i> Pickup Request
                                </span>
                                <h4 class="font-bold text-slate-800">Booking #{{ str_pad($appointment->id, 4, '0', STR_PAD_LEFT) }}</h4>
                            </div>
                            <div class="text-right">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">
                                    {{ ucfirst($appointment->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="relative z-10 flex justify-between items-end">
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ \Carbon\Carbon::parse($appointment->pickup_scheduled_at)->format('M d, Y h:i A') }}</p>
                                <p class="text-xs text-slate-500">Scheduled Pickup</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="alert('Payment will be activated once the laundry is weighed and the final bill is generated.');" class="text-xs font-bold text-white bg-slate-800 hover:bg-slate-900 px-3 py-1.5 rounded-lg transition-colors shadow-sm">
                                    Pay Now
                                </button>
                                <a href="{{ route('customer.appointments.show', $appointment->id) }}" class="text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-100 px-3 py-1.5 rounded-lg transition-colors inline-block">
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Active Processing Orders -->
                @foreach($activeOrders as $order)
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <span class="text-xs font-bold bg-slate-100 text-slate-600 px-2 py-1 rounded-md mb-2 inline-block">
                                    {{ $order->order_number }}
                                </span>
                                <h4 class="font-bold text-slate-800">{{ $order->items->count() }} Items</h4>
                            </div>
                            <div class="text-right">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold
                                    @if($order->status === 'pending') bg-yellow-100 text-yellow-700
                                    @elseif(in_array($order->status, ['processing', 'washing', 'drying', 'ironing'])) bg-blue-100 text-blue-700
                                    @elseif($order->status === 'ready') bg-green-100 text-green-700
                                    @endif
                                ">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex justify-between items-end">
                            <div>
                                <p class="text-sm font-bold text-slate-800">LKR {{ number_format($order->total, 2) }}</p>
                                <p class="text-xs text-slate-500">{{ $order->created_at->format('M d, Y') }}</p>
                            </div>
                            @if($order->payment_status === 'unpaid')
                                <a href="{{ route('customer.orders.pay', $order->id) }}" class="text-xs font-bold text-white bg-slate-800 hover:bg-slate-900 px-3 py-1.5 rounded-lg transition-colors">
                                    Pay Now
                                </a>
                                <a href="{{ route('customer.orders.show', $order) }}" class="text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors ml-2">
                                    View
                                </a>
                            @else
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-green-600 flex items-center gap-1">
                                        <i class="fa-solid fa-circle-check"></i> Paid
                                    </span>
                                    <a href="{{ route('customer.orders.show', $order) }}" class="text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors">
                                        View
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-slate-50 rounded-2xl border border-slate-200 border-dashed p-8 text-center">
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center text-slate-300 mx-auto mb-3 shadow-sm">
                    <i class="fa-solid fa-shirt text-2xl"></i>
                </div>
                <h4 class="font-bold text-slate-700 mb-1">No active orders</h4>
                <p class="text-sm text-slate-500 mb-4">You don't have any ongoing laundry orders at the moment.</p>
                <a href="{{ route('customer.booking') }}" class="inline-block bg-white text-blue-600 border border-blue-200 hover:border-blue-300 hover:bg-blue-50 px-4 py-2 rounded-xl text-sm font-bold transition-all">
                    Book a Pickup
                </a>
            </div>
        @endif
    </div>

</x-app-layout>
