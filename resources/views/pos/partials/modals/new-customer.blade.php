<!-- ===================== Add New Customer Modal ===================== -->
        <div x-data="{ 
            open: false, 
            isSaving: false,
            async createAndSelectCustomer() {
                let title = document.getElementById('nc-title').value;
                let name = document.getElementById('nc-name').value;
                let phone = document.getElementById('nc-phone').value;
                let email = document.getElementById('nc-email').value;
                let address = document.getElementById('nc-address').value;
                let date_of_birth = document.getElementById('nc-dob').value;
                let nic = document.getElementById('nc-nic').value;
                let customer_mark = document.getElementById('nc-mark').value;
                if(!name || !phone) return alert('Name and phone are required.');
                
                this.isSaving = true;
                try {
                    let res = await fetch('/customers', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ title, name, phone, email, address, date_of_birth, nic, customer_mark, source: 'pos' })
                    });
                    let data = await res.json();
                    if(data.success) {
                        window.dispatchEvent(new CustomEvent('customer-created', { detail: data.customer }));
                        this.open = false;
                        document.getElementById('new-customer-form').reset();
                    } else {
                        alert('Error: ' + (data.message || 'Validation failed.'));
                    }
                } catch(e) {
                    alert('Network error.');
                } finally {
                    this.isSaving = false;
                }
            }
        }"
            x-on:open-customer-modal.window="open = true; document.getElementById('nc-name').focus(); setTimeout(()=>document.getElementById('nc-name').focus(), 100);">
            <template x-teleport="body">
                <div x-show="open" x-cloak class="fixed inset-0 flex items-center justify-center p-4" style="z-index: 99999;">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" x-transition.opacity @click="open = false"></div>
            
            <div class="relative bg-white rounded-[24px] shadow-2xl w-full max-w-md z-10 animate-slide-up" @click.stop>
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="font-black text-lg text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                        New Customer
                    </h3>
                    <button @click="open = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form id="new-customer-form" class="p-6 space-y-4">
                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-1">
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Title</label>
                            <select id="nc-title" class="w-full bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl px-4 py-3 text-[14px] font-black text-slate-800 outline-none transition-all">
                                <option value="">None</option>
                                <option value="Mr.">Mr.</option>
                                <option value="Mrs.">Mrs.</option>
                                <option value="Ms.">Ms.</option>
                                <option value="Miss">Miss</option>
                                <option value="Dr.">Dr.</option>
                                <option value="Prof.">Prof.</option>
                                <option value="Rev.">Rev.</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Full Name</label>
                            <input type="text" id="nc-name" required placeholder="e.g. Kamal Perera"
                                class="w-full bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl px-4 py-3 text-[14px] font-black text-slate-800 outline-none transition-all placeholder:text-slate-400">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Phone Number</label>
                            <input type="text" id="nc-phone" required placeholder="e.g. 077 123 4567"
                                class="w-full bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl px-4 py-3 text-[14px] font-black text-slate-800 outline-none transition-all placeholder:text-slate-400">
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Email (Optional)</label>
                            <input type="email" id="nc-email" placeholder="e.g. name@example.com"
                                class="w-full bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl px-4 py-3 text-[14px] font-black text-slate-800 outline-none transition-all placeholder:text-slate-400">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Address (Optional)</label>
                        <input type="text" id="nc-address" placeholder="Home address"
                            class="w-full bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl px-4 py-3 text-[14px] font-black text-slate-800 outline-none transition-all placeholder:text-slate-400">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Date of Birth (Optional)</label>
                            <input type="date" id="nc-dob"
                                class="w-full bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl px-4 py-3 text-[14px] font-black text-slate-800 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">NIC (Optional)</label>
                            <input type="text" id="nc-nic" placeholder="e.g. 200012345678"
                                class="w-full bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl px-4 py-3 text-[14px] font-black text-slate-800 outline-none transition-all placeholder:text-slate-400">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Customer Mark / Note (Optional)</label>
                        <input type="text" id="nc-mark" placeholder="e.g. 01 (For manual routing)"
                            class="w-full bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl px-4 py-3 text-[14px] font-black text-slate-800 outline-none transition-all placeholder:text-slate-400">
                    </div>
                </form>
                <div class="px-6 py-5 bg-white border-t border-slate-100 flex gap-3">
                    <button @click="open = false" class="flex-1 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[14px] font-black rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button @click="createAndSelectCustomer()" id="save-customer-btn" :disabled="isSaving"
                        class="flex-1 py-3.5 bg-green-600 hover:bg-green-700 text-white text-[14px] font-black rounded-xl shadow-lg shadow-green-500/30 transition-all transform hover:-translate-y-0.5"
                        x-text="isSaving ? 'Saving...' : 'Save Customer'">
                    </button>
                </div>
            </div>
                </div>
            </template>
        </div>
