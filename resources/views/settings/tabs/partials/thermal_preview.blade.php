<!-- Thermal Bill Preview -->
<div class="flex-1 max-w-sm">
    <h4 class="text-xs font-bold text-gray-700 uppercase mb-3">Live Thermal Bill Preview</h4>

    <div class="bg-white mx-auto shadow-[0_10px_30px_rgba(0,0,0,0.1)] relative"
        style="width: 74mm; padding: 4mm 3mm; font-family: 'Inter', system-ui, -apple-system, sans-serif; font-size: 12px; color: #000; min-height: 400px;">

        <div class="absolute top-0 left-0 right-0 h-2"
            style="background-image: linear-gradient(135deg, transparent 50%, #fff 50%), linear-gradient(45deg, #fff 50%, transparent 50%); background-size: 8px 8px; background-position: 0 -4px; transform: rotate(180deg); margin-top: -8px;">
        </div>

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
        <div style="font-size: 12px; margin-bottom: 6px;">2 x Rs.600.00</div>

        <div style="display: flex; justify-content: space-between; padding: 3px 0; gap: 4px; margin-bottom: 2px;">
            <span style="font-weight: 700; font-size: 12px; display: block; word-break: break-word;">Ironing</span>
            <span style="font-weight: 450.00">Rs.450.00</span>
        </div>
        <div style="font-size: 12px; margin-bottom: 6px;">3 x Rs.150.00</div>

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
            Thank you for choosing {{ \App\Models\Setting::get('company_name', 'Laundry Zone') }}.
        </div>
        <div style="text-align: center; font-size: 10px; margin-top: 3px; color: #333;">
            If you are happy with our service, please leave us a Google Review!
        </div>
        <div style="text-align: center; font-size: 12px; margin-top: 8px;">
            Software by DSRZone
        </div>

        <div class="absolute bottom-0 left-0 right-0 h-2"
            style="background-image: linear-gradient(135deg, transparent 50%, #fff 50%), linear-gradient(45deg, #fff 50%, transparent 50%); background-size: 8px 8px; margin-bottom: -8px;">
        </div>
    </div>
</div>

