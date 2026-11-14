<div style="display: block;" x-data="{ 
    formCompanyName: '{{ addslashes(\App\Models\Setting::get('company_name', '')) }}',
    formName: '{{ addslashes($branch->name ?? '') }}',
    formAddress: '{{ addslashes($branch->address ?? '') }}',
    formPhone: '{{ addslashes($branch->phone ?? '') }}',
    formTagline: '{{ addslashes($branch->tagline ?? '') }}',
    formLogoSize: {{ $branch->logo_size ?? 140 }},
    previewUrl: '{{ $branch->logo_path ? asset($branch->logo_path) : '' }}'
}">
    <form action="{{ route('settings.updateGeneral') }}" method="POST" enctype="multipart/form-data" class="flex flex-col lg:flex-row gap-6">
        @csrf
        <div class="flex-1 space-y-4 {{ auth()->user()->role === 'super_admin' ? 'w-full' : 'max-w-xl' }}">
            @if(auth()->user()->role === 'super_admin')
                @include('settings.tabs.partials.superadmin_general')
            @else
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Branch Name</label>
                    <input type="text" name="name" x-model="formName" class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Address</label>
                    <input type="text" name="address" x-model="formAddress" class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Latitude</label>
                        <input type="text" name="latitude" value="{{ old('latitude', optional($branch)->latitude) }}" class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Longitude</label>
                        <input type="text" name="longitude" value="{{ old('longitude', optional($branch)->longitude) }}" class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Telephone Number</label>
                        <input type="text" name="phone" x-model="formPhone" class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">WhatsApp Number</label>
                        <input type="text" name="whatsapp_number" value="{{ optional($branch)->whatsapp_number ?? '' }}" class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Tagline (Will appear on print bills)</label>
                    <input type="text" name="tagline" x-model="formTagline" class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="pt-4 mt-4 border-t border-gray-100">
                    <label class="block text-xs font-bold text-gray-800 uppercase mb-1">Default POS Pickup Days</label>
                    <p class="text-[11px] text-gray-500 mb-2">Number of days from today to auto-set the estimated pickup date for new POS orders.</p>
                    <input type="number" name="default_pickup_days" value="{{ \App\Models\Setting::get('default_pickup_days', '3') }}" min="0" max="365" class="w-full text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500 font-mono">
                </div>
                <div class="pt-4 mt-4 border-t border-gray-100">
                    <label class="block text-xs font-bold text-gray-800 uppercase mb-1">Default POS Pickup Time</label>
                    <p class="text-[11px] text-gray-500 mb-2">Fixed time for the estimated pickup. e.g. 5:00 PM enter <code class="bg-gray-100 px-1 rounded">17:00</code></p>
                    <input type="time" name="default_pickup_time" value="{{ \App\Models\Setting::get('default_pickup_time', '17:00') }}" class="w-48 text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500 font-mono">
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
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">Branch Thermal Bill Logo</label>
                    <div @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false"
                        @drop.prevent="handleDrop($event)" @click="$refs.fileInput.click()"
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
            @endif

            <div class="pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 hover:shadow-md hover:-translate-y-0.5 transition-all w-full md:w-auto justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Save Settings
                </button>
            </div>
        </div>

        @if(auth()->user()->role !== 'super_admin')
            @include('settings.tabs.partials.thermal_preview')
        @endif
    </form>
</div>
