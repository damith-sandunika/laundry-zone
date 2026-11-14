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

    @include('layouts.documents.partials.invoice_init')

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
                                <option value="20px">20px</option>
                                <option value="24px">24px (Default)</option>
                                <option value="28px">28px (Large)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Company Name</label>
                            <input type="text" name="header_title" x-model="headerTitle" value="{{ $headerTitle }}" class="w-full text-xs font-bold rounded-xl border-slate-200 bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Invoice Heading</label>
                            <input type="text" name="custom_fields[invoice_title]" x-model="invoiceTitle" value="{{ $invoiceTitle }}" class="w-full text-xs font-bold rounded-xl border-slate-200 bg-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Sub-heading</label>
                        <input type="text" name="header_subtitle" x-model="headerSubtitle" value="{{ $headerSubtitle }}" class="w-full text-xs font-medium rounded-xl border-slate-200 bg-white">
                    </div>
                </div>

                <!-- 2. Contacts -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
                    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider">2. Branch / Company Contact Details</div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Contact Phone</label>
                            <input type="text" name="custom_fields[contact_phone]" x-model="contactPhone" value="{{ $contactPhone }}" class="w-full text-xs rounded-xl border-slate-200 bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Contact Email</label>
                            <input type="text" name="custom_fields[contact_email]" x-model="contactEmail" value="{{ $contactEmail }}" class="w-full text-xs rounded-xl border-slate-200 bg-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Address</label>
                        <input type="text" name="custom_fields[contact_address]" x-model="contactAddress" value="{{ $contactAddress }}" class="w-full text-xs rounded-xl border-slate-200 bg-white">
                    </div>
                </div>

                <!-- 3. Styling & Colors -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
                    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider">3. Colors & Theme</div>
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
                            <input type="text" name="custom_fields[pay_btn_text]" x-model="payBtnText" value="{{ $payBtnText }}" class="w-full text-xs font-semibold rounded-lg border-slate-200 bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-1">Paid Status Badge</label>
                            <input type="text" name="custom_fields[badge_text_paid]" x-model="badgeTextPaid" value="{{ $badgeTextPaid }}" class="w-full text-xs font-semibold rounded-lg border-slate-200 bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-1">Pending Status Badge</label>
                            <input type="text" name="custom_fields[badge_text_pending]" x-model="badgeTextPending" value="{{ $badgeTextPending }}" class="w-full text-xs font-semibold rounded-lg border-slate-200 bg-white">
                        </div>
                    </div>
                </div>

                <!-- 5. Terms & Footer -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
                    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider">5. Terms, Conditions & Note</div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Terms & Conditions</label>
                        <textarea name="terms_and_conditions" rows="2" x-model="termsText" class="w-full text-xs rounded-xl border-slate-200 bg-white">{{ $termsText }}</textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Footer Message</label>
                            <input type="text" name="footer_text" x-model="footerText" value="{{ $footerText }}" class="w-full text-xs rounded-xl border-slate-200 bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Digital Terms Link Note</label>
                            <input type="text" name="custom_fields[footer_note]" x-model="footerNote" value="{{ $footerNote }}" class="w-full text-xs rounded-xl border-slate-200 bg-white">
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition">Save All Layout Changes</button>
                    <button type="button" onclick="if(confirm('Reset invoice to defaults?')) document.getElementById('resetForm').submit();" class="text-xs font-bold text-slate-400 hover:text-rose-600 transition">Reset Default</button>
                </div>
            </form>

            <form id="resetForm" action="{{ route('tenant.layouts.reset') }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="type" value="invoice">
            </form>
        </div>

        @include('layouts.documents.partials.invoice_preview')
    </div>
</x-app-layout>
