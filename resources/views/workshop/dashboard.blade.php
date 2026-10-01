<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-slide-up">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-tight">Dashboard</h2>
                <p class="text-[13px] font-bold text-slate-500 mt-1">Today's updates and key metrics for <strong class="text-slate-700">{{ $branchName }}</strong></p>
            </div>
            @if($activeSession)
                <div class="flex flex-row flex-wrap items-center gap-2 sm:gap-3 w-full md:w-auto justify-end mt-3 md:mt-0">
                    <div class="flex items-center justify-center gap-1.5 px-3 sm:px-4 py-2 bg-green-50 border border-green-200 rounded-xl h-9 sm:h-10 shadow-sm flex-1 sm:flex-none">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        <span class="text-[10px] sm:text-xs font-bold text-green-700 tracking-wide uppercase whitespace-nowrap">Shift Active</span>
                    </div>

                    <div x-data="{ showCloseShiftModal: false }">
                        <button @click="showCloseShiftModal = true" class="flex items-center justify-center w-full px-3 sm:px-5 py-2 bg-white text-slate-600 text-[11px] sm:text-[13px] font-bold border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-slate-800 transition-colors h-9 sm:h-10 shadow-sm whitespace-nowrap">
                            Close Shift
                        </button>

                        <!-- Close Shift Modal -->
                        <template x-teleport="body">
                            <div x-show="showCloseShiftModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak>
                                <div @click.away="showCloseShiftModal = false" x-show="showCloseShiftModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden flex flex-col">
                                    <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                                        <h3 class="text-lg font-black text-slate-900">Close Today's Shift</h3>
                                        <button @click="showCloseShiftModal = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <div class="p-6 flex flex-col gap-0 divide-y divide-slate-100">
                                        <div class="flex justify-between items-center py-3">
                                            <span class="text-[13px] font-bold text-slate-500">Opening Balance</span>
                                            <span class="text-[14px] font-black text-slate-800">Rs.{{ number_format($activeSession->opening_cash ?? 0, 0) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-3">
                                            <span class="text-[13px] font-bold text-slate-500">Cash Sales</span>
                                            <span class="text-[14px] font-black text-slate-800">Rs.{{ number_format($stats['cash_collected'] ?? 0, 0) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-3">
                                            <span class="text-[13px] font-bold text-slate-500">Card Sales</span>
                                            <span class="text-[14px] font-black text-slate-800">Rs.{{ number_format($stats['card_payments_amount'] ?? 0, 0) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-3">
                                            <span class="text-[13px] font-bold text-slate-500">Bank Transfers</span>
                                            <span class="text-[14px] font-black text-slate-800">Rs.{{ number_format($stats['bank_transfers'] ?? 0, 0) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-3 bg-blue-50/50 -mx-6 px-6">
                                            <span class="text-[14px] font-black text-blue-700">Total Sales</span>
                                            <span class="text-[16px] font-black text-blue-700">Rs.{{ number_format($stats['total_sale'] ?? 0, 0) }}</span>
                                        </div>

                                        <div class="flex justify-between items-center py-4 bg-emerald-50/50 -mx-6 px-6 border-b-0">
                                            <span class="text-[14px] font-black text-emerald-700 uppercase tracking-wide">Cash in Drawer</span>
                                            <span class="text-[20px] font-black text-emerald-700">Rs.{{ number_format($stats['cash_balance'] ?? 0, 0) }}</span>
                                        </div>
                                        
                                        <div class="mt-6 flex flex-col gap-3">
                                            <a href="{{ route('reports.closeShift', ['action' => 'print']) }}" target="_blank" class="w-full flex items-center justify-center gap-2 py-3 bg-slate-100 text-slate-700 text-[13px] font-extrabold rounded-xl hover:bg-slate-200 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                Get Receipt
                                            </a>
                                            <form action="{{ route('shifts.close') }}" method="POST" onsubmit="return confirm('Are you sure you want to close this shift?')">
                                                @csrf
                                                <button type="submit" class="w-full py-3 bg-red-500 text-white text-[13px] font-extrabold rounded-xl hover:bg-red-600 transition-colors">
                                                    Close Branch
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            @endif
        </div>
    </x-slot>

    @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl p-4 mb-6 animate-fade-in shadow-sm">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            <span class="text-[13px] font-bold text-green-800">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl p-4 mb-6 animate-fade-in shadow-sm">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            <span class="text-[13px] font-bold text-red-800">{{ session('error') }}</span>
        </div>
    @endif

    @if(!$activeSession)
        @include('workshop.dashboard.partials.shift-closed')
    @else

        @include('workshop.dashboard.partials.stat-cards')

        {{-- ── MAIN GRID ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                        @include('workshop.dashboard.partials.active-items')

                        @include('workshop.dashboard.partials.performance-chart')
        </div>

        {{-- ── BOTTOM GRID ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                        @include('workshop.dashboard.partials.inventory-alerts')

                        @include('workshop.dashboard.partials.shift-register')
        </div>

                @if(auth()->user()->role !== 'staff')
                    @include('workshop.dashboard.partials.staff-attendance')
                @endif

    @endif

    @include('workshop.dashboard.partials.deposit-modal')

    @include('workshop.dashboard.partials.scripts')
</x-app-layout>

