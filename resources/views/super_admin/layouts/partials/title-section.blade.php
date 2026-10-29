<!-- ══════════ SIGNATURE TITLE SECTION (GEOMETRIC PATTERN) ══════════ -->
<div class="mb-6 lg:mb-8 bg-white rounded-2xl p-5 shadow-sm border border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative overflow-hidden">
    <!-- Geometric Pattern Right Side (Signature Laundry Zone Diamond Motif) -->
    <div class="absolute right-0 top-1/2 -translate-y-1/2 flex items-center pointer-events-none opacity-60">
        <div class="w-24 h-24 bg-blue-50 rotate-45 translate-x-12 rounded-lg"></div>
        <div class="w-36 h-36 bg-blue-100 rotate-45 translate-x-6 -ml-8 rounded-xl"></div>
        <div class="w-48 h-48 bg-blue-200 rotate-45 -ml-12 rounded-2xl"></div>
        <div class="w-32 h-32 bg-blue-300 rotate-45 -ml-16 rounded-lg"></div>
    </div>

    <!-- Content Area: Title, Subtitle, and Action Buttons -->
    <div class="relative z-10 flex-1 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 w-full">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-tight">{{ $title ?? '' }}</h2>
            @if(!empty($subtitle))
                <p class="text-[13px] font-bold text-slate-500 mt-1">{{ $subtitle }}</p>
            @endif
        </div>

        @if(isset($slot) && trim($slot))
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
