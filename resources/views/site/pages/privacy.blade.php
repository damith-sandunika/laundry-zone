@extends('layouts.site')

@section('content')
<div class="bg-blue-50 py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl lg:text-4xl font-black text-brand mb-4">Privacy Policy</h1>
        <p class="text-slate-600">Last updated: {{ date('F d, Y') }}</p>
    </div>
</div>

<div class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="prose prose-slate prose-blue max-w-none">
            
            <p class="text-slate-600 mb-8 leading-relaxed text-lg">
                At 365laundry.lk, we are committed to protecting your privacy and ensuring the security of your personal information. This Privacy Policy outlines how we collect, use, and safeguard your data.
            </p>

            <h2 class="text-2xl font-bold text-slate-800 mb-4 mt-8">1. Information We Collect</h2>
            <p class="text-slate-600 mb-4 leading-relaxed">
                When you use our services, we may collect the following information:
            </p>
            <ul class="list-disc pl-6 text-slate-600 mb-6 space-y-2">
                <li><strong>Personal Details:</strong> Name, phone number, and email address provided during registration.</li>
                <li><strong>Location Data:</strong> Pickup and delivery addresses, including GPS coordinates pinned via our map interface.</li>
                <li><strong>Order History:</strong> Details of your laundry orders, preferences, and special instructions.</li>
            </ul>

            <h2 class="text-2xl font-bold text-slate-800 mb-4 mt-8">2. How We Use Your Information</h2>
            <p class="text-slate-600 mb-4 leading-relaxed">
                The information we collect is used solely for providing and improving our services:
            </p>
            <ul class="list-disc pl-6 text-slate-600 mb-6 space-y-2">
                <li>To facilitate laundry pickups and deliveries accurately.</li>
                <li>To communicate with you regarding your order status, billing, and customer support via SMS, WhatsApp, or email.</li>
                <li>To personalize your experience and remember your preferences.</li>
                <li>To improve our website, application, and overall service quality.</li>
            </ul>

            <h2 class="text-2xl font-bold text-slate-800 mb-4 mt-8">3. Data Protection and Security</h2>
            <p class="text-slate-600 mb-6 leading-relaxed">
                We implement a variety of security measures to maintain the safety of your personal information. Your data is stored on secure servers and is only accessible by authorized personnel who are required to keep the information confidential. We do not sell, trade, or otherwise transfer your personally identifiable information to outside parties.
            </p>

            <h2 class="text-2xl font-bold text-slate-800 mb-4 mt-8">4. Third-Party Services</h2>
            <p class="text-slate-600 mb-6 leading-relaxed">
                We may use third-party service providers to assist us in operating our business (e.g., mapping services like Leaflet/OpenStreetMap). These third parties have access to your personal information only to perform specific tasks on our behalf and are obligated not to disclose or use it for any other purpose.
            </p>

            <h2 class="text-2xl font-bold text-slate-800 mb-4 mt-8">5. Your Rights</h2>
            <p class="text-slate-600 mb-6 leading-relaxed">
                You have the right to access, update, or delete your personal information at any time. You can do this by logging into your account dashboard or by contacting our support team. 
            </p>

            <div class="mt-12 p-6 bg-slate-50 rounded-xl border border-slate-200">
                <h3 class="text-lg font-bold text-slate-800 mb-2">Contact Us</h3>
                <p class="text-slate-600">
                    If you have any questions about this Privacy Policy, please contact us at:<br>
                    <strong>Email:</strong> privacy@365laundry.lk<br>
                    <strong>Phone:</strong> +94 77 123 4567
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
