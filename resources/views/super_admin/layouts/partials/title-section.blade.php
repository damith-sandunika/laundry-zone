<!-- ══════════ SIGNATURE TITLE SECTION (GEOMETRIC PATTERN) ══════════ -->
<div class="mb-6 lg:mb-8 bg-white rounded-xl p-3.5 sm:p-4 md:p-5 shadow-sm border border-slate-200/90 relative flex items-center justify-between gap-3 md:gap-4 overflow-hidden">
    <!-- Geometric Pattern Right Side (Signature Laundry Zone Diamond Motif) -->
    <div class="absolute right-0 top-1/2 -translate-y-1/2 flex items-center pointer-events-none opacity-60">
        <div class="w-24 h-24 bg-blue-50/80 rotate-45 translate-x-12 rounded-lg"></div>
        <div class="w-36 h-36 bg-blue-100/70 rotate-45 translate-x-6 -ml-8 rounded-xl"></div>
        <div class="w-48 h-48 bg-blue-200/50 rotate-45 -ml-12 rounded-2xl"></div>
        <div class="w-32 h-32 bg-blue-300/40 rotate-45 -ml-16 rounded-lg"></div>
    </div>

    <!-- Content Area: Back Button, Title, Subtitle, and Action Buttons -->
    <div class="relative z-10 flex-1 flex flex-col md:flex-row md:items-center md:justify-between gap-4 w-full">
        <div class="flex items-center gap-3">
            <button type="button"
                onclick="if(window.history.length > 1) { window.history.back(); } else { window.location.href='{{ route('super_admin.dashboard') }}'; }"
                class="p-2 sm:p-2.5 min-w-[38px] sm:min-w-[42px] min-h-[38px] sm:min-h-[42px] rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 hover:text-blue-600 border border-slate-200/80 shadow-sm transition-all flex items-center justify-center shrink-0 active:scale-95 group"
                title="Go Back" aria-label="Go Back">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 transition-transform group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </button>
            <div>
                <h2 class="text-lg sm:text-xl md:text-2xl font-black text-slate-900 tracking-tight leading-tight">{{ $title ?? '' }}</h2>
                @if(!empty($subtitle))
                    <p class="text-xs sm:text-[13px] font-semibold text-slate-500 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        @if(isset($slot) && trim($slot))
            <div class="flex flex-wrap items-center gap-2 sm:gap-3 shrink-0">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
