@extends('super_admin.layouts.app', ['title' => 'Document & Print Layouts'])

@section('content')
<div class="space-y-6" x-data="{
    activeTab: '{{ $currentType }}',
    companyId: '{{ $selectedTenant->id ?? '' }}',
    fontFamily: '{{ $layout->font_family }}',
    fontSize: '{{ $layout->font_size }}',
    primaryColor: '{{ $layout->primary_color }}',
    accentColor: '{{ $layout->accent_color }}',
    headerTitle: '{{ addslashes($layout->header_title) }}',
    headerSubtitle: '{{ addslashes($layout->header_subtitle) }}',
    termsText: `{{ addslashes($layout->terms_and_conditions) }}`,
    footerText: '{{ addslashes($layout->footer_text) }}',
    showLogo: {{ $layout->show_logo ? 'true' : 'false' }},
    showQrCode: {{ $layout->show_qr_code ? 'true' : 'false' }},
    showBarcode: {{ $layout->show_barcode ? 'true' : 'false' }},
    showBranchInfo: {{ $layout->show_branch_info ? 'true' : 'false' }},
    showCustomerInfo: {{ $layout->show_customer_info ? 'true' : 'false' }},
    showFooter: {{ $layout->show_footer ? 'true' : 'false' }}
}">
    <!-- Signature Title Section -->
    @component('super_admin.layouts.partials.title-section', [
        'title' => 'Document & Print Layout Customizer',
        'subtitle' => 'Configure customized print formats, colors, fonts, and live visual preview for each tenant company'
    ])
        <div class="flex items-center gap-2">
            <form action="{{ route('super_admin.layouts.reset') }}" method="POST" onsubmit="return confirm('Reset this layout to factory default?');">
                @csrf
                <input type="hidden" name="tenant_id" :value="companyId">
                <input type="hidden" name="type" :value="activeTab">
                <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors h-9 sm:h-10">
                    Reset to Default
                </button>
            </form>
        </div>
    @endcomponent

    <!-- Top Controls: Select Company & Document Type -->
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-sm p-4 sm:p-5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex-1 max-w-md">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Select Tenant Company</label>
                <form id="companyFilterForm" method="GET" action="{{ route('super_admin.layouts.index') }}">
                    <input type="hidden" name="type" :value="activeTab">
                    <select name="tenant_id" onchange="this.form.submit()" class="w-full text-xs font-bold text-slate-800 rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 focus:ring-blue-500">
                        @foreach($tenants as $t)
                            <option value="{{ $t->id }}" {{ $selectedTenant && $selectedTenant->id == $t->id ? 'selected' : '' }}>
                                {{ $t->name }} ({{ $t->subdomain }}.{{ config('tenancy.central_domain', 'localhost') }})
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Document Layout Type</label>
                <div class="flex items-center gap-2">
                    <a href="{{ route('super_admin.layouts.index', ['tenant_id' => $selectedTenant?->id, 'type' => 'bill']) }}"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $currentType === 'bill' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        🧾 Thermal Bill (80mm)
                    </a>
                    <a href="{{ route('super_admin.layouts.index', ['tenant_id' => $selectedTenant?->id, 'type' => 'receipt']) }}"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $currentType === 'receipt' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        💳 Payment Receipt
                    </a>
                    <a href="{{ route('super_admin.layouts.index', ['tenant_id' => $selectedTenant?->id, 'type' => 'invoice']) }}"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $currentType === 'invoice' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        📄 Invoice PDF (A4)
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Controls Column -->
        <div class="lg:col-span-5 bg-white rounded-xl border border-slate-200/90 shadow-sm p-6 space-y-6">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="text-sm font-black text-slate-900">Customize {{ ucfirst($currentType) }} Design</h3>
                <p class="text-xs text-slate-400 font-medium">Real-time settings for {{ $selectedTenant->name ?? 'Company' }}</p>
            </div>

            <form action="{{ route('super_admin.layouts.update') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="tenant_id" :value="companyId">
                <input type="hidden" name="type" :value="activeTab">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Font Family</label>
                        <select name="font_family" x-model="fontFamily" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-slate-50">
                            <option value="Inter">Inter (Clean Modern)</option>
                            <option value="'Plus Jakarta Sans', sans-serif">Plus Jakarta Sans</option>
                            <option value="'Courier New', monospace">Courier (Thermal Classic)</option>
                            <option value="Roboto, sans-serif">Roboto</option>
                            <option value="'Segoe UI', sans-serif">Segoe UI</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Base Font Size</label>
                        <select name="font_size" x-model="fontSize" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-slate-50">
                            <option value="11px">Compact (11px)</option>
                            <option value="12px">Standard (12px)</option>
                            <option value="13px">Medium (13px)</option>
                            <option value="14px">Large (14px)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Primary Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="primary_color" x-model="primaryColor" class="h-9 w-9 rounded-lg border-0 cursor-pointer p-0.5">
                            <input type="text" x-model="primaryColor" class="flex-1 text-xs font-mono font-bold rounded-xl border-slate-200">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Accent / Highlight</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="accent_color" x-model="accentColor" class="h-9 w-9 rounded-lg border-0 cursor-pointer p-0.5">
                            <input type="text" x-model="accentColor" class="flex-1 text-xs font-mono font-bold rounded-xl border-slate-200">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">Sections & Components</label>
                    <div class="grid grid-cols-2 gap-2 text-xs font-semibold text-slate-700">
                        <label class="flex items-center gap-2 cursor-pointer bg-slate-50 p-2 rounded-lg border border-slate-200/60">
                            <input type="checkbox" name="show_logo" value="1" x-model="showLogo" class="rounded text-blue-600 focus:ring-blue-500">
                            <span>Company Logo</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer bg-slate-50 p-2 rounded-lg border border-slate-200/60">
                            <input type="checkbox" name="show_branch_info" value="1" x-model="showBranchInfo" class="rounded text-blue-600 focus:ring-blue-500">
                            <span>Branch & Contact</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer bg-slate-50 p-2 rounded-lg border border-slate-200/60">
                            <input type="checkbox" name="show_customer_info" value="1" x-model="showCustomerInfo" class="rounded text-blue-600 focus:ring-blue-500">
                            <span>Customer Info</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer bg-slate-50 p-2 rounded-lg border border-slate-200/60">
                            <input type="checkbox" name="show_qr_code" value="1" x-model="showQrCode" class="rounded text-blue-600 focus:ring-blue-500">
                            <span>QR Order Tracker</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer bg-slate-50 p-2 rounded-lg border border-slate-200/60">
                            <input type="checkbox" name="show_barcode" value="1" x-model="showBarcode" class="rounded text-blue-600 focus:ring-blue-500">
                            <span>Order Barcode</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer bg-slate-50 p-2 rounded-lg border border-slate-200/60">
                            <input type="checkbox" name="show_footer" value="1" x-model="showFooter" class="rounded text-blue-600 focus:ring-blue-500">
                            <span>Footer Notes</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Header Title</label>
                        <input type="text" name="header_title" x-model="headerTitle" class="w-full text-xs font-bold rounded-xl border-slate-200">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Subtitle / Tagline</label>
                        <input type="text" name="header_subtitle" x-model="headerSubtitle" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Terms & Conditions / Policy Note</label>
                    <textarea name="terms_and_conditions" x-model="termsText" rows="3" class="w-full text-xs rounded-xl border-slate-200"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Footer Message</label>
                    <input type="text" name="footer_text" x-model="footerText" class="w-full text-xs rounded-xl border-slate-200">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="submit" class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition-colors">
                        Save & Apply Changes to Company
                    </button>
                </div>
            </form>
        </div>

        @include('super_admin.layouts.partials.preview_column')

    </div>
</div>
@endsection
