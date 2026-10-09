<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Portal - #{{ $order->order_number }}</title>
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://www.payhere.lk/lib/payhere.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Inter', sans-serif;
            position: relative;
            z-index: 1;
        }

        .bubbles {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
            pointer-events: none;
        }

        .bubble {
            position: absolute;
            bottom: -100px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: rise infinite ease-in;
        }

        @keyframes rise {
            0% {
                bottom: -100px;
                transform: translateX(0);
                opacity: 0;
            }

            50% {
                opacity: 1;
            }

            100% {
                bottom: 1080px;
                transform: translateX(100px);
                opacity: 0;
            }
        }

        .bubble:nth-child(1) {
            width: 40px;
            height: 40px;
            left: 10%;
            animation-duration: 8s;
        }

        .bubble:nth-child(2) {
            width: 20px;
            height: 20px;
            left: 20%;
            animation-duration: 5s;
            animation-delay: 1s;
        }

        .bubble:nth-child(3) {
            width: 50px;
            height: 50px;
            left: 35%;
            animation-duration: 7s;
            animation-delay: 2s;
        }

        .bubble:nth-child(4) {
            width: 80px;
            height: 80px;
            left: 50%;
            animation-duration: 11s;
            animation-delay: 0s;
        }

        .bubble:nth-child(5) {
            width: 35px;
            height: 35px;
            left: 55%;
            animation-duration: 6s;
            animation-delay: 1s;
        }

        .bubble:nth-child(6) {
            width: 45px;
            height: 45px;
            left: 65%;
            animation-duration: 8s;
            animation-delay: 3s;
        }

        .bubble:nth-child(7) {
            width: 90px;
            height: 90px;
            left: 75%;
            animation-duration: 12s;
            animation-delay: 2s;
        }

        .bubble:nth-child(8) {
            width: 25px;
            height: 25px;
            left: 80%;
            animation-duration: 6s;
            animation-delay: 2s;
        }
    </style>
</head>

<body
    class="bg-gradient-to-br from-blue-700 via-blue-800 to-blue-900 min-h-screen text-slate-800 flex items-center justify-center p-4">
    <!-- Bubble Animation Background -->
    <div class="bubbles">
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
    </div>

    <div class="w-full max-w-sm mx-auto">
        <!-- Header / Logo -->
        <div class="text-center mb-4">
            @if($order->branch && $order->branch->logo_path)
                <img src="{{ asset($order->branch->logo_path) }}" alt="Logo"
                    class="mx-auto mb-2 object-contain bg-white p-2 rounded-xl shadow-md"
                    style="max-height: 50px; max-width: 80%;">
            @else
                <div
                    class="w-12 h-12 bg-white rounded-xl shadow-md flex items-center justify-center mx-auto mb-2 text-blue-600">
                    <i class="fa-solid fa-shirt text-xl"></i>
                </div>
            @endif
            <h1 class="text-lg font-black text-white tracking-tight drop-shadow-sm leading-tight">
                {{ \App\Models\Setting::get('company_name', 'Laundry Zone') }}</h1>
            <p class="text-blue-200/80 font-bold text-[9px] mt-0.5 tracking-[0.1em] uppercase">Secure Payment Gateway
            </p>
        </div>

        @if(session('success'))
            <div
                class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-6 py-4 rounded-2xl mb-6 shadow-sm flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-xl"></i>
                <span class="font-bold">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div
                class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-2xl mb-6 shadow-sm flex items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                <span class="font-bold">{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-2xl mb-6 shadow-sm">
                <ul class="list-disc list-inside font-bold text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($order->payment_status === 'paid' || $order->payment_approved)
            <!-- ALREADY PAID STATE -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden text-center p-8">
                <div
                    class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-check text-3xl"></i>
                </div>
                <h2 class="text-xl font-black text-slate-900 mb-1">Payment Successful</h2>
                <p class="text-slate-500 text-xs font-medium mb-6">Thank you! Your payment for Order
                    #{{ $order->order_number }} has been processed and approved.</p>

                <div class="bg-slate-50 rounded-xl p-4 text-left border border-slate-100">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-slate-500 text-xs font-bold">Total Paid</span>
                        <span class="text-sm font-black text-slate-900">Rs.
                            {{ number_format($order->paid_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 text-xs font-bold">Date</span>
                        <span
                            class="text-xs font-bold text-slate-900">{{ $order->updated_at->format('d M Y, h:i A') }}</span>
                    </div>
                </div>
            </div>
        @elseif($hasPendingPayment)
            <!-- PENDING VERIFICATION STATE -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden text-center p-8">
                <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-hourglass-half text-2xl animate-pulse"></i>
                </div>
                <h2 class="text-xl font-black text-slate-900 mb-1">Payment Under Review</h2>
                <p class="text-slate-500 text-xs font-medium mb-6">We have received your payment submission for Order
                    #{{ $order->order_number }}. Our team is verifying it and will approve it shortly.</p>
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <p class="text-xs font-bold text-slate-600">You will be notified once the payment is approved.</p>
                </div>
            </div>
        @else
            <!-- PAYMENT PENDING STATE -->
            <div class="bg-white rounded-xl shadow-2xl overflow-hidden mb-3"
                x-data="{ confirmedMethod: '', pendingMethod: '', showAmountModal: false, paymentAmount: '{{ $order->total - $order->paid_amount }}' }">

                <!-- Order Summary Header -->
                <div class="p-4 border-b border-slate-100 bg-white">
                    <div class="flex justify-between items-center mb-3">
                        <div>
                            <p class="text-slate-400 font-bold uppercase tracking-wider" style="font-size: 9px;">Order No.
                            </p>
                            <h2 class="text-sm font-black text-slate-800">#{{ $order->order_number }}</h2>
                        </div>
                        <div class="text-right">
                            <p class="text-slate-400 font-bold uppercase tracking-wider" style="font-size: 9px;">Status</p>
                            <span
                                class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-700">
                                Pending Payment
                            </span>
                        </div>
                    </div>

                    <div class="bg-blue-50/50 rounded-lg p-4 border border-blue-100/50 text-center">
                        <p class="text-blue-600/80 font-black text-[9px] mb-1 uppercase tracking-[0.1em]">Amount Due</p>
                        <p class="text-3xl font-black tracking-tighter text-blue-900 leading-none">
                            <span
                                class="text-xl text-blue-600/50 mr-1">Rs.</span>{{ number_format($order->total - $order->paid_amount, 2) }}
                        </p>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="p-4 bg-slate-50/50">
                    <h3 class="font-black text-[10px] text-slate-800 uppercase tracking-wider mb-2">Select Payment Method
                    </h3>

                    <div class="grid grid-cols-2 gap-2 mb-3">
                        @foreach($paymentMethods as $method)
                            <button @click="pendingMethod = '{{ $method->type }}'; showAmountModal = true;"
                                :class="confirmedMethod === '{{ $method->type }}' ? 'border-blue-600 bg-blue-50 ring-1 ring-blue-600' : 'border-slate-200 bg-white hover:border-blue-300 hover:bg-slate-50'"
                                class="flex flex-col items-center justify-center gap-1 p-2 rounded-lg border text-center transition-all relative overflow-hidden group">

                                <div
                                    class="w-8 h-8 rounded-md flex items-center justify-center shrink-0 
                                        {{ $method->type === 'bank_transfer' ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-600' }}">
                                    @if($method->type === 'bank_transfer')
                                        <i class="fa-solid fa-building-columns text-sm"></i>
                                    @else
                                        <i class="fa-solid fa-credit-card text-sm"></i>
                                    @endif
                                </div>

                                <div>
                                    <h4 class="font-black text-[11px] text-slate-800 leading-tight">{{ $method->name }}</h4>
                                    <p class="text-[9px] font-bold text-slate-500 mt-0.5 leading-tight">
                                        @if($method->type === 'bank_transfer')
                                            Upload slip
                                        @else
                                            Online gateway
                                        @endif
                                    </p>
                                </div>

                                <div x-show="confirmedMethod === '{{ $method->type }}'"
                                    class="absolute top-1.5 right-1.5 text-blue-600">
                                    <i class="fa-solid fa-circle-check text-xs"></i>
                                </div>
                            </button>
                        @endforeach
                    </div>

                    <!-- Action Area after Confirmation -->
                    <div x-show="confirmedMethod !== ''" x-collapse>
                        <!-- Bank Transfer Form (Only shows if confirmedMethod is bank_transfer) -->
                        <div x-show="confirmedMethod === 'bank_transfer'" x-cloak>
                            <div class="bg-white border border-slate-200 rounded-lg p-2.5 mb-2.5">
                                <h4 class="font-black text-[9px] text-slate-800 uppercase tracking-wider mb-1">Bank Details
                                </h4>
                                <div
                                    class="text-[10px] font-semibold text-slate-600 whitespace-pre-line bg-slate-50 p-2 rounded border border-slate-100 font-mono leading-tight">
                                    {{ $bankDetails }}</div>
                            </div>

                            <form action="{{ route('payment.link.submit', $order->payment_token) }}" method="POST"
                                enctype="multipart/form-data" class="space-y-2">
                                @csrf
                                <input type="hidden" name="amount" :value="paymentAmount">

                                <div>
                                    <label
                                        class="block text-[9px] font-black text-slate-700 mb-1 uppercase tracking-wider">Upload
                                        Slip <span class="text-red-500">*</span></label>
                                    <input type="file" name="receipt_image" accept="image/*" required
                                        class="block w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:font-bold file:bg-blue-50 file:text-blue-700 bg-white border border-slate-200 rounded cursor-pointer">
                                </div>

                                <button type="submit"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-lg py-2.5 font-black text-xs shadow-md transition-all flex items-center justify-center gap-2 mt-2">
                                    <i class="fa-solid fa-upload"></i> Submit Payment
                                </button>
                            </form>
                        </div>

                        <!-- Card Transfer Message -->
                        <div x-show="confirmedMethod === 'card_transfer'" x-cloak
                            class="text-center p-3 bg-blue-50 rounded-lg border border-blue-100">
                            <i class="fa-solid fa-circle-notch fa-spin text-blue-500 text-xl mb-2"></i>
                            <p class="text-[10px] font-bold text-blue-800">Redirecting to Secure Gateway...</p>
                        </div>
                    </div>
                </div>

                <!-- Editable Amount Modal -->
                <div x-show="showAmountModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showAmountModal = false"
                        x-transition.opacity></div>
                    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-[280px] flex flex-col overflow-hidden"
                        x-transition.scale.origin.bottom>
                        <div class="p-3 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                            <h3 class="text-[10px] font-black text-slate-800 uppercase tracking-wider">Confirm Amount</h3>
                            <button type="button" @click="showAmountModal = false"
                                class="text-slate-400 hover:text-slate-600">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div class="p-4">
                            <label class="block text-[10px] font-black text-slate-700 mb-2 uppercase tracking-wider">How
                                much are you paying?</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-slate-500 font-black text-xs">Rs.</span>
                                </div>
                                <input type="number" step="0.01" x-model="paymentAmount"
                                    class="w-full pl-8 pr-3 py-1.5 text-base bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-black text-slate-900 outline-none transition-all">
                            </div>
                        </div>
                        <div class="p-3 bg-slate-50 border-t border-slate-100 flex gap-2">
                            <button type="button" @click="showAmountModal = false; pendingMethod = '';"
                                class="flex-1 py-2 text-[9px] font-black text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors uppercase tracking-wider">Cancel</button>
                            <button type="button" @click="
                                    if(paymentAmount <= 0) return;
                                    confirmedMethod = pendingMethod;
                                    showAmountModal = false;
                                    if(confirmedMethod === 'card_transfer') {
                                        startPayHere(paymentAmount);
                                    }
                                "
                                class="flex-1 py-2 text-[9px] font-black text-white bg-emerald-500 rounded-lg hover:bg-emerald-600 shadow-md transition-all uppercase tracking-wider">
                                Confirm
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // Function to start PayHere with the custom amount
                function startPayHere(amount) {
                    payhere.onCompleted = function onCompleted(orderId) {
                        alert("Payment successful! The system will process your payment momentarily.");
                        window.location.reload();
                    };
                    payhere.onDismissed = function onDismissed() {
                        console.log("Payment dismissed");
                    };
                    payhere.onError = function onError(error) {
                        alert("Error:" + error);
                    };

                    var payment = {
                        "sandbox": true,
                        "merchant_id": "{{ $merchantId }}",
                        "return_url": "{{ route('payment.link.show', $order->payment_token) }}",
                        "cancel_url": "{{ route('payment.link.show', $order->payment_token) }}",
                        "notify_url": "{{ route('payment.link.webhook', $order->payment_token) }}",
                        "order_id": "{{ $order->id }}",
                        "items": "Laundry Services Order #{{ $order->order_number }}",
                        "amount": amount,
                        "currency": "LKR",
                        "first_name": "{{ $order->customer->name ?? 'Guest' }}",
                        "last_name": "",
                        "email": "{{ $order->customer->email ?? 'no-email@laundry.com' }}",
                        "phone": "{{ $order->customer->phone ?? '0000000000' }}",
                        "address": "{{ $order->delivery_address ?? 'Pickup' }}",
                        "city": "Colombo",
                        "country": "Sri Lanka"
                    };

                    payhere.startPayment(payment);
                }
            </script>
        @endif

        <div class="text-center mt-4 mb-2">
            <p class="text-[9px] font-bold text-blue-300 uppercase tracking-[0.1em]">Powered by
                {{ \App\Models\Setting::get('company_name', 'Laundry Zone') }} Management System</p>
        </div>
    </div>
</body>

</html>