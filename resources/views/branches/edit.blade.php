<x-app-layout>
    <x-slot name="header">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <div class="flex items-center gap-3">
            <a href="{{ route('branches.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-500 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <p class="text-xs text-gray-400 font-medium">Branch Management</p>
                <h2 class="text-xl font-bold text-gray-800">Edit Branch Details</h2>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('branches.update', $branch->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Left: Main Info -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="font-bold text-gray-800 text-sm">Branch Information</h3>
                        </div>
                        <div class="p-6 space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Branch Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" value="{{ old('name', $branch->name) }}" required
                                        class="w-full text-sm bg-gray-50 border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all py-2.5 px-3.5"
                                        placeholder="e.g. Colombo Main Branch">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Branch Type <span class="text-red-500">*</span></label>
                                    <select name="type" class="w-full text-sm bg-gray-50 border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all py-2.5 px-3.5">
                                        <option value="branch" {{ old('type', $branch->type) === 'branch' ? 'selected' : '' }}>🏪 Branch (Laundromat)</option>
                                        <option value="workshop" {{ old('type', $branch->type) === 'workshop' ? 'selected' : '' }}>⚙️ Workshop</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Phone Number</label>
                                    <input type="text" name="phone" value="{{ old('phone', $branch->phone) }}"
                                        class="w-full text-sm bg-gray-50 border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all py-2.5 px-3.5">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">WhatsApp Number</label>
                                    <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $branch->whatsapp_number) }}"
                                        class="w-full text-sm bg-gray-50 border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all py-2.5 px-3.5">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Address</label>
                                <textarea name="address" rows="2"
                                    class="w-full text-sm bg-gray-50 border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all py-2.5 px-3.5">{{ old('address', $branch->address) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Tagline (Optional)</label>
                                <input type="text" name="tagline" value="{{ old('tagline', $branch->tagline) }}"
                                    class="w-full text-sm bg-gray-50 border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all py-2.5 px-3.5"
                                    placeholder="A short description or slogan">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide">Branch Location <span class="text-red-500">*</span></label>
                                <p class="text-[11px] text-blue-600 font-semibold mb-2"><i class="fa-solid fa-circle-info"></i> Click on the map to pin the branch location</p>
                                <div class="relative rounded-xl overflow-hidden border border-slate-200 shadow-sm">
                                    <div id="addMap" style="height: 250px; width: 100%; z-index: 1;"></div>
                                    <div id="add_latlng_badge" class="absolute bottom-2 left-2 z-[400] hidden bg-slate-900/90 text-white text-[10px] font-bold px-2.5 py-1.5 rounded-lg flex items-center gap-1.5">
                                        <i class="fa-solid fa-location-dot text-blue-400"></i>
                                        <span id="add_latlng_text">—</span>
                                    </div>
                                </div>
                                <input type="hidden" name="latitude" id="add_lat" value="{{ old('latitude', $branch->latitude) }}" required>
                                <input type="hidden" name="longitude" id="add_lng" value="{{ old('longitude', $branch->longitude) }}" required>
                            </div>
                        </div>
                    </div>

                    <!-- Logo Upload -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="font-bold text-gray-800 text-sm">Logo & Branding</h3>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Branch Logo</label>
                                <input type="file" name="logo" accept="image/*"
                                    class="w-full text-sm text-gray-600 bg-gray-50 border border-gray-200 rounded-xl file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100 transition-all py-2">
                                <p class="text-xs text-gray-400 mt-1">Max 5MB. JPG, PNG.</p>
                                @if($branch->logo_path)
                                    <div class="mt-2">
                                        <img src="{{ str_starts_with($branch->logo_path, 'http') ? $branch->logo_path : asset($branch->logo_path) }}" alt="Logo" class="h-12 w-12 rounded object-cover border border-gray-200">
                                    </div>
                                @endif
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Logo Display Size (px)</label>
                                <input type="number" name="logo_size" value="{{ old('logo_size', $branch->logo_size ?? 120) }}" min="20" max="500"
                                    class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all py-2.5 px-3.5">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Settings -->
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="font-bold text-gray-800 text-sm">Branch Settings</h3>
                        </div>
                        <div class="p-6 space-y-5">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Branch Profit %</label>
                                <input type="number" step="0.01" name="branch_profit_percentage" value="{{ old('branch_profit_percentage', $branch->branch_profit_percentage ?? 0) }}" min="0" max="100"
                                    class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all py-2.5 px-3.5">
                            </div>

                            <div class="border-t border-gray-100 pt-4">
                                <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wide mb-3">Thermal Bill Settings</h4>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1">Invoice Header</label>
                                        <textarea name="invoice_header" rows="2" class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 bg-gray-50 p-2" placeholder="e.g. Welcome to Laundry Zone!">{{ old('invoice_header', $branch->invoice_header) }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1">Invoice Footer</label>
                                        <textarea name="invoice_footer" rows="2" class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 bg-gray-50 p-2" placeholder="e.g. Thank you for choosing us">{{ old('invoice_footer', $branch->invoice_footer) }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1">Terms & Conditions</label>
                                        <textarea name="invoice_terms" rows="2" class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 bg-gray-50 p-2" placeholder="e.g. Clothes not claimed within 30 days...">{{ old('invoice_terms', $branch->invoice_terms) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3 pt-2">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $branch->is_active) ? 'checked' : '' }}
                                        class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                    <div>
                                        <span class="text-sm font-bold text-gray-800 block">Active Branch</span>
                                        <span class="text-xs text-gray-500">Branch is visible and operational</span>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="is_delivery_enabled" value="1" {{ old('is_delivery_enabled', $branch->is_delivery_enabled) ? 'checked' : '' }}
                                        class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                    <div>
                                        <span class="text-sm font-bold text-gray-800 block">Enable Delivery</span>
                                        <span class="text-xs text-gray-500">Allow delivery orders for this branch</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col gap-3">
                        <button type="submit"
                            class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-all shadow-sm shadow-blue-600/30 text-sm">
                            💾 Save Changes
                        </button>
                        <a href="{{ route('branches.index') }}"
                            class="w-full py-3 bg-white border border-gray-200 text-gray-600 font-bold rounded-xl hover:bg-gray-50 transition-all text-sm text-center">
                            Cancel
                        </a>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <x-slot name="scripts">
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            const CARTO_TILE = 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png';
            const CARTO_ATTR = '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/">CARTO</a>';
            const DEFAULT_CENTER = [6.9271, 79.8612]; // Colombo
            let addMap = null;
            let addMarker = null;

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

            document.addEventListener('DOMContentLoaded', () => {
                addMap = L.map('addMap', { zoomControl: false }).setView(DEFAULT_CENTER, 13);
                L.tileLayer(CARTO_TILE, { attribution: CARTO_ATTR, subdomains: 'abcd', maxZoom: 20 }).addTo(addMap);
                L.control.zoom({ position: 'bottomright' }).addTo(addMap);

                // Handle old values if returning with validation errors
                const oldLat = document.getElementById('add_lat').value;
                const oldLng = document.getElementById('add_lng').value;
                if (oldLat && oldLng) {
                    const latlng = [parseFloat(oldLat), parseFloat(oldLng)];
                    addMap.setView(latlng, 15);
                    addMarker = L.marker(latlng, { icon: makeBranchIcon() }).addTo(addMap);
                    document.getElementById('add_latlng_badge').classList.remove('hidden');
                    document.getElementById('add_latlng_text').textContent = parseFloat(oldLat).toFixed(5) + ', ' + parseFloat(oldLng).toFixed(5);
                }

                addMap.on('click', (e) => {
                    if (addMarker) addMap.removeLayer(addMarker);
                    addMarker = L.marker(e.latlng, { icon: makeBranchIcon() }).addTo(addMap);
                    document.getElementById('add_lat').value = e.latlng.lat.toFixed(6);
                    document.getElementById('add_lng').value = e.latlng.lng.toFixed(6);
                    document.getElementById('add_latlng_badge').classList.remove('hidden');
                    document.getElementById('add_latlng_text').textContent = e.latlng.lat.toFixed(5) + ', ' + e.latlng.lng.toFixed(5);
                });
            });
        </script>
    </x-slot>
</x-app-layout>
