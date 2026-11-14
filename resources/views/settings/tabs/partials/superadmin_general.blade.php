<div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
    <div class="space-y-4">
        <div>
            <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Company Name (Global for all Bills)</label>
            <input type="text" name="company_name" x-model="formCompanyName" class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
        </div>
        <div class="pt-2">
            <label class="block text-xs font-bold text-gray-800 uppercase mb-1">Global Auto-Close Time</label>
            <p class="text-[11px] text-gray-500 mb-2">All branches will be automatically closed and their daily shift reports will be generated at this time.</p>
            @php $globalCloseTime = \App\Models\Branch::first()?->auto_close_time; @endphp
            <input type="time" name="auto_close_time" value="{{ $globalCloseTime ? \Carbon\Carbon::parse($globalCloseTime)->format('H:i') : '' }}" class="w-48 text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500 font-mono">
        </div>
        <div class="pt-4 mt-4 border-t border-gray-100">
            <label class="block text-xs font-bold text-gray-800 uppercase mb-1">Site Landing Page (Redirect)</label>
            <p class="text-[11px] text-gray-500 mb-2">
                When visitors open the website, redirect them to this URL path. Leave blank to show the default homepage.<br>
                Example: <code class="bg-gray-100 px-1 rounded">/customer/login</code> or <code class="bg-gray-100 px-1 rounded">/customer/book</code>
            </p>
            <input type="text" name="site_landing_page" value="{{ \App\Models\Setting::get('site_landing_page', '') }}" placeholder="Leave blank for default homepage" class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500 font-mono">
        </div>
    </div>
    <div class="space-y-4">
        <div class="pt-4 border-t border-gray-100 md:border-t-0 md:pt-0">
            <label class="flex items-start gap-2 cursor-pointer w-max max-w-full">
                <input type="checkbox" name="centralized_management" value="1" {{ \App\Models\Setting::get('centralized_management', '1') == '1' ? 'checked' : '' }} class="w-4 h-4 mt-0.5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <div>
                    <span class="text-sm font-semibold text-gray-700 block">Centralized Configuration Management</span>
                    <p class="text-[11px] text-gray-500 mt-1">If enabled, Super Admin manages Services, Packages, Extras, etc., for ALL branches.<br>If disabled, Branch Admins manage their own configurations locally.</p>
                </div>
            </label>
        </div>
        <div class="pt-4 mt-4 border-t border-gray-100">
            <label class="flex items-start gap-2 cursor-pointer w-max max-w-full">
                <input type="checkbox" name="auto_print_label" value="1" {{ \App\Models\Setting::get('auto_print_label', '0') == '1' ? 'checked' : '' }} class="w-4 h-4 mt-0.5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <div>
                    <span class="text-sm font-semibold text-gray-700 block">Auto Print Label on Order Completion</span>
                    <p class="text-[11px] text-gray-500 mt-1">If enabled, the label page will automatically open in a new tab when an order is marked as complete.</p>
                </div>
            </label>
        </div>
        <div class="pt-4 mt-4 border-t border-gray-100">
            <label class="flex items-start gap-2 cursor-pointer w-max max-w-full">
                <input type="checkbox" name="auto_print_pos_chit" value="1" {{ \App\Models\Setting::get('auto_print_pos_chit', '0') == '1' ? 'checked' : '' }} class="w-4 h-4 mt-0.5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <div>
                    <span class="text-sm font-semibold text-gray-700 block">Auto Print Chit on POS Order</span>
                    <p class="text-[11px] text-gray-500 mt-1">If enabled, the order chit (bill) will automatically open in a new tab when an order is placed from the POS system.</p>
                </div>
            </label>
        </div>
        <div class="pt-4 mt-4 border-t border-gray-100">
            <label class="flex items-start gap-2 cursor-pointer w-max max-w-full">
                <input type="checkbox" name="auto_print_workshop_chit" value="1" {{ \App\Models\Setting::get('auto_print_workshop_chit', '1') == '1' ? 'checked' : '' }} class="w-4 h-4 mt-0.5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <div>
                    <span class="text-sm font-semibold text-gray-700 block">Auto Print Job Chit on Workshop Start (To Wash)</span>
                    <p class="text-[11px] text-gray-500 mt-1">If enabled (<strong>Default: ON</strong>), clicking "Start" in the Workshop "To Wash" queue will automatically open the order chit in a new tab for printing.</p>
                </div>
            </label>
        </div>
        <div class="pt-4 mt-4 border-t border-gray-100">
            <label class="flex items-start gap-2 cursor-pointer w-max max-w-full">
                <input type="checkbox" name="chit_show_totals" value="1" {{ \App\Models\Setting::get('chit_show_totals', '0') == '1' ? 'checked' : '' }} class="w-4 h-4 mt-0.5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <div>
                    <span class="text-sm font-semibold text-gray-700 block">Show Payment Totals on Job Chit</span>
                    <p class="text-[11px] text-gray-500 mt-1">If enabled, the job chit will display the order total, amount paid, and balance due at the bottom.</p>
                </div>
            </label>
        </div>
        <div class="pt-4 mt-4 border-t border-gray-100">
            <label class="flex items-start gap-2 cursor-pointer w-max max-w-full">
                <input type="checkbox" name="allow_branch_admins_delete_orders" value="1" {{ \App\Models\Setting::get('allow_branch_admins_delete_orders', '0') == '1' ? 'checked' : '' }} class="w-4 h-4 mt-0.5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <div>
                    <span class="text-sm font-semibold text-gray-700 block">Allow Branch & Workshop Admins to Delete Orders</span>
                    <p class="text-[11px] text-gray-500 mt-1">If checked, Branch Admins and Workshop Admins can delete orders from History pages.</p>
                </div>
            </label>
        </div>
        <div class="pt-4 mt-4 border-t border-gray-100">
            <label class="flex items-start gap-2 cursor-pointer w-max max-w-full">
                <input type="checkbox" name="require_superadmin_approval_to_reopen_shift" value="1" {{ \App\Models\Setting::get('require_superadmin_approval_to_reopen_shift', '1') == '1' ? 'checked' : '' }} class="w-4 h-4 mt-0.5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <div>
                    <span class="text-sm font-semibold text-gray-700 block">Require Super Admin Approval to Re-Open Closed Shifts</span>
                    <p class="text-[11px] text-gray-500 mt-1"><strong class="text-slate-700">Default: ON.</strong> Branches can only open their shift once a day. Re-opening strictly requires Super Admin approval.</p>
                </div>
            </label>
        </div>
    </div>
</div>

