{{-- Inventory Alerts --}}
            <div x-data="{ showInvModal: false }" class="bg-white/70 backdrop-blur-md rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden animate-slide-up" style="animation-delay: 300ms;">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between gap-4 bg-slate-50/50">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 tracking-tight">Inventory Alerts</h3>
                            <p class="text-[12px] font-semibold text-slate-500">Low stock warnings</p>
                        </div>
                    </div>
                    <button @click="showInvModal = true" class="px-3.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl transition-colors">
                        View Full Inventory
                    </button>
                </div>
                <div class="p-6 flex flex-col gap-3">
                    @forelse($lowStockItems as $item)
                        <div class="flex justify-between items-center p-4 bg-red-50/50 border border-red-100 rounded-2xl hover:bg-red-50 transition-colors">
                            <div>
                                <p class="text-[13px] font-black text-slate-900 mb-1">{{ $item->name }}</p>
                                <p class="text-[10px] font-extrabold text-red-500 uppercase tracking-widest">Min: {{ $item->low_stock_threshold }}</p>
                            </div>
                            <div class="text-center bg-white border border-red-100 rounded-xl px-4 py-2 shadow-sm">
                                <p class="text-xl font-black text-red-600 leading-none">{{ $item->current_stock }}</p>
                                <p class="text-[9px] font-extrabold text-red-400 uppercase mt-1">{{ $item->unit }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center p-8 bg-green-50/50 rounded-2xl text-center border border-green-100">
                            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <p class="text-sm font-black text-green-700 mb-1">Stock Levels Good</p>
                            <p class="text-xs font-semibold text-green-600/80">No items are currently running low.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Inventory Modal -->
                <template x-teleport="body">
                    <div x-show="showInvModal" class="fixed inset-0 z-[100] flex items-start justify-center pt-16 sm:pt-24 bg-slate-900/40 backdrop-blur-sm p-4" x-cloak>
                        <div @click.away="showInvModal = false" x-show="showInvModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="bg-white rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden max-h-[85vh] flex flex-col">
                            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                                <h3 class="text-lg font-black text-slate-900">Current Inventory Status</h3>
                                <button @click="showInvModal = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div class="overflow-y-auto flex-1 p-0">
                                <table class="w-full text-left border-collapse">
                                    <thead class="bg-slate-50 sticky top-0">
                                        <tr>
                                            <th class="px-6 py-4 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest border-b border-slate-200">Item Name</th>
                                            <th class="px-6 py-4 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest border-b border-slate-200">Stock Level</th>
                                            <th class="px-6 py-4 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest border-b border-slate-200">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($allInventory as $inv)
                                            <tr class="hover:bg-slate-50/50">
                                                <td class="px-6 py-4 text-[13px] font-bold text-slate-900">{{ $inv->name }}</td>
                                                <td class="px-6 py-4">
                                                    <span class="text-sm font-black text-blue-700">{{ $inv->current_stock }}</span>
                                                    <span class="text-[11px] font-bold text-slate-500">{{ $inv->unit }}</span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    @if($inv->current_stock <= $inv->low_stock_threshold)
                                                        <span class="inline-flex px-2.5 py-1 bg-red-50 text-red-700 border border-red-200 rounded-lg text-[10px] font-extrabold uppercase tracking-wide">Low Stock</span>
                                                    @else
                                                        <span class="inline-flex px-2.5 py-1 bg-green-50 text-green-700 border border-green-200 rounded-lg text-[10px] font-extrabold uppercase tracking-wide">Good</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
