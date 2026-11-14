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
                <div class="text-xs font-bold text-center mt-1">VIP - MR. KAMAL PERERA<br>0771234567</div>
            </div>
            <div class="date font-black text-xs my-2 text-center uppercase">{{ date('d M Y h:i A') }}</div>
            <div class="items my-2 space-y-2 text-xs">
                <div>
                    <div class="text-[9px] font-bold uppercase">&bull; Gents Wear > Wash & Iron</div>
                    <div class="flex justify-between font-bold text-xs"><span class="ml-2">Formal Shirts</span><span>x3</span></div>
                </div>
                <div>
                    <div class="text-[9px] font-bold uppercase">&bull; Dry Cleaning</div>
                    <div class="flex justify-between font-bold text-xs"><span class="ml-2">Trousers</span><span>x2</span></div>
                    <div class="text-[10px] ml-3 text-amber-600">[Express (50%)]</div>
                </div>
                <div class="flex justify-between font-black text-xs pt-1 border-t border-black">
                    <span>Total Qty</span><span>x5</span>
                </div>
            </div>
            <div class="border-t border-black my-2 pt-2 pb-2 text-xs space-y-1">
                <div class="flex justify-between text-xs"><span>Subtotal</span><span>Rs. 2,250.00</span></div>
                <div class="flex justify-between font-black text-xs border-t border-black pt-1"><span>TOTAL</span><span>Rs. 2,250.00</span></div>
                <div class="flex justify-between text-xs"><span>Paid</span><span>Rs. 2,250.00</span></div>
                <div class="flex justify-between font-bold text-xs text-emerald-700"><span>✓ Fully Paid</span><span>Rs. 2,250.00</span></div>
            </div>
            <div class="border-t border-black pt-2 text-xs font-bold">
                <div>Special Note :</div>
                <div class="border-b border-black h-4 mt-1"></div>
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
                </div>
                <div class="text-right">
                    <h2 class="text-2xl font-black text-slate-300 uppercase tracking-wider" x-text="headerTitle"></h2>
                    <p class="text-xs font-mono font-bold" :style="`color: ${accentColor};`">INV-2026-0089</p>
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

