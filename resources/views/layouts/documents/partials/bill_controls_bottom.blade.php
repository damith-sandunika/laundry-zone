<!-- 4. Payment Badges Custom Labels -->
<div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider">4. Payment Status Badge Labels</div>
    <div class="grid grid-cols-3 gap-2">
        <div>
            <label class="block text-[10px] font-bold text-slate-600 mb-1">Paid Status</label>
            <input type="text" name="custom_fields[badge_text_paid]" x-model="badgeTextPaid" value="{{ $badgeTextPaid }}" class="w-full text-xs font-semibold rounded-lg border-slate-200 bg-white">
        </div>
        <div>
            <label class="block text-[10px] font-bold text-slate-600 mb-1">Credit Status</label>
            <input type="text" name="custom_fields[badge_text_credit]" x-model="badgeTextCredit" value="{{ $badgeTextCredit }}" class="w-full text-xs font-semibold rounded-lg border-slate-200 bg-white">
        </div>
        <div>
            <label class="block text-[10px] font-bold text-slate-600 mb-1">Pickup Status</label>
            <input type="text" name="custom_fields[badge_text_pickup]" x-model="badgeTextPickup" value="{{ $badgeTextPickup }}" class="w-full text-xs font-semibold rounded-lg border-slate-200 bg-white">
        </div>
    </div>
</div>

<!-- 5. Additional Editable Notice & Custom Lines -->
<div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider flex items-center justify-between">
        <span>5. Custom Bill Notice & Editable Alerts</span>
        <button type="button" @click="addExtraLine()" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[11px] font-bold inline-flex items-center gap-1 shadow-sm transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            <span>Add Custom Text</span>
        </button>
    </div>

    <div>
        <label class="block text-[11px] font-bold text-slate-600 mb-1">Important Highlight / Top Notice</label>
        <input type="text" name="custom_fields[bill_notice]" x-model="billNotice" value="{{ $billNotice }}" placeholder="Optional: Highlighted notice printed right below order info" class="w-full text-xs rounded-xl border-slate-200 bg-white">
    </div>

    <div class="space-y-2 pt-2">
        <template x-for="(line, lIdx) in extraLines" :key="lIdx">
            <div class="flex items-center gap-2 bg-white p-2 rounded-xl border border-slate-200 shadow-2xs">
                <span class="text-xs font-bold text-slate-400 w-5 text-center" x-text="lIdx + 1"></span>
                <input type="text" x-model="line.text" @input="syncExtraLines()" class="flex-1 text-xs font-medium rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:ring-blue-500" placeholder="Enter custom notice or instruction here">
                <button type="button" @click="removeExtraLine(lIdx)" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Delete">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </template>
    </div>
</div>

<!-- 6. Footer, Terms & Branding -->
<div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider">6. Footer, Terms & Branding</div>
    <div>
        <label class="block text-[11px] font-bold text-slate-600 mb-1">Terms & Conditions</label>
        <textarea name="terms_and_conditions" rows="2" x-model="termsText" class="w-full text-xs rounded-xl border-slate-200 bg-white">{{ $termsText }}</textarea>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-[11px] font-bold text-slate-600 mb-1">Thank You Text</label>
            <input type="text" name="footer_text" x-model="footerText" value="{{ $footerText }}" class="w-full text-xs rounded-xl border-slate-200 bg-white">
        </div>
        <div>
            <label class="block text-[11px] font-bold text-slate-600 mb-1">Review Prompt</label>
            <input type="text" name="custom_fields[review_text]" x-model="reviewText" value="{{ $reviewText }}" class="w-full text-xs rounded-xl border-slate-200 bg-white">
        </div>
    </div>
    <div>
        <label class="block text-[11px] font-bold text-slate-600 mb-1">Software Branding</label>
        <input type="text" name="custom_fields[software_branding]" x-model="softwareBranding" value="{{ $softwareBranding }}" class="w-full text-xs rounded-xl border-slate-200 bg-white">
    </div>
</div>

