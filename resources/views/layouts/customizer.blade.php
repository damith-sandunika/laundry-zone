<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="ph-title">Document & Print Layouts</h2>
                <p class="ph-sub">Customize thermal bills, payment receipts, and invoice designs with real-time visual preview</p>
            </div>
            <div>
                <form action="{{ route('tenant.layouts.reset') }}" method="POST" onsubmit="return confirm('Reset this layout to default?');">
                    @csrf
                    <input type="hidden" name="type" value="{{ $currentType }}">
                    <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors h-9">
                        Reset to Default
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

<div class="space-y-6" x-data="{
    activeTab: '{{ $currentType }}',
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

    @if(session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 shadow-sm animate-fade-in text-xs font-bold">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Top Tabs: Document Types -->
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-sm p-4">
        <div class="flex items-center gap-2 overflow-x-auto">
            <a href="{{ route('tenant.layouts.bill') }}"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $currentType === 'bill' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                🧾 Thermal Bill (80mm)
            </a>
            <a href="{{ route('tenant.layouts.receipt') }}"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $currentType === 'receipt' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                💳 Job Card / Receipt
            </a>
            <a href="{{ route('tenant.layouts.invoice') }}"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $currentType === 'invoice' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                📄 Invoice PDF (A4)
            </a>
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Controls Column -->
        <div class="lg:col-span-5 bg-white rounded-xl border border-slate-200/90 shadow-sm p-6 space-y-6">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="text-sm font-black text-slate-900">Customize {{ ucfirst($currentType) }} Layout</h3>
                <p class="text-xs text-slate-400 font-medium">Changes apply immediately to your company print bills</p>
            </div>

            <form action="{{ route('tenant.layouts.update') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="type" :value="activeTab">

                <!-- Typography -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Font Family</label>
                        <select name="font_family" x-model="fontFamily" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-slate-50">
                            <option value="Inter">Inter (Clean Modern)</option>
                            <option value="'Plus Jakarta Sans', sans-serif">Plus Jakarta Sans</option>
                            <option value="'Courier New', monospace">Courier (Thermal Mono)</option>
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
                        <label class="block text-xs font-bold text-slate-700 mb-1">Accent Color</label>
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
                            <span>QR Code</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer bg-slate-50 p-2 rounded-lg border border-slate-200/60">
                            <input type="checkbox" name="show_barcode" value="1" x-model="showBarcode" class="rounded text-blue-600 focus:ring-blue-500">
                            <span>Barcode</span>
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
                    <label class="block text-xs font-bold text-slate-700 mb-1">Terms & Policy Note</label>
                    <textarea name="terms_and_conditions" x-model="termsText" rows="3" class="w-full text-xs rounded-xl border-slate-200"></textarea>
                </div>

                <!-- Footer Text -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Footer Message</label>
                    <input type="text" name="footer_text" x-model="footerText" class="w-full text-xs rounded-xl border-slate-200">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="submit" class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition-colors">
                        Save & Apply Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Live Visual Preview Column -->
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

            <!-- PREVIEW 1: THERMAL BILL -->
            <template x-if="activeTab === 'bill'">
                <div class="bg-white shadow-xl rounded-sm p-4 text-slate-900 border border-slate-300 w-[300px] transition-all"
                    :style="`font-family: ${fontFamily}; font-size: ${fontSize}; color: ${primaryColor};`">
                    
                    <div class="text-center pb-2 border-b border-dashed border-slate-400">
                        <template x-if="showLogo">
                            <div class="w-10 h-10 mx-auto mb-1 rounded bg-slate-100 flex items-center justify-center p-1">
                                <img src="{{ $tenant && $tenant->logo_path ? asset($tenant->logo_path) : asset('images/laundry_main_logo.png') }}" class="max-h-full max-w-full object-contain">
                            </div>
                        </template>
                        <h4 class="font-black text-sm uppercase" x-text="'{{ $tenant->name ?? 'Laundry Company' }}'"></h4>
                        <template x-if="showBranchInfo">
                            <p class="text-[10px] text-slate-600 mt-0.5">Main Branch &bull; 011-2345678</p>
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

            <!-- PREVIEW 2: JOB CARD / RECEIPT -->
            <template x-if="activeTab === 'receipt'">
                <div class="bg-white shadow-xl rounded-sm p-4 text-slate-900 border border-slate-300 w-[300px] transition-all"
                    :style="`font-family: ${fontFamily}; font-size: ${fontSize}; color: ${primaryColor};`">
                    
                    <div class="header text-center pb-2 border-b border-black">
                        <div class="text-xl font-black text-center">ORD-98421</div>
                        <div class="text-xs font-bold text-center mt-1">
                            VIP - MR. KAMAL PERERA<br>
                            0771234567
                        </div>
                    </div>

                    <div class="date font-black text-xs my-2 text-center uppercase">{{ date('d M Y h:i A') }}</div>

                    <div class="items my-2 space-y-2 text-xs">
                        <div>
                            <div class="text-[9px] font-bold uppercase">&bull; Gents Wear > Wash & Iron</div>
                            <div class="flex justify-between font-bold text-xs">
                                <span class="ml-2">Formal Shirts</span>
                                <span>x3</span>
                            </div>
                        </div>
                        <div>
                            <div class="text-[9px] font-bold uppercase">&bull; Dry Cleaning</div>
                            <div class="flex justify-between font-bold text-xs">
                                <span class="ml-2">Trousers</span>
                                <span>x2</span>
                            </div>
                            <div class="text-[10px] ml-3 text-amber-600">[Express (50%)]</div>
                        </div>

                        <div class="flex justify-between font-black text-xs pt-1 border-t border-black">
                            <span>Total Qty</span>
                            <span>x5</span>
                        </div>
                    </div>

                    <!-- Payment Totals -->
                    <div class="border-t border-black my-2 pt-2 pb-2 text-xs space-y-1">
                        <div class="flex justify-between text-xs">
                            <span>Subtotal</span>
                            <span>Rs. 2,250.00</span>
                        </div>
                        <div class="flex justify-between font-black text-xs border-t border-black pt-1">
                            <span>TOTAL</span>
                            <span>Rs. 2,250.00</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span>Paid</span>
                            <span>Rs. 2,250.00</span>
                        </div>
                        <div class="flex justify-between font-bold text-xs text-emerald-700">
                            <span>✓ Fully Paid</span>
                            <span>Rs. 2,250.00</span>
                        </div>
                    </div>

                    <div class="border-t border-black pt-2 text-xs font-bold">
                        <div>Special Note :</div>
                        <div class="border-b border-black h-4 mt-1"></div>
                        <div class="border-b border-black h-4 mt-1"></div>
                    </div>

                    <div class="mt-2 space-y-1 text-xs font-bold">
                        <div>Collected : Kamal</div>
                        <div>Wash : ________</div>
                        <div>Iron : ________</div>
                        <div>Quality check : ________</div>
                        <div>Final inspection : ________</div>
                    </div>

                    <template x-if="showFooter">
                        <div class="text-center pt-2 mt-2 border-t border-dashed border-slate-400 text-[9px] text-slate-500 space-y-1">
                            <p class="italic" x-text="termsText"></p>
                            <p class="font-bold text-slate-700 uppercase" x-text="footerText"></p>
                        </div>
                    </template>
                </div>
            </template>

            <!-- PREVIEW 3: INVOICE PDF (A4) -->
            <template x-if="activeTab === 'invoice'">
                <div class="bg-white shadow-2xl rounded-sm p-8 text-slate-900 border border-slate-300 w-[420px] transition-all relative overflow-hidden"
                    :style="`font-family: ${fontFamily}; font-size: ${fontSize}; color: ${primaryColor};`">
                    
                    <div class="absolute top-0 left-0 right-0 h-2" :style="`background-color: ${accentColor};`"></div>

                    <div class="flex justify-between items-start pt-2 pb-6 border-b border-slate-200">
                        <div>
                            <h3 class="text-xl font-black uppercase tracking-tight" x-text="'{{ $tenant->name ?? 'Laundry Company' }}'"></h3>
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
</x-app-layout>

