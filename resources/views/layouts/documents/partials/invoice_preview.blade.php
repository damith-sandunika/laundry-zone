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
            <div class="inline-block bg-emerald-600 text-white font-bold text-xs px-3 py-1 rounded-full" x-text="badgeTextPaid"></div>
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

