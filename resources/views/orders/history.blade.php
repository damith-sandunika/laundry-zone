<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <h2 class="ph-title">All Orders History</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">
                    @if(auth()->user()->isSuperAdmin())
                        System-wide log of all orders across branches & workshops
                    @elseif($isWorkshop ?? false)
                        Log of all orders for this workshop (including branch dispatches)
                    @else
                        Log of all orders for this branch
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if($isWorkshop ?? false)
                    <a href="{{ route('workshop.operations') }}" class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition-colors">
                        Workshop Operations
                    </a>
                @elseif(!auth()->user()->isSuperAdmin())
                    <a href="{{ route('orders.index') }}" class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition-colors">
                        Back to Orders
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    {{-- Filter bar --}}
    @include('orders.partials.history.filters')

    {{-- Summary KPIs and Statistics --}}
    @include('orders.partials.history.stats')

    {{-- Orders History Table & Pagination --}}
    @include('orders.partials.history.table')

    {{-- Rejected Online Bookings Log & Details Modal --}}
    @include('orders.partials.history.rejected_bookings')
</x-app-layout>
