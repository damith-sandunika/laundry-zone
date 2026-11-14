    <div
        x-data="{ open: {{ request()->routeIs('email-templates.*') || request()->routeIs('sms-templates.*') ? 'true' : 'false' }} }">
        <button @click="open = !open"
            class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('email-templates.*') || request()->routeIs('sms-templates.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
            <div class="flex items-center gap-3">
                <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                Notifications
            </div>
            <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200 opacity-60" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div x-show="open" style="display: none;" class="pl-9 mt-1 space-y-1">
            <a href="{{ route('sms.sender') }}"
                class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('sms.sender') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Message
                Sender</a>
            <a href="{{ route('sms.payments') }}"
                class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('sms.payments') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">SMS
                Payment</a>
            <a href="{{ route('sms-templates.index') }}"
                class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('sms-templates.*') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">SMS
                Templates</a>
            <a href="{{ route('email-templates.index') }}"
                class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('email-templates.*') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Email
                Templates</a>
        </div>
    </div>
