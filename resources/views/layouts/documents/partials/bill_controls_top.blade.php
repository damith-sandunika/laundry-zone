<!-- 1. Header & Logo -->
<div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider flex items-center justify-between">
        <span>1. Logo, Header & Contacts</span>
        <label class="flex items-center gap-1.5 cursor-pointer text-xs font-semibold text-slate-600">
            <input type="checkbox" name="show_logo" value="1" x-model="showLogo" {{ $showLogo ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500">
            <span>Show Logo</span>
        </label>
    </div>

    <div x-show="showLogo" class="grid grid-cols-2 gap-3 pt-1">
        <div>
            <label class="block text-[11px] font-bold text-slate-600 mb-1">Logo Size (px)</label>
            <input type="range" name="custom_fields[logo_size]" min="30" max="120" x-model="logoSize" value="{{ $logoSize }}" class="w-full">
            <span class="text-[11px] font-mono text-blue-600 font-bold" x-text="logoSize + 'px'"></span>
        </div>
        <div>
            <label class="block text-[11px] font-bold text-slate-600 mb-1">Roll Width</label>
            <select name="custom_fields[paper_width]" x-model="paperWidth" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-white">
                <option value="260px" {{ $paperWidth === '260px' ? 'selected' : '' }}>58mm Thermal (Narrow)</option>
                <option value="300px" {{ $paperWidth === '300px' ? 'selected' : '' }}>80mm Thermal (Standard)</option>
                <option value="340px" {{ $paperWidth === '340px' ? 'selected' : '' }}>Wide Receipt</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-[11px] font-bold text-slate-600 mb-1">Company Name</label>
            <input type="text" name="header_title" x-model="headerTitle" value="{{ $headerTitle }}" class="w-full text-xs font-bold rounded-xl border-slate-200 bg-white">
        </div>
        <div>
            <label class="block text-[11px] font-bold text-slate-600 mb-1">Title Size</label>
            <select name="custom_fields[company_name_size]" x-model="companyNameSize" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-white">
                <option value="15px" {{ $companyNameSize === '15px' ? 'selected' : '' }}>15px</option>
                <option value="18px" {{ $companyNameSize === '18px' ? 'selected' : '' }}>18px (Default)</option>
                <option value="20px" {{ $companyNameSize === '20px' ? 'selected' : '' }}>20px</option>
                <option value="22px" {{ $companyNameSize === '22px' ? 'selected' : '' }}>22px (Bold Large)</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-[11px] font-bold text-slate-600 mb-1">Subtitle / Tagline</label>
            <input type="text" name="header_subtitle" x-model="headerSubtitle" value="{{ $headerSubtitle }}" class="w-full text-xs font-medium rounded-xl border-slate-200 bg-white">
        </div>
        <div>
            <label class="block text-[11px] font-bold text-slate-600 mb-1">Contact Phone</label>
            <input type="text" name="custom_fields[branch_phone]" x-model="branchPhone" value="{{ $branchPhone }}" class="w-full text-xs font-medium rounded-xl border-slate-200 bg-white">
        </div>
    </div>

    <div>
        <label class="block text-[11px] font-bold text-slate-600 mb-1">Branch Address</label>
        <input type="text" name="custom_fields[branch_address]" x-model="branchAddress" value="{{ $branchAddress }}" class="w-full text-xs font-medium rounded-xl border-slate-200 bg-white">
    </div>
</div>

<!-- 2. Typography -->
<div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider">2. Typography</div>
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-[11px] font-bold text-slate-600 mb-1">Font Family</label>
            <select name="font_family" x-model="fontFamily" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-white">
                <option value="'Inter', system-ui, sans-serif" {{ str_contains($layout->font_family, 'Inter') ? 'selected' : '' }}>Inter</option>
                <option value="'Helvetica Neue', Helvetica, Arial, sans-serif" {{ str_contains($layout->font_family, 'Helvetica') ? 'selected' : '' }}>Helvetica / Arial</option>
                <option value="'Courier New', monospace" {{ str_contains($layout->font_family, 'Courier') ? 'selected' : '' }}>Courier Mono</option>
                <option value="'Segoe UI', sans-serif" {{ str_contains($layout->font_family, 'Segoe UI') ? 'selected' : '' }}>Segoe UI</option>
            </select>
        </div>
        <div>
            <label class="block text-[11px] font-bold text-slate-600 mb-1">Base Font Size</label>
            <select name="font_size" x-model="fontSize" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-white">
                <option value="11px" {{ $layout->font_size === '11px' ? 'selected' : '' }}>11px (Compact)</option>
                <option value="12px" {{ $layout->font_size === '12px' ? 'selected' : '' }}>12px (Standard)</option>
                <option value="13px" {{ $layout->font_size === '13px' ? 'selected' : '' }}>13px (Clear)</option>
            </select>
        </div>
    </div>
</div>

<!-- 3. Section Toggles -->
<div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider">3. Section Visibility</div>
    <div class="grid grid-cols-3 gap-2 text-xs font-semibold text-slate-700">
        <label class="flex items-center gap-2 cursor-pointer bg-white p-2 rounded-lg border border-slate-200">
            <input type="checkbox" name="custom_fields[show_due_date]" value="1" x-model="showDueDate" {{ $showDueDate == '1' ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500">
            <span>Due Date</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer bg-white p-2 rounded-lg border border-slate-200">
            <input type="checkbox" name="custom_fields[show_delivery_fee]" value="1" x-model="showDeliveryFee" {{ $showDeliveryFee == '1' ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500">
            <span>Delivery Fee</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer bg-white p-2 rounded-lg border border-slate-200">
            <input type="checkbox" name="custom_fields[show_discount]" value="1" x-model="showDiscount" {{ $showDiscount == '1' ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500">
            <span>Discount Row</span>
        </label>
    </div>
</div>

