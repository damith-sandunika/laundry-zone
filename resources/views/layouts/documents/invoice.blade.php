<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="ph-title">Invoice PDF Layout (A4)</h2>
                <p class="ph-sub">Manage formal A4 invoice and corporate statement layout</p>
            </div>
            <div>
                <a href="{{ route('orders.invoice', \App\Models\Order::first()?->id ?? '1') }}" target="_blank"
                    class="px-4 py-2 bg-blue-50 text-blue-700 hover:bg-blue-100 font-bold text-xs rounded-xl inline-flex items-center gap-1.5 transition">
                    <span>🖨 Open Real A4 Invoice</span>
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

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start" x-data="{
        headerTitle: {{ json_encode($headerTitle) }},
        headerSubtitle: {{ json_encode($headerSubtitle) }},
        invoiceTitle: {{ json_encode($invoiceTitle) }},
        invoiceTitleSize: {{ json_encode($invoiceTitleSize) }},
        companyNameSize: {{ json_encode($companyNameSize) }},
        fontFamily: {{ json_encode($layout->font_family) }},
        fontSize: {{ json_encode($layout->font_size) }},
        primaryColor: {{ json_encode($primaryColor) }},
        accentColor: {{ json_encode($accentColor) }},
        showLogo: {{ $showLogo ? 'true' : 'false' }},
        logoSize: {{ json_encode($logoSize) }},
        contactEmail: {{ json_encode($contactEmail) }},
        contactPhone: {{ json_encode($contactPhone) }},
        contactAddress: {{ json_encode($contactAddress) }},
        badgeTextPaid: {{ json_encode($badgeTextPaid) }},
        badgeTextPending: {{ json_encode($badgeTextPending) }},
        showPayOnlineBtn: {{ $showPayOnlineBtn == '1' ? 'true' : 'false' }},
        payBtnText: {{ json_encode($payBtnText) }},
        termsText: {{ json_encode($termsText) }},
        footerText: {{ json_encode($footerText) }},
        footerNote: {{ json_encode($footerNote) }}
    }">
        <!-- Controls Column -->
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/90 shadow-sm p-6 space-y-6">
            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-black text-slate-900">A4 Invoice Customization</h3>
                    <p class="text-xs text-slate-400 font-medium">Design and content of the official client invoice PDF</p>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-blue-50 text-blue-700">Live Editor</span>
            </div>

            <form action="{{ route('tenant.layouts.update') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="type" value="invoice">

                <!-- 1. Header & Logo -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
                    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider flex items-center justify-between">
                        <span>1. Company Header & Invoice Title</span>
                        <label class="flex items-center gap-1.5 cursor-pointer text-xs font-semibold text-slate-600">
                            <input type="checkbox" name="show_logo" value="1" x-model="showLogo" {{ $showLogo ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500">
                            <span>Show Logo</span>
                        </label>
                    </div>

                    <div x-show="showLogo" class="grid grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Logo Size (px)</label>
                            <input type="range" name="custom_fields[logo_size]" min="30" max="120" x-model="logoSize" value="{{ $logoSize }}" class="w-full">
                            <span class="text-[11px] font-mono text-blue-600 font-bold" x-text="logoSize + 'px'"></span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Company Title Size</label>
                            <select name="custom_fields[company_name_size]" x-model="companyNameSize" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-white">
                                <option value="20px" {{ $companyNameSize === '20px' ? 'selected' : '' }}>20px</option>
                                <option value="24px" {{ $companyNameSize === '24px' ? 'selected' : '' }}>24px (Default)</option>
                                <option value="28px" {{ $companyNameSize === '28px' ? 'selected' : '' }}>28px (Large)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Company Name</label>
                            <input type="text" name="header_title" x-model="headerTitle" value="{{ $headerTitle }}"
                                class="w-full text-xs font-bold rounded-xl border-slate-200 bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Invoice Heading</label>
                            <input type="text" name="custom_fields[invoice_title]" x-model="invoiceTitle" value="{{ $invoiceTitle }}"
                                class="w-full text-xs font-bold rounded-xl border-slate-200 bg-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Sub-heading</label>
                        <input type="text" name="header_subtitle" x-model="headerSubtitle" value="{{ $headerSubtitle }}"
                            class="w-full text-xs font-medium rounded-xl border-slate-200 bg-white">
                    </div>
                </div>

                <!-- 2. Contacts -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
                    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider">
                        2. Branch / Company Contact Details
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Contact Phone</label>
                            <input type="text" name="custom_fields[contact_phone]" x-model="contactPhone" value="{{ $contactPhone }}"
                                class="w-full text-xs rounded-xl border-slate-200 bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Contact Email</label>
                            <input type="text" name="custom_fields[contact_email]" x-model="contactEmail" value="{{ $contactEmail }}"
                                class="w-full text-xs rounded-xl border-slate-200 bg-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Address</label>
                        <input type="text" name="custom_fields[contact_address]" x-model="contactAddress" value="{{ $contactAddress }}"
                            class="w-full text-xs rounded-xl border-slate-200 bg-white">
                    </div>
                </div>

                <!-- 3. Styling & Colors -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
                    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider">
                        3. Colors & Theme
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Header Bar Primary Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" name="primary_color" x-model="primaryColor" value="{{ $primaryColor }}" class="w-9 h-9 rounded cursor-pointer border-0">
                                <input type="text" x-model="primaryColor" value="{{ $primaryColor }}" class="flex-1 text-xs font-mono rounded-xl border-slate-200 bg-white">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Accent & Button Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" name="accent_color" x-model="accentColor" value="{{ $accentColor }}" class="w-9 h-9 rounded cursor-pointer border-0">
                                <input type="text" x-model="accentColor" value="{{ $accentColor }}" class="flex-1 text-xs font-mono rounded-xl border-slate-200 bg-white">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Pay Online Button & Badges -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
                    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider flex items-center justify-between">
                        <span>4. Pay Online & Status Labels</span>
                        <label class="flex items-center gap-1.5 cursor-pointer text-xs font-semibold text-slate-600">
                            <input type="checkbox" name="custom_fields[show_pay_online_btn]" value="1" x-model="showPayOnlineBtn" {{ $showPayOnlineBtn == '1' ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500">
                            <span>Enable Pay Online Button</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-1">Pay Button Text</label>
                            <input type="text" name="custom_fields[pay_btn_text]" x-model="payBtnText" value="{{ $payBtnText }}"
                                class="w-full text-xs font-semibold rounded-lg border-slate-200 bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-1">Paid Status Badge</label>
                            <input type="text" name="custom_fields[badge_text_paid]" x-model="badgeTextPaid" value="{{ $badgeTextPaid }}"
                                class="w-full text-xs font-semibold rounded-lg border-slate-200 bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-1">Pending Status Badge</label>
                            <input type="text" name="custom_fields[badge_text_pending]" x-model="badgeTextPending" value="{{ $badgeTextPending }}"
                                class="w-full text-xs font-semibold rounded-lg border-slate-200 bg-white">
                        </div>
                    </div>
                </div>

                <!-- 5. Terms & Footer -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
                    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider">
                        5. Terms, Conditions & Note
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Terms & Conditions</label>
                        <textarea name="terms_and_conditions" rows="2" x-model="termsText"
                            class="w-full text-xs rounded-xl border-slate-200 bg-white">{{ $termsText }}</textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Footer Message</label>
                            <input type="text" name="footer_text" x-model="footerText" value="{{ $footerText }}"
                                class="w-full text-xs rounded-xl border-slate-200 bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Digital Terms Link Note</label>
                            <input type="text" name="custom_fields[footer_note]" x-model="footerNote" value="{{ $footerNote }}"
                                class="w-full text-xs rounded-xl border-slate-200 bg-white">
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                    <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                        Save All Layout Changes
                    </button>
                    <button type="button" onclick="if(confirm('Reset invoice to defaults?')) document.getElementById('resetForm').submit();"
                        class="text-xs font-bold text-slate-400 hover:text-rose-600 transition">
                        Reset Default
                    </button>
                </div>
            </form>

            <form id="resetForm" action="{{ route('tenant.layouts.reset') }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="type" value="invoice">
            </form>
        </div>

        <!-- Live Preview Column: Scaled A4 Simulation -->
        <div class="lg:col-span-6 bg-slate-100 rounded-2xl border border-slate-200/90 p-6 flex flex-col items-center">
            <div class="w-full flex items-center justify-between mb-3 text-xs">
                <span class="font-extrabold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                    Live A4 Invoice Preview
                </span>
                <span class="font-mono text-slate-400 font-bold">A4 Page (Scaled)</span>
            </div>

            <!-- Scaled A4 Sheet -->
            <div class="w-full bg-white border border-slate-300 shadow-xl rounded-sm p-6 text-slate-900 text-left transition-all relative overflow-hidden"
                :style="`font-family: ${fontFamily}; font-size: 11px;`">

                <!-- Colored Header Strip -->
                <div class="absolute top-0 left-0 right-0 h-2" :style="`background: ${primaryColor};`"></div>

                <!-- Header -->
                <div class="flex justify-between items-start mt-2 pb-4 border-b border-slate-200">
                    <div>
                        <template x-if="showLogo">
                            <div class="mb-2">
                                <img src="{{ $tenant && $tenant->logo_path ? asset($tenant->logo_path) : asset('images/laundry_main_logo.png') }}"
                                    :style="`max-height: ${logoSize}px; max-width: ${logoSize}px; object-contain: true;`"
                                    onerror="this.style.display='none'">
                            </div>
                        </template>
                        <div class="font-black" :style="`font-size: ${companyNameSize}; color: ${primaryColor};`" x-text="headerTitle"></div>
                        <div class="text-xs text-slate-500 font-medium" x-text="headerSubtitle"></div>
                        <div class="text-[11px] text-slate-600 mt-1" x-text="contactPhone"></div>
                        <div class="text-[11px] text-slate-600" x-text="contactEmail"></div>
                        <div class="text-[11px] text-slate-600" x-text="contactAddress"></div>
                    </div>

                    <div class="text-right">
                        <div class="font-black text-2xl tracking-wider text-slate-300" x-text="invoiceTitle"></div>
                        <div class="text-xs font-bold text-slate-800 mt-1">Invoice: #INV-10245</div>
                        <div class="text-[11px] text-slate-500">Date: 10 Nov 2026</div>
                    </div>
                </div>

                <!-- Bill To & Order Grid -->
                <div class="grid grid-cols-2 gap-4 py-3 text-xs">
                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-200">
                        <div class="text-[10px] uppercase font-bold text-slate-400">Bill To</div>
                        <div class="font-bold text-slate-900 mt-0.5">Mr. Kamal Perera</div>
                        <div class="text-[11px] text-slate-600">077-1234567</div>
                        <div class="text-[11px] text-slate-600">No 12, Flower Road, Colombo 07</div>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-200">
                        <div class="text-[10px] uppercase font-bold text-slate-400">Order Details</div>
                        <div class="text-[11px] text-slate-700 mt-0.5 flex justify-between">
                            <span>Status:</span>
                            <span class="font-bold text-emerald-600">COMPLETED</span>
                        </div>
                        <div class="text-[11px] text-slate-700 flex justify-between">
                            <span>Delivery:</span>
                            <span class="font-medium">14 Nov 2026 05:00 PM</span>
                        </div>
                    </div>
                </div>

                <!-- Table Simulation -->
                <div class="border border-slate-200 rounded-lg overflow-hidden my-3">
                    <div class="text-white text-[10px] font-bold uppercase p-2 flex justify-between" :style="`background: ${primaryColor};`">
                        <span class="w-1/2">Item Description</span>
                        <span class="w-16 text-center">Qty</span>
                        <span class="w-20 text-right">Price</span>
                        <span class="w-20 text-right">Total</span>
                    </div>
                    <div class="p-2 border-b border-slate-100 flex justify-between text-[11px] font-medium">
                        <span class="w-1/2 font-bold">Formal Long Sleeve Shirt (Iron Only)</span>
                        <span class="w-16 text-center font-bold">3</span>
                        <span class="w-20 text-right">Rs. 300.00</span>
                        <span class="w-20 text-right font-bold">Rs. 900.00</span>
                    </div>
                    <div class="p-2 flex justify-between text-[11px] font-medium">
                        <span class="w-1/2 font-bold">2-Piece Executive Suit (Dry Clean)</span>
                        <span class="w-16 text-center font-bold">1</span>
                        <span class="w-20 text-right">Rs. 1,650.00</span>
                        <span class="w-20 text-right font-bold">Rs. 1,650.00</span>
                    </div>
                </div>

                <!-- Totals -->
                <div class="flex justify-end my-2">
                    <div class="w-56 space-y-1 text-xs">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal</span>
                            <span>Rs. 2,550.00</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Discount</span>
                            <span>- Rs. 100.00</span>
                        </div>
                        <div class="flex justify-between font-black text-sm pt-1 border-t border-slate-200">
                            <span>TOTAL</span>
                            <span>Rs. 2,450.00</span>
                        </div>
                        <div class="flex justify-between text-emerald-600 font-bold">
                            <span>Paid</span>
                            <span>Rs. 2,450.00</span>
                        </div>
                    </div>
                </div>

                <!-- Online Pay Button Sample & Badge -->
                <div class="text-center my-4 space-y-2">
                    <div class="inline-block bg-emerald-600 text-white font-bold text-xs px-3 py-1 rounded-full"
                        x-text="badgeTextPaid"></div>
                    <div x-show="showPayOnlineBtn">
                        <span class="inline-block text-white font-bold text-xs px-5 py-2 rounded-xl shadow-md cursor-pointer"
                            :style="`background: ${accentColor};`" x-text="payBtnText"></span>
                    </div>
                </div>

                <!-- Terms & Footer -->
                <div class="pt-3 border-t border-slate-200 text-center text-[10px] text-slate-500 space-y-1">
                    <div class="text-justify text-slate-600" x-text="termsText"></div>
                    <div class="font-bold text-slate-700" x-text="footerText"></div>
                    <div x-text="footerNote"></div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
