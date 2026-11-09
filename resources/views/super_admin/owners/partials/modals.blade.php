<!-- ══════════ OWNER MODALS (LIGHT THEME) ══════════ -->

<!-- 1. Reset Password Modal -->
<div x-show="resetModal" style="display:none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
    <div class="w-full max-w-md bg-white border border-slate-200 rounded-3xl p-6 shadow-2xl relative" @click.away="resetModal = false">
        <h3 class="text-base font-bold text-slate-900">Reset Password for Owner</h3>
        <p class="text-xs text-slate-400 mt-1">This will update the owner's credentials in both Landlord and Tenant databases.</p>

        <form :action="`/saas-admin/owners/${activeOwner.id}/reset-password`" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">New Password</label>
                <input type="text" name="new_password" required placeholder="Enter new strong password"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" @click="resetModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-sm">Save New Password</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Edit Owner Modal -->
<div x-show="editModal" style="display:none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
    <div class="w-full max-w-md bg-white border border-slate-200 rounded-3xl p-6 shadow-2xl relative" @click.away="editModal = false">
        <h3 class="text-base font-bold text-slate-900">Edit Owner Profile</h3>
        <p class="text-xs text-slate-400 mt-1">Update contact details for this company owner.</p>

        <form :action="`/saas-admin/owners/${activeOwner.id}`" method="POST" class="mt-4 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Owner Full Name</label>
                <input type="text" name="name" :value="activeOwner.name" required
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Email Address</label>
                <input type="email" name="email" :value="activeOwner.email" required
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Phone Number</label>
                <input type="text" name="phone" :value="activeOwner.phone || ''"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm">Save Changes</button>
            </div>
        </form>
    </div>
</div>
