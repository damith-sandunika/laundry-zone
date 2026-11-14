@php
    $cfg = $layout->custom_fields ?? [];
    $showLogo = $layout->show_logo;
    $logoSize = $cfg['logo_size'] ?? '60';
    $companyNameSize = $cfg['company_name_size'] ?? '24px';
    $invoiceTitle = $cfg['invoice_title'] ?? 'INVOICE';
    $invoiceTitleSize = $cfg['invoice_title_size'] ?? '28px';
    $primaryColor = $layout->primary_color ?: '#0f172a';
    $accentColor = $layout->accent_color ?: '#3b82f6';
    $contactEmail = $cfg['contact_email'] ?? 'contact@company.com';
    $contactPhone = $cfg['contact_phone'] ?? '+94 11 234 5678';
    $contactAddress = $cfg['contact_address'] ?? 'Colombo, Sri Lanka';
    $badgeTextPaid = $cfg['badge_text_paid'] ?? 'FULLY PAID';
    $badgeTextPending = $cfg['badge_text_pending'] ?? 'PAYMENT PENDING';
    $showPayOnlineBtn = $cfg['show_pay_online_btn'] ?? '1';
    $payBtnText = $cfg['pay_btn_text'] ?? 'PAY NOW - CLICK HERE';
    $footerNote = $cfg['footer_note'] ?? 'Please visit our website for full digital terms & conditions.';

    $headerTitle = $layout->header_title ?: ($tenant->name ?? 'Laundry Zone');
    $headerSubtitle = $layout->header_subtitle ?: 'Express Laundry & Premium Dry Cleaning';
    $termsText = $layout->terms_and_conditions ?: 'All dry cleaning and laundry services subject to company terms. Payment due upon order collection.';
    $footerText = $layout->footer_text ?: 'Thank you for your business! 🤍';
@endphp

