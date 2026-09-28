            <div style="display: block;" x-data="{ 
                formCompanyName: '{{ addslashes(\App\Models\Setting::get('company_name', '')) }}',
                formName: '{{ addslashes($branch->name ?? '') }}',
                formAddress: '{{ addslashes($branch->address ?? '') }}',
                formPhone: '{{ addslashes($branch->phone ?? '') }}',
                formTagline: '{{ addslashes($branch->tagline ?? '') }}',
                formLogoSize: {{ $branch->logo_size ?? 140 }},
                previewUrl: '{{ $branch->logo_path ? asset($branch->logo_path) : '' }}'
            }">
                <form action="{{ route('settings.updateGeneral') }}" method="POST" enctype="multipart/form-data"
                    class="flex flex-col lg:flex-row gap-6">
                    <div class="flex-1 space-y-4 {{ auth()->user()->role === 'super_admin' ? 'w-full' : 'max-w-xl' }}">
                        @csrf
                        @if(auth()->user()->role === 'super_admin')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Company Name (Global
                                            for all Bills)</label>
                                        <input type="text" name="company_name" x-model="formCompanyName"
                                            class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    <div class="pt-2">
                                        <label class="block text-xs font-bold text-gray-800 uppercase mb-1">Global Auto-Close
                                            Time</label>
                                        <p class="text-[11px] text-gray-500 mb-2">All branches will be automatically closed and
                                            their daily shift reports will be generated at this time.</p>
                                        @php
                                            $globalCloseTime = \App\Models\Branch::first()?->auto_close_time;
                                        @endphp
                                        <input type="time" name="auto_close_time"
                                            value="{{ $globalCloseTime ? \Carbon\Carbon::parse($globalCloseTime)->format('H:i') : '' }}"
                                            class="w-48 text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500 font-mono">
                                    </div>

                                    <div class="pt-4 mt-4 border-t border-gray-100">
                                        <label class="block text-xs font-bold text-gray-800 uppercase mb-1">Site Landing Page (Redirect)</label>
                                        <p class="text-[11px] text-gray-500 mb-2">
                                            When visitors open the website, redirect them to this URL path. Leave blank to show the default homepage.
                                            <br>Example: <code class="bg-gray-100 px-1 rounded">/customer/login</code> or <code class="bg-gray-100 px-1 rounded">/customer/book</code>
                                        </p>
                                        <input type="text" name="site_landing_page"
                                            value="{{ \App\Models\Setting::get('site_landing_page', '') }}"
                                            placeholder="Leave blank for default homepage"
                                            class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500 font-mono">
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div class="pt-4 border-t border-gray-100 md:border-t-0 md:pt-0">
                                        <label class="flex items-start gap-2 cursor-pointer w-max max-w-full">
                                            <input type="checkbox" name="centralized_management" value="1" {{ \App\Models\Setting::get('centralized_management', '1') == '1' ? 'checked' : '' }} class="w-4 h-4 mt-0.5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            <div>
                                                <span class="text-sm font-semibold text-gray-700 block">Centralized Configuration Management</span>
                                                <p class="text-[11px] text-gray-500 mt-1">
                                                    If enabled, Super Admin manages Services, Packages, Extras, etc., for ALL branches. <br>
                                                    If disabled, Branch Admins manage their own configurations locally.
                                                </p>
                                            </div>
                                        </label>
                                    </div>

                                    <div class="pt-4 mt-4 border-t border-gray-100">
                                        <label class="flex items-start gap-2 cursor-pointer w-max max-w-full">
                                            <input type="checkbox" name="auto_print_label" value="1" {{ \App\Models\Setting::get('auto_print_label', '0') == '1' ? 'checked' : '' }} class="w-4 h-4 mt-0.5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            <div>
                                                <span class="text-sm font-semibold text-gray-700 block">Auto Print Label on Order Completion</span>
                                                <p class="text-[11px] text-gray-500 mt-1">
                                                    If enabled, the label page will automatically open in a new tab when an order is marked as complete.
                                                </p>
                                            </div>
                                        </label>
                                    </div>

                                    <div class="pt-4 mt-4 border-t border-gray-100">
                                        <label class="flex items-start gap-2 cursor-pointer w-max max-w-full">
                                            <input type="checkbox" name="auto_print_pos_chit" value="1" {{ \App\Models\Setting::get('auto_print_pos_chit', '0') == '1' ? 'checked' : '' }} class="w-4 h-4 mt-0.5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            <div>
                                                <span class="text-sm font-semibold text-gray-700 block">Auto Print Chit on POS Order</span>
                                                <p class="text-[11px] text-gray-500 mt-1">
                                                    If enabled, the order chit (bill) will automatically open in a new tab when an order is placed from the POS system.
                                                </p>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                                    Branch Name
                                </label>
                                <input type="text" name="name" x-model="formName"
                                    class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Address</label>
                                <input type="text" name="address" x-model="formAddress"
                                    class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Latitude</label>
                                    <input type="text" name="latitude"
                                        value="{{ old('latitude', optional($branch)->latitude) }}"
                                        class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-gray-700 uppercase mb-1">Longitude</label>
                                    <input type="text" name="longitude"
                                        value="{{ old('longitude', optional($branch)->longitude) }}"
                                        class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Telephone
                                        Number</label>
                                    <input type="text" name="phone" x-model="formPhone"
                                        class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">WhatsApp
                                        Number</label>
                                    <input type="text" name="whatsapp_number"
                                        value="{{ optional($branch)->whatsapp_number ?? '' }}"
                                        class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Tagline (Will appear
                                    on print bills)</label>
                                <input type="text" name="tagline" x-model="formTagline"
                                    class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div class="pt-4 mt-4 border-t border-gray-100">
                                <label class="block text-xs font-bold text-gray-800 uppercase mb-1">Default POS Pickup Days</label>
                                <p class="text-[11px] text-gray-500 mb-2">
                                    Number of days from today to auto-set the estimated pickup date for new POS orders.
                                </p>
                                <input type="number" name="default_pickup_days"
                                    value="{{ \App\Models\Setting::get('default_pickup_days', '3') }}"
                                    min="0" max="365"
                                    class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500 font-mono">
                            </div>

                            <div class="pt-4 mt-4 border-t border-gray-100">
                                <label class="block text-xs font-bold text-gray-800 uppercase mb-1">Default POS Pickup Time</label>
                                <p class="text-[11px] text-gray-500 mb-2">
                                    Fixed time for the estimated pickup. e.g. 5:00 PM enter <code class="bg-gray-100 px-1 rounded">17:00</code>
                                </p>
                                <input type="time" name="default_pickup_time"
                                    value="{{ \App\Models\Setting::get('default_pickup_time', '17:00') }}"
                                    class="w-48 text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500 font-mono">
                            </div>

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
                            <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">Branch Thermal Bill
                                Logo</label>

                            <div @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false"
                                @drop.prevent="handleDrop($event)" @click="$refs.fileInput.click()"
                                :class="dragOver ? 'border-blue-500 bg-blue-50' : 'border-dashed border-gray-300 bg-gray-50 hover:bg-gray-100'"
                                class="relative flex flex-col items-center justify-center w-full h-36 border-2 rounded-xl cursor-pointer transition-colors group mb-4">

                                <input x-ref="fileInput" type="file" name="logo"
                                    accept="image/png, image/jpeg, image/jpg" class="hidden" @change="updatePreview">

                                <div x-show="!previewUrl" class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 mb-3 text-gray-400 group-hover:text-blue-500 transition-colors"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <p class="mb-1 text-sm text-gray-500"><span class="font-bold text-gray-700">Click to
                                            upload</span> or drag and drop</p>
                                    <p class="text-xs text-gray-400">PNG, JPG (Recommended: Black & White)</p>
                                </div>

                                <div x-show="previewUrl" style="display: none;"
                                    class="flex flex-col items-center justify-center p-2 h-full w-full">
                                    <img :src="previewUrl" class="h-20 object-contain mb-2">
                                    <p class="text-xs font-bold text-blue-600 truncate max-w-[80%]"
                                        x-text="fileName ? fileName : 'Current Active Logo'"></p>
                                    <p class="text-[10px] text-gray-400 mt-0.5 group-hover:text-gray-500">Click or drag
                                        to replace</p>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label class="block text-xs font-semibold text-gray-700 uppercase">Logo Size</label>
                                    <span class="text-xs font-bold text-blue-600" x-text="formLogoSize + 'px'"></span>
                                </div>
                                <input type="range" name="logo_size" x-model="formLogoSize" min="20" max="300"
                                    class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                                <p class="text-[10px] text-gray-400 mt-1">Adjust to scale the logo appropriately on the
                                    thermal bill.</p>
                            </div>
                        </div>
                        @endif
                        <div class="pt-6 border-t border-gray-100 flex justify-end">
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 hover:shadow-md hover:-translate-y-0.5 transition-all w-full md:w-auto justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Save Settings
                            </button>
                        </div>
                    </div>

                    @if(auth()->user()->role !== 'super_admin')
                    <!-- Thermal Bill Preview -->
                    <div class="flex-1 max-w-sm">
                        <h4 class="text-xs font-bold text-gray-700 uppercase mb-3">Live Thermal Bill Preview</h4>

                        <!-- Thermal Bill Actual Mockup -->
                        <div class="bg-white mx-auto shadow-[0_10px_30px_rgba(0,0,0,0.1)] relative"
                            style="width: 74mm; padding: 4mm 3mm; font-family: 'Inter', system-ui, -apple-system, sans-serif; font-size: 12px; color: #000; min-height: 400px;">

                            <!-- Jagged top edge effect -->
                            <div class="absolute top-0 left-0 right-0 h-2"
                                style="background-image: linear-gradient(135deg, transparent 50%, #fff 50%), linear-gradient(45deg, #fff 50%, transparent 50%); background-size: 8px 8px; background-position: 0 -4px; transform: rotate(180deg); margin-top: -8px;">
                            </div>

                            <!-- Header -->
                            <div class="text-center" style="margin-bottom: 12px;">
                                <template x-if="previewUrl">
                                    <img :src="previewUrl" alt="Logo"
                                        :style="`max-width: ${formLogoSize}px; height: auto; display: inline-block;`">
                                </template>
                            </div>

                            <div class="text-center"
                                style="font-size:18px; letter-spacing:-0.03em; font-weight: 800; margin-bottom: 2px;">
                                <span x-text="formName || 'SMART LAUNDRY'"></span>
                            </div>

                            <template x-if="formTagline">
                                <div class="text-center" style="font-size:10px; margin-bottom:2px;"
                                    x-text="formTagline"></div>
                            </template>

                            <template x-if="formPhone">
                                <div class="text-center" style="font-size:10px;">📞 <span x-text="formPhone"></span>
                                </div>
                            </template>

                            <template x-if="formAddress">
                                <div class="text-center" style="font-size:10px;" x-text="formAddress"></div>
                            </template>

                            <div style="border-top: 1.5px dashed #000; margin: 8px 0;"></div>

                            <!-- Details -->
                            <div
                                style="display: flex; justify-content: space-between; padding: 3px 0; gap: 4px; font-size: 12px;">
                                <span style="flex: 1; text-align: left;">Bill No:</span>
                                <span style="font-weight: 800; text-align: right;">ORD-9999</span>
                            </div>
                            <div
                                style="display: flex; justify-content: space-between; padding: 3px 0; gap: 4px; font-size: 12px;">
                                <span style="flex: 1; text-align: left;">Date:</span>
                                <span
                                    style="font-weight: 800; text-align: right;">{{ now()->format('d M Y h:i A') }}</span>
                            </div>
                            <div
                                style="display: flex; justify-content: space-between; padding: 3px 0; gap: 4px; font-size: 12px;">
                                <span style="flex: 1; text-align: left;">Customer:</span>
                                <span style="font-weight: 800; text-align: right;">John Doe</span>
                            </div>

                            <div style="border-top: 1.5px dashed #000; margin: 8px 0;"></div>

                            <!-- Sample Items -->
                            <div
                                style="display: flex; justify-content: space-between; padding: 3px 0; gap: 4px; margin-bottom: 2px;">
                                <span
                                    style="font-weight: 700; font-size: 12px; display: block; word-break: break-word;">Wash
                                    & Fold</span>
                                <span style="font-weight: 800;">Rs.1,200.00</span>
                            </div>
                            <div style="font-size: 12px; margin-bottom: 6px;">
                                2 x Rs.600.00
                            </div>

                            <div
                                style="display: flex; justify-content: space-between; padding: 3px 0; gap: 4px; margin-bottom: 2px;">
                                <span
                                    style="font-weight: 700; font-size: 12px; display: block; word-break: break-word;">Ironing</span>
                                <span style="font-weight: 450.00">Rs.450.00</span>
                            </div>
                            <div style="font-size: 12px; margin-bottom: 6px;">
                                3 x Rs.150.00
                            </div>

                            <div style="border-top: 1.5px dashed #000; margin: 8px 0;"></div>

                            <!-- Totals -->
                            <div
                                style="display: flex; justify-content: space-between; padding: 3px 0; gap: 4px; font-weight: 900; font-size: 15px; border-top: 2px solid #000; padding-top: 6px; margin-top: 6px;">
                                <span>TOTAL</span>
                                <span>Rs.1,650.00</span>
                            </div>

                            <div style="text-align: center; margin: 8px 0;">
                                <span
                                    style="display: inline-block; border: 1.5px solid #000; padding: 4px 10px; margin-top: 6px; font-size: 12px; font-weight: 800; border-radius: 6px;">
                                    🕒 PAY ON PICKUP
                                </span>
                            </div>

                            <div style="border-top: 1.5px dashed #000; margin: 8px 0;"></div>

                            <div style="text-align: center; font-weight: 800; font-size: 12px; margin-top: 8px;">
                                Thank you for choosing {{ \App\Models\Setting::get('company_name', 'Laundry Zone') }}.
                            </div>
                            <div style="text-align: center; font-size: 10px; margin-top: 3px; color: #333;">
                                If you are happy with our service, please leave us a Google Review!
                            </div>
                            <div style="text-align: center; font-size: 12px; margin-top: 8px;">
                                Software by DSRZone
                            </div>

                            <!-- Jagged bottom edge effect -->
                            <div class="absolute bottom-0 left-0 right-0 h-2"
                                style="background-image: linear-gradient(135deg, transparent 50%, #fff 50%), linear-gradient(45deg, #fff 50%, transparent 50%); background-size: 8px 8px; margin-bottom: -8px;">
                            </div>
                        </div>
                    </div>
                    @endif
                </form>
            </div>
