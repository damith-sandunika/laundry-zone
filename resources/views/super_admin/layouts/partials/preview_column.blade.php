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

    <!-- PREVIEW 1: THERMAL BILL (80mm) -->
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
                <div class="flex justify-between"><span class="text-slate-500">Bill No:</span><span class="font-bold">#ORD-98421</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Date:</span><span>{{ date('Y-m-d h:i A') }}</span></div>
                <template x-if="showCustomerInfo">
                    <div class="flex justify-between font-bold"><span>Customer:</span><span>Kamal Perera (0771234567)</span></div>
                </template>
            </div>

            <table class="w-full text-left my-2 text-[10px]">
                <thead>
                    <tr class="border-b border-slate-400 text-slate-600 font-bold">
                        <th class="pb-1">Item</th><th class="pb-1 text-center">Qty</th><th class="pb-1 text-right">Price</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr><td class="py-1">Formal Shirt (Wash & Iron)</td><td class="py-1 text-center font-bold">3</td><td class="py-1 text-right font-bold">Rs. 1,050.00</td></tr>
                    <tr><td class="py-1">Trousers (Dry Clean)</td><td class="py-1 text-center font-bold">2</td><td class="py-1 text-right font-bold">Rs. 1,200.00</td></tr>
                </tbody>
            </table>

            <div class="pt-2 border-t-2 border-slate-800 space-y-1 text-[11px]">
                <div class="flex justify-between font-black text-sm">
                    <span>TOTAL DUE:</span><span :style="`color: ${accentColor};`">Rs. 2,250.00</span>
                </div>
                <div class="flex justify-between text-[10px]"><span class="text-slate-500">Payment:</span><span class="font-bold text-emerald-700">Cash / Paid</span></div>
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

    <!-- PREVIEW 2: PAYMENT RECEIPT -->
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
                <div class="flex justify-between py-1 border-b border-slate-100"><span class="text-slate-500">Order Reference:</span><span class="font-bold font-mono">#ORD-98421</span></div>
                <div class="flex justify-between py-1 border-b border-slate-100"><span class="text-slate-500">Customer Name:</span><span class="font-bold">Kamal Perera</span></div>
                <div class="flex justify-between py-1 border-b border-slate-100"><span class="text-slate-500">Payment Method:</span><span class="font-bold">Direct Bank Transfer</span></div>
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

    <!-- PREVIEW 3: INVOICE PDF (A4) -->
    <template x-if="activeTab === 'invoice'">
        <div class="bg-white shadow-2xl rounded-sm p-8 text-slate-900 border border-slate-300 w-[420px] transition-all relative overflow-hidden"
            :style="`font-family: ${fontFamily}; font-size: ${fontSize}; color: ${primaryColor};`">
            <div class="absolute top-0 left-0 right-0 h-2" :style="`background-color: ${accentColor};`"></div>
            <div class="flex justify-between items-start pt-2 pb-6 border-b border-slate-200">
                <div>
                    <h3 class="text-xl font-black uppercase tracking-tight" x-text="'{{ $selectedTenant->name ?? 'Laundry Company' }}'"></h3>
                    <p class="text-xs text-slate-500 font-semibold" x-text="headerSubtitle"></p>
                </div>
                <div class="text-right">
                    <h2 class="text-2xl font-black text-slate-300 uppercase tracking-wider" x-text="headerTitle"></h2>
                    <p class="text-xs font-mono font-bold" :style="`color: ${accentColor};`">INV-2026-0089</p>
                </div>
            </div>
            <table class="w-full text-left my-4 text-xs">
                <thead>
                    <tr class="bg-slate-900 text-white text-[10px] uppercase font-bold">
                        <th class="p-2 rounded-l">Description</th><th class="p-2 text-center">Qty</th><th class="p-2 text-right rounded-r">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[11px]">
                    <tr><td class="p-2 font-medium">Bedsheet Laundering</td><td class="p-2 text-center font-bold">50</td><td class="p-2 text-right font-bold">Rs. 15,000.00</td></tr>
                </tbody>
            </table>
            <template x-if="showFooter">
                <div class="mt-8 pt-4 border-t border-slate-100 text-[10px] text-slate-400 text-center space-y-1">
                    <p x-text="termsText"></p>
                    <p class="font-bold text-slate-600" x-text="footerText"></p>
                </div>
            </template>
        </div>
    </template>
</div>

