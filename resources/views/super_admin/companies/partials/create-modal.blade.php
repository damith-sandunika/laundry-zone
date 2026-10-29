<!-- ══════════ PROGRESSIVE PROVISIONING LIVE MODAL (LIGHT THEME) ══════════ -->
<div x-show="showModal" style="display: none;"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity"
    x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

    <div class="w-full max-w-xl bg-white border border-slate-200 rounded-3xl shadow-2xl overflow-hidden"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0">

        <!-- Modal Header -->
        <div class="px-6 py-5 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center shadow-sm"
                    :class="hasError ? 'bg-rose-50 text-rose-600 border border-rose-200' : (isCompleted ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-blue-50 text-blue-600 border border-blue-200')">
                    <template x-if="!isCompleted && !hasError">
                        <svg class="animate-spin w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </template>
                    <template x-if="isCompleted">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </template>
                    <template x-if="hasError">
                        <svg class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </template>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900" x-text="isCompleted ? 'Tenant Provisioning Completed!' : (hasError ? 'Provisioning Failed' : 'Auto-Provisioning Tenant...')"></h3>
                    <p class="text-xs text-slate-400" x-text="isCompleted ? 'Database isolated, tables migrated, owner account active.' : (hasError ? 'An error occurred during provisioning.' : 'Step-by-step isolated environment setup in progress.')"></p>
                </div>
            </div>
            <span class="text-xs font-mono font-black" :class="hasError ? 'text-rose-600' : (isCompleted ? 'text-emerald-600' : 'text-blue-600')" x-text="progressPercent + '%'"></span>
        </div>

        <!-- Progress Bar -->
        <div class="w-full bg-slate-100 h-1.5 overflow-hidden">
            <div class="h-full transition-all duration-500 ease-out"
                :class="hasError ? 'bg-rose-500' : (isCompleted ? 'bg-emerald-500' : 'bg-blue-600')"
                :style="`width: ${progressPercent}%`"></div>
        </div>

        <!-- Steps Body -->
        <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto custom-scrollbar">
            <!-- Error Alert -->
            <template x-if="hasError">
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-3">
                    <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div class="flex-1">
                        <strong class="font-bold text-rose-900 block text-sm">Provisioning Error</strong>
                        <p class="mt-1" x-text="errorMessage"></p>
                    </div>
                </div>
            </template>

            <!-- List of Steps -->
            <div class="space-y-2.5">
                <template x-for="step in steps" :key="step.id">
                    <div class="flex items-start gap-3.5 p-3 rounded-2xl transition-all duration-300"
                        :class="step.status === 'active' ? 'bg-blue-50/80 border border-blue-200' : (step.status === 'completed' ? 'bg-slate-50/60 border border-slate-200/60' : (step.status === 'failed' ? 'bg-rose-50 border border-rose-200' : 'opacity-40 border border-transparent'))">
                        
                        <div class="w-7 h-7 rounded-xl flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold transition-colors"
                            :class="{
                                'bg-emerald-100 text-emerald-700 border border-emerald-200': step.status === 'completed',
                                'bg-blue-600 text-white animate-pulse shadow-sm shadow-blue-500/30': step.status === 'active',
                                'bg-rose-100 text-rose-700 border border-rose-200': step.status === 'failed',
                                'bg-slate-100 text-slate-400': step.status === 'pending'
                            }">
                            <template x-if="step.status === 'completed'"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg></template>
                            <template x-if="step.status === 'active'"><svg class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg></template>
                            <template x-if="step.status === 'failed'"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></template>
                            <template x-if="step.status === 'pending'"><span x-text="step.id"></span></template>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold" :class="{'text-emerald-700': step.status === 'completed', 'text-slate-900': step.status === 'active', 'text-rose-700': step.status === 'failed', 'text-slate-400': step.status === 'pending'}" x-text="step.title"></h4>
                                <span class="text-[10px] font-mono font-semibold uppercase tracking-wider px-2 py-0.5 rounded-md"
                                    :class="{'text-emerald-700 bg-emerald-100': step.status === 'completed', 'text-blue-700 bg-blue-100': step.status === 'active', 'text-rose-700 bg-rose-100': step.status === 'failed', 'text-slate-400 bg-slate-100': step.status === 'pending'}"
                                    x-text="step.status === 'completed' ? 'Done' : (step.status === 'active' ? 'Running...' : (step.status === 'failed' ? 'Failed' : 'Pending'))"></span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5" x-text="step.desc"></p>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Success Card -->
            <template x-if="isCompleted && tenantResult">
                <div class="mt-4 p-5 rounded-2xl bg-slate-50 border border-emerald-300 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-200/80 pb-3">
                        <div>
                            <span class="text-[10px] uppercase font-bold tracking-wider text-emerald-600">Company Ready & Live</span>
                            <h4 class="text-base font-black text-slate-900" x-text="tenantResult.name"></h4>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-purple-50 text-purple-700 border border-purple-200" x-text="tenantResult.database_name"></span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="p-3 rounded-xl bg-white border border-slate-200">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Subdomain Access URL</span>
                            <a :href="tenantResult.url" target="_blank" class="font-mono text-blue-600 font-bold hover:underline truncate block mt-0.5" x-text="tenantResult.url"></a>
                        </div>
                        <div class="p-3 rounded-xl bg-white border border-slate-200">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Staff Login URL</span>
                            <a :href="tenantResult.staff_login_url" target="_blank" class="font-mono text-sky-600 font-bold hover:underline truncate block mt-0.5" x-text="tenantResult.staff_login_url"></a>
                        </div>
                        <div class="p-3 rounded-xl bg-white border border-slate-200">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Owner Email</span>
                            <span class="font-mono text-slate-800 font-bold block mt-0.5" x-text="tenantResult.owner_email"></span>
                        </div>
                        <div class="p-3 rounded-xl bg-white border border-slate-200">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Initial Password</span>
                            <span class="font-mono text-amber-700 font-bold block mt-0.5" x-text="tenantResult.owner_password"></span>
                        </div>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3">
                        <a :href="redirectUrl" class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold transition">&larr; Companies List</a>
                        <a :href="tenantResult.staff_login_url" target="_blank" class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition">
                            <span>Open Staff Login</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                </div>
            </template>
        </div>

        <template x-if="hasError">
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button type="button" @click="showModal = false" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition">Back to Form</button>
            </div>
        </template>
    </div>
</div>
