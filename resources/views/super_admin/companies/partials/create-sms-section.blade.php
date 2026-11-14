<!-- ══════════ SECTION 4: PER-COMPANY SMS & BRANDED COMMUNICATIONS ══════════ -->
<div class="rounded-3xl bg-white border border-slate-200/80 p-6 md:p-8 space-y-6 shadow-sm">
    <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">4</div>
        <div>
            <h2 class="text-base font-bold text-slate-900">Custom SMS Gateway & Brand Masking</h2>
            <p class="text-xs text-slate-400">Configure isolated SMS gateway parameters and Sender Mask for this laundry company.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">SMS Sender Mask (Header)</label>
            <input type="text" name="sms_mask" value="{{ old('sms_mask') }}" placeholder="e.g. CLEANWAVE (Max 11 chars)" maxlength="11"
                class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-bold uppercase text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            <p class="text-[11px] text-slate-400 mt-1.5">Displayed as the sender on customer mobile phones.</p>
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">SMS Gateway Provider</label>
            <select name="sms_gateway" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                <option value="textware" selected>Textware (api.textware.lk)</option>
                <option value="mobitel">Mobitel mBiz Gateway</option>
                <option value="dialog">Dialog Enterprise SMS</option>
                <option value="custom">Generic REST API</option>
                <option value="mock">Simulated / Mock Gateway (Testing)</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">SMS API Key / Token</label>
            <input type="password" name="sms_api_key" value="{{ old('sms_api_key') }}" placeholder="Enter Gateway API Key"
                class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">SMS API Endpoint URL (Optional)</label>
            <input type="url" name="sms_api_url" value="{{ old('sms_api_url') }}" placeholder="https://api.textware.lk/sms/send"
                class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Email Sender Name</label>
            <input type="text" name="mail_from_name" value="{{ old('mail_from_name') }}" placeholder="e.g. CleanWave Laundry"
                class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Email Sender Address</label>
            <input type="email" name="mail_from_address" value="{{ old('mail_from_address') }}" placeholder="e.g. notifications@cleanwave.lk"
                class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
        </div>
    </div>
</div>
