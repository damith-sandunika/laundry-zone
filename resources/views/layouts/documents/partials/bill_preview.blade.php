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
        <span class="inline-block border border-black font-extrabold text-xs px-3 py-1 rounded" x-text="badgeTextPaid"></span>
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

