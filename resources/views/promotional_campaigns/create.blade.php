<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="ph-title text-indigo-600 flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    New Campaign
                </h2>
                <p class="text-[13px] text-slate-500 font-medium m-0">Create and dispatch a new promotional campaign</p>
            </div>
            
            <a href="{{ route('campaigns.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl font-bold text-sm hover:bg-slate-200 transition-colors">
                Cancel
            </a>
        </div>
    </x-slot>

    <div class="bg-white rounded-[20px] border border-slate-200/60 shadow-sm overflow-hidden max-w-4xl" x-data="{ audience: '{{ old('audience_type', 'all') }}' }">
        <form action="{{ route('campaigns.store') }}" method="POST" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">Campaign Details</h3>
                    
                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Campaign Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full text-sm font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-3 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 shadow-sm outline-none transition-all"
                            placeholder="e.g. October 2026 Sale">
                        @error('name') <span class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Communication Channel</label>
                        <select name="channel" required
                            class="w-full text-sm font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-3 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 shadow-sm outline-none transition-all">
                            <option value="">-- Select Channel --</option>
                            <option value="sms" {{ old('channel') == 'sms' ? 'selected' : '' }}>SMS</option>
                            <option value="email" {{ old('channel') == 'email' ? 'selected' : '' }}>Email</option>
                            <option value="whatsapp" {{ old('channel') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                        </select>
                        <span class="block mt-2 text-xs text-slate-500">The template is automatically chosen based on Audience (Birthday or General Promotion). Edit templates in the Templates menus.</span>
                        @error('channel') <span class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">Target Audience</h3>
                    
                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Select Audience</label>
                        <select name="audience_type" x-model="audience" required
                            class="w-full text-sm font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl p-3 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 shadow-sm outline-none transition-all">
                            <option value="all">All Active Customers</option>
                            <option value="birthday">Birthdays Today</option>
                            <option value="inactive">Inactive Customers (>3 Months)</option>
                            <option value="top_customers">Top Customers (By Spending/Orders)</option>
                            <option value="branch_specific">Branch Specific</option>
                        </select>
                        @error('audience_type') <span class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Branch Specific Options -->
                    <div x-show="audience === 'branch_specific'" style="display: none;" class="p-4 bg-indigo-50/50 rounded-xl border border-indigo-100">
                        <label class="block text-[11px] font-extrabold text-indigo-700 uppercase tracking-widest mb-1.5">Select Branch</label>
                        <select name="branch_id"
                            class="w-full text-sm font-bold text-slate-700 bg-white border border-indigo-200 rounded-xl p-3 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 shadow-sm outline-none transition-all">
                            <option value="">-- Select Branch --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        @error('branch_id') <span class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Top Customers Options -->
                    <div x-show="audience === 'top_customers'" style="display: none;" class="p-4 bg-amber-50/50 rounded-xl border border-amber-100 space-y-4">
                        <div>
                            <label class="block text-[11px] font-extrabold text-amber-700 uppercase tracking-widest mb-1.5">Minimum Spent (Rs.)</label>
                            <input type="number" name="min_spent" value="{{ old('min_spent') }}"
                                class="w-full text-sm font-bold text-slate-700 bg-white border border-amber-200 rounded-xl p-3 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 shadow-sm outline-none transition-all"
                                placeholder="e.g. 5000">
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-amber-700 uppercase tracking-widest mb-1.5">Minimum Orders</label>
                            <input type="number" name="min_orders" value="{{ old('min_orders') }}"
                                class="w-full text-sm font-bold text-slate-700 bg-white border border-amber-200 rounded-xl p-3 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 shadow-sm outline-none transition-all"
                                placeholder="e.g. 5">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-100 flex justify-end gap-3 items-center">
                <span class="text-xs font-bold text-slate-400">Clicking send will queue the messages in the background.</span>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 shadow-md hover:shadow-indigo-500/30 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    Dispatch Campaign
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
