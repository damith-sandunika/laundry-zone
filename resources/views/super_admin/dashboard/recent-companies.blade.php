<!-- ══════════ RECENT COMPANIES TABLE (LIGHT THEME) ══════════ -->
<div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h2 class="text-base font-bold text-slate-900">Recent Registered Companies</h2>
            <p class="text-xs text-slate-500 mt-0.5">Active laundry businesses provisioned on this platform cluster.</p>
        </div>
        <a href="{{ route('super_admin.companies.index') }}"
            class="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1">
            <span>View All ({{ $totalTenants }})</span>
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <th class="px-5 py-3.5">Company & Subdomain</th>
                    <th class="px-4 py-3.5">Database Name</th>
                    <th class="px-4 py-3.5">Owner Contact</th>
                    <th class="px-4 py-3.5">Plan / MRR</th>
                    <th class="px-4 py-3.5">Status</th>
                    <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($recentTenants as $tenant)
                    <tr class="hover:bg-slate-50/80 transition duration-150">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                @if($tenant->logo_path)
                                    <img src="{{ asset($tenant->logo_path) }}" alt="{{ $tenant->name }}" class="w-9 h-9 rounded-xl object-cover border border-slate-200">
                                @else
                                    <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center font-black text-sm">
                                        {{ strtoupper(substr($tenant->name, 0, 2)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <a href="{{ route('super_admin.companies.show', $tenant) }}" class="font-bold text-slate-900 hover:text-blue-600 truncate block">
                                        {{ $tenant->name }}
                                    </a>
                                    <div class="text-xs font-mono text-blue-600">
                                        <a href="{{ $tenant->url }}" target="_blank" class="hover:underline">
                                            {{ parse_url($tenant->url, PHP_URL_HOST) ?? ($tenant->subdomain . '.' . \App\Services\Tenancy\TenantManager::getCentralBaseDomain()) }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="px-4 py-4">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                {{ $tenant->database_name }}
                            </span>
                        </td>

                        <td class="px-4 py-4">
                            <div class="font-medium text-slate-800 text-xs">{{ $tenant->latestOwner->name ?? 'No Owner' }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">{{ $tenant->latestOwner->phone ?? $tenant->phone ?? '—' }}</div>
                        </td>

                        <td class="px-4 py-4">
                            <div class="text-xs font-bold text-slate-900">{{ $tenant->plan }}</div>
                            <div class="text-xs font-bold text-emerald-600">
                                Rs. {{ number_format($tenant->total_monthly_price, 0) }} /mo
                            </div>
                        </td>

                        <td class="px-4 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $tenant->status_badge['bg'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $tenant->status_badge['dot'] }}"></span>
                                <span>{{ $tenant->status_badge['label'] }}</span>
                            </span>
                        </td>

                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ $tenant->staff_login_url }}" target="_blank"
                                    class="p-2 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold transition flex items-center gap-1"
                                    title="Open Staff Login">
                                    <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                                    <span class="hidden sm:inline">Login</span>
                                </a>
                                <a href="{{ route('super_admin.companies.show', $tenant) }}"
                                    class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition"
                                    title="View Details">
                                    <i class="fa-regular fa-eye text-xs"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-slate-400 text-xs">
                            No companies found. Create your first company above.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
