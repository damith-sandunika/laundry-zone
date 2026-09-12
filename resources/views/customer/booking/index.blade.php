<x-customer-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div class="flex items-center gap-4">
                <div>
                    <h2 class="font-bold text-xl sm:text-2xl text-slate-800 tracking-tight">Book a Pickup</h2>
                    <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1 hidden sm:block">Schedule a laundry pickup right to your doorstep.</p>
                </div>
            </div>
            <a href="{{ route('customer.dashboard') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl text-sm font-bold shadow-sm transition-all flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </x-slot>

    <x-booking-wizard 
        :categories="$categories"
        :minimumOrderLimit="$minimumOrderLimit"
        :deliveryRanges="$deliveryRanges"
        :workshops="$workshops"
        :turnaroundTypes="$turnaroundTypes"
        :extras="$extras"
        :shopLat="$shopLat"
        :shopLng="$shopLng"
        :pickupTimePeriods="$pickupTimePeriods"
        :paymentMethods="$paymentMethods"
    />
</x-customer-layout>
