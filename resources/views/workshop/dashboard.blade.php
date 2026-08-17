<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <h2 class="ph-title">Workshop Dashboard</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Manage incoming orders, processing, and dispatches</p>
            </div>
            <a href="{{ route('workshop.history') }}" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-lg hover:bg-slate-200">View History</a>
        </div>
    </x-slot>

    <div x-data="{ 
        currentTab: 'arrival_processing',
        selectedBranch: '',
        selectedDispatches: [],
        showBulkModal: false,
        bulkStatus: '',
        dispatchesList: JSON.parse(atob('{{ base64_encode(json_encode($dispatches)) }}')),
        
        toggleSelectAll(e) {
            if (e.target.checked) {
                this.selectedDispatches = this.filteredDispatches.map(d => d.id);
            } else {
                this.selectedDispatches = [];
            }
        },
        
        get filteredDispatches() {
            let list = this.dispatchesList;
            if (this.selectedBranch !== '') {
                list = list.filter(d => d.branch_id == this.selectedBranch);
            }
            if (this.currentTab === 'arrival_processing') {
                return list.filter(d => d.status === 'dispatched_to_workshop' || d.status === 'processing');
            }
            return list.filter(d => d.status === this.currentTab);
        },

        get counts() {
            let list = this.dispatchesList;
            if (this.selectedBranch !== '') {
                list = list.filter(d => d.branch_id == this.selectedBranch);
            }
            return {
                arrival_processing: list.filter(d => d.status === 'dispatched_to_workshop' || d.status === 'processing').length,
                done: list.filter(d => d.status === 'completed_at_workshop').length,
                returned: list.filter(d => d.status === 'returned_to_branch').length
            };
        },

        async workshopAction(dispatchId, action) {
            try {
                const res = await fetch(`/workshop/dispatches/${dispatchId}/${action}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) window.location.reload();
            } catch (err) {
                alert('Connection error');
            }
        },
        
        async submitBulkUpdate() {
            if (!this.bulkStatus) { alert('Please select an action'); return; }
            if (this.selectedDispatches.length === 0) { alert('No orders selected'); return; }
            
            try {
                const res = await fetch('{{ route('workshop.bulkStatus') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({
                        dispatch_ids: this.selectedDispatches,
                        status: this.bulkStatus
                    })
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Error updating statuses');
                }
            } catch (err) {
                alert('Connection error');
            }
        }
    }">

        <!-- STAT CARDS / TABS -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 md:gap-4 mb-4">
            <!-- Arrival & Processing -->
            <div @click="currentTab = 'arrival_processing'"
                :class="currentTab === 'arrival_processing' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md'"
                class="flex flex-col items-center justify-center p-2 md:px-6 md:py-5 rounded-xl border-2 cursor-pointer transition-all duration-300">
                <span class="text-xs md:text-sm font-black mt-1" :class="currentTab === 'arrival_processing' ? 'text-orange-700' : 'text-gray-900'" x-text="counts.arrival_processing"></span>
                <span class="text-[10px] md:text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Arrival & Processing</span>
            </div>

            <!-- Done -->
            <div @click="currentTab = 'completed_at_workshop'"
                :class="currentTab === 'completed_at_workshop' ? 'border-green-500 bg-green-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md'"
                class="flex flex-col items-center justify-center p-2 md:px-6 md:py-5 rounded-xl border-2 cursor-pointer transition-all duration-300">
                <span class="text-xs md:text-sm font-black mt-1" :class="currentTab === 'completed_at_workshop' ? 'text-green-700' : 'text-gray-900'" x-text="counts.done"></span>
                <span class="text-[10px] md:text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Done</span>
            </div>

            <!-- Dispatched Back -->
            <div @click="currentTab = 'returned_to_branch'"
                :class="currentTab === 'returned_to_branch' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md'"
                class="flex flex-col items-center justify-center p-2 md:px-6 md:py-5 rounded-xl border-2 cursor-pointer transition-all duration-300">
                <span class="text-xs md:text-sm font-black mt-1" :class="currentTab === 'returned_to_branch' ? 'text-purple-700' : 'text-gray-900'" x-text="counts.returned"></span>
                <span class="text-[10px] md:text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Dispatched</span>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <button x-show="selectedDispatches.length > 0" @click="showBulkModal = true"
                    class="px-4 py-2 bg-blue-600 text-white font-bold rounded-lg text-sm hover:bg-blue-700 transition-colors shadow-sm flex items-center gap-2">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Bulk Action (<span x-text="selectedDispatches.length"></span>)
                </button>
            </div>
            <select x-model="selectedBranch" class="px-4 py-2 pr-8 border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 outline-none focus:border-blue-500">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="bg-white rounded-md border border-gray-100 shadow-sm overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left" style="border-collapse:collapse;">
                    <thead style="background:#f8fafc;border-bottom:1.5px solid #e2e8f0;">
                        <tr>
                            <th style="padding:14px 20px;width:40px;">
                                <input type="checkbox" @change="toggleSelectAll($event)" :checked="selectedDispatches.length === filteredDispatches.length && filteredDispatches.length > 0" style="cursor:pointer;">
                            </th>
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Order Info</th>
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Dispatch Time</th>
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Branch</th>
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Status</th>
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="dispatch in filteredDispatches" :key="dispatch.id">
                            <tr style="background:white;border-bottom:1px solid #f1f5f9;transition:all 0.1s;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                                <td style="padding:12px 20px;">
                                    <input type="checkbox" :value="dispatch.id" x-model="selectedDispatches" style="cursor:pointer;">
                                </td>
                                <td style="padding:12px 20px;">
                                    <span style="font-size:13px;font-weight:800;color:#1d4ed8;display:block;" x-text="'#' + dispatch.order.order_number"></span>
                                    <div style="margin-top:2px;display:flex;flex-direction:column;gap:1px;">
                                        <span style="font-weight:700;color:#0f172a;" x-text="dispatch.order.customer ? dispatch.order.customer.name : 'Unknown Customer'"></span>
                                    </div>
                                </td>
                                <td style="padding:12px 20px;font-weight:600;color:#475569;">
                                    <span style="display:block;" x-text="new Date(dispatch.dispatched_at).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'})"></span>
                                    <span style="font-size:11px;color:#94a3b8;font-weight:500;" x-text="new Date(dispatch.dispatched_at).toLocaleTimeString('en-US', {hour:'2-digit', minute:'2-digit'})"></span>
                                </td>
                                <td style="padding:12px 20px;font-weight:700;color:#4f46e5;">
                                    <span x-text="dispatch.branch ? dispatch.branch.name : 'Unknown'"></span>
                                </td>
                                <td style="padding:12px 20px;">
                                    <span :style="
                                        dispatch.status === 'dispatched_to_workshop' ? 'background:#fef3c7;color:#d97706;border:1px solid #fde68a;' :
                                        dispatch.status === 'received_at_workshop' ? 'background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;' :
                                        dispatch.status === 'processing' ? 'background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;' :
                                        dispatch.status === 'completed_at_workshop' ? 'background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;' :
                                        dispatch.status === 'returned_to_branch' ? 'background:#f3e8ff;color:#7e22ce;border:1px solid #e9d5ff;' :
                                        'background:#fef2f2;color:#dc2626;border:1px solid #fecaca;'
                                    " style="font-size:11px;font-weight:800;padding:4px 9px;border-radius:6px;text-transform:uppercase;letter-spacing:0.04em;">
                                        <span x-text="dispatch.status.replace(/_/g, ' ')"></span>
                                    </span>
                                </td>
                                <td style="padding:12px 20px;text-align:center;">
                                    <div style="display:inline-flex;gap:6px;align-items:center;justify-content:center;width:100%;">
                                        <!-- Actions based on status -->
                                        <template x-if="dispatch.status === 'dispatched_to_workshop'">
                                            <button @click="workshopAction(dispatch.id, 'start-processing')"
                                                style="padding:5px 10px;background:#eab308;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(234,179,8,0.3);transition:transform 0.1s;"
                                                onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                                Receive & Start Washing
                                            </button>
                                        </template>

                                        <template x-if="dispatch.status === 'processing'">
                                            <button @click="workshopAction(dispatch.id, 'complete')"
                                                style="padding:5px 10px;background:#10b981;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(16,185,129,0.3);transition:transform 0.1s;"
                                                onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                                Mark Done
                                            </button>
                                        </template>

                                        <template x-if="dispatch.status === 'completed_at_workshop'">
                                            <button @click="workshopAction(dispatch.id, 'return')"
                                                style="padding:5px 10px;background:#8b5cf6;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(139,92,246,0.3);transition:transform 0.1s;"
                                                onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                                Move to Dispatch
                                            </button>
                                        </template>
                                        
                                        <template x-if="dispatch.status === 'returned_to_branch'">
                                            <span style="font-size:11px;color:#94a3b8;font-weight:600;">Awaiting Branch...</span>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredDispatches.length === 0">
                            <td colspan="5" style="padding:40px;text-align:center;color:#94a3b8;font-size:13px;font-weight:500;">
                                No records found in this category.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── Bulk Status Update Modal ── -->
        <div x-cloak x-show="showBulkModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-[4px] p-4">
            <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:400px;overflow:hidden;" @click.away="showBulkModal = false">
                <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                    <h3 style="font-size:16px;font-weight:900;color:#0f172a;margin:0;">Bulk Action</h3>
                    <button @click="showBulkModal = false" style="background:none;border:none;cursor:pointer;color:#94a3b8;">✕</button>
                </div>
                <div style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Action to Perform</label>
                        <select x-model="bulkStatus" style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;">
                            <option value="">-- Select Action --</option>
                            <option value="processing" x-show="currentTab === 'arrival_processing'">👕 Receive & Start Washing</option>
                            <option value="completed_at_workshop" x-show="currentTab === 'arrival_processing'">✅ Mark Done</option>
                            <option value="returned_to_branch" x-show="currentTab === 'completed_at_workshop'">🚚 Move to Dispatch</option>
                        </select>
                    </div>
                    <div style="display:flex;gap:10px;">
                        <button @click="showBulkModal = false" style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                        <button @click="submitBulkUpdate" style="flex:1;padding:11px;background:#1d4ed8;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;">Apply Changes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
