<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="ph-title">Job Card / Receipt Layout</h2>
                <p class="ph-sub">Manage workshop chit, production job card, and workshop receipt layout</p>
            </div>
            <div>
                <a href="{{ route('orders.printChit', \App\Models\Order::first()?->id ?? '1') }}" target="_blank"
                    class="px-4 py-2 bg-blue-50 text-blue-700 hover:bg-blue-100 font-bold text-xs rounded-xl inline-flex items-center gap-1.5 transition">
                    <span>🖨 Open Real Job Card (Chit)</span>
                </a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 shadow-sm mb-6 text-xs font-bold">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @include('layouts.documents.partials.receipt_init')

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start" x-data="{
        headerTitle: {{ json_encode($headerTitle) }},
        headerSubtitle: {{ json_encode($headerSubtitle) }},
        fontFamily: {{ json_encode($layout->font_family) }},
        fontSize: {{ json_encode($layout->font_size) }},
        orderNoSize: {{ json_encode($orderNoSize) }},
        showLogo: {{ $showLogo ? 'true' : 'false' }},
        logoSize: {{ json_encode($logoSize) }},
        paperWidth: {{ json_encode($paperWidth) }},
        showCustomerPhone: {{ $showCustomerPhone == '1' ? 'true' : 'false' }},
        showDueDate: {{ $showDueDate == '1' ? 'true' : 'false' }},
        showTotals: {{ $showTotals == '1' ? 'true' : 'false' }},
        showSpecialNotes: {{ $showSpecialNotes == '1' ? 'true' : 'false' }},
        notesLabel: {{ json_encode($notesLabel) }},
        notesLineCount: {{ (int)$notesLineCount }},
        showProcessList: {{ $showProcessList == '1' ? 'true' : 'false' }},
        processItems: {{ json_encode($savedProcesses) }},
        addProcessItem() {
            this.processItems.push({ name: 'New Process Step', suffix: '________' });
            this.syncJson();
        },
        removeProcessItem(index) {
            this.processItems.splice(index, 1);
            this.syncJson();
        },
        syncJson() {
            this.$nextTick(() => {
                const hiddenInput = document.getElementById('process_items_json_field');
                if (hiddenInput) {
                    hiddenInput.value = JSON.stringify(this.processItems);
                }
            });
        }
    }" x-init="syncJson()">
        <!-- Controls Column -->
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/90 shadow-sm p-6 space-y-6">
            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-black text-slate-900">Customizable Receipt / Job Card Fields</h3>
                    <p class="text-xs text-slate-400 font-medium">All text labels, checklist items, and sizes are editable</p>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-blue-50 text-blue-700">Dynamic Checklist</span>
            </div>

            <form action="{{ route('tenant.layouts.update') }}" method="POST" class="space-y-4" @submit="syncJson()">
                @csrf
                <input type="hidden" name="type" value="receipt">
                <input type="hidden" id="process_items_json_field" name="process_items_json" value="{{ json_encode($savedProcesses) }}">

                <!-- 1. Header & Logo Controls -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
                    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider flex items-center justify-between">
                        <span>1. Logo & Header Section</span>
                        <label class="flex items-center gap-1.5 cursor-pointer text-xs font-semibold text-slate-600">
                            <input type="checkbox" name="show_logo" value="1" x-model="showLogo" {{ $showLogo ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500">
                            <span>Show Logo</span>
                        </label>
                    </div>

                    <div x-show="showLogo" class="grid grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Logo Size (px)</label>
                            <input type="range" name="custom_fields[logo_size]" min="25" max="100" x-model="logoSize" value="{{ $logoSize }}" class="w-full">
                            <span class="text-[11px] font-mono text-blue-600 font-bold" x-text="logoSize + 'px'"></span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Paper Roll Width</label>
                            <select name="custom_fields[paper_width]" x-model="paperWidth" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-white">
                                <option value="260px" {{ $paperWidth === '260px' ? 'selected' : '' }}>58mm Thermal (Narrow)</option>
                                <option value="300px" {{ $paperWidth === '300px' ? 'selected' : '' }}>80mm Thermal (Standard)</option>
                                <option value="360px" {{ $paperWidth === '360px' ? 'selected' : '' }}>100mm Wide / Label</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Company / Header Title</label>
                            <input type="text" name="header_title" x-model="headerTitle" value="{{ $headerTitle }}"
                                class="w-full text-xs font-bold rounded-xl border-slate-200 bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Subtitle</label>
                            <input type="text" name="header_subtitle" x-model="headerSubtitle" value="{{ $headerSubtitle }}"
                                class="w-full text-xs font-medium rounded-xl border-slate-200 bg-white">
                        </div>
                    </div>
                </div>

                <!-- 2. Typography & Order Number Size -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
                    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider">2. Fonts & Text Sizes</div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Font Family</label>
                            <select name="font_family" x-model="fontFamily" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-white">
                                <option value="'Helvetica Neue', Helvetica, Arial, sans-serif">Helvetica / Arial</option>
                                <option value="'Inter', sans-serif">Inter</option>
                                <option value="'Plus Jakarta Sans', sans-serif">Plus Jakarta Sans</option>
                                <option value="'Courier New', monospace">Courier Mono</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Base Font Size</label>
                            <select name="font_size" x-model="fontSize" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-white">
                                <option value="11px">11px (Compact)</option>
                                <option value="12px">12px (Standard)</option>
                                <option value="13px">13px (Large)</option>
                                <option value="14px">14px (Extra Large)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Order # Size</label>
                            <select name="custom_fields[order_no_size]" x-model="orderNoSize" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-white">
                                <option value="16px">16px</option>
                                <option value="18px">18px</option>
                                <option value="20px">20px (Default)</option>
                                <option value="24px">24px (Bold Big)</option>
                                <option value="28px">28px (Jumbo)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 3. Section Toggles -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
                    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider">3. Section Visibility</div>
                    <div class="grid grid-cols-2 gap-2 text-xs font-semibold text-slate-700">
                        <label class="flex items-center gap-2 cursor-pointer bg-white p-2 rounded-lg border border-slate-200">
                            <input type="checkbox" name="custom_fields[show_customer_phone]" value="1" x-model="showCustomerPhone" {{ $showCustomerPhone == '1' ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500">
                            <span>Customer Phone</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer bg-white p-2 rounded-lg border border-slate-200">
                            <input type="checkbox" name="custom_fields[show_due_date]" value="1" x-model="showDueDate" {{ $showDueDate == '1' ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500">
                            <span>Due Date Banner</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer bg-white p-2 rounded-lg border border-slate-200">
                            <input type="checkbox" name="custom_fields[show_totals]" value="1" x-model="showTotals" {{ $showTotals == '1' ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500">
                            <span>Payment Totals</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer bg-white p-2 rounded-lg border border-slate-200">
                            <input type="checkbox" name="custom_fields[show_special_notes]" value="1" x-model="showSpecialNotes" {{ $showSpecialNotes == '1' ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500">
                            <span>Special Notes Area</span>
                        </label>
                    </div>
                </div>

                @include('layouts.documents.partials.receipt_checklist')

                <input type="hidden" name="primary_color" value="{{ $layout->primary_color ?? '#000000' }}">
                <input type="hidden" name="accent_color" value="{{ $layout->accent_color ?? '#059669' }}">

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition">Save All Layout Changes</button>
                    <button type="button" onclick="if(confirm('Reset all fields to original defaults?')) document.getElementById('resetForm').submit();" class="text-xs font-bold text-slate-400 hover:text-rose-600 transition">Reset Default</button>
                </div>
            </form>

            <form id="resetForm" action="{{ route('tenant.layouts.reset') }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="type" value="receipt">
            </form>
        </div>

        @include('layouts.documents.partials.receipt_preview')
    </div>
</x-app-layout>
