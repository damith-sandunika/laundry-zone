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
