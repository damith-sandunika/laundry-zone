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
            <!-- Company Selector -->
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

            <!-- Document Type Tabs -->
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

    <!-- Main Workspace: Left Controls Form / Right Live Preview -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- ═════════ LEFT: CUSTOMIZATION CONTROLS ═════════ -->
        <div class="lg:col-span-5 bg-white rounded-xl border border-slate-200/90 shadow-sm p-6 space-y-6">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="text-sm font-black text-slate-900">Customize {{ ucfirst($currentType) }} Design</h3>
                <p class="text-xs text-slate-400 font-medium">Real-time settings for {{ $selectedTenant->name ?? 'Company' }}</p>
            </div>

            <form action="{{ route('super_admin.layouts.update') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="tenant_id" :value="companyId">
                <input type="hidden" name="type" :value="activeTab">

                <!-- Typography -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Font Family</label>
                        <select name="font_family" x-model="fontFamily" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-slate-50">
                            <option value="Inter">Inter (Clean Modern)</option>
                            <option value="'Plus Jakarta Sans', sans-serif">Plus Jakarta Sans (Saman ERP)</option>
                            <option value="'Courier New', monospace">Courier (Thermal Classic)</option>
                            <option value="Roboto, sans-serif">Roboto (Google Standard)</option>
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

                <!-- Brand Colors -->
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

                <!-- Section Toggles -->
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

                <!-- Custom Header Titles -->
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

                <!-- Terms and Conditions -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Terms & Conditions / Policy Note</label>
                    <textarea name="terms_and_conditions" x-model="termsText" rows="3" class="w-full text-xs rounded-xl border-slate-200"></textarea>
                </div>

                <!-- Footer Text -->
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

        <!-- ═════════ RIGHT: LIVE VISUAL PREVIEW ═════════ -->
        <div class="lg:col-span-7 bg-slate-100 rounded-xl border border-slate-200/90 p-6 flex flex-col items-center justify-start min-h-[600px] overflow-x-auto">
            <div class="w-full flex items-center justify-between mb-4">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live Document Simulation
                </span>
                <span class="text-xs font-mono font-bold text-slate-400">
                    {{ $currentType === 'invoice' ? 'A4 Paper (210mm)' : '80mm Thermal Roll' }}
                </span>
            </div>

            <!-- ── PREVIEW 1: THERMAL BILL (80mm) ── -->
            <template x-if="activeTab === 'bill'">
                <div class="bg-white shadow-xl rounded-sm p-4 text-slate-900 border border-slate-300 w-[300px] transition-all"
                    :style="`font-family: ${fontFamily}; font-size: ${fontSize}; color: ${primaryColor};`">
                    
                    <div class="text-center pb-2 border-b border-dashed border-slate-400">
                        <template x-if="showLogo">
                            <div class="w-10 h-10 mx-auto mb-1 rounded bg-slate-100 flex items-center justify-center p-1">
                                <img src="{{ $selectedTenant->logo_path ? asset($selectedTenant->logo_path) : asset('images/laundry_main_logo.png') }}" class="max-h-full max-w-full object-contain">
                            </div>
                        </template>
                        <h4 class="font-black text-sm uppercase" x-text="'{{ $selectedTenant->name ?? 'Laundry Company' }}'"></h4>
                        <template x-if="showBranchInfo">
                            <p class="text-[10px] text-slate-600 mt-0.5">124 Galle Road, Colombo &bull; 011-2345678</p>
                        </template>
                        <div class="mt-1 px-2 py-0.5 inline-block text-[10px] font-black tracking-widest text-white uppercase rounded"
                            :style="`background-color: ${accentColor};`" x-text="headerTitle"></div>
                    </div>

                    <div class="py-2 text-[10px] space-y-0.5 border-b border-dashed border-slate-400">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Bill No:</span>
                            <span class="font-bold">#ORD-98421</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Date:</span>
                            <span>{{ date('Y-m-d h:i A') }}</span>
                        </div>
                        <template x-if="showCustomerInfo">
                            <div class="flex justify-between font-bold">
                                <span>Customer:</span>
                                <span>Kamal Perera (0771234567)</span>
                            </div>
                        </template>
                    </div>

                    <table class="w-full text-left my-2 text-[10px]">
                        <thead>
                            <tr class="border-b border-slate-400 text-slate-600 font-bold">
                                <th class="pb-1">Item</th>
                                <th class="pb-1 text-center">Qty</th>
                                <th class="pb-1 text-right">Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr>
                                <td class="py-1">Formal Shirt (Wash & Iron)</td>
                                <td class="py-1 text-center font-bold">3</td>
                                <td class="py-1 text-right font-bold">Rs. 1,050.00</td>
                            </tr>
                            <tr>
                                <td class="py-1">Trousers (Dry Clean)</td>
                                <td class="py-1 text-center font-bold">2</td>
                                <td class="py-1 text-right font-bold">Rs. 1,200.00</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="pt-2 border-t-2 border-slate-800 space-y-1 text-[11px]">
                        <div class="flex justify-between font-black text-sm">
                            <span>TOTAL DUE:</span>
                            <span :style="`color: ${accentColor};`">Rs. 2,250.00</span>
                        </div>
                        <div class="flex justify-between text-[10px]">
                            <span class="text-slate-500">Payment:</span>
                            <span class="font-bold text-emerald-700">Cash / Paid</span>
                        </div>
                    </div>

                    <template x-if="showBarcode">
                        <div class="text-center my-3">
                            <div class="font-mono text-xl tracking-widest text-slate-800 font-bold">||| | ||||| |||| |</div>
                            <span class="text-[9px] text-slate-400 font-mono">ORD-98421</span>
                        </div>
                    </template>

                    <template x-if="showFooter">
                        <div class="text-center pt-2 border-t border-dashed border-slate-400 text-[9px] text-slate-500 space-y-1">
                            <p class="italic" x-text="termsText"></p>
                            <p class="font-bold uppercase tracking-wider text-slate-700" x-text="footerText"></p>
                        </div>
                    </template>
                </div>
            </template>

            <!-- ── PREVIEW 2: PAYMENT RECEIPT ── -->
            <template x-if="activeTab === 'receipt'">
                <div class="bg-white shadow-xl rounded-xl p-6 text-slate-900 border border-slate-300 w-[360px] transition-all"
                    :style="`font-family: ${fontFamily}; font-size: ${fontSize}; color: ${primaryColor};`">
                    
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div>
                            <h4 class="font-black text-base uppercase" x-text="'{{ $selectedTenant->name ?? 'Laundry Company' }}'"></h4>
                            <p class="text-xs text-slate-500 font-semibold" x-text="headerSubtitle"></p>
                        </div>
                        <template x-if="showLogo">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center p-1">
                                <img src="{{ $selectedTenant->logo_path ? asset($selectedTenant->logo_path) : asset('images/laundry_main_logo.png') }}" class="max-h-full max-w-full object-contain">
                            </div>
                        </template>
                    </div>

                    <div class="my-4 p-3 rounded-xl bg-slate-50 border border-slate-200/60 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Receipt No</span>
                            <div class="text-xs font-mono font-bold text-slate-800">#REC-2026-00412</div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Status</span>
                            <div class="text-xs font-black text-emerald-600 uppercase">Paid & Settled</div>
                        </div>
                    </div>

                    <div class="space-y-2 text-xs py-2">
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500">Order Reference:</span>
                            <span class="font-bold font-mono">#ORD-98421</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500">Customer Name:</span>
                            <span class="font-bold">Kamal Perera</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500">Payment Method:</span>
                            <span class="font-bold">Direct Bank Transfer</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500">Transaction Date:</span>
                            <span class="font-semibold">{{ date('M d, Y h:i A') }}</span>
                        </div>
                    </div>

                    <div class="mt-4 p-4 rounded-xl text-center" :style="`background-color: ${accentColor}15; border: 1px solid ${accentColor}40;`">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-600">Amount Received</span>
                        <div class="text-2xl font-black mt-1" :style="`color: ${accentColor};`">Rs. 2,250.00</div>
                    </div>

                    <template x-if="showFooter">
                        <div class="text-center pt-4 mt-4 border-t border-slate-100 text-[10px] text-slate-400 space-y-1">
                            <p x-text="termsText"></p>
                            <p class="font-bold text-slate-600" x-text="footerText"></p>
                        </div>
                    </template>
                </div>
            </template>

            <!-- ── PREVIEW 3: INVOICE PDF (A4) ── -->
            <template x-if="activeTab === 'invoice'">
                <div class="bg-white shadow-2xl rounded-sm p-8 text-slate-900 border border-slate-300 w-[420px] transition-all relative overflow-hidden"
                    :style="`font-family: ${fontFamily}; font-size: ${fontSize}; color: ${primaryColor};`">
                    
                    <div class="absolute top-0 left-0 right-0 h-2" :style="`background-color: ${accentColor};`"></div>

                    <div class="flex justify-between items-start pt-2 pb-6 border-b border-slate-200">
                        <div>
                            <h3 class="text-xl font-black uppercase tracking-tight" x-text="'{{ $selectedTenant->name ?? 'Laundry Company' }}'"></h3>
                            <p class="text-xs text-slate-500 font-semibold" x-text="headerSubtitle"></p>
                            <p class="text-[11px] text-slate-400 mt-1">contact@company.com &bull; +94 11 234 5678</p>
                        </div>
                        <div class="text-right">
                            <h2 class="text-2xl font-black text-slate-300 uppercase tracking-wider" x-text="headerTitle"></h2>
                            <p class="text-xs font-mono font-bold" :style="`color: ${accentColor};`">INV-2026-0089</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 my-4 p-3 bg-slate-50 rounded-lg text-xs">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Billed To:</span>
                            <div class="font-bold text-slate-800 mt-0.5">Hilton Residencies</div>
                            <div class="text-slate-500 text-[11px]">Corporate Account #CA-402</div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Invoice Date:</span>
                            <div class="font-bold text-slate-800 mt-0.5">{{ date('M d, Y') }}</div>
                            <div class="text-slate-500 text-[11px]">Due: On Receipt</div>
                        </div>
                    </div>

                    <table class="w-full text-left my-4 text-xs">
                        <thead>
                            <tr class="bg-slate-900 text-white text-[10px] uppercase font-bold">
                                <th class="p-2 rounded-l">Description</th>
                                <th class="p-2 text-center">Qty</th>
                                <th class="p-2 text-right rounded-r">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-[11px]">
                            <tr>
                                <td class="p-2 font-medium">Bedsheet Laundering & Pressing</td>
                                <td class="p-2 text-center font-bold">50</td>
                                <td class="p-2 text-right font-bold">Rs. 15,000.00</td>
                            </tr>
                            <tr>
                                <td class="p-2 font-medium">Table Linen & Napkins Dry Clean</td>
                                <td class="p-2 text-center font-bold">120</td>
                                <td class="p-2 text-right font-bold">Rs. 18,000.00</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="flex justify-end pt-2 border-t border-slate-200">
                        <div class="w-48 space-y-1 text-xs">
                            <div class="flex justify-between font-semibold text-slate-600">
                                <span>Subtotal:</span>
                                <span>Rs. 33,000.00</span>
                            </div>
                            <div class="flex justify-between font-black text-sm pt-2 border-t border-slate-200" :style="`color: ${accentColor};`">
                                <span>Total Payable:</span>
                                <span>Rs. 33,000.00</span>
                            </div>
                        </div>
                    </div>

                    <template x-if="showFooter">
                        <div class="mt-8 pt-4 border-t border-slate-100 text-[10px] text-slate-400 text-center space-y-1">
                            <p x-text="termsText"></p>
                            <p class="font-bold text-slate-600" x-text="footerText"></p>
                        </div>
                    </template>
                </div>
            </template>
        </div>

    </div>
</div>
@endsection

