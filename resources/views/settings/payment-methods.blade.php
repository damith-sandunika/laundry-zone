<x-app-layout>
    <x-slot name="header">
        <div class="ph-title">💳 Payment Methods</div>
        <div class="ph-sub">Manage available payment options for your branch</div>
    </x-slot>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 max-w-4xl">
        <!-- Branch Selector -->
        <form method="GET" class="mb-6 flex gap-4 items-center bg-slate-50 p-4 rounded-xl border border-slate-200">
            <label class="text-sm font-bold text-slate-700">Select Branch:</label>
            <select name="branch_id" onchange="this.form.submit()" class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm font-semibold text-slate-700 bg-white min-w-[200px]">
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
        </form>

        <form action="{{ route('settings.payment-methods.update') }}" method="POST">
            @csrf
            <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
            
            <div class="space-y-6">
                @foreach($allMethods as $key => $defaultName)
                @php
                    $setting = $settings[$key] ?? null;
                    $isEnabled = $setting ? $setting->is_enabled : true; // Default to true as per request
                    $displayName = $setting ? $setting->display_name : $defaultName;
                    $instructions = $setting ? $setting->instructions : '';
                @endphp
                <div class="p-5 border border-slate-200 rounded-xl bg-slate-50 flex flex-col md:flex-row gap-6">
                    
                    <div class="md:w-1/4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" name="methods[{{ $key }}][is_enabled]" value="1" class="sr-only" {{ $isEnabled ? 'checked' : '' }} onchange="this.nextElementSibling.classList.toggle('bg-blue-600'); this.nextElementSibling.classList.toggle('bg-slate-300'); this.nextElementSibling.firstElementChild.classList.toggle('translate-x-5');">
                                <div class="block w-11 h-6 rounded-full transition-colors {{ $isEnabled ? 'bg-blue-600' : 'bg-slate-300' }}">
                                    <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition transform {{ $isEnabled ? 'translate-x-5' : '' }}"></div>
                                </div>
                            </div>
                            <span class="font-bold text-slate-800 uppercase tracking-wide text-sm">{{ $key }}</span>
                        </label>
                    </div>

                    <div class="md:w-3/4 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Display Name</label>
                            <input type="text" name="methods[{{ $key }}][display_name]" value="{{ $displayName }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Instructions (Optional)</label>
                            <textarea name="methods[{{ $key }}][instructions]" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="E.g. Bank account details for transfer">{{ $instructions }}</textarea>
                            <div class="text-xs text-slate-400 mt-1">Displayed to customer when they select this method.</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                    Save Payment Settings
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
