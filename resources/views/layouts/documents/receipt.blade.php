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

    @php
        $cfg = $layout->custom_fields ?? [];
        $showLogo = $layout->show_logo;
        $logoSize = $cfg['logo_size'] ?? '45';
        $paperWidth = $cfg['paper_width'] ?? '300px';
        $orderNoSize = $cfg['order_no_size'] ?? '20px';
        $showCustomerPhone = $cfg['show_customer_phone'] ?? '1';
        $showDueDate = $cfg['show_due_date'] ?? '1';
        $showTotals = $cfg['show_totals'] ?? '1';
        $showSpecialNotes = $cfg['show_special_notes'] ?? '1';
        $notesLabel = $cfg['notes_label'] ?? 'Special Note :';
        $notesLineCount = (int)($cfg['notes_line_count'] ?? 2);
        $showProcessList = $cfg['show_process_list'] ?? '1';

        // Dynamic process checklist items
        $savedProcesses = $cfg['process_items'] ?? null;
        if (!is_array($savedProcesses) || empty($savedProcesses)) {
            $savedProcesses = [
                ['name' => 'Collected', 'suffix' => 'Kamal'],
                ['name' => 'Wash', 'suffix' => '________'],
                ['name' => 'Iron', 'suffix' => '________'],
                ['name' => 'Quality check', 'suffix' => '________'],
                ['name' => 'Final inspection', 'suffix' => '________'],
            ];
        }

        $headerTitle = $layout->header_title ?: ($tenant->name ?? 'Laundry Zone');
        $headerSubtitle = $layout->header_subtitle ?: 'Workshop Job Card';
    @endphp

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
                    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider">
                        2. Fonts & Text Sizes
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Font Family</label>
                            <select name="font_family" x-model="fontFamily" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-white">
                                <option value="'Helvetica Neue', Helvetica, Arial, sans-serif" {{ str_contains($layout->font_family, 'Helvetica') ? 'selected' : '' }}>Helvetica / Arial</option>
                                <option value="'Inter', sans-serif" {{ str_contains($layout->font_family, 'Inter') ? 'selected' : '' }}>Inter</option>
                                <option value="'Plus Jakarta Sans', sans-serif" {{ str_contains($layout->font_family, 'Plus Jakarta') ? 'selected' : '' }}>Plus Jakarta Sans</option>
                                <option value="'Courier New', monospace" {{ str_contains($layout->font_family, 'Courier') ? 'selected' : '' }}>Courier Mono</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Base Font Size</label>
                            <select name="font_size" x-model="fontSize" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-white">
                                <option value="11px" {{ $layout->font_size === '11px' ? 'selected' : '' }}>11px (Compact)</option>
                                <option value="12px" {{ $layout->font_size === '12px' ? 'selected' : '' }}>12px (Standard)</option>
                                <option value="13px" {{ $layout->font_size === '13px' ? 'selected' : '' }}>13px (Large)</option>
                                <option value="14px" {{ $layout->font_size === '14px' ? 'selected' : '' }}>14px (Extra Large)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Order # Size</label>
                            <select name="custom_fields[order_no_size]" x-model="orderNoSize" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-white">
                                <option value="16px" {{ $orderNoSize === '16px' ? 'selected' : '' }}>16px</option>
                                <option value="18px" {{ $orderNoSize === '18px' ? 'selected' : '' }}>18px</option>
                                <option value="20px" {{ $orderNoSize === '20px' ? 'selected' : '' }}>20px (Default)</option>
                                <option value="24px" {{ $orderNoSize === '24px' ? 'selected' : '' }}>24px (Bold Big)</option>
                                <option value="28px" {{ $orderNoSize === '28px' ? 'selected' : '' }}>28px (Jumbo)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 3. Section Toggles -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
                    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider">
                        3. Section Visibility
                    </div>
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

                <!-- 4. Notes & Checklist Customization (Fully Dynamic Add/Remove) -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-4">
                    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider flex items-center justify-between">
                        <span>4. Special Note & Dynamic Workshop Checklist</span>
                        <label class="flex items-center gap-1.5 cursor-pointer text-xs font-semibold text-slate-600">
                            <input type="checkbox" name="custom_fields[show_process_list]" value="1" x-model="showProcessList" {{ $showProcessList == '1' ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500">
                            <span>Show Checklist</span>
                        </label>
                    </div>

                    <!-- Special Notes Customizer -->
                    <div x-show="showSpecialNotes" class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Special Note Label</label>
                            <input type="text" name="custom_fields[notes_label]" x-model="notesLabel" value="{{ $notesLabel }}"
                                class="w-full text-xs font-medium rounded-xl border-slate-200 bg-white" placeholder="e.g. Special Note :">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Blank Lines</label>
                            <select name="custom_fields[notes_line_count]" x-model="notesLineCount" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-white">
                                <option value="1" {{ $notesLineCount === 1 ? 'selected' : '' }}>1 Line</option>
                                <option value="2" {{ $notesLineCount === 2 ? 'selected' : '' }}>2 Lines</option>
                                <option value="3" {{ $notesLineCount === 3 ? 'selected' : '' }}>3 Lines</option>
                                <option value="4" {{ $notesLineCount === 4 ? 'selected' : '' }}>4 Lines</option>
                            </select>
                        </div>
                    </div>

                    <!-- Dynamic Checklist Steps (Add / Edit / Remove) -->
                    <div x-show="showProcessList" class="space-y-3 pt-2 border-t border-slate-200/60">
                        <div class="flex items-center justify-between">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                                Checklist Process Steps (<span x-text="processItems.length"></span> steps)
                            </label>
                            <button type="button" @click="addProcessItem()"
                                class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold inline-flex items-center gap-1 shadow-sm transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                <span>Add Step</span>
                            </button>
                        </div>

                        <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                            <template x-for="(item, idx) in processItems" :key="idx">
                                <div class="flex items-center gap-2 bg-white p-2 rounded-xl border border-slate-200 shadow-2xs">
                                    <span class="text-xs font-bold text-slate-400 w-5 text-center" x-text="idx + 1"></span>
                                    <input type="text" x-model="item.name" @input="syncJson()"
                                        class="flex-1 text-xs font-bold rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:ring-blue-500"
                                        placeholder="Process Label (e.g. Wash)">
                                    
                                    <span class="text-xs font-bold text-slate-400">:</span>
                                    
                                    <input type="text" x-model="item.suffix" @input="syncJson()"
                                        class="w-28 text-xs font-mono rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:ring-blue-500"
                                        placeholder="Sign / Line">
                                    
                                    <button type="button" @click="removeProcessItem(idx)"
                                        class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Delete Step">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="primary_color" value="{{ $layout->primary_color ?? '#000000' }}">
                <input type="hidden" name="accent_color" value="{{ $layout->accent_color ?? '#059669' }}">

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                    <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                        Save All Layout Changes
                    </button>
                    <button type="button" onclick="if(confirm('Reset all fields to original defaults?')) document.getElementById('resetForm').submit();"
                        class="text-xs font-bold text-slate-400 hover:text-rose-600 transition">
                        Reset Default
                    </button>
                </div>
            </form>

            <form id="resetForm" action="{{ route('tenant.layouts.reset') }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="type" value="receipt">
            </form>
        </div>

        <!-- Live Preview Column: Dynamic Replica of chit.blade.php -->
        <div class="lg:col-span-6 bg-slate-100 rounded-2xl border border-slate-200/90 p-6 flex flex-col items-center">
            <div class="w-full flex items-center justify-between mb-3 text-xs">
                <span class="font-extrabold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live Job Card Preview
                </span>
                <span class="font-mono text-slate-400 font-bold" x-text="paperWidth"></span>
            </div>

            <!-- Chit Wrapper -->
            <div class="bg-white border border-slate-300 shadow-xl rounded-sm p-4 text-slate-900 text-left transition-all"
                :style="`font-family: ${fontFamily}; font-size: ${fontSize}; width: ${paperWidth}; color: #000; font-weight: 600;`">

                <!-- Header & Optional Logo -->
                <div class="text-center pb-2 border-b border-black">
                    <template x-if="showLogo">
                        <div class="mx-auto mb-1 flex items-center justify-center">
                            <img src="{{ $tenant && $tenant->logo_path ? asset($tenant->logo_path) : asset('images/laundry_main_logo.png') }}"
                                :style="`max-height: ${logoSize}px; max-width: ${logoSize}px; object-contain: true;`"
                                onerror="this.style.display='none'">
                        </div>
                    </template>
                    <div class="font-black" :style="`font-size: ${orderNoSize};`">#10245</div>
                    <div class="text-xs font-bold uppercase mt-1">MR. KAMAL PERERA</div>
                    <div x-show="showCustomerPhone" class="text-[11px] font-mono text-slate-700">077-1234567</div>
                    <div class="text-[10px] text-slate-500 font-bold mt-0.5" x-text="headerSubtitle"></div>
                </div>

                <!-- Due Date Box -->
                <div x-show="showDueDate" class="text-center py-1.5 my-2 border-y border-dashed border-black font-black text-xs uppercase tracking-wide">
                    DUE: 14 NOV 2026 05:00 PM
                </div>

                <!-- Items Sample -->
                <div class="py-2 border-b border-black text-xs space-y-1.5">
                    <div class="text-[10px] text-slate-500 uppercase font-black">&bull; GENTS WEAR &gt; SHIRTS</div>
                    <div class="flex justify-between items-center font-bold">
                        <span class="pl-2">Formal Long Sleeve Shirt (Iron Only)</span>
                        <span class="font-black">x3</span>
                    </div>
                    <div class="text-[10px] text-slate-500 uppercase font-black pt-1">&bull; DRY CLEANING &gt; SUITS</div>
                    <div class="flex justify-between items-center font-bold">
                        <span class="pl-2">2-Piece Executive Suit</span>
                        <span class="font-black">x1</span>
                    </div>
                    <div class="flex justify-between items-center font-black pt-2 border-t border-black text-xs">
                        <span>Total Qty</span>
                        <span>x4</span>
                    </div>
                </div>

                <!-- Totals Section -->
                <div x-show="showTotals" class="py-2 border-b border-black text-xs space-y-1">
                    <div class="flex justify-between text-slate-700">
                        <span>Subtotal</span>
                        <span>Rs. 2,450.00</span>
                    </div>
                    <div class="flex justify-between text-slate-700">
                        <span>Discount</span>
                        <span>- Rs. 200.00</span>
                    </div>
                    <div class="flex justify-between font-black text-sm pt-1 border-t border-slate-300">
                        <span>TOTAL</span>
                        <span>Rs. 2,250.00</span>
                    </div>
                    <div class="flex justify-between text-slate-700">
                        <span>Paid</span>
                        <span>Rs. 2,250.00</span>
                    </div>
                    <div class="flex justify-between font-black text-emerald-700 pt-1">
                        <span>✓ Fully Paid</span>
                        <span>Rs. 2,250.00</span>
                    </div>
                </div>

                <!-- Special Notes Section -->
                <div x-show="showSpecialNotes" class="py-2 border-b border-black text-xs">
                    <div class="font-black mb-1" x-text="notesLabel"></div>
                    <template x-for="i in notesLineCount" :key="i">
                        <div class="border-b border-dotted border-black/40 h-5 mb-1"></div>
                    </template>
                </div>

                <!-- Dynamic Checklist Process Steps -->
                <div x-show="showProcessList" class="py-3 text-xs space-y-3 font-black">
                    <template x-for="(proc, pIdx) in processItems" :key="pIdx">
                        <div class="flex justify-between items-center">
                            <span x-text="proc.name + ' :'"></span>
                            <span class="font-mono text-slate-600" x-text="proc.suffix"></span>
                        </div>
                    </template>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
