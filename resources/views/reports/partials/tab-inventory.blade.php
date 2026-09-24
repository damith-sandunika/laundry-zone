            <!-- INVENTORY USAGE LOG -->
            <div x-show="activeReport === 'inventory'" style="display:none;" x-transition>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-[10px] md:text-sm">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Date</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Supply Item</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Type</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-center">Qty Changes</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Reason / By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($inventoryLogs as $log)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-semibold text-slate-500">{{ $log->created_at->format('Y-m-d h:i A') }}</td>
                                    <td class="px-2 py-2 md:px-6 md:py-4 font-bold text-slate-900">{{ $log->inventoryItem->name ?? 'Deleted Item' }}</td>
                                    <td class="px-2 py-2 md:px-6 md:py-4">
                                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest rounded-lg border {{ $log->type === 'stock_in' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                            {{ strtoupper(str_replace('_', ' ', $log->type)) }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-2 md:px-6 md:py-4 text-center font-black text-[10px] md:text-sm {{ $log->type === 'stock_in' ? 'text-emerald-500' : 'text-rose-500' }}">
                                        {{ $log->type === 'stock_in' ? '+' : '-' }}{{ $log->quantity }}
                                    </td>
                                    <td class="px-2 py-2 md:px-6 md:py-4 text-[10px] md:text-sm font-medium text-slate-500">
                                        {{ $log->notes ?? '-' }} <br><span class="font-bold text-slate-900">{{ $log->createdBy->name ?? 'System' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-6 md:px-6 md:py-10 text-center text-slate-400 text-[10px] md:text-sm font-semibold">No inventory transactions tracked in this range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Inventory Pagination -->
                <div class="p-4 border-t border-slate-100 bg-slate-50">
                    {{ $inventoryLogs->appends(request()->query())->links() }}
                </div>
            </div>
