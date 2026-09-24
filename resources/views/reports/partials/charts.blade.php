    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <h3 class="text-[10px] md:text-sm font-black text-slate-900 mb-4">Daily Revenue Trend</h3>
            <div id="revenueChart" class="h-[300px] w-full"></div>
        </div>
        <!-- Payment Breakdown Chart -->
        <div class="bg-white/80 backdrop-blur-xl border border-slate-200/60 rounded-3xl p-6 shadow-sm flex flex-col h-[350px]">
            <h3 class="text-[10px] md:text-sm font-black text-slate-900 mb-4">Payment Breakdown</h3>
            <div class="flex-1 flex items-center justify-center">
                <div id="paymentChart" class="w-full"></div>
            </div>
        </div>
    </div>
