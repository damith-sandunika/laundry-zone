<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <h2 class="ph-title">Workshop Dashboard</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Manage incoming orders, processing, and dispatches</p>
            </div>
            <a href="{{ route('orders.history') }}" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-lg hover:bg-slate-200">View History</a>
        </div>
    </x-slot>

    <div x-data="workshopOperations">
        @include('workshop.partials.tabs-filter')
        @include('workshop.partials.orders-table')
        @include('workshop.partials.modals')
    </div>{{-- /main x-data --}}

    @push('scripts')
        @include('workshop.partials.operations_scripts')
    @endpush
</x-app-layout>
