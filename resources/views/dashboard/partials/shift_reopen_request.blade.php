{{-- Branch shift was closed today and strictly requires Super Admin approval to re-open --}}
<div class="text-center mb-8">
    <div class="w-16 h-16 rounded-2xl bg-amber-50 flex items-center justify-center mx-auto mb-4 ring-8 ring-amber-50/50">
        <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
    </div>
    <h3 class="text-2xl font-black text-slate-900 tracking-tight mb-2">Today's Shift is Closed</h3>
    <p class="text-[14px] font-medium text-slate-500 max-w-lg mx-auto">
        The branch shift was already closed for today. Under company policy, branches can only open once per day. Re-opening on the same day requires <span class="font-bold text-slate-700">Super Admin Approval</span>.
    </p>

    {{-- Next Daily Shift Open Countdown Timer --}}
    <div class="mt-6 inline-flex flex-col items-center justify-center bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl p-5 shadow-lg shadow-slate-900/10 border border-slate-700/50"
         x-data="branchCountdownTimer()" x-init="start()">
        <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 mb-2 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-amber-400 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Next Standard Shift Opens In (Tomorrow at Midnight / 00:00)
        </span>
        <div class="flex items-center gap-3">
            <div class="flex flex-col items-center">
                <span class="text-2xl sm:text-3xl font-black font-mono tracking-tight text-white bg-slate-800/80 px-3 py-1.5 rounded-xl border border-slate-700" x-text="hours">00</span>
                <span class="text-[10px] font-bold text-slate-400 mt-1 uppercase">Hours</span>
            </div>
            <span class="text-2xl font-black text-slate-500 mb-4">:</span>
            <div class="flex flex-col items-center">
                <span class="text-2xl sm:text-3xl font-black font-mono tracking-tight text-white bg-slate-800/80 px-3 py-1.5 rounded-xl border border-slate-700" x-text="minutes">00</span>
                <span class="text-[10px] font-bold text-slate-400 mt-1 uppercase">Mins</span>
            </div>
            <span class="text-2xl font-black text-slate-500 mb-4">:</span>
            <div class="flex flex-col items-center">
                <span class="text-2xl sm:text-3xl font-black font-mono tracking-tight text-amber-400 bg-slate-800/80 px-3 py-1.5 rounded-xl border border-slate-700" x-text="seconds">00</span>
                <span class="text-[10px] font-bold text-slate-400 mt-1 uppercase">Secs</span>
            </div>
        </div>
        <span class="text-[11px] font-medium text-slate-400 mt-2">
            Or request Super Admin approval below to re-open immediately today.
        </span>
    </div>
</div>

<script>
    function branchCountdownTimer() {
        return {
            hours: '00',
            minutes: '00',
            seconds: '00',
            start() {
                const update = () => {
                    const now = new Date();
                    const midnight = new Date();
                    midnight.setHours(24, 0, 0, 0);
                    const diff = Math.max(0, midnight - now);
                    const h = Math.floor(diff / (1000 * 60 * 60));
                    const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const s = Math.floor((diff % (1000 * 60)) / 1000);
                    this.hours = String(h).padStart(2, '0');
                    this.minutes = String(m).padStart(2, '0');
                    this.seconds = String(s).padStart(2, '0');
                };
                update();
                setInterval(update, 1000);
            }
        }
    }
</script>

@if(!empty($pendingReopenRequest))
    <div class="bg-amber-50/80 border border-amber-200/80 rounded-2xl p-6 text-center max-w-lg mx-auto">
        <div class="flex items-center justify-center gap-2 mb-2 text-amber-800 font-black text-base">
            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
            Approval Request Pending
        </div>
        <p class="text-[13px] font-medium text-amber-700 mb-3">
            Your re-open request has been submitted to Super Admin on <strong>{{ $pendingReopenRequest->created_at->format('h:i A') }}</strong>. Please wait for Super Admin approval.
        </p>
        <div class="bg-white/80 rounded-xl p-3 text-left border border-amber-200 text-xs text-slate-600">
            <strong class="text-slate-800">Submitted Reason:</strong> {{ $pendingReopenRequest->reason }}
        </div>
    </div>
@else
    <form action="{{ route('shifts.reopen.request') }}" method="POST" class="max-w-xl mx-auto">
        @csrf
        <div class="bg-slate-50 rounded-2xl border border-slate-200 p-6 mb-6">
            <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                Reason for Re-opening Branch Shift Today <span class="text-red-500">*</span>
            </label>
            <textarea name="reason" rows="3" required placeholder="Explain why the branch shift needs to be re-opened (e.g. Late customer pickup, emergency deliveries)..." class="w-full text-sm bg-white border border-slate-200 rounded-xl p-3 text-slate-800 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all placeholder:text-slate-400"></textarea>
            <p class="text-[11px] text-slate-400 mt-2 font-medium">
                This request will be immediately dispatched to Super Admin for authorization.
            </p>
        </div>

        <div class="flex justify-center">
            <button type="submit" class="w-full sm:w-auto px-8 py-3.5 bg-gradient-to-r from-amber-500 to-orange-600 text-white text-[14px] font-black tracking-wide rounded-xl shadow-lg shadow-orange-500/30 hover:shadow-orange-500/50 hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                Send Request to Super Admin
            </button>
        </div>
    </form>
@endif
