<x-customer-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('customer.orders.show', $order->order_number) }}" class="text-slate-400 hover:text-blue-600 transition p-2 bg-white rounded-lg shadow-sm border border-slate-100"><i class="fa-solid fa-arrow-left"></i></a>
            <div>
                <h2 class="font-black text-2xl text-slate-800 leading-tight">Complete Payment</h2>
                <p class="text-sm font-bold text-slate-500">Order #{{ $order->order_number }} &middot; Amount Due: <span class="text-blue-600">LKR {{ number_format($order->total, 2) }}</span></p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto" x-data="paymentForm()">
        
        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-100 text-red-600 p-4 rounded-xl">
                <ul class="list-disc list-inside text-sm font-bold">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('customer.orders.submitPayment', $order->order_number) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            <!-- Step 1: Select Payment Method -->
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 lg:p-8">
                <h3 class="font-black text-slate-800 mb-6 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm">1</span>
                    Select Payment Method
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($paymentMethods as $method)
                        <label class="relative cursor-pointer">
                            <input type="radio" name="payment_method" value="{{ $method->name }}" x-model="selectedMethod" class="peer sr-only" required>
                            <div class="p-5 rounded-xl border-2 transition-all duration-200 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-hover:border-blue-300 bg-white">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="text-2xl text-blue-600">
                                        @if($method->type === 'card_transfer')
                                            <i class="fa-brands fa-cc-visa"></i> <i class="fa-brands fa-cc-mastercard ml-1"></i>
                                        @elseif($method->type === 'bank_transfer')
                                            <i class="fa-solid fa-building-columns"></i>
                                        @else
                                            <i class="fa-solid fa-money-bill-wave"></i>
                                        @endif
                                    </div>
                                    <div class="w-5 h-5 rounded-full border-2 border-slate-300 peer-checked:border-blue-600 flex items-center justify-center">
                                        <div class="w-2.5 h-2.5 rounded-full bg-blue-600 opacity-0 transition-opacity" :class="selectedMethod === '{{ $method->name }}' ? 'opacity-100' : ''"></div>
                                    </div>
                                </div>
                                <h4 class="font-black text-slate-800">{{ $method->name }}</h4>
                                @if($method->type === 'bank_transfer')
                                    <p class="text-xs font-bold text-slate-500 mt-1">Upload your transfer receipt</p>
                                @elseif($method->type === 'card_transfer')
                                    <p class="text-xs font-bold text-slate-500 mt-1">Pay securely via Credit/Debit card</p>
                                @else
                                    <p class="text-xs font-bold text-slate-500 mt-1">Pay when order is delivered</p>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Step 2: Bank Transfer Details (Dynamic) -->
            <div x-show="isBankTransfer()" x-transition.opacity style="display: none;" class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 lg:p-8">
                <h3 class="font-black text-slate-800 mb-6 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm">2</span>
                    Bank Transfer Details
                </h3>
                
                <div class="bg-slate-50 rounded-xl p-5 mb-6">
                    <p class="text-sm font-bold text-slate-600 mb-2">Please transfer <strong class="text-blue-600">LKR {{ number_format($order->total, 2) }}</strong> to the following account:</p>
                    <div class="grid grid-cols-2 gap-y-2 text-sm">
                        <div class="text-slate-500">Bank Name:</div><div class="font-bold text-slate-800">{{ \App\Models\Setting::get('bank_name', 'Commercial Bank') }}</div>
                        <div class="text-slate-500">Account Name:</div><div class="font-bold text-slate-800">{{ \App\Models\Setting::get('bank_account_name', '365 Laundry') }}</div>
                        <div class="text-slate-500">Account Number:</div><div class="font-bold text-slate-800">{{ \App\Models\Setting::get('bank_account_number', '1234567890') }}</div>
                        <div class="text-slate-500">Branch:</div><div class="font-bold text-slate-800">{{ \App\Models\Setting::get('bank_branch', 'Colombo 07') }}</div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Upload Payment Receipt</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-200 border-dashed rounded-xl hover:border-blue-400 transition-colors bg-slate-50/50 cursor-pointer" @click="$refs.receipt_input.click()">
                        <div class="space-y-1 text-center">
                            <i class="fa-solid fa-cloud-arrow-up text-3xl text-slate-400 mb-3"></i>
                            <div class="flex text-sm text-slate-600 justify-center">
                                <span class="relative cursor-pointer bg-white rounded-md font-bold text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500 px-1">
                                    <span>Upload a file</span>
                                    <input id="receipt_image" name="receipt_image" type="file" class="sr-only" x-ref="receipt_input" @change="fileName = $event.target.files[0].name" accept="image/jpeg,image/png,image/jpg">
                                </span>
                                <p class="pl-1 font-medium">or drag and drop</p>
                            </div>
                            <p class="text-xs font-bold text-slate-500">PNG, JPG, JPEG up to 5MB</p>
                            <p x-show="fileName" x-text="fileName" class="text-sm font-bold text-green-600 mt-2 bg-green-50 inline-block px-3 py-1 rounded-full"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Simulated Card Payment (Dynamic) -->
            <div x-show="isCardPayment()" x-transition.opacity style="display: none;" class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 lg:p-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-black text-slate-800 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm">2</span>
                        Card Details
                    </h3>
                    <div class="flex gap-2 text-2xl text-slate-300">
                        <i class="fa-brands fa-cc-visa text-blue-600"></i>
                        <i class="fa-brands fa-cc-mastercard text-red-500"></i>
                        <i class="fa-brands fa-cc-amex text-blue-400"></i>
                    </div>
                </div>
                
                <div class="space-y-4 max-w-md">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Cardholder Name</label>
                        <input type="text" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-bold placeholder-slate-400 transition-all" placeholder="JOHN DOE" :required="isCardPayment()">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Card Number</label>
                        <div class="relative">
                            <input type="text" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-bold placeholder-slate-400 transition-all font-mono tracking-widest" placeholder="0000 0000 0000 0000" maxlength="19" :required="isCardPayment()">
                            <i class="fa-solid fa-credit-card absolute right-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Expiry Date</label>
                            <input type="text" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-bold placeholder-slate-400 transition-all font-mono text-center" placeholder="MM/YY" maxlength="5" :required="isCardPayment()">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">CVC/CVV</label>
                            <input type="password" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-bold placeholder-slate-400 transition-all font-mono text-center tracking-widest" placeholder="•••" maxlength="4" :required="isCardPayment()">
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 flex gap-3 mt-2">
                        <i class="fa-solid fa-shield-halved text-blue-500 text-lg mt-0.5"></i>
                        <p class="text-xs font-bold text-blue-700 leading-tight">This is a secure 256-bit SSL encrypted payment. Your card details are never stored on our servers.</p>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-right border-t border-slate-200 pt-6">
                <button type="submit" class="bg-slate-800 text-white px-8 py-3.5 rounded-xl font-black text-lg hover:bg-slate-900 hover:shadow-xl hover:shadow-slate-200 transition-all inline-flex items-center gap-2" :class="!selectedMethod ? 'opacity-50 cursor-not-allowed' : ''" :disabled="!selectedMethod">
                    <span x-text="selectedMethod === 'Bank Transfer' ? 'Submit Receipt' : 'Pay LKR {{ number_format($order->total, 2) }}'"></span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('paymentForm', () => ({
                selectedMethod: '',
                fileName: '',
                isBankTransfer() {
                    return this.selectedMethod === 'Bank Transfer';
                },
                isCardPayment() {
                    return this.selectedMethod === 'Card';
                }
            }))
        })
    </script>
</x-customer-layout>


