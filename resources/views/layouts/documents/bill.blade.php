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

        // Additional Custom Text & Note Fields
        $billNotice = $cfg['bill_notice'] ?? '';
        $customNoteTitle = $cfg['custom_note_title'] ?? 'Notice:';
        $customNoteBody = $cfg['custom_note_body'] ?? '';
        $customExtraTexts = $cfg['custom_extra_texts'] ?? [];
        if (!is_array($customExtraTexts)) {
            $customExtraTexts = [];
        }

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
        customNoteTitle: {{ json_encode($customNoteTitle) }},
        customNoteBody: {{ json_encode($customNoteBody) }},
        extraLines: {{ json_encode($customExtraTexts) }},
        addExtraLine() {
            this.extraLines.push({ text: 'Special Customer Note / Alert' });
            this.syncExtraLines();
        },
        removeExtraLine(idx) {
            this.extraLines.splice(idx, 1);
            this.syncExtraLines();
        },
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

                <!-- 1. Header & Logo -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
                    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider flex items-center justify-between">
                        <span>1. Logo, Header & Contacts</span>
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
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Roll Width</label>
                            <select name="custom_fields[paper_width]" x-model="paperWidth" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-white">
                                <option value="260px" {{ $paperWidth === '260px' ? 'selected' : '' }}>58mm Thermal (Narrow)</option>
                                <option value="300px" {{ $paperWidth === '300px' ? 'selected' : '' }}>80mm Thermal (Standard)</option>
                                <option value="340px" {{ $paperWidth === '340px' ? 'selected' : '' }}>Wide Receipt</option>
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
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Title Size</label>
                            <select name="custom_fields[company_name_size]" x-model="companyNameSize" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-white">
                                <option value="15px" {{ $companyNameSize === '15px' ? 'selected' : '' }}>15px</option>
                                <option value="18px" {{ $companyNameSize === '18px' ? 'selected' : '' }}>18px (Default)</option>
                                <option value="20px" {{ $companyNameSize === '20px' ? 'selected' : '' }}>20px</option>
                                <option value="22px" {{ $companyNameSize === '22px' ? 'selected' : '' }}>22px (Bold Large)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Subtitle / Tagline</label>
                            <input type="text" name="header_subtitle" x-model="headerSubtitle" value="{{ $headerSubtitle }}"
                                class="w-full text-xs font-medium rounded-xl border-slate-200 bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Contact Phone</label>
                            <input type="text" name="custom_fields[branch_phone]" x-model="branchPhone" value="{{ $branchPhone }}"
                                class="w-full text-xs font-medium rounded-xl border-slate-200 bg-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Branch Address</label>
                        <input type="text" name="custom_fields[branch_address]" x-model="branchAddress" value="{{ $branchAddress }}"
                            class="w-full text-xs font-medium rounded-xl border-slate-200 bg-white">
                    </div>
                </div>

                <!-- 2. Typography -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
                    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider">
                        2. Typography
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Font Family</label>
                            <select name="font_family" x-model="fontFamily" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-white">
                                <option value="'Inter', system-ui, sans-serif" {{ str_contains($layout->font_family, 'Inter') ? 'selected' : '' }}>Inter</option>
                                <option value="'Helvetica Neue', Helvetica, Arial, sans-serif" {{ str_contains($layout->font_family, 'Helvetica') ? 'selected' : '' }}>Helvetica / Arial</option>
                                <option value="'Courier New', monospace" {{ str_contains($layout->font_family, 'Courier') ? 'selected' : '' }}>Courier Mono</option>
                                <option value="'Segoe UI', sans-serif" {{ str_contains($layout->font_family, 'Segoe UI') ? 'selected' : '' }}>Segoe UI</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Base Font Size</label>
                            <select name="font_size" x-model="fontSize" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-white">
                                <option value="11px" {{ $layout->font_size === '11px' ? 'selected' : '' }}>11px (Compact)</option>
                                <option value="12px" {{ $layout->font_size === '12px' ? 'selected' : '' }}>12px (Standard)</option>
                                <option value="13px" {{ $layout->font_size === '13px' ? 'selected' : '' }}>13px (Clear)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 3. Section Toggles -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
                    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider">
                        3. Section Visibility
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-xs font-semibold text-slate-700">
                        <label class="flex items-center gap-2 cursor-pointer bg-white p-2 rounded-lg border border-slate-200">
                            <input type="checkbox" name="custom_fields[show_due_date]" value="1" x-model="showDueDate" {{ $showDueDate == '1' ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500">
                            <span>Due Date</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer bg-white p-2 rounded-lg border border-slate-200">
                            <input type="checkbox" name="custom_fields[show_delivery_fee]" value="1" x-model="showDeliveryFee" {{ $showDeliveryFee == '1' ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500">
                            <span>Delivery Fee</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer bg-white p-2 rounded-lg border border-slate-200">
                            <input type="checkbox" name="custom_fields[show_discount]" value="1" x-model="showDiscount" {{ $showDiscount == '1' ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500">
                            <span>Discount Row</span>
                        </label>
                    </div>
                </div>

                <!-- 4. Payment Badges Custom Labels -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
                    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider">
                        4. Payment Status Badge Labels
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-1">Paid Status</label>
                            <input type="text" name="custom_fields[badge_text_paid]" x-model="badgeTextPaid" value="{{ $badgeTextPaid }}"
                                class="w-full text-xs font-semibold rounded-lg border-slate-200 bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-1">Credit Status</label>
                            <input type="text" name="custom_fields[badge_text_credit]" x-model="badgeTextCredit" value="{{ $badgeTextCredit }}"
                                class="w-full text-xs font-semibold rounded-lg border-slate-200 bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-1">Pickup Status</label>
                            <input type="text" name="custom_fields[badge_text_pickup]" x-model="badgeTextPickup" value="{{ $badgeTextPickup }}"
                                class="w-full text-xs font-semibold rounded-lg border-slate-200 bg-white">
                        </div>
                    </div>
                </div>

                <!-- 5. Additional Editable Notice & Custom Lines (Dynamic Add/Remove) -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
                    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider flex items-center justify-between">
                        <span>5. Custom Bill Notice & Editable Alerts</span>
                        <button type="button" @click="addExtraLine()"
                            class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[11px] font-bold inline-flex items-center gap-1 shadow-sm transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            <span>Add Custom Text</span>
                        </button>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Important Highlight / Top Notice (e.g. Bring receipt for collection)</label>
                        <input type="text" name="custom_fields[bill_notice]" x-model="billNotice" value="{{ $billNotice }}"
                            placeholder="Optional: Highlighted notice printed right below order info"
                            class="w-full text-xs rounded-xl border-slate-200 bg-white">
                    </div>

                    <!-- Dynamic Extra Lines List -->
                    <div class="space-y-2 pt-2">
                        <template x-for="(line, lIdx) in extraLines" :key="lIdx">
                            <div class="flex items-center gap-2 bg-white p-2 rounded-xl border border-slate-200 shadow-2xs">
                                <span class="text-xs font-bold text-slate-400 w-5 text-center" x-text="lIdx + 1"></span>
                                <input type="text" x-model="line.text" @input="syncExtraLines()"
                                    class="flex-1 text-xs font-medium rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:ring-blue-500"
                                    placeholder="Enter custom notice or instruction here">
                                <button type="button" @click="removeExtraLine(lIdx)"
                                    class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 6. Footer, Terms & Branding -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
                    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider">
                        6. Footer, Terms & Branding
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Terms & Conditions</label>
                        <textarea name="terms_and_conditions" rows="2" x-model="termsText"
                            class="w-full text-xs rounded-xl border-slate-200 bg-white">{{ $termsText }}</textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Thank You Text</label>
                            <input type="text" name="footer_text" x-model="footerText" value="{{ $footerText }}"
                                class="w-full text-xs rounded-xl border-slate-200 bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Review Prompt</label>
                            <input type="text" name="custom_fields[review_text]" x-model="reviewText" value="{{ $reviewText }}"
                                class="w-full text-xs rounded-xl border-slate-200 bg-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Software Branding</label>
                        <input type="text" name="custom_fields[software_branding]" x-model="softwareBranding" value="{{ $softwareBranding }}"
                            class="w-full text-xs rounded-xl border-slate-200 bg-white">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                    <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
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

            <!-- Receipt Container -->
            <div class="bg-white border border-slate-300 shadow-xl rounded-sm p-4 text-slate-900 text-left transition-all"
                :style="`font-family: ${fontFamily}; font-size: ${fontSize}; width: ${paperWidth};`">

                <!-- Header Logo & Store Info -->
                <div class="text-center pb-2">
                    <template x-if="showLogo">
                        <div class="mx-auto mb-1 flex items-center justify-center">
                            <img src="{{ $tenant && $tenant->logo_path ? asset($tenant->logo_path) : asset('images/laundry_main_logo.png') }}"
                                :style="`max-height: ${logoSize}px; max-width: ${logoSize}px; object-contain: true;`"
                                onerror="this.style.display='none'">
                        </div>
                    </template>
                    <div class="font-extrabold" :style="`font-size: ${companyNameSize};`" x-text="headerTitle"></div>
                    <div class="text-xs font-medium text-slate-600 mt-0.5" x-text="headerSubtitle"></div>
                    <div class="text-xs font-medium text-slate-600" x-text="branchPhone"></div>
                    <div class="text-xs font-medium text-slate-600" x-text="branchAddress"></div>
                </div>

                <div class="border-t border-dashed border-black my-2"></div>

                <!-- Order Info -->
                <div class="space-y-1 text-xs">
                    <div class="flex justify-between font-bold">
                        <span>Bill No:</span>
                        <span>#10245</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Order Date:</span>
                        <span>10 Nov 2026 10:30 AM</span>
                    </div>
                    <div x-show="showDueDate" class="flex justify-between">
                        <span>Delivery Date:</span>
                        <span>14 Nov 2026 05:00 PM</span>
                    </div>
                    <div class="flex justify-between font-bold">
                        <span>Customer:</span>
                        <span>Mr. Kamal Perera</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Phone:</span>
                        <span>077-1234567</span>
                    </div>
                </div>

                <!-- Optional Highlight Notice -->
                <div x-show="billNotice" class="my-2 p-1.5 border border-dashed border-black text-center font-bold text-[11px]" x-text="billNotice"></div>

                <div class="border-t border-dashed border-black my-2"></div>

                <!-- Items Sample -->
                <div class="space-y-2 text-xs">
                    <div>
                        <div class="flex justify-between font-bold">
                            <span>Formal Shirt (Iron Only)</span>
                            <span>Rs. 600.00</span>
                        </div>
                        <div class="text-[11px] text-slate-500 pl-1">2 x Rs. 300.00</div>
                    </div>
                    <div>
                        <div class="flex justify-between font-bold">
                            <span>Dry Cleaning Suit (2-Piece)</span>
                            <span>Rs. 1,650.00</span>
                        </div>
                        <div class="text-[11px] text-slate-500 pl-1">1 x Rs. 1,650.00 [Express]</div>
                    </div>
                </div>

                <div class="border-t border-dashed border-black my-2"></div>

                <!-- Totals -->
                <div class="space-y-1 text-xs">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span>Rs. 2,250.00</span>
                    </div>
                    <div x-show="showDiscount" class="flex justify-between text-slate-700">
                        <span>Discount</span>
                        <span>- Rs. 0.00</span>
                    </div>
                    <div x-show="showDeliveryFee" class="flex justify-between text-slate-700">
                        <span>Delivery Fee</span>
                        <span>Rs. 200.00</span>
                    </div>
                    <div class="flex justify-between font-extrabold text-sm border-t border-black pt-1">
                        <span>TOTAL</span>
                        <span>Rs. 2,450.00</span>
                    </div>
                    <div class="flex justify-between pt-1">
                        <span>Amount Received</span>
                        <span>Rs. 2,450.00</span>
                    </div>
                </div>

                <div class="border-t border-dashed border-black my-2"></div>

                <!-- Badge -->
                <div class="text-center my-2">
                    <span class="inline-block border border-black font-extrabold text-xs px-3 py-1 rounded"
                        x-text="badgeTextPaid"></span>
                </div>

                <!-- Dynamic Extra Notice Lines in Preview -->
                <template x-if="extraLines && extraLines.length > 0">
                    <div class="border-t border-dashed border-black my-2 pt-1 space-y-1">
                        <template x-for="(line, idx) in extraLines" :key="idx">
                            <div class="text-[11px] font-bold text-center text-slate-800" x-text="line.text"></div>
                        </template>
                    </div>
                </template>

                <div class="border-t border-dashed border-black my-2"></div>

                <!-- Terms & Footer -->
                <div class="text-[10px] text-center text-slate-700 space-y-1">
                    <div class="font-bold">Terms & Conditions</div>
                    <div class="text-justify" x-text="termsText"></div>
                </div>

                <div class="border-t border-dashed border-black my-2"></div>

                <div class="text-center text-xs space-y-1 pt-1">
                    <div class="font-bold" x-text="footerText"></div>
                    <div class="text-[10px] text-slate-500" x-text="reviewText"></div>
                    <div class="text-[9px] text-slate-400 pt-1" x-text="softwareBranding"></div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
