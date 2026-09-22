<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Successful - {{ \App\Models\Setting::get('company_name', 'Laundry Zone') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">

<div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden text-center">
    <div class="bg-green-500 py-10">
        <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
            <span class="text-4xl">✅</span>
        </div>
        <h1 class="text-2xl font-bold text-white">Booking Received!</h1>
    </div>

    <div class="p-8">
        <p class="text-slate-600 mb-6">Thank you for choosing {{ \App\Models\Setting::get('company_name', 'Laundry Zone') }}. Your pickup request has been received and is pending confirmation.</p>
        
        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 mb-6">
            <div class="text-sm text-slate-500 mb-1">Booking Reference</div>
            <div class="text-2xl font-black text-slate-800">#{{ str_pad($appointment->id, 5, '0', STR_PAD_LEFT) }}</div>
        </div>

        <div class="space-y-3 mb-8 text-left text-sm">
            <div class="flex justify-between border-b border-slate-100 pb-2">
                <span class="text-slate-500">Scheduled Time</span>
                <span class="font-semibold text-slate-800">{{ $appointment->pickup_scheduled_at->format('d M Y, h:i A') }}</span>
            </div>
            <div class="flex justify-between pb-2">
                <span class="text-slate-500">Branch</span>
                <span class="font-semibold text-slate-800">{{ $appointment->branch->name }}</span>
            </div>
        </div>

        <p class="text-xs text-slate-400 mb-6 italic">We've sent a WhatsApp notification to your number. We will notify you again once a rider is assigned.</p>

        <a href="{{ url('/') }}" class="inline-block w-full bg-slate-800 text-white font-semibold py-3 px-4 rounded-lg hover:bg-slate-900 transition">
            Back to Home
        </a>
    </div>
</div>

</body>
</html>


