<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="ph-title">Thermal Bill Layout (80mm)</h2>
                <p class="ph-sub">Manage your company's POS thermal roll print design and layout</p>
            </div>
            <div>
                <a href="{{ route('orders.print', \App\Models\Order::first()?->order_number ?? '1') }}" target="_blank"
                    class="px-4 py-2 bg-blue-50 text-blue-700 hover:bg-blue-100 font-bold text-xs rounded-xl inline-flex items-center gap-1.5 transition">
                    <span>🖨 Open Real Bill Print</span>
                </a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 shadow-sm mb-6 text-xs font-bold">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @php
        $cfg = $layout->custom_fields ?? [];
        $showLogo = $layout->show_logo;
        $logoSize = $cfg['logo_size'] ?? '50';
        $paperWidth = $cfg['paper_width'] ?? '300px';
        $companyNameSize = $cfg['company_name_size'] ?? '18px';
        $branchPhone = $cfg['branch_phone'] ?? '011-2345678';
        $branchAddress = $cfg['branch_address'] ?? 'No 45, High Level Road, Colombo';
        $showDueDate = $cfg['show_due_date'] ?? '1';
        $showDeliveryFee = $cfg['show_delivery_fee'] ?? '1';
        $showDiscount = $cfg['show_discount'] ?? '1';
        $badgeTextPaid = $cfg['badge_text_paid'] ?? '✓ PAID IN FULL';
        $badgeTextCredit = $cfg['badge_text_credit'] ?? '⏳ CREDIT';
        $badgeTextPickup = $cfg['badge_text_pickup'] ?? '🕒 PAY ON PICKUP';
        $reviewText = $cfg['review_text'] ?? 'If you are happy with our service, please leave us a Google Review!';
        $softwareBranding = $cfg['software_branding'] ?? 'Software by DSRZone';
        $billNotice = $cfg['bill_notice'] ?? '';
        $customExtraTexts = is_array($cfg['custom_extra_texts'] ?? null) ? $cfg['custom_extra_texts'] : [];
        $headerTitle = $layout->header_title ?: ($tenant->name ?? 'Laundry Zone');
        $headerSubtitle = $layout->header_subtitle ?: 'Main Branch & Express Care';
        $termsText = $layout->terms_and_conditions ?: 'Please bring this receipt for collection. Garments not claimed within 30 days will be disposed of.';
        $footerText = $layout->footer_text ?: ('Thank you for choosing ' . ($tenant->name ?? 'Laundry Zone') . '.');
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start" x-data="{
        headerTitle: {{ json_encode($headerTitle) }},
        headerSubtitle: {{ json_encode($headerSubtitle) }},
        branchPhone: {{ json_encode($branchPhone) }},
        branchAddress: {{ json_encode($branchAddress) }},
        fontFamily: {{ json_encode($layout->font_family) }},
        fontSize: {{ json_encode($layout->font_size) }},
        companyNameSize: {{ json_encode($companyNameSize) }},
        showLogo: {{ $showLogo ? 'true' : 'false' }},
        logoSize: {{ json_encode($logoSize) }},
        paperWidth: {{ json_encode($paperWidth) }},
        showDueDate: {{ $showDueDate == '1' ? 'true' : 'false' }},
        showDeliveryFee: {{ $showDeliveryFee == '1' ? 'true' : 'false' }},
        showDiscount: {{ $showDiscount == '1' ? 'true' : 'false' }},
        badgeTextPaid: {{ json_encode($badgeTextPaid) }},
        badgeTextCredit: {{ json_encode($badgeTextCredit) }},
        badgeTextPickup: {{ json_encode($badgeTextPickup) }},
        termsText: {{ json_encode($termsText) }},
        footerText: {{ json_encode($footerText) }},
        reviewText: {{ json_encode($reviewText) }},
        softwareBranding: {{ json_encode($softwareBranding) }},
        billNotice: {{ json_encode($billNotice) }},
        extraLines: {{ json_encode($customExtraTexts) }},
        addExtraLine() { this.extraLines.push({ text: 'Special Customer Note / Alert' }); this.syncExtraLines(); },
        removeExtraLine(idx) { this.extraLines.splice(idx, 1); this.syncExtraLines(); },
        syncExtraLines() {
            this.$nextTick(() => {
                const el = document.getElementById('custom_extra_texts_json');
                if (el) el.value = JSON.stringify(this.extraLines);
            });
        }
    }" x-init="syncExtraLines()">
        <!-- Controls Column -->
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/90 shadow-sm p-6 space-y-6">
            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-black text-slate-900">Thermal Bill Fields & Sizing</h3>
                    <p class="text-xs text-slate-400 font-medium">All text labels, custom alert notes, sizes, and badges are fully editable</p>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-blue-50 text-blue-700">Live Editor</span>
            </div>

            <form action="{{ route('tenant.layouts.update') }}" method="POST" class="space-y-4" @submit="syncExtraLines()">
                @csrf
                <input type="hidden" name="type" value="bill">
                <input type="hidden" id="custom_extra_texts_json" name="custom_fields[custom_extra_texts_json]" value="{{ json_encode($customExtraTexts) }}">

                @include('layouts.documents.partials.bill_controls_top')
                @include('layouts.documents.partials.bill_controls_bottom')

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                        Save All Layout Changes
                    </button>
                    <button type="button" onclick="if(confirm('Reset bill settings to defaults?')) document.getElementById('resetForm').submit();"
                        class="text-xs font-bold text-slate-400 hover:text-rose-600 transition">
                        Reset Default
                    </button>
                </div>
            </form>

            <form id="resetForm" action="{{ route('tenant.layouts.reset') }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="type" value="bill">
            </form>
        </div>

        <!-- Live Preview Column -->
        <div class="lg:col-span-6 bg-slate-100 rounded-2xl border border-slate-200/90 p-6 flex flex-col items-center">
            <div class="w-full flex items-center justify-between mb-3 text-xs">
                <span class="font-extrabold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live Thermal Bill Preview
                </span>
                <span class="font-mono text-slate-400 font-bold" x-text="paperWidth"></span>
            </div>

            @include('layouts.documents.partials.bill_preview')
        </div>
    </div>
</x-app-layout>
