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

