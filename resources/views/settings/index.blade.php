<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <h2 class="ph-title">System Settings</h2>
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
                @if(in_array(auth()->user()->role, ['branch_admin', 'workshop_admin', 'admin']))
                    <button @click="activeTab = 'services'; localStorage.setItem('settingsActiveTab', 'services')" 
                        :class="activeTab === 'services' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'" 
                        class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                        Services
                    </button>
                    <button @click="activeTab = 'packages'; localStorage.setItem('settingsActiveTab', 'packages')" 
                        :class="activeTab === 'packages' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'" 
                        class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                        Packages
                    </button>
                @endif
                @if(auth()->user()->role === 'super_admin')
                    <button @click="activeTab = 'delivery'; localStorage.setItem('settingsActiveTab', 'delivery')" 
                        :class="activeTab === 'delivery' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'" 
                        class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                        Delivery Settings
                    </button>
                    <button @click="activeTab = 'pickup_times'; localStorage.setItem('settingsActiveTab', 'pickup_times')" 
                        :class="activeTab === 'pickup_times' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'" 
                        class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                        Pickup Times
                    </button>
                    <button @click="activeTab = 'payment_methods'; localStorage.setItem('settingsActiveTab', 'payment_methods')" 
                        :class="activeTab === 'payment_methods' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'" 
                        class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                        Payment Methods
                    </button>
                @endif
                @if(auth()->user()->role !== 'super_admin')
                <button @click="activeTab = 'cubbies'; localStorage.setItem('settingsActiveTab', 'cubbies')" 
                    :class="activeTab === 'cubbies' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'" 
                    class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                    Stock Locations
                </button>
                @endif

            </div>
        </div>

        <div class="p-6">
            <!-- GENERAL SETTINGS TAB -->
            <div x-show="activeTab === 'general'" x-data="{ 
                formCompanyName: '{{ addslashes(\App\Models\Setting::get('company_name', '360 Laundry')) }}',
                formName: '{{ addslashes($branch->name ?? 'SMART LAUNDRY') }}',
                formAddress: '{{ addslashes($branch->address) }}',
                formPhone: '{{ addslashes($branch->phone) }}',
                formTagline: '{{ addslashes($branch->tagline ?? 'Fast & Clean Laundry Service') }}',
                formLogoSize: {{ $branch->logo_size ?? 140 }},
                previewUrl: '{{ $branch->logo_path ? asset($branch->logo_path) : '' }}'
            }">
                <form action="{{ route('settings.updateGeneral') }}" method="POST" enctype="multipart/form-data" class="flex flex-col lg:flex-row gap-6">
                    <div class="flex-1 space-y-4 max-w-xl">
                        @csrf
                    @if(auth()->user()->role === 'super_admin')
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Company Name (Global for all Bills)</label>
                        <input type="text" name="company_name" x-model="formCompanyName" class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    @endif
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                            Branch Name
                        </label>
                        <input type="text" name="name" x-model="formName" class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Address</label>
                        <input type="text" name="address" x-model="formAddress" class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Latitude</label>
                            <input type="text" name="latitude" value="{{ old('latitude', $branch->latitude) }}" class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Longitude</label>
                            <input type="text" name="longitude" value="{{ old('longitude', $branch->longitude) }}" class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Telephone Number</label>
                            <input type="text" name="phone" x-model="formPhone" class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">WhatsApp Number</label>
                            <input type="text" name="whatsapp_number" value="{{ $branch->whatsapp_number ?? '' }}" class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Tagline (Will appear on print bills)</label>
                        <input type="text" name="tagline" x-model="formTagline" class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    
                    @if(auth()->user()->role === 'super_admin')
                    <div class="pt-2">
                        <label class="block text-xs font-bold text-gray-800 uppercase mb-1">Global Auto-Close Time</label>
                        <p class="text-[11px] text-gray-500 mb-2">All branches will be automatically closed and their daily shift reports will be generated at this time.</p>
                        <input type="time" name="auto_close_time" value="{{ $branch->auto_close_time ?? '' }}" class="w-48 text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500 font-mono">
                    </div>
                    @endif

                    <div x-data="{ 
                        dragOver: false, 
                        fileName: '', 
                        handleDrop(e) {
                            this.dragOver = false;
                            if(e.dataTransfer.files.length > 0) {
                                this.$refs.fileInput.files = e.dataTransfer.files;
                                this.updatePreview();
                            }
                        },
                        updatePreview() {
                            if(this.$refs.fileInput.files.length > 0) {
                                let file = this.$refs.fileInput.files[0];
                                this.fileName = file.name;
                                let reader = new FileReader();
                                reader.onload = e => previewUrl = e.target.result;
                                reader.readAsDataURL(file);
                            }
                        }
                    }">
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">Branch Thermal Bill Logo</label>
                        
                        <div @dragover.prevent="dragOver = true" 
                             @dragleave.prevent="dragOver = false" 
                             @drop.prevent="handleDrop($event)"
                             @click="$refs.fileInput.click()"
                             :class="dragOver ? 'border-blue-500 bg-blue-50' : 'border-dashed border-gray-300 bg-gray-50 hover:bg-gray-100'"
                             class="relative flex flex-col items-center justify-center w-full h-36 border-2 rounded-xl cursor-pointer transition-colors group mb-4">
                             
                            <input x-ref="fileInput" type="file" name="logo" accept="image/png, image/jpeg, image/jpg" class="hidden" @change="updatePreview">
                            
                            <div x-show="!previewUrl" class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-8 h-8 mb-3 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <p class="mb-1 text-sm text-gray-500"><span class="font-bold text-gray-700">Click to upload</span> or drag and drop</p>
                                <p class="text-xs text-gray-400">PNG, JPG (Recommended: Black & White)</p>
                            </div>

                            <div x-show="previewUrl" style="display: none;" class="flex flex-col items-center justify-center p-2 h-full w-full">
                                <img :src="previewUrl" class="h-20 object-contain mb-2">
                                <p class="text-xs font-bold text-blue-600 truncate max-w-[80%]" x-text="fileName ? fileName : 'Current Active Logo'"></p>
                                <p class="text-[10px] text-gray-400 mt-0.5 group-hover:text-gray-500">Click or drag to replace</p>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="block text-xs font-semibold text-gray-700 uppercase">Logo Size</label>
                                <span class="text-xs font-bold text-blue-600" x-text="formLogoSize + 'px'"></span>
                            </div>
                            <input type="range" name="logo_size" x-model="formLogoSize" min="20" max="300" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                            <p class="text-[10px] text-gray-400 mt-1">Adjust to scale the logo appropriately on the thermal bill.</p>
                        </div>
                    </div>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                            Save Settings
                        </button>
                    </div>

                    <!-- Thermal Bill Preview -->
                    <div class="flex-1 max-w-sm">
                        <h4 class="text-xs font-bold text-gray-700 uppercase mb-3">Live Thermal Bill Preview</h4>
                        
                        <!-- Thermal Bill Actual Mockup -->
                        <div class="bg-white mx-auto shadow-[0_10px_30px_rgba(0,0,0,0.1)] relative" style="width: 74mm; padding: 4mm 3mm; font-family: 'Inter', system-ui, -apple-system, sans-serif; font-size: 12px; color: #000; min-height: 400px;">
                            
                            <!-- Jagged top edge effect -->
                            <div class="absolute top-0 left-0 right-0 h-2" style="background-image: linear-gradient(135deg, transparent 50%, #fff 50%), linear-gradient(45deg, #fff 50%, transparent 50%); background-size: 8px 8px; background-position: 0 -4px; transform: rotate(180deg); margin-top: -8px;"></div>
                            
                            <!-- Header -->
                            <div class="text-center" style="margin-bottom: 12px;">
                                <template x-if="previewUrl">
                                    <img :src="previewUrl" alt="Logo" :style="`max-width: ${formLogoSize}px; height: auto; display: inline-block;`">
                                </template>
                            </div>
                            
                            <div class="text-center" style="font-size:18px; letter-spacing:-0.03em; font-weight: 800; margin-bottom: 2px;">
                                <span x-text="formName || 'SMART LAUNDRY'"></span>
                            </div>
                            
                            <template x-if="formTagline">
                                <div class="text-center" style="font-size:10px; margin-bottom:2px;" x-text="formTagline"></div>
                            </template>
                            
                            <template x-if="formPhone">
                                <div class="text-center" style="font-size:10px;">📞 <span x-text="formPhone"></span></div>
                            </template>
                            
                            <template x-if="formAddress">
                                <div class="text-center" style="font-size:10px;" x-text="formAddress"></div>
                            </template>

                            <div style="border-top: 1.5px dashed #000; margin: 8px 0;"></div>

                            <!-- Details -->
                            <div style="display: flex; justify-content: space-between; padding: 3px 0; gap: 4px; font-size: 12px;">
                                <span style="flex: 1; text-align: left;">Bill No:</span>
                                <span style="font-weight: 800; text-align: right;">ORD-9999</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 3px 0; gap: 4px; font-size: 12px;">
                                <span style="flex: 1; text-align: left;">Date:</span>
                                <span style="font-weight: 800; text-align: right;">{{ now()->format('d M Y h:i A') }}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 3px 0; gap: 4px; font-size: 12px;">
                                <span style="flex: 1; text-align: left;">Customer:</span>
                                <span style="font-weight: 800; text-align: right;">John Doe</span>
                            </div>

                            <div style="border-top: 1.5px dashed #000; margin: 8px 0;"></div>

                            <!-- Sample Items -->
                            <div style="display: flex; justify-content: space-between; padding: 3px 0; gap: 4px; margin-bottom: 2px;">
                                <span style="font-weight: 700; font-size: 12px; display: block; word-break: break-word;">Wash & Fold</span>
                                <span style="font-weight: 800;">Rs.1,200.00</span>
                            </div>
                            <div style="font-size: 12px; margin-bottom: 6px;">
                                2 x Rs.600.00
                            </div>

                            <div style="display: flex; justify-content: space-between; padding: 3px 0; gap: 4px; margin-bottom: 2px;">
                                <span style="font-weight: 700; font-size: 12px; display: block; word-break: break-word;">Ironing</span>
                                <span style="font-weight: 450.00">Rs.450.00</span>
                            </div>
                            <div style="font-size: 12px; margin-bottom: 6px;">
                                3 x Rs.150.00
                            </div>

                            <div style="border-top: 1.5px dashed #000; margin: 8px 0;"></div>

                            <!-- Totals -->
                            <div style="display: flex; justify-content: space-between; padding: 3px 0; gap: 4px; font-weight: 900; font-size: 15px; border-top: 2px solid #000; padding-top: 6px; margin-top: 6px;">
                                <span>TOTAL</span>
                                <span>Rs.1,650.00</span>
                            </div>

                            <div style="text-align: center; margin: 8px 0;">
                                <span style="display: inline-block; border: 1.5px solid #000; padding: 4px 10px; margin-top: 6px; font-size: 12px; font-weight: 800; border-radius: 6px;">
                                    🕒 PAY ON PICKUP
                                </span>
                            </div>

                            <div style="border-top: 1.5px dashed #000; margin: 8px 0;"></div>
                            
                            <div style="text-align: center; font-weight: 800; font-size: 12px; margin-top: 8px;">
                                Thank you for choosing 360 Laundry.
                            </div>
                            <div style="text-align: center; font-size: 10px; margin-top: 3px; color: #333;">
                                If you are happy with our service, please leave us a Google Review!
                            </div>
                            <div style="text-align: center; font-size: 12px; margin-top: 8px;">
                                Software by DSRZone
                            </div>

                            <!-- Jagged bottom edge effect -->
                            <div class="absolute bottom-0 left-0 right-0 h-2" style="background-image: linear-gradient(135deg, transparent 50%, #fff 50%), linear-gradient(45deg, #fff 50%, transparent 50%); background-size: 8px 8px; margin-bottom: -8px;"></div>
                        </div>
                    </div>
                </form>
            </div>



            <!-- SERVICES TAB -->
            <div x-show="activeTab === 'services'" style="display: none;">
                @php
                    $colorStyles = [
                        'blue' => ['bg' => '#eff6ff', 'border' => '#bfdbfe', 'text' => '#1d4ed8'],
                        'green' => ['bg' => '#f0fdf4', 'border' => '#bbf7d0', 'text' => '#15803d'],
                        'purple' => ['bg' => '#fdf4ff', 'border' => '#e9d5ff', 'text' => '#7e22ce'],
                        'orange' => ['bg' => '#fff7ed', 'border' => '#fed7aa', 'text' => '#c2410c'],
                        'teal' => ['bg' => '#f0fdfa', 'border' => '#99f6e4', 'text' => '#0f766e'],
                        'red' => ['bg' => '#fef2f2', 'border' => '#fecaca', 'text' => '#b91c1c'],
                    ];
                    
                    $availableColors = ['blue', 'green', 'purple', 'orange', 'teal', 'red'];
                @endphp

                <style>
                    .color-radio input[type="radio"] { display: none; }
                    .color-radio label {
                        display: flex; align-items: center; justify-content: center; width: 30px; height: 30px;
                        border-radius: 50%; cursor: pointer; transition: all 0.15s; border: 2px solid transparent;
                    }
                    .color-radio input[type="radio"]:checked + label {
                        transform: scale(1.15); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); border-color: #0f172a;
                    }
                    .category-group-header {
                        cursor: pointer;
                    }
                    .category-group-header:hover {
                        background-color: #f8fafc;
                    }
                </style>

                <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
                    <h3 class="text-sm font-semibold text-gray-800">Service Categories & Services</h3>
                    <div class="flex gap-2">
                        <button onclick="document.getElementById('add-category-modal').style.display='flex'" class="px-4 py-2 bg-slate-800 text-white text-xs font-semibold rounded hover:bg-slate-900 transition-colors">
                            + Add Category
                        </button>
                        <button onclick="document.getElementById('add-service-modal').style.display='flex'" class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition-colors">
                            + Add New Service
                        </button>
                    </div>
                </div>

                <div class="space-y-6">
                    @foreach($serviceCategories as $category)
                    <div x-data="{ expanded: true }" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div @click="expanded = !expanded" class="category-group-header flex justify-between items-center p-4 border-b border-gray-100 transition-colors">
                            <div class="flex items-center gap-3">
                                @if($category->image_path)
                                    <img src="{{ asset($category->image_path) }}" alt="{{ $category->name }}" class="w-8 h-8 object-cover rounded-md border border-gray-200">
                                @else
                                    <svg :class="expanded ? 'rotate-90' : 'rotate-0'" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                @endif
                                <h4 class="font-bold text-gray-800">{{ $category->name }}</h4>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $category->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span>
                            </div>
                            <div class="flex gap-2" @click.stop>
                                <button onclick="openEditCategoryModal({{ $category->id }}, '{{ addslashes($category->name) }}', {{ $category->is_active ? 1 : 0 }})" class="text-xs font-semibold text-blue-600 hover:text-blue-800 px-2 py-1 bg-blue-50 rounded">Edit</button>
                                <form method="POST" action="{{ route('settings.destroyServiceCategory', $category) }}" onsubmit="return confirm('Are you sure you want to delete this category? All related services will be uncategorized.')" class="m-0">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800 px-2 py-1 bg-red-50 rounded">Delete</button>
                                </form>
                            </div>
                        </div>

                        <div x-show="expanded" x-collapse>
                            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;padding:16px;background:#f8fafc;">
                                @forelse($category->services as $service)
                                @php
                                    $style = $colorStyles[$service->color ?? 'blue'] ?? $colorStyles['blue'];
                                @endphp
                                <div style="background:white;border-radius:16px;border:1.5px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.04);overflow:hidden;transition:box-shadow 0.15s;"
                                    onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='0 1px 4px rgba(0,0,0,0.04)'">
                                    <div style="padding:20px 20px 14px;border-bottom:1px solid #f1f5f9;">
                                        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">
                                            <div style="display:flex;align-items:center;gap:12px;">
                                                <div style="width:40px;height:40px;border-radius:12px;background:{{ $style['bg'] }};border:1.5px solid {{ $style['border'] }};color:{{ $style['text'] }};display:flex;align-items:center;justify-content:center;">
                                                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                                </div>
                                                <div>
                                                    <h3 style="font-size:16px;font-weight:800;color:#0f172a;margin:0;letter-spacing:-0.01em;">{{ $service->name }}</h3>
                                                    <p style="font-size:12px;font-weight:600;color:#64748b;margin:0;text-transform:uppercase;letter-spacing:0.05em;">
                                                        {{ $service->price_type === 'per_kg' ? 'Per Kilogram' : 'Per Item' }}
                                                    </p>
                                                </div>
                                            </div>
                                            <span style="font-size:10px;font-weight:800;padding:3px 8px;border-radius:6px;text-transform:uppercase;letter-spacing:0.06em;
                                                {{ $service->is_active ? 'background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;' : 'background:#fef2f2;color:#dc2626;border:1px solid #fecaca;' }}">
                                                {{ $service->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div style="padding:14px 20px;display:flex;justify-content:space-between;align-items:center;">
                                        <span style="font-size:22px;font-weight:900;color:{{ $style['text'] }};letter-spacing:-0.02em;">Rs.{{ number_format($service->base_price, 2) }}</span>
                                        <div style="display:flex;gap:8px;">
                                            <button onclick="openEditModal({{ $service->id }}, '{{ addslashes($service->name) }}', {{ $service->base_price }}, '{{ $service->price_type }}', {{ $service->is_active ? 1 : 0 }}, '{{ $service->icon ?? '👕' }}', '{{ $service->color ?? 'blue' }}', {{ $service->category_id ?? 'null' }})"
                                                style="padding:7px 14px;background:#eff6ff;color:#1d4ed8;font-size:12px;font-weight:700;border:1.5px solid #bfdbfe;border-radius:8px;cursor:pointer;transition:all 0.15s;"
                                                onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                                                Edit
                                            </button>
                                            <form method="POST" action="{{ route('settings.destroyService', $service) }}" onsubmit="return confirm('Delete this service?')" style="margin:0;">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    style="padding:7px 14px;background:#fef2f2;color:#dc2626;font-size:12px;font-weight:700;border:1.5px solid #fecaca;border-radius:8px;cursor:pointer;transition:all 0.15s;"
                                                    onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div style="grid-column:1/-1;padding:30px;text-align:center;color:#64748b;font-size:13px;font-weight:500;">
                                    No services in this category.
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <!-- Uncategorized Services -->
                    @php
                        $uncategorizedServices = $services->whereNull('category_id');
                    @endphp
                    @if($uncategorizedServices->count() > 0)
                    <div x-data="{ expanded: true }" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div @click="expanded = !expanded" class="category-group-header flex justify-between items-center p-4 border-b border-gray-100 transition-colors">
                            <div class="flex items-center gap-3">
                                <svg :class="expanded ? 'rotate-90' : 'rotate-0'" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                <h4 class="font-bold text-gray-800">Uncategorized Services</h4>
                            </div>
                        </div>

                        <div x-show="expanded" x-collapse>
                            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;padding:16px;background:#f8fafc;">
                                @foreach($uncategorizedServices as $service)
                                @php
                                    $style = $colorStyles[$service->color ?? 'blue'] ?? $colorStyles['blue'];
                                @endphp
                                <div style="background:white;border-radius:16px;border:1.5px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.04);overflow:hidden;transition:box-shadow 0.15s;"
                                    onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='0 1px 4px rgba(0,0,0,0.04)'">
                                    <div style="padding:20px 20px 14px;border-bottom:1px solid #f1f5f9;">
                                        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">
                                            <div style="display:flex;align-items:center;gap:12px;">
                                                <div style="width:40px;height:40px;border-radius:12px;background:{{ $style['bg'] }};border:1.5px solid {{ $style['border'] }};color:{{ $style['text'] }};display:flex;align-items:center;justify-content:center;">
                                                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                                </div>
                                                <div>
                                                    <h3 style="font-size:16px;font-weight:800;color:#0f172a;margin:0;letter-spacing:-0.01em;">{{ $service->name }}</h3>
                                                    <p style="font-size:12px;font-weight:600;color:#64748b;margin:0;text-transform:uppercase;letter-spacing:0.05em;">
                                                        {{ $service->price_type === 'per_kg' ? 'Per Kilogram' : 'Per Item' }}
                                                    </p>
                                                </div>
                                            </div>
                                            <span style="font-size:10px;font-weight:800;padding:3px 8px;border-radius:6px;text-transform:uppercase;letter-spacing:0.06em;
                                                {{ $service->is_active ? 'background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;' : 'background:#fef2f2;color:#dc2626;border:1px solid #fecaca;' }}">
                                                {{ $service->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div style="padding:14px 20px;display:flex;justify-content:space-between;align-items:center;">
                                        <span style="font-size:22px;font-weight:900;color:{{ $style['text'] }};letter-spacing:-0.02em;">Rs.{{ number_format($service->base_price, 2) }}</span>
                                        <div style="display:flex;gap:8px;">
                                            <button onclick="openEditModal({{ $service->id }}, '{{ addslashes($service->name) }}', {{ $service->base_price }}, '{{ $service->price_type }}', {{ $service->is_active ? 1 : 0 }}, '{{ $service->icon ?? '👕' }}', '{{ $service->color ?? 'blue' }}', null)"
                                                style="padding:7px 14px;background:#eff6ff;color:#1d4ed8;font-size:12px;font-weight:700;border:1.5px solid #bfdbfe;border-radius:8px;cursor:pointer;transition:all 0.15s;"
                                                onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                                                Edit
                                            </button>
                                            <form method="POST" action="{{ route('settings.destroyService', $service) }}" onsubmit="return confirm('Delete this service?')" style="margin:0;">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    style="padding:7px 14px;background:#fef2f2;color:#dc2626;font-size:12px;font-weight:700;border:1.5px solid #fecaca;border-radius:8px;cursor:pointer;transition:all 0.15s;"
                                                    onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($serviceCategories->count() === 0 && $uncategorizedServices->count() === 0)
                    <div style="grid-column:1/-1;padding:60px;text-align:center;background:white;border-radius:16px;border:1.5px solid #e2e8f0;">
                        <svg width="48" height="48" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 16px;display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <p style="font-size:15px;font-weight:800;color:#0f172a;margin:0 0 6px;">No services yet</p>
                        <p style="font-size:13px;color:#94a3b8;font-weight:500;">Click "Add Category" and "Add New Service" to get started.</p>
                    </div>
                    @endif
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
                        <div x-data="{ editing: false, newName: '{{ addslashes($cubby->name) }}' }" class="p-4 border rounded-md text-center relative bg-green-50 border-green-200">
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
                                <span class="text-xs font-semibold px-2 py-0.5 rounded mt-2 inline-block bg-green-100 text-green-700">
                                    Active
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
                        <div class="col-span-full p-4 border rounded-md text-center bg-gray-50 border-gray-200">
                            <span class="text-gray-500 text-sm font-medium">No cubbies added yet.</span>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- DELIVERY SETTINGS TAB -->
            @if(auth()->user()->role === 'super_admin')
            <div x-show="activeTab === 'delivery'" style="display: none;">
                
                <!-- Global Delivery Settings -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-8 p-6">
                    <h3 class="text-sm font-semibold text-gray-800 mb-4 border-b pb-2">Global Delivery Settings</h3>
                    <form action="{{ route('settings.updateDeliverySettings') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Minimum Delivery Cost (Rs.)</label>
                                <input type="number" step="0.01" name="minimum_delivery_cost" value="{{ $minimumDeliveryCost }}" class="w-full text-sm border-gray-300 rounded-md p-2">
                                <p class="text-xs text-gray-500 mt-1">If the calculated cost is lower than this, this amount will be charged.</p>
                            </div>
                        </div>



                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md font-bold text-sm hover:bg-blue-700">Save Settings</button>
                        </div>
                    </form>
                </div>

                <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
                    <h3 class="text-sm font-semibold text-gray-800">Delivery Ranges & Cost Calculation</h3>
                    <button onclick="document.getElementById('add-delivery-range-modal').style.display='flex'" class="px-4 py-2 bg-slate-800 text-white text-xs font-semibold rounded hover:bg-slate-900 transition-colors">
                        + Add Delivery Range
                    </button>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-8">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Distance Range (km)</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Delivery Cost (Rs.)</th>
                                <th class="px-6 py-3 text-right font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($deliveryRanges ?? [] as $range)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-800">
                                        {{ $range->min_distance_km }} - {{ $range->max_distance_km }} km
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-blue-600">
                                        Rs. {{ number_format($range->cost, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <form method="POST" action="{{ route('settings.deleteDeliveryRange', $range) }}" onsubmit="return confirm('Are you sure you want to delete this range?')" class="inline-block m-0">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800 px-2 py-1 bg-red-50 rounded">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-6 text-center text-gray-500 font-medium">No delivery ranges set.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- PICKUP TIMES TAB -->
            @if(auth()->user()->role === 'super_admin')
            <div x-show="activeTab === 'pickup_times'" style="display: none;">
                <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
                    <h3 class="text-sm font-semibold text-gray-800">Pickup Time Periods</h3>
                    <button onclick="document.getElementById('add-pickup-time-modal').style.display='flex'" class="px-4 py-2 bg-slate-800 text-white text-xs font-semibold rounded hover:bg-slate-900 transition-colors">
                        + Add Time Period
                    </button>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-8">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Label</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Time Range</th>
                                <th class="px-6 py-3 text-right font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($pickupTimePeriods ?? [] as $period)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-800">
                                        {{ $period->label }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-600">
                                        {{ \Carbon\Carbon::parse($period->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($period->end_time)->format('h:i A') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <form method="POST" action="{{ route('settings.deletePickupTimePeriod', $period) }}" onsubmit="return confirm('Are you sure you want to delete this period?')" class="inline-block m-0">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800 px-2 py-1 bg-red-50 rounded">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-6 text-center text-gray-500 font-medium">No pickup time periods set.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- PAYMENT METHODS TAB -->
            @if(auth()->user()->role === 'super_admin')
            <div x-show="activeTab === 'payment_methods'" style="display: none;">
                <form action="{{ route('settings.payment-methods.update') }}" method="POST">
                    @csrf
                    
                    <!-- Payment Methods Toggles -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="text-lg font-black text-slate-800">Available Payment Methods</h3>
                            <p class="text-sm text-slate-500 font-medium">Enable or disable payment options for your customers.</p>
                        </div>
                        
                        <div class="divide-y divide-slate-100">
                            @foreach($paymentMethodsList as $method)
                                <div class="p-6 flex items-center justify-between hover:bg-slate-50/50 transition-colors">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0
                                            {{ $method->type === 'bank_transfer' ? 'bg-blue-50 text-blue-600' : '' }}
                                            {{ $method->type === 'card_transfer' ? 'bg-purple-50 text-purple-600' : '' }}
                                            {{ $method->type === 'cash_on_delivery' ? 'bg-green-50 text-green-600' : '' }}
                                        ">
                                            @if($method->type === 'bank_transfer')
                                                <i class="fa-solid fa-building-columns text-xl"></i>
                                            @elseif($method->type === 'card_transfer')
                                                <i class="fa-solid fa-credit-card text-xl"></i>
                                            @else
                                                <i class="fa-solid fa-money-bill-wave text-xl"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="font-black text-slate-800 flex items-center gap-2">
                                                {{ $method->name }}
                                                @if($method->type === 'card_transfer')
                                                    <span class="text-[10px] font-bold uppercase tracking-wider bg-orange-100 text-orange-600 px-2 py-0.5 rounded-md">Mock Gateway Integration</span>
                                                @endif
                                            </h4>
                                            <p class="text-sm font-medium text-slate-500">
                                                @if($method->type === 'bank_transfer')
                                                    Customers will see your bank details and upload a receipt.
                                                @elseif($method->type === 'card_transfer')
                                                    Customers pay securely online via Mock Credit/Debit card page.
                                                @else
                                                    Customers pay in cash when the order is delivered.
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="is_active[{{ $method->id }}]" value="1" class="sr-only peer" {{ $method->is_active ? 'checked' : '' }}>
                                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Bank Details Form -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="text-lg font-black text-slate-800">Bank Transfer Details</h3>
                            <p class="text-sm text-slate-500 font-medium">These details will be displayed to customers when they select Bank Transfer during checkout.</p>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1">Bank Name</label>
                                    <input type="text" name="bank_name" value="{{ old('bank_name', $bankDetails['bank_name']) }}" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-blue-500 focus:border-blue-500 text-sm font-semibold" placeholder="e.g. Commercial Bank">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1">Branch Name</label>
                                    <input type="text" name="bank_branch" value="{{ old('bank_branch', $bankDetails['bank_branch']) }}" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-blue-500 focus:border-blue-500 text-sm font-semibold" placeholder="e.g. Colombo 07">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1">Account Name</label>
                                    <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $bankDetails['bank_account_name']) }}" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-blue-500 focus:border-blue-500 text-sm font-semibold" placeholder="e.g. 360 Laundry">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1">Account Number</label>
                                    <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $bankDetails['bank_account_number']) }}" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-blue-500 focus:border-blue-500 text-sm font-semibold font-mono" placeholder="e.g. 1234567890">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mock Payment Gateway Settings -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="text-lg font-black text-slate-800">Online Payment Gateway Settings (Mock)</h3>
                            <p class="text-sm text-slate-500 font-medium">Enter the API credentials provided by your bank or payment gateway provider.</p>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1">Merchant ID</label>
                                    <input type="text" name="gateway_merchant_id" value="{{ old('gateway_merchant_id', $gatewayDetails['gateway_merchant_id']) }}" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-blue-500 focus:border-blue-500 text-sm font-semibold" placeholder="e.g. 123456789">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1">Secret Key / API Password</label>
                                    <input type="password" name="gateway_secret_key" value="{{ old('gateway_secret_key', $gatewayDetails['gateway_secret_key']) }}" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-blue-500 focus:border-blue-500 text-sm font-semibold" placeholder="********">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-bold shadow-md shadow-blue-200 transition-all flex items-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i> Save Payment Settings
                        </button>
                    </div>
                </form>
            </div>
            @endif



            <!-- PACKAGES TAB -->
            <div x-show="activeTab === 'packages'" style="display: none;" x-data="{ packageModalOpen: false }">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-semibold text-gray-800">Subscription Packages</h3>
                    <button @click="packageModalOpen = true" class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition-colors">
                        + Add New Package
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($packages as $package)
                        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col relative group">
                            <div class="p-5 flex-1">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-gray-900 text-lg">{{ $package->name }}</h4>
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider rounded-md {{ $package->payment_type === 'prepaid' ? 'bg-green-100 text-green-700' : 'bg-purple-100 text-purple-700' }}">
                                        {{ $package->payment_type }}
                                    </span>
                                </div>
                                <div class="text-2xl font-black text-blue-600 mb-4">
                                    Rs.{{ number_format($package->price, 2) }}
                                </div>
                                
                                <div class="space-y-2 mb-4">
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Included Services</p>
                                    @foreach($package->services as $ps)
                                        <div class="flex justify-between items-center text-sm border-b border-gray-50 pb-1">
                                            <span class="text-gray-700 font-medium flex items-center gap-2">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                                                {{ $ps->serviceType->name }}
                                            </span>
                                            <span class="font-bold text-gray-900">{{ number_format($ps->quantity_limit, 0) }}</span>
                                        </div>
                                    @endforeach
                                </div>


                            </div>
                            
                            <div class="bg-gray-50 p-3 border-t border-gray-100 flex justify-end gap-2" x-data="{ editPackageModalOpen_{{ $package->id }}: false }">
                                <button type="button" @click="editPackageModalOpen_{{ $package->id }} = true" class="text-xs font-bold text-blue-600 hover:bg-blue-50 px-3 py-1.5 rounded transition-colors">
                                    Edit
                                </button>
                                <form action="{{ route('settings.destroyPackage', $package) }}" method="POST" onsubmit="return confirm('Delete this package?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-red-600 hover:bg-red-50 px-3 py-1.5 rounded transition-colors">
                                        Delete
                                    </button>
                                </form>

                                <!-- Edit Package Modal -->
                                <div x-show="editPackageModalOpen_{{ $package->id }}" class="fixed inset-0 z-[9999] overflow-y-auto" style="display: none;">
                                    <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
                                        <div x-show="editPackageModalOpen_{{ $package->id }}" x-transition.opacity class="fixed inset-0 transition-opacity" @click="editPackageModalOpen_{{ $package->id }} = false">
                                            <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm"></div>
                                        </div>
                                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                        <div x-show="editPackageModalOpen_{{ $package->id }}" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-gray-100">
                                            <form action="{{ route('settings.updatePackage', $package) }}" method="POST">
                                                @csrf @method('PUT')
                                                <div class="bg-white px-6 pt-6 pb-6">
                                                    <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                        Edit Package
                                                    </h3>
                                                    <div class="space-y-5">
                                                        <div class="text-left">
                                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Package Name <span class="text-red-500">*</span></label>
                                                            <input type="text" name="name" value="{{ $package->name }}" required class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                                        </div>
                                                        <div class="grid grid-cols-2 gap-4 text-left">
                                                            <div>
                                                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Package Price (Rs) <span class="text-red-500">*</span></label>
                                                                <input type="number" step="0.01" name="price" value="{{ $package->price }}" required class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                                            </div>
                                                            <div>
                                                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Payment Type</label>
                                                                <select name="payment_type" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50 font-semibold">
                                                                    <option value="prepaid" {{ $package->payment_type === 'prepaid' ? 'selected' : '' }}>Pre-paid (Pay upfront)</option>
                                                                    <option value="postpaid" {{ $package->payment_type === 'postpaid' ? 'selected' : '' }}>Post-paid (Pay later)</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="border-t border-gray-100 pt-5 text-left">
                                                            <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-3">Included Services & Limits</h4>
                                                            <div class="space-y-3 max-h-64 overflow-y-auto custom-scrollbar pr-2">
                                                                @foreach($serviceCategories as $category)
                                                                    @if($category->services->count() > 0)
                                                                    <div class="bg-gray-50/50 rounded-xl border border-gray-100 overflow-hidden">
                                                                        <div class="bg-gray-100/50 px-3 py-2 border-b border-gray-100">
                                                                            <h5 class="text-[10px] font-black text-gray-700 uppercase tracking-wider">{{ $category->name }}</h5>
                                                                        </div>
                                                                        <div class="p-1 space-y-1">
                                                                            @foreach($category->services as $service)
                                                                                @php $qty = $package->services->where('service_type_id', $service->id)->first()->quantity_limit ?? 0; @endphp
                                                                                <div class="flex items-center justify-between p-2 hover:bg-white rounded-lg transition-colors border border-transparent hover:border-gray-200">
                                                                                    <span class="text-sm font-semibold text-gray-700">{{ $service->name }} <span class="text-[10px] text-gray-400 font-normal ml-1">({{ $service->price_type === 'per_kg' ? 'KG' : 'Items' }})</span></span>
                                                                                    <input type="number" step="any" name="services[{{ $service->id }}]" value="{{ $qty }}" class="w-24 text-sm border-gray-200 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-center font-bold">
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="bg-gray-50/80 px-6 py-4 flex justify-end gap-3 border-t border-gray-100">
                                                    <button type="button" @click="editPackageModalOpen_{{ $package->id }} = false" class="px-5 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-sm">Cancel</button>
                                                    <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:shadow-lg hover:shadow-blue-500/30 transition-all transform hover:-translate-y-0.5">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-10 text-center text-gray-500 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                            <p class="mb-2">No packages defined.</p>
                            <button @click="packageModalOpen = true" class="text-sm text-blue-600 font-bold hover:underline">Create your first package</button>
                        </div>
                    @endforelse
                </div>

                <!-- Add Package Modal -->
                <div x-show="packageModalOpen" class="fixed inset-0 z-[9999] overflow-y-auto" style="display: none;">
                    <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
                        <div x-show="packageModalOpen" x-transition.opacity class="fixed inset-0 transition-opacity" @click="packageModalOpen = false">
                            <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm"></div>
                        </div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                        <div x-show="packageModalOpen" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-gray-100">
                            <form action="{{ route('settings.storePackage') }}" method="POST">
                                @csrf
                                <div class="bg-white px-6 pt-6 pb-6">
                                    <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        Create New Package
                                    </h3>
                                    
                                    <div class="space-y-5">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Package Name <span class="text-red-500">*</span></label>
                                            <input type="text" name="name" required placeholder="e.g. Premium Monthly Plan" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Package Price (Rs) <span class="text-red-500">*</span></label>
                                                <input type="number" step="0.01" name="price" required placeholder="0.00" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Payment Type</label>
                                                <select name="payment_type" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50 font-semibold">
                                                    <option value="prepaid">Pre-paid (Pay upfront)</option>
                                                    <option value="postpaid">Post-paid (Pay later)</option>
                                                </select>
                                            </div>
                                        </div>



                                        <div class="border-t border-gray-100 pt-5">
                                            <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-3">Included Services & Limits</h4>
                                            <p class="text-[11px] text-gray-500 mb-3">Set the quantity limit for services included in this package. Leave as 0 if the service is not included.</p>
                                            
                                            <div class="space-y-3 max-h-64 overflow-y-auto custom-scrollbar pr-2">
                                                @foreach($serviceCategories as $category)
                                                    @if($category->services->count() > 0)
                                                    <div class="bg-gray-50/50 rounded-xl border border-gray-100 overflow-hidden">
                                                        <div class="bg-gray-100/50 px-3 py-2 border-b border-gray-100">
                                                            <h5 class="text-[10px] font-black text-gray-700 uppercase tracking-wider">{{ $category->name }}</h5>
                                                        </div>
                                                        <div class="p-1 space-y-1">
                                                            @foreach($category->services as $service)
                                                            <div class="flex items-center justify-between p-2 hover:bg-white rounded-lg transition-colors border border-transparent hover:border-gray-200">
                                                                <span class="text-sm font-semibold text-gray-700">{{ $service->name }} <span class="text-[10px] text-gray-400 font-normal ml-1">({{ $service->price_type === 'per_kg' ? 'KG' : 'Items' }})</span></span>
                                                                <input type="number" step="any" name="services[{{ $service->id }}]" placeholder="0" class="w-24 text-sm border-gray-200 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-center font-bold">
                                                            </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50/80 px-6 py-4 flex justify-end gap-3 border-t border-gray-100">
                                    <button type="button" @click="packageModalOpen = false" class="px-5 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-sm">
                                        Cancel
                                    </button>
                                    <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:shadow-lg hover:shadow-blue-500/30 transition-all transform hover:-translate-y-0.5">
                                        Save Package
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>


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
                                                <input type="number" step="any" name="inventory[{{ $item->id }}]" placeholder="0" class="w-20 text-xs border-gray-300 rounded p-1 text-right">
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
    </div>    <!-- ── Add Service Modal ── -->
    <div id="add-service-modal" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);">
        <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:480px;overflow:hidden;" onclick="event.stopPropagation()">
            <div style="padding:24px 28px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h3 style="font-size:17px;font-weight:900;color:#0f172a;margin:0 0 3px;">Add New Service</h3>
                    <p style="font-size:12px;color:#94a3b8;margin:0;font-weight:500;">Define a new laundry service type</p>
                </div>
                <button onclick="document.getElementById('add-service-modal').style.display='none'"
                    style="width:32px;height:32px;background:#f1f5f9;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('settings.storeService') }}" style="padding:24px 28px;display:flex;flex-direction:column;gap:16px;max-height:80vh;overflow-y:auto;">
                @csrf
                                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Category</label>
                    <select name="category_id"
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        <option value="">Uncategorized</option>
                        @foreach($serviceCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
<div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Service Name</label>
                    <input type="text" name="name" required placeholder="e.g. Wash & Iron"
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                </div>

                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.05em;">Select Color</label>
                    <div style="display:flex;flex-wrap:wrap;gap:10px;">
                        @foreach($availableColors ?? ['blue', 'green', 'purple', 'orange', 'teal', 'red'] as $i => $color)
                        @php $bg = ($colorStyles ?? ['blue' => ['text' => '#1d4ed8'], 'green' => ['text' => '#15803d'], 'purple' => ['text' => '#7e22ce'], 'orange' => ['text' => '#c2410c'], 'teal' => ['text' => '#0f766e'], 'red' => ['text' => '#b91c1c']])[$color]['text']; @endphp
                        <div class="color-radio">
                            <input type="radio" name="color" id="add-color-{{ $i }}" value="{{ $color }}" {{ $i === 0 ? 'checked' : '' }}>
                            <label for="add-color-{{ $i }}" style="background:{{ $bg }};"></label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Base Price (Rs.)</label>
                        <input type="number" name="base_price" required min="0" step="0.01" placeholder="0.00"
                            style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                    </div>
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Price Type</label>
                        <select name="price_type" required
                            style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                            <option value="per_item">Per Item</option>
                            <option value="per_kg">Per KG</option>
                        </select>
                    </div>
                </div>

                <div style="display:flex;gap:10px;margin-top:4px;">
                    <button type="button" onclick="document.getElementById('add-service-modal').style.display='none'"
                        style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                    <button type="submit"
                        style="flex:1;padding:11px;background:#1d4ed8;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;"
                        onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='#1d4ed8'">Save Service</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Edit Service Modal ── -->
    <div id="edit-service-modal" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);">
        <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:480px;overflow:hidden;" onclick="event.stopPropagation()">
            <div style="padding:24px 28px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h3 style="font-size:17px;font-weight:900;color:#0f172a;margin:0 0 3px;">Edit Service</h3>
                    <p style="font-size:12px;color:#94a3b8;margin:0;font-weight:500;">Update service details</p>
                </div>
                <button onclick="document.getElementById('edit-service-modal').style.display='none'"
                    style="width:32px;height:32px;background:#f1f5f9;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="edit-service-form" method="POST" style="padding:24px 28px;display:flex;flex-direction:column;gap:16px;max-height:80vh;overflow-y:auto;">
                @csrf @method('PUT')
                                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Category</label>
                    <select id="edit-category-id" name="category_id"
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        <option value="">Uncategorized</option>
                        @foreach($serviceCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
<div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Service Name</label>
                    <input type="text" id="edit-name" name="name" required
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                </div>

                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.05em;">Select Color</label>
                    <div style="display:flex;flex-wrap:wrap;gap:10px;">
                        @foreach($availableColors ?? ['blue', 'green', 'purple', 'orange', 'teal', 'red'] as $i => $color)
                        @php $bg = ($colorStyles ?? ['blue' => ['text' => '#1d4ed8'], 'green' => ['text' => '#15803d'], 'purple' => ['text' => '#7e22ce'], 'orange' => ['text' => '#c2410c'], 'teal' => ['text' => '#0f766e'], 'red' => ['text' => '#b91c1c']])[$color]['text']; @endphp
                        <div class="color-radio">
                            <input type="radio" name="color" id="edit-color-{{ $i }}" value="{{ $color }}">
                            <label for="edit-color-{{ $i }}" style="background:{{ $bg }};"></label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Base Price (Rs.)</label>
                        <input type="number" id="edit-price" name="base_price" required min="0" step="0.01"
                            style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                    </div>
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Price Type</label>
                        <select id="edit-price-type" name="price_type" required
                            style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;">
                            <option value="per_item">Per Item</option>
                            <option value="per_kg">Per KG</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Status</label>
                    <select id="edit-active" name="is_active"
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                
                <div style="display:flex;gap:10px;margin-top:4px;">
                    <button type="button" onclick="document.getElementById('edit-service-modal').style.display='none'"
                        style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                    <button type="submit"
                        style="flex:1;padding:11px;background:#1d4ed8;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;"
                        onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='#1d4ed8'">Update Service</button>
                </div>
            </form>
        </div>
    </div>

        <!-- ── Add Category Modal ── -->
    <div id="add-category-modal" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);">
        <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:400px;overflow:hidden;" onclick="event.stopPropagation()">
            <div style="padding:24px 28px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h3 style="font-size:17px;font-weight:900;color:#0f172a;margin:0 0 3px;">Add Category</h3>
                    <p style="font-size:12px;color:#94a3b8;margin:0;font-weight:500;">Group your services</p>
                </div>
                <button onclick="document.getElementById('add-category-modal').style.display='none'"
                    style="width:32px;height:32px;background:#f1f5f9;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('settings.storeServiceCategory') }}" enctype="multipart/form-data" style="padding:24px 28px;display:flex;flex-direction:column;gap:16px;">
                @csrf
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Category Image (Optional)</label>
                    <input type="file" name="image" accept="image/*"
                        style="width:100%;padding:8px 10px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Category Name</label>
                    <input type="text" name="name" required placeholder="e.g. Wash & Fold"
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                </div>
                
                <div style="display:flex;gap:10px;margin-top:4px;">
                    <button type="button" onclick="document.getElementById('add-category-modal').style.display='none'"
                        style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                    <button type="submit"
                        style="flex:1;padding:11px;background:#1d4ed8;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;"
                        onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='#1d4ed8'">Save Category</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Edit Category Modal ── -->
    <div id="edit-category-modal" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);">
        <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:400px;overflow:hidden;" onclick="event.stopPropagation()">
            <div style="padding:24px 28px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h3 style="font-size:17px;font-weight:900;color:#0f172a;margin:0 0 3px;">Edit Category</h3>
                </div>
                <button onclick="document.getElementById('edit-category-modal').style.display='none'"
                    style="width:32px;height:32px;background:#f1f5f9;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="edit-category-form" method="POST" enctype="multipart/form-data" style="padding:24px 28px;display:flex;flex-direction:column;gap:16px;">
                @csrf @method('PUT')
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Category Image (Optional)</label>
                    <input type="file" name="image" accept="image/*"
                        style="width:100%;padding:8px 10px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                    <p style="font-size:10px;color:#64748b;margin-top:4px;">Leave blank to keep existing image</p>
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Category Name</label>
                    <input type="text" id="edit-category-name" name="name" required
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Status</label>
                    <select id="edit-category-active" name="is_active"
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                
                <div style="display:flex;gap:10px;margin-top:4px;">
                    <button type="button" onclick="document.getElementById('edit-category-modal').style.display='none'"
                        style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                    <button type="submit"
                        style="flex:1;padding:11px;background:#1d4ed8;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;"
                        onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='#1d4ed8'">Update Category</button>
                </div>
            </form>
        </div>
    </div>
    <!-- ── Add Delivery Range Modal ── -->
    <div id="add-delivery-range-modal" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);">
        <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:400px;overflow:hidden;" onclick="event.stopPropagation()">
            <div style="padding:24px 28px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h3 style="font-size:17px;font-weight:900;color:#0f172a;margin:0 0 3px;">Add Delivery Range</h3>
                </div>
                <button onclick="document.getElementById('add-delivery-range-modal').style.display='none'"
                    style="width:32px;height:32px;background:#f1f5f9;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('settings.storeDeliveryRange') }}" style="padding:24px 28px;display:flex;flex-direction:column;gap:16px;">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Min Distance (km)</label>
                        <input type="number" name="min_distance_km" required step="0.01" min="0" value="0"
                            style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Max Distance (km)</label>
                        <input type="number" name="max_distance_km" required step="0.01" min="0.1"
                            style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                    </div>
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Delivery Cost (Rs.)</label>
                    <input type="number" name="cost" required step="0.01" min="0"
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                </div>
                
                <div style="display:flex;gap:10px;margin-top:4px;">
                    <button type="button" onclick="document.getElementById('add-delivery-range-modal').style.display='none'"
                        style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                    <button type="submit"
                        style="flex:1;padding:11px;background:#1d4ed8;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;"
                        onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='#1d4ed8'">Save Range</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Add Pickup Time Modal ── -->
    <div id="add-pickup-time-modal" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);">
        <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:400px;overflow:hidden;" onclick="event.stopPropagation()">
            <div style="padding:24px 28px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h3 style="font-size:17px;font-weight:900;color:#0f172a;margin:0 0 3px;">Add Pickup Time Period</h3>
                </div>
                <button onclick="document.getElementById('add-pickup-time-modal').style.display='none'"
                    style="width:32px;height:32px;background:#f1f5f9;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('settings.storePickupTimePeriod') }}" style="padding:24px 28px;display:flex;flex-direction:column;gap:16px;">
                @csrf
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Label (e.g. Morning)</label>
                    <input type="text" name="label" required
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Start Time</label>
                        <input type="time" name="start_time" required
                            style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">End Time</label>
                        <input type="time" name="end_time" required
                            style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                    </div>
                </div>
                
                <div style="display:flex;gap:10px;margin-top:4px;">
                    <button type="button" onclick="document.getElementById('add-pickup-time-modal').style.display='none'"
                        style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                    <button type="submit"
                        style="flex:1;padding:11px;background:#1d4ed8;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;"
                        onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='#1d4ed8'">Save</button>
                </div>
            </form>
        </div>
    </div>

<script>
                function openEditModal(id, name, price, priceType, isActive, icon, color, categoryId) {
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-price').value = price;
            document.getElementById('edit-price-type').value = priceType;
            document.getElementById('edit-active').value = isActive;
            document.getElementById('edit-category-id').value = categoryId || '';
            document.getElementById('edit-service-form').action = '/settings/services/' + id;

            // Select the color
            document.querySelectorAll('input[name="color"][id^="edit-color-"]').forEach(radio => {
                if (radio.value === color) radio.checked = true;
            });

            document.getElementById('edit-service-modal').style.display = 'flex';
        }
        
        function openEditCategoryModal(id, name, isActive) {
            document.getElementById('edit-category-name').value = name;
            document.getElementById('edit-category-active').value = isActive;
            document.getElementById('edit-category-form').action = '/settings/service-categories/' + id;
            document.getElementById('edit-category-modal').style.display = 'flex';
        }
        
        document.getElementById('add-service-modal').addEventListener('click', function(e) {
            if(e.target === this) this.style.display = 'none';
        });
        document.getElementById('edit-service-modal').addEventListener('click', function(e) {
            if(e.target === this) this.style.display = 'none';
        });
        document.getElementById('add-category-modal').addEventListener('click', function(e) {
            if(e.target === this) this.style.display = 'none';
        });
        document.getElementById('edit-category-modal').addEventListener('click', function(e) {
            if(e.target === this) this.style.display = 'none';
        });
        document.getElementById('add-delivery-range-modal').addEventListener('click', function(e) {
            if(e.target === this) this.style.display = 'none';
        });
        document.getElementById('add-pickup-time-modal').addEventListener('click', function(e) {
            if(e.target === this) this.style.display = 'none';
        });
    </script>
</x-app-layout>

