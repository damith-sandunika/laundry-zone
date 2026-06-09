<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center mb-2">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    System Settings
                </h2>
                <p class="text-sm text-gray-500 mt-1">Configure laundry details, services, and stock locations.</p>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="bg-green-50 text-green-700 px-6 py-4 mb-6 text-sm font-semibold border border-green-200 rounded-xl shadow-sm flex items-center">
            <svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <div x-data="{ activeTab: localStorage.getItem('settingsActiveTab') || 'general', serviceModalOpen: false, cubbyModalOpen: false }" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        
        <!-- Premium Horizontally Scrollable Tab Header -->
        <div class="p-3 border-b border-gray-100 bg-gray-50/50 overflow-x-auto scrollbar-none">
            <div class="flex gap-2 w-max bg-gray-200/50 p-1 rounded-xl">
                <button @click="activeTab = 'general'; localStorage.setItem('settingsActiveTab', 'general')" 
                    :class="activeTab === 'general' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'" 
                    class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                    General Settings
                </button>
                <button @click="activeTab = 'services'; localStorage.setItem('settingsActiveTab', 'services')" 
                    :class="activeTab === 'services' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'" 
                    class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                    Services & Inventory
                </button>
                <button @click="activeTab = 'cubbies'; localStorage.setItem('settingsActiveTab', 'cubbies')" 
                    :class="activeTab === 'cubbies' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'" 
                    class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                    Stock Locations
                </button>
                <button @click="activeTab = 'whatsapp'; localStorage.setItem('settingsActiveTab', 'whatsapp')" 
                    :class="activeTab === 'whatsapp' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'" 
                    class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                    WhatsApp API
                </button>
            </div>
        </div>

        <div class="p-6">
            <!-- GENERAL SETTINGS TAB -->
            <div x-show="activeTab === 'general'">
                <form action="{{ route('settings.updateGeneral') }}" method="POST" class="max-w-xl space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Laundry Shop Name</label>
                        <input type="text" name="name" value="{{ $branch->name }}" class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Address</label>
                        <input type="text" name="address" value="{{ $branch->address }}" class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Telephone Number</label>
                            <input type="text" name="phone" value="{{ $branch->phone }}" class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">WhatsApp Number</label>
                            <input type="text" name="whatsapp_number" value="{{ $branch->whatsapp_number ?? '' }}" class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Tagline (Will appear on print bills)</label>
                        <input type="text" name="tagline" value="{{ $branch->tagline ?? 'Fast & Clean Laundry Service' }}" class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition-colors">
                        Save Settings
                    </button>
                </form>
            </div>

            <!-- SERVICES TAB -->
            <div x-show="activeTab === 'services'" style="display: none;">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-semibold text-gray-800">Available Services</h3>
                    <button @click="serviceModalOpen = true" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors">
                        + Add New Service
                    </button>
                </div>

                <div class="overflow-x-auto border border-gray-100 rounded-md">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                            <tr>
                                <th class="px-4 py-2">Service Name</th>
                                <th class="px-4 py-2">Price Type</th>
                                <th class="px-4 py-2">Price</th>
                                <th class="px-4 py-2">Inventory Deducted (Per Qty)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($services as $service)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-gray-850">{{ $service->name }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">{{ $service->price_type === 'per_kg' ? 'Per KG' : 'Per Item' }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">Rs.{{ number_format($service->base_price, 2) }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">
                                        @forelse($service->inventoryItems as $invItem)
                                            <span class="inline-block bg-blue-50 text-blue-700 px-2 py-0.5 rounded mr-1">
                                                {{ $invItem->name }}: {{ $invItem->pivot->qty_per_use }} {{ $invItem->unit }}
                                            </span>
                                        @empty
                                            <span class="text-gray-400 italic">None linked</span>
                                        @endforelse
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">No services found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- CUBBIES (STOCK LOCATIONS) TAB -->
            <div x-show="activeTab === 'cubbies'" style="display: none;">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-semibold text-gray-800">Cubbets / Racks</h3>
                    <button @click="cubbyModalOpen = true" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors">
                        + Add Location
                    </button>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @forelse($cubbies as $cubby)
                        <div x-data="{ editing: false, newName: '{{ addslashes($cubby->name) }}' }" class="p-4 border rounded-md text-center relative {{ $cubby->status === 'occupied' ? 'bg-orange-50 border-orange-200' : 'bg-green-50 border-green-200' }}">
                            <div class="absolute top-1 right-1 flex gap-1">
                                <button type="button" @click="editing = true"
                                    class="text-blue-500 hover:text-blue-700 p-1 rounded hover:bg-blue-50 transition-colors" title="Edit">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </button>
                                <form action="{{ route('settings.destroyCubby', $cubby) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="text-red-400 hover:text-red-600 p-1 rounded hover:bg-red-50 transition-colors" title="Delete">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                            
                            <div x-show="!editing">
                                <h4 class="font-bold text-gray-800 mt-2">{{ $cubby->name }}</h4>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded mt-2 inline-block {{ $cubby->status === 'occupied' ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700' }}">
                                    {{ ucfirst($cubby->status) }}
                                </span>
                            </div>
                            
                            <div x-show="editing" class="mt-4" style="display:none;">
                                <form action="{{ route('settings.updateCubby', $cubby) }}" method="POST" class="flex flex-col gap-2">
                                    @csrf @method('PUT')
                                    <input type="text" name="name" x-model="newName" class="w-full text-xs border-gray-300 rounded px-2 py-1 text-center" required>
                                    <div class="flex gap-1 justify-center">
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-[10px] px-3 py-1.5 rounded font-bold">Save</button>
                                        <button type="button" @click="editing = false" class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-[10px] px-3 py-1.5 rounded font-bold">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-6 text-center text-gray-500">No stock locations found.</div>
                    @endforelse
                </div>
            </div>

            <!-- WHATSAPP API TAB -->
            <div x-show="activeTab === 'whatsapp'" style="display: none;" x-data="whatsappAPI()" x-init="fetchStatus(); startPolling()">

                <!-- Status Header Bar -->
                <div class="flex flex-wrap justify-between items-center mb-6 gap-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-800">WhatsApp Notification Service</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Automatically sends order ready & delivery notifications to customers.</p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="resetQR()"
                            class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-lg flex items-center gap-1.5 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Reset & New QR
                        </button>
                        <button @click="fetchStatus()"
                            class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold rounded-lg flex items-center gap-1.5 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Refresh
                        </button>
                    </div>
                </div>

                <!-- Service not running -->
                <div x-show="error" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div>
                        <p class="text-sm font-bold text-red-700">WhatsApp Service Not Running</p>
                        <p class="text-xs text-red-600 mt-1">Run <code class="bg-red-100 px-1.5 py-0.5 rounded font-mono">start_all.bat</code> or manually run <code class="bg-red-100 px-1.5 py-0.5 rounded font-mono">cd whatsapp-service && node index.js</code></p>
                    </div>
                </div>

                <!-- Main Panel -->
                <div class="rounded-2xl border border-gray-200 overflow-hidden">

                    <!-- Loading -->
                    <div x-show="loading" class="p-12 flex flex-col items-center justify-center bg-gray-50">
                        <svg class="animate-spin h-10 w-10 text-blue-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-sm font-semibold text-gray-600">Checking service status...</p>
                    </div>

                    <!-- Disconnected: waiting for QR -->
                    <div x-show="!loading && !error && !connected" class="p-8 bg-gray-50 flex flex-col items-center text-center">

                        <div class="mb-5">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">
                                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                                Disconnected
                            </span>
                        </div>

                        <!-- QR Code display -->
                        <div x-show="qrCode" class="mb-6">
                            <div class="bg-white p-4 border-2 border-green-300 rounded-2xl shadow-lg inline-block mb-3">
                                <img :src="qrCode" alt="WhatsApp QR Code" class="w-56 h-56 object-contain">
                            </div>
                            <p class="text-xs text-gray-500 font-medium mb-4">Open WhatsApp → <strong>Linked Devices</strong> → <strong>Link a Device</strong> → Scan</p>
                            <button @click="openQRTab()"
                                class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-bold rounded-xl shadow-md transition-all flex items-center gap-2 mx-auto">
                                🖥️ Open QR Full Screen
                            </button>
                        </div>

                        <!-- Waiting for QR -->
                        <div x-show="!qrCode" class="py-8">
                            <div class="w-16 h-16 bg-yellow-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-yellow-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <p class="text-sm font-bold text-gray-700 mb-2">Waiting for QR Code...</p>
                            <p class="text-xs text-gray-500 mb-5">Service is starting up. QR will appear automatically. If it doesn't show, click <strong>Reset & New QR</strong>.</p>
                            <button @click="resetQR()"
                                class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold rounded-xl shadow-md transition-all mx-auto">
                                🔄 Generate New QR
                            </button>
                        </div>

                    </div>

                    <!-- Connected -->
                    <div x-show="!loading && !error && connected" class="p-8 bg-gray-50 flex flex-col items-center text-center">
                        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mb-5 shadow-inner">
                            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="inline-flex items-center gap-2 px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full mb-4">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            Connected & Sending Messages
                        </span>
                        <p class="text-sm text-gray-600 font-medium max-w-sm mb-6">
                            The background service is active. WhatsApp notifications will be sent automatically when orders are marked Ready or Delivered.
                        </p>
                        <button @click="logout()"
                            class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-xl shadow-sm transition-all">
                            🔌 Disconnect / Logout
                        </button>
                    </div>

                </div>

                <!-- How to start info box -->
                <div class="mt-5 p-4 bg-blue-50 border border-blue-100 rounded-xl">
                    <p class="text-xs font-bold text-blue-800 mb-2">💡 How to start the WhatsApp service:</p>
                    <ol class="text-xs text-blue-700 space-y-1 list-decimal list-inside">
                        <li>Double-click <strong>start_all.bat</strong> in the project folder, OR</li>
                        <li>Open a terminal → go to <code class="bg-blue-100 px-1 rounded">whatsapp-service</code> folder → run <code class="bg-blue-100 px-1 rounded">node index.js</code></li>
                        <li>Come back here and scan the QR code with WhatsApp</li>
                    </ol>
                </div>

            </div>

            <script>
                function whatsappAPI() {
                    return {
                        connected: false,
                        qrCode: null,
                        loading: true,
                        error: null,
                        pollInterval: null,

                        fetchStatus() {
                            fetch("{{ route('whatsapp.status') }}")
                                .then(res => res.json())
                                .then(data => {
                                    this.connected = data.connected;
                                    this.qrCode = data.qr || null;
                                    this.loading = false;
                                    this.error = null;
                                })
                                .catch(() => {
                                    this.error = true;
                                    this.loading = false;
                                    this.qrCode = null;
                                    this.connected = false;
                                });
                        },

                        startPolling() {
                            if (this.pollInterval) clearInterval(this.pollInterval);
                            this.pollInterval = setInterval(() => this.fetchStatus(), 4000);
                        },

                        resetQR() {
                            this.loading = true;
                            this.qrCode = null;
                            fetch('/settings/whatsapp/reset', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                            })
                            .then(() => setTimeout(() => this.fetchStatus(), 3000))
                            .catch(() => { this.loading = false; this.error = true; });
                        },

                        openQRTab() {
                            if (!this.qrCode) return;
                            const win = window.open('', '_blank');
                            win.document.write(`
                                <html><head><title>WhatsApp QR - Scan Now</title>
                                <style>body{margin:0;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh;background:#f0fdf4;font-family:sans-serif;}
                                img{width:300px;height:300px;border:4px solid #22c55e;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,0.1);}
                                h2{color:#15803d;margin-bottom:16px;}p{color:#6b7280;font-size:14px;}</style></head>
                                <body><h2>📱 Scan with WhatsApp</h2><img src="${this.qrCode}" alt="QR"><p style="margin-top:16px;">WhatsApp → Linked Devices → Link a Device</p></body></html>
                            `);
                            win.document.close();
                        },

                        logout() {
                            if (!confirm('Disconnect WhatsApp? You will need to scan QR again.')) return;
                            this.loading = true;
                            fetch("{{ route('whatsapp.logout') }}", {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                            })
                            .then(() => setTimeout(() => this.fetchStatus(), 2000))
                            .catch(() => this.fetchStatus());
                        }
                    }
                }
            </script>
        </div>


        <!-- Add Service Modal -->
        <div x-show="serviceModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
                <div x-show="serviceModalOpen" x-transition.opacity class="fixed inset-0 transition-opacity" @click="serviceModalOpen = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="serviceModalOpen" x-transition class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <form action="{{ route('settings.storeService') }}" method="POST">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Add New Laundry Service</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Service Name</label>
                                    <input type="text" name="name" required class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Base Price</label>
                                        <input type="number" step="0.01" name="base_price" required class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Pricing Unit</label>
                                        <select name="price_type" class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                            <option value="per_kg">Per KG</option>
                                            <option value="per_item">Per Item</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="border-t border-gray-100 pt-4">
                                    <h4 class="text-xs font-bold text-gray-800 uppercase mb-2">Link Supplies to Auto-Deduct</h4>
                                    <div class="space-y-2 max-h-40 overflow-y-auto">
                                        @foreach($inventoryItems as $item)
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs text-gray-600">{{ $item->name }} ({{ $item->unit }})</span>
                                                <input type="number" name="inventory[{{ $item->id }}]" placeholder="0" class="w-20 text-xs border-gray-300 rounded p-1 text-right">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                                Add Service
                            </button>
                            <button type="button" @click="serviceModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Cubby Modal -->
        <div x-show="cubbyModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
                <div x-show="cubbyModalOpen" x-transition.opacity class="fixed inset-0 transition-opacity" @click="cubbyModalOpen = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="cubbyModalOpen" x-transition class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <form action="{{ route('settings.storeCubby') }}" method="POST">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Add Stock Location (Cubby/Rack)</h3>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Name (e.g. Cubby 15)</label>
                                <input type="text" name="name" required class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                                Save
                            </button>
                            <button type="button" @click="cubbyModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
