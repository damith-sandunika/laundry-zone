<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account On Hold - {{ $tenant->name ?? 'Laundry Portal' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased min-h-screen flex items-center justify-center p-4 sm:p-6">
    <div class="max-w-lg w-full text-center space-y-6 bg-slate-900/80 border border-slate-800 rounded-3xl p-6 sm:p-8 backdrop-blur-xl shadow-2xl shadow-rose-950/20">
        <div class="w-20 h-20 rounded-3xl bg-rose-500/10 border border-rose-500/20 text-rose-400 mx-auto flex items-center justify-center shadow-xl shadow-rose-500/10">
            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>

        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">{{ $tenant->name }}</h1>
            <div class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-black uppercase tracking-wider">
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                Account On Hold / Suspended
            </div>
            
            @if(isset($outstandingAmount) && $outstandingAmount > 0)
                <div class="mt-5 p-4 rounded-2xl bg-rose-500/5 border border-rose-500/20 text-left">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Outstanding Payment Due</span>
                        <span class="text-lg font-black text-rose-400 font-mono">Rs. {{ number_format($outstandingAmount, 0) }}</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-2">
                        Your monthly subscription payment has not been settled. Please settle this outstanding payment to reactivate full system access immediately.
                    </p>
                    @if(isset($unpaidInvoices) && $unpaidInvoices->count())
                        <div class="mt-3 pt-3 border-t border-rose-500/10 space-y-1.5">
                            @foreach($unpaidInvoices as $inv)
                                <div class="flex justify-between text-[11px] text-slate-300">
                                    <span>Invoice #{{ $inv->invoice_number }} ({{ $inv->billing_period_start ? $inv->billing_period_start->format('F Y') : 'Monthly' }})</span>
                                    <span class="font-bold text-rose-300">Rs. {{ number_format($inv->total_amount, 0) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <p class="text-sm text-slate-400 mt-3 leading-relaxed">
                    Access to this company portal has been temporarily paused by SaaS Administration. Please contact administration to resume operations.
                </p>
            @endif
        </div>

        <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 text-xs text-slate-400 space-y-1.5">
            <div>Support Desk: <strong class="text-white">{{ \App\Models\Central\PlatformSetting::get('contact_email', 'support@laundryzone.dsrzone.com') }}</strong></div>
            <div>Hotline / WhatsApp: <strong class="text-white">{{ \App\Models\Central\PlatformSetting::get('contact_phone', '+94 11 234 5678') }}</strong></div>
        </div>
    </div>
</body>
</html>
