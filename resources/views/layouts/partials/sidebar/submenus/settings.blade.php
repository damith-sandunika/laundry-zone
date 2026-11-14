    <div x-data="{ open: {{ request()->routeIs('settings.*') ? 'true' : 'false' }} }">
        <button @click="open = !open"
            class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('settings.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
            <div class="flex items-center gap-3">
                <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Settings
            </div>
            <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200 opacity-60" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div x-show="open" style="display: none;" class="pl-9 mt-1 space-y-1">
            <a href="{{ route('settings.general') }}"
                class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('settings.general') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">General
                Settings</a>
            @if($canManageConfig)
                <a href="{{ route('settings.extras') }}"
                    class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('settings.extras') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Extras</a>
                <a href="{{ route('settings.turnarounds') }}"
                    class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('settings.turnarounds') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Turnarounds</a>
                @if(config('app.delivery_enabled'))
                    <a href="{{ route('settings.delivery') }}"
                        class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('settings.delivery') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Delivery</a>
                @endif
                <a href="{{ route('settings.pickup-times') }}"
                    class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('settings.pickup-times') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Pickup
                    Times</a>
                <a href="{{ route('settings.expense_categories') }}"
                    class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('settings.expense_categories') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Expense
                    Categories</a>
            @endif
            @if(!in_array($role, ['owner', 'super_admin']))
                <a href="{{ route('settings.cubbies') }}"
                    class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('settings.cubbies') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Stock
                    Locations</a>
            @endif
            @if(in_array($role, ['owner', 'super_admin']))
                <a href="{{ route('settings.payment-methods') }}"
                    class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('settings.payment-methods') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Payment
                    Methods</a>
                <a href="{{ route('settings.notifications') }}"
                    class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('settings.notifications') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Notifications</a>
            @endif
        </div>
    </div>
