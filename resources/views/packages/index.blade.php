<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="ph-title">Customer Packages</h2>
            <p class="ph-sub">Manage and issue subscriptions to customers</p>
        </div>
        <button x-data @click="$dispatch('open-issue-package-modal')" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:shadow-lg hover:shadow-blue-500/30 transition-all transform hover:-translate-y-0.5">
            + Issue Package
        </button>
    </x-slot>

    <!-- Success/Error Alerts -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl font-semibold flex items-center gap-3">
            <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl font-semibold flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($customerPackages as $cp)
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col relative">
                <div class="p-5 flex-1">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h4 class="font-black text-slate-900 text-lg leading-tight">{{ $cp->customer->name }}</h4>
                            <p class="text-xs font-bold text-slate-500">{{ $cp->customer->phone }}</p>
                        </div>
                        <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-widest rounded-lg {{ $cp->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $cp->status }}
                        </span>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 mb-4">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-bold text-slate-800">{{ $cp->package->name }}</span>
                            <span class="text-xs font-black text-blue-600">Rs.{{ number_format($cp->package->price, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-[11px] font-semibold text-slate-500">
                            <span>Expires: {{ $cp->end_date->format('M d, Y') }}</span>
                            <span class="px-2 py-0.5 rounded-md {{ $cp->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }} uppercase tracking-wider text-[9px] font-black">
                                {{ $cp->payment_status }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Service Usage</p>
                        @foreach($cp->services as $svc)
                            @php
                                $percent = $svc->total_quantity > 0 ? min(100, ($svc->used_quantity / $svc->total_quantity) * 100) : 0;
                                $color = $percent >= 100 ? 'bg-red-500' : ($percent > 75 ? 'bg-amber-500' : 'bg-blue-500');
                            @endphp
                            <div>
                                <div class="flex justify-between text-xs font-bold mb-1">
                                    <span class="text-slate-700">{{ $svc->serviceType->name }}</span>
                                    <span class="text-slate-900">{{ floatval($svc->used_quantity) }} / {{ floatval($svc->total_quantity) }}</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="{{ $color }} h-1.5 rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-slate-50/50 p-4 border-t border-slate-100 flex items-center justify-between gap-2" x-data="{ deleteModalOpen_{{ $cp->id }}: false }">
                    <button type="button" @click="deleteModalOpen_{{ $cp->id }} = true" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete Package">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>

                    <!-- Delete Authorization Modal -->
                    <div x-show="deleteModalOpen_{{ $cp->id }}" class="fixed inset-0 z-[9999] overflow-y-auto" style="display: none;">
                        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
                            <div x-show="deleteModalOpen_{{ $cp->id }}" x-transition.opacity class="fixed inset-0 transition-opacity" @click="deleteModalOpen_{{ $cp->id }} = false">
                                <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm"></div>
                            </div>
                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                            <div x-show="deleteModalOpen_{{ $cp->id }}" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full border border-gray-100">
                                <form action="{{ route('packages.destroy', $cp) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <div class="bg-white px-6 pt-6 pb-6">
                                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            Delete Authorization Required
                                        </h3>
                                        <p class="text-sm text-gray-600 mb-4">Deleting this package will reverse any payments related to it. Please enter a Branch Admin or Super Admin password to confirm.</p>
                                        
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Admin Password</label>
                                            <input type="password" name="admin_password" required class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-red-500 focus:border-red-500 bg-gray-50">
                                        </div>
                                    </div>
                                    <div class="bg-gray-50/80 px-6 py-4 flex justify-end gap-3 border-t border-gray-100">
                                        <button type="button" @click="deleteModalOpen_{{ $cp->id }} = false" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 transition-colors shadow-sm">Cancel</button>
                                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-red-700 transition-colors">Confirm Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        @if($cp->payment_status === 'pending')
                            <form action="{{ route('packages.markPaid', $cp) }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-black text-[11px] uppercase tracking-wider rounded-lg transition-colors border border-emerald-200">
                                    Mark as Paid
                                </button>
                            </form>
                        @endif
                        <button onclick="printSilent('{{ route('packages.print', $cp) }}')" class="px-3 py-1.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-black text-[11px] uppercase tracking-wider rounded-lg transition-colors shadow-sm flex items-center gap-1.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Print Receipt
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 flex flex-col items-center justify-center text-center opacity-70 bg-white border border-slate-200 rounded-3xl border-dashed">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <p class="text-sm font-bold text-slate-500">No active packages found</p>
                <button x-data @click="$dispatch('open-issue-package-modal')" class="mt-2 text-[13px] font-black text-blue-600 hover:underline">Issue a new package</button>
            </div>
        @endforelse
    </div>

    <!-- Issue Package Modal -->
    <div x-data="{ open: false }" x-show="open" @open-issue-package-modal.window="open = true" x-cloak class="fixed inset-0 z-[60] overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
            <div x-show="open" x-transition.opacity class="fixed inset-0 transition-opacity" @click="open = false">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div x-show="open" x-transition class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-slate-100">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="font-black text-lg text-slate-900">Issue Package</h3>
                    <button @click="open = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                
                <form action="{{ route('packages.store') }}" method="POST" class="p-6">
                    @csrf
                    <div class="space-y-5">
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Select Customer <span class="text-red-500">*</span></label>
                            <select name="customer_id" required class="w-full bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl px-4 py-3 text-[14px] font-black text-slate-800 outline-none transition-all">
                                <option value="">-- Choose Customer --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Select Package <span class="text-red-500">*</span></label>
                            <div class="space-y-3 max-h-60 overflow-y-auto custom-scrollbar">
                                @forelse($packages as $p)
                                    <label class="flex items-start gap-3 p-3 border-2 border-slate-100 rounded-xl cursor-pointer hover:border-blue-200 hover:bg-blue-50/50 transition-colors has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/50 has-[:checked]:ring-4 has-[:checked]:ring-blue-500/10">
                                        <div class="flex items-center h-5 mt-1">
                                            <input type="radio" name="package_id" value="{{ $p->id }}" required class="w-4 h-4 text-blue-600 bg-white border-slate-300 focus:ring-blue-500">
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex justify-between items-center mb-0.5">
                                                <span class="font-black text-slate-900 text-sm">{{ $p->name }}</span>
                                                <span class="font-black text-blue-600 text-sm">Rs.{{ number_format($p->price, 0) }}</span>
                                            </div>
                                            <div class="text-[11px] font-bold text-slate-500">
                                                {{ $p->payment_type === 'prepaid' ? 'Pre-paid' : 'Post-paid' }}
                                            </div>
                                        </div>
                                    </label>
                                @empty
                                    <div class="text-sm font-bold text-slate-500 text-center py-4 bg-slate-50 rounded-xl">No packages available. Create one in Settings.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex gap-3">
                        <button type="button" @click="open = false" class="flex-1 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[14px] font-black rounded-xl transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="flex-1 py-3.5 bg-blue-600 hover:bg-blue-700 text-white text-[14px] font-black rounded-xl shadow-lg shadow-blue-500/30 transition-all transform hover:-translate-y-0.5">
                            Issue Package
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function printSilent(url) {
            let iframe = document.getElementById('print-iframe');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'print-iframe';
                iframe.style.display = 'none';
                document.body.appendChild(iframe);
            }
            iframe.src = url;
        }
    </script>
</x-app-layout>
