<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Payment Methods') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <form action="{{ route('settings.payment-methods.update') }}" method="POST">
                @csrf
                
                <!-- Payment Methods Toggles -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                    <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-lg font-black text-slate-800">Available Payment Methods</h3>
                        <p class="text-sm text-slate-500 font-medium">Enable or disable payment options for your customers.</p>
                    </div>
                    
                    <div class="divide-y divide-slate-100">
                        @foreach($methods as $method)
                            <div class="p-6 flex items-center justify-between hover:bg-slate-50/50 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0
                                        {{ $method->type === 'bank_transfer' ? 'bg-blue-50 text-blue-600' : '' }}
                                        {{ $method->type === 'card_transfer' ? 'bg-purple-50 text-purple-600' : '' }}
                                        {{ $method->type === 'cash_on_delivery' ? 'bg-green-50 text-green-600' : '' }}
                                    ">
                                        @if($method->type === 'bank_transfer')
                                            <i class="fa-solid fa-building-columns text-xl"></i>
                                        @elseif($method->type === 'card_transfer')
                                            <i class="fa-solid fa-credit-card text-xl"></i>
                                        @else
                                            <i class="fa-solid fa-money-bill-wave text-xl"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="font-black text-slate-800 flex items-center gap-2">
                                            {{ $method->name }}
                                            @if($method->type === 'card_transfer')
                                                <span class="text-[10px] font-bold uppercase tracking-wider bg-orange-100 text-orange-600 px-2 py-0.5 rounded-md">Gateway Integration Pending</span>
                                            @endif
                                        </h4>
                                        <p class="text-sm font-medium text-slate-500">
                                            @if($method->type === 'bank_transfer')
                                                Customers will see your bank details and upload a receipt.
                                            @elseif($method->type === 'card_transfer')
                                                Customers pay securely online via Credit/Debit card.
                                            @else
                                                Customers pay in cash when the order is delivered.
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="is_active[{{ $method->id }}]" value="1" class="sr-only peer" {{ $method->is_active ? 'checked' : '' }}>
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Bank Details Form -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                    <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-lg font-black text-slate-800">Bank Transfer Details</h3>
                        <p class="text-sm text-slate-500 font-medium">These details will be displayed to customers when they select Bank Transfer during checkout.</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Bank Name</label>
                                <input type="text" name="bank_name" value="{{ old('bank_name', $bankDetails['bank_name']) }}" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-blue-500 focus:border-blue-500 text-sm font-semibold" placeholder="e.g. Commercial Bank">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Branch Name</label>
                                <input type="text" name="bank_branch" value="{{ old('bank_branch', $bankDetails['bank_branch']) }}" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-blue-500 focus:border-blue-500 text-sm font-semibold" placeholder="e.g. Colombo 07">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Account Name</label>
                                <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $bankDetails['bank_account_name']) }}" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-blue-500 focus:border-blue-500 text-sm font-semibold" placeholder="e.g. RAPID Laundromat">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Account Number</label>
                                <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $bankDetails['bank_account_number']) }}" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-blue-500 focus:border-blue-500 text-sm font-semibold font-mono" placeholder="e.g. 1234567890">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-bold shadow-md shadow-blue-200 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Save Settings
                    </button>
                </div>
            </form>
            
        </div>
    </div>
</x-app-layout>
