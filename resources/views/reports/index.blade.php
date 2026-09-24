<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Business Analytics & Reports</h2>
                <p class="text-[10px] md:text-sm text-slate-500 font-medium mt-1">Comprehensive insights, sales trends, and performance metrics</p>
            </div>
        </div>
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    @include('reports.partials.filters')

    @include('reports.partials.key-metrics')

    @include('reports.partials.charts')

    <!-- Reports Content Wrapper -->
    <div x-data="reportData()" class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        
        @include('reports.partials.tabs')

        <div>
            @include('reports.partials.tab-credit-orders')

            @include('reports.partials.tab-expenses')

            @include('reports.partials.tab-inventory')

            @include('reports.partials.tab-petty-summary')

            @include('reports.partials.tab-bank-deposits')
        </div>

        @include('reports.partials.modals')
    </div>

    @include('reports.partials.scripts')
</x-app-layout>


