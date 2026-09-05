<x-app-layout>
    <x-slot name="header">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <h2 class="ph-title">Global Branches</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Manage all laundry branches across the network</p>
            </div>
            <a href="{{ route('branches.create') }}" class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition-colors flex items-center gap-2">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                Add Branch
            </a>
        </div>
    </x-slot>

    @if(session('success'))
        <div style="display:flex;align-items:center;gap:10px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;padding:14px 18px;margin-bottom:24px;">
            <svg width="16" height="16" fill="none" stroke="#16a34a" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span style="font-size:13px;font-weight:600;color:#15803d;">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div style="display:flex;align-items:center;gap:10px;background:#fef2f2;border:1.5px solid #fca5a5;border-radius:12px;padding:14px 18px;margin-bottom:24px;">
            <svg width="16" height="16" fill="none" stroke="#dc2626" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span style="font-size:13px;font-weight:600;color:#b91c1c;">{{ session('error') }}</span>
        </div>
    @endif

    <div x-data="branchManager()" @open-add-branch.window="openAddModal()" style="display:flex;flex-direction:column;gap:20px;">

        <div class="bg-white rounded-md border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Branch Name</th>
                        <th class="px-6 py-3 font-semibold">Contact Info</th>
                        <th class="px-6 py-3 font-semibold">Location</th>
                        <th class="px-6 py-3 font-semibold">Coordinates</th>
                        <th class="px-6 py-3 font-semibold text-center">Status</th>
                        <th class="px-6 py-3 font-semibold text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($branches as $branch)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($branch->logo_path)
                                        <img src="{{ str_starts_with($branch->logo_path, 'http') ? $branch->logo_path : asset($branch->logo_path) }}" alt="{{ $branch->name }}" class="w-8 h-8 rounded-full object-cover border border-gray-200">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                                            {{ substr($branch->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="font-bold text-gray-900">{{ $branch->name }}</p>
                                            @if($branch->type === 'workshop')
                                                <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-purple-100 text-purple-700 uppercase">Workshop</span>
                                            @else
                                                <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-blue-100 text-blue-700 uppercase">Branch</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500">{{ $branch->tagline ?? 'No Tagline' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-gray-700">📞 {{ $branch->phone ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500">💬 {{ $branch->whatsapp_number ?? 'N/A' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-gray-600 max-w-[200px] truncate" title="{{ $branch->address }}">{{ $branch->address ?? 'No Address' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                @if($branch->latitude && $branch->longitude)
                                    <a href="https://www.google.com/maps?q={{ $branch->latitude }},{{ $branch->longitude }}" target="_blank"
                                        class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 hover:text-blue-800 bg-blue-50 border border-blue-100 rounded-lg px-2 py-1 transition-colors">
                                        <i class="fa-solid fa-location-dot text-blue-500"></i>
                                        {{ number_format($branch->latitude, 4) }}, {{ number_format($branch->longitude, 4) }}
                                    </a>
                                @else
                                    <span class="text-[10px] text-red-400 font-bold flex items-center gap-1">
                                        <i class="fa-solid fa-triangle-exclamation"></i> No coordinates
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($branch->is_active)
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-green-100 text-green-700 uppercase tracking-wider">Active</span>
                                @else
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-red-100 text-red-700 uppercase tracking-wider">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center space-x-2">
                                <button
                                    @click="openEditModal({{ json_encode($branch) }})"
                                    class="text-xs font-semibold px-2.5 py-1 bg-gray-50 border border-gray-200 text-gray-600 rounded hover:bg-gray-100">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('branches.destroy', $branch) }}" onsubmit="return confirm('Permanently delete this branch?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold px-2.5 py-1 bg-red-50 border border-red-200 text-red-600 rounded hover:bg-red-100">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        <!-- Add Branch Modal -->
        <div x-show="addModalOpen" class="fixed inset-0 z-[60] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
                <div x-show="addModalOpen" x-transition.opacity class="fixed inset-0 transition-opacity" @click="addModalOpen = false">
                    <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="addModalOpen" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-gray-100">
                    <form action="{{ route('branches.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="bg-white px-6 pt-6 pb-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-6">Register New Branch</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Branch Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" required class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Facility Type <span class="text-red-500">*</span></label>
                                    <select name="type" required class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                        <option value="branch">Retail Branch</option>
                                        <option value="workshop">Central Workshop</option>
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Phone</label>
                                        <input type="text" name="phone" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">WhatsApp Number</label>
                                        <input type="text" name="whatsapp_number" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Physical Address</label>
                                    <input type="text" name="address" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Tagline</label>
                                    <input type="text" name="tagline" placeholder="e.g. Premium Laundry Services" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wider">Branch Location <span class="text-red-500">*</span></label>
                                    <p class="text-[11px] text-blue-600 font-semibold mb-2"><i class="fa-solid fa-circle-info"></i> Click on the map to pin the branch location</p>
                                    <div class="relative rounded-xl overflow-hidden border border-slate-200 shadow-sm">
                                        <div id="addMap" style="height: 220px; width: 100%; z-index: 1;"></div>
                                        <div id="add_latlng_badge" class="absolute bottom-2 left-2 z-[400] hidden bg-slate-900/90 text-white text-[10px] font-bold px-2.5 py-1.5 rounded-lg flex items-center gap-1.5">
                                            <i class="fa-solid fa-location-dot text-blue-400"></i>
                                            <span id="add_latlng_text">—</span>
                                        </div>
                                    </div>
                                    <input type="hidden" name="latitude" id="add_lat" required>
                                    <input type="hidden" name="longitude" id="add_lng" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Branch Logo</label>
                                    <input type="file" name="logo" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors">
                                </div>
                                <div class="grid grid-cols-2 gap-4 mt-2">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                        <label class="text-sm font-semibold text-gray-700">Set as Active Branch</label>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" name="is_delivery_enabled" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                        <label class="text-sm font-semibold text-gray-700">Enable Delivery Ops</label>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Branch Profit Percentage (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" name="branch_profit_percentage" value="0" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50" placeholder="e.g. 20">
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50/80 px-6 py-4 flex justify-end gap-3 border-t border-gray-100">
                            <button type="button" @click="addModalOpen = false" class="px-5 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 transition-colors shadow-sm">Cancel</button>
                            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl text-sm font-bold hover:shadow-lg hover:shadow-blue-500/30 transition-all">Save Branch</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Branch Modal -->
        <div x-show="editModalOpen" class="fixed inset-0 z-[60] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
                <div x-show="editModalOpen" x-transition.opacity class="fixed inset-0 transition-opacity" @click="editModalOpen = false">
                    <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="editModalOpen" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-gray-100">
                    <form :action="'{{ url('/branches') }}/' + selectedBranch?.id" method="POST" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <div class="bg-white px-6 pt-6 pb-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-6">Edit Branch Details</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Branch Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" :value="selectedBranch?.name" required class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Facility Type <span class="text-red-500">*</span></label>
                                    <select name="type" :value="selectedBranch?.type" required class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                        <option value="branch">Retail Branch</option>
                                        <option value="workshop">Central Workshop</option>
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Phone</label>
                                        <input type="text" name="phone" :value="selectedBranch?.phone" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">WhatsApp Number</label>
                                        <input type="text" name="whatsapp_number" :value="selectedBranch?.whatsapp_number" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Physical Address</label>
                                    <input type="text" name="address" :value="selectedBranch?.address" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Tagline</label>
                                    <input type="text" name="tagline" :value="selectedBranch?.tagline" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wider">Branch Location <span class="text-red-500">*</span></label>
                                    <p class="text-[11px] text-blue-600 font-semibold mb-2"><i class="fa-solid fa-circle-info"></i> Click on the map to move the pin to a new location</p>
                                    <div class="relative rounded-xl overflow-hidden border border-slate-200 shadow-sm">
                                        <div id="editMap" style="height: 220px; width: 100%; z-index: 1;"></div>
                                        <div id="edit_latlng_badge" class="absolute bottom-2 left-2 z-[400] bg-slate-900/90 text-white text-[10px] font-bold px-2.5 py-1.5 rounded-lg flex items-center gap-1.5">
                                            <i class="fa-solid fa-location-dot text-blue-400"></i>
                                            <span id="edit_latlng_text">Loading...</span>
                                        </div>
                                    </div>
                                    <input type="hidden" name="latitude" id="edit_lat" :value="selectedBranch?.latitude" required>
                                    <input type="hidden" name="longitude" id="edit_lng" :value="selectedBranch?.longitude" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Update Logo (Optional)</label>
                                    <input type="file" name="logo" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors">
                                </div>
                                <div class="grid grid-cols-2 gap-4 mt-2">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" name="is_active" value="1" :checked="selectedBranch?.is_active" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                        <label class="text-sm font-semibold text-gray-700">Active Branch</label>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" name="is_delivery_enabled" value="1" :checked="selectedBranch?.is_delivery_enabled" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                        <label class="text-sm font-semibold text-gray-700">Enable Delivery Ops</label>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Branch Profit Percentage (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" name="branch_profit_percentage" :value="selectedBranch?.branch_profit_percentage" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                </div>
                                <div class="col-span-1 md:col-span-2 border-t border-gray-100 pt-3 mt-1">
                                    <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-2">Thermal Bill Settings</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-1">Invoice Header</label>
                                            <textarea name="invoice_header" :value="selectedBranch?.invoice_header" rows="2" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 bg-gray-50"></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-1">Invoice Footer</label>
                                            <textarea name="invoice_footer" :value="selectedBranch?.invoice_footer" rows="2" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 bg-gray-50"></textarea>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-bold text-gray-700 mb-1">Terms & Conditions</label>
                                            <textarea name="invoice_terms" :value="selectedBranch?.invoice_terms" rows="2" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 bg-gray-50"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50/80 px-6 py-4 flex justify-end gap-3 border-t border-gray-100">
                            <button type="button" @click="editModalOpen = false" class="px-5 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 transition-colors shadow-sm">Cancel</button>
                            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl text-sm font-bold hover:shadow-lg hover:shadow-blue-500/30 transition-all">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <x-slot name="scripts">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const CARTO_TILE = 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png';
        const CARTO_ATTR = '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/">CARTO</a>';
        const DEFAULT_CENTER = [6.9271, 79.8612]; // Colombo

        function makeBranchIcon() {
            return L.divIcon({
                html: `<div style="transform:translate(-50%,-100%)">
                    <div style="width:30px;height:30px;background:#2563eb;border-radius:50%;display:flex;align-items:center;justify-content:center;border:3px solid white;box-shadow:0 2px 10px rgba(37,99,235,0.4);">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="white"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div style="width:6px;height:6px;background:#2563eb;border-radius:50%;margin:2px auto 0;box-shadow:0 1px 4px rgba(37,99,235,0.3);"></div>
                </div>`,
                className: 'bg-transparent',
                iconSize: [1, 1],
                iconAnchor: [0, 0]
            });
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('branchManager', () => ({
                addModalOpen: false,
                editModalOpen: false,
                selectedBranch: null,
                addMap: null,
                addMarker: null,
                editMap: null,
                editMarker: null,

                openAddModal() {
                    this.addModalOpen = true;
                    setTimeout(() => {
                        if (!this.addMap) {
                            this.addMap = L.map('addMap', { zoomControl: false }).setView(DEFAULT_CENTER, 13);
                            L.tileLayer(CARTO_TILE, { attribution: CARTO_ATTR, subdomains: 'abcd', maxZoom: 20 }).addTo(this.addMap);
                            L.control.zoom({ position: 'bottomright' }).addTo(this.addMap);

                            this.addMap.on('click', (e) => {
                                if (this.addMarker) this.addMap.removeLayer(this.addMarker);
                                this.addMarker = L.marker(e.latlng, { icon: makeBranchIcon() }).addTo(this.addMap);
                                document.getElementById('add_lat').value = e.latlng.lat.toFixed(6);
                                document.getElementById('add_lng').value = e.latlng.lng.toFixed(6);
                                const badge = document.getElementById('add_latlng_badge');
                                badge.classList.remove('hidden');
                                document.getElementById('add_latlng_text').textContent =
                                    e.latlng.lat.toFixed(5) + ', ' + e.latlng.lng.toFixed(5);
                            });
                        }
                        this.addMap.invalidateSize();
                    }, 150);
                },

                openEditModal(branch) {
                    this.selectedBranch = branch;
                    this.editModalOpen = true;
                    setTimeout(() => {
                        if (!this.editMap) {
                            this.editMap = L.map('editMap', { zoomControl: false }).setView(DEFAULT_CENTER, 13);
                            L.tileLayer(CARTO_TILE, { attribution: CARTO_ATTR, subdomains: 'abcd', maxZoom: 20 }).addTo(this.editMap);
                            L.control.zoom({ position: 'bottomright' }).addTo(this.editMap);

                            this.editMap.on('click', (e) => {
                                if (this.editMarker) this.editMap.removeLayer(this.editMarker);
                                this.editMarker = L.marker(e.latlng, { icon: makeBranchIcon() }).addTo(this.editMap);
                                document.getElementById('edit_lat').value = e.latlng.lat.toFixed(6);
                                document.getElementById('edit_lng').value = e.latlng.lng.toFixed(6);
                                document.getElementById('edit_latlng_text').textContent =
                                    e.latlng.lat.toFixed(5) + ', ' + e.latlng.lng.toFixed(5);
                            });
                        }
                        this.editMap.invalidateSize();

                        if (this.editMarker) {
                            this.editMap.removeLayer(this.editMarker);
                            this.editMarker = null;
                        }

                        if (branch.latitude && branch.longitude) {
                            const latlng = [parseFloat(branch.latitude), parseFloat(branch.longitude)];
                            this.editMap.setView(latlng, 16);
                            this.editMarker = L.marker(latlng, { icon: makeBranchIcon() }).addTo(this.editMap);
                            document.getElementById('edit_lat').value = branch.latitude;
                            document.getElementById('edit_lng').value = branch.longitude;
                            document.getElementById('edit_latlng_text').textContent =
                                parseFloat(branch.latitude).toFixed(5) + ', ' + parseFloat(branch.longitude).toFixed(5);
                        } else {
                            this.editMap.setView(DEFAULT_CENTER, 13);
                            document.getElementById('edit_latlng_text').textContent = 'Click map to set location';
                        }
                    }, 150);
                }
            }))
        });
    </script>
    </x-slot>
</x-app-layout>


