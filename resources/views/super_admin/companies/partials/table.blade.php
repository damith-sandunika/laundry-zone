<!-- ══════════ COMPANIES DATA TABLE (LIGHT THEME) ══════════ -->
<div class="overflow-x-auto">
    <table class="w-full text-left text-sm text-slate-700">
        <thead class="bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
            <tr>
                <th class="px-5 py-3.5">Company Name</th>
                <th class="px-4 py-3.5">Subdomain & Access</th>
                <th class="px-4 py-3.5">Database Name</th>
                <th class="px-4 py-3.5">Plan & Monthly Fee</th>
                <th class="px-4 py-3.5">Status</th>
                <th class="px-5 py-3.5 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 font-medium">
            @foreach($companies as $company)
                <tr class="hover:bg-slate-50/80 transition">
                    <!-- Company Info -->
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm overflow-hidden flex-shrink-0 shadow-sm">
                                @if($company->logo_path)
                                    <img src="{{ asset($company->logo_path) }}" alt="{{ $company->name }}" class="w-full h-full object-cover">
                                @else
                                    {{ strtoupper(substr($company->name, 0, 2)) }}
                                @endif
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('super_admin.companies.show', $company) }}" class="font-bold text-slate-900 hover:text-blue-600 block truncate">
                                    {{ $company->name }}
                                </a>
                                <div class="text-xs text-slate-400 truncate">
                                    Owner: {{ $company->latestOwner->name ?? 'None' }} ({{ $company->latestOwner->email ?? 'N/A' }})
                                </div>
                            </div>
                        </div>
                    </td>

                    <!-- Subdomain & Domain -->
                    <td class="px-4 py-4">
                        <div class="text-xs font-mono font-semibold text-blue-600">
                            <a href="{{ $company->url }}" target="_blank" class="hover:underline flex items-center gap-1">
                                <span>{{ parse_url($company->url, PHP_URL_HOST) ?? ($company->subdomain . '.' . $centralDomain) }}</span>
                                <svg class="w-3 h-3 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            </a>
                        </div>
                        <div class="mt-1">
                            <a href="{{ $company->staff_login_url }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-sky-600 hover:text-sky-700">
                                <span>Staff Login</span>
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                            </a>
                        </div>
                        @if($company->custom_domain)
                            <div class="text-[11px] font-mono text-emerald-600 mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                                <span>{{ $company->custom_domain }}</span>
                            </div>
                        @endif
                    </td>

                    <!-- Database Name -->
                    <td class="px-4 py-4">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                            {{ $company->database_name }}
                        </span>
                    </td>

                    <!-- Plan & Price -->
                    <td class="px-4 py-4">
                        <div class="text-xs font-bold text-slate-800">{{ $company->plan }}</div>
                        <div class="text-xs font-bold text-emerald-600 mt-0.5">
                            Rs. {{ number_format($company->total_monthly_price, 0) }} /mo
                        </div>
                    </td>

                    <!-- Status -->
                    <td class="px-4 py-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $company->status_badge['bg'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $company->status_badge['dot'] }}"></span>
                            <span>{{ $company->status_badge['label'] }}</span>
                        </span>
                    </td>

                    <!-- Actions -->
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('super_admin.companies.show', $company) }}"
                                class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition"
                                title="View Details">
                                <i class="fa-regular fa-eye text-xs"></i>
                            </a>
                            <a href="{{ route('super_admin.companies.edit', $company) }}"
                                class="p-2 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 transition"
                                title="Edit Company">
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                            </a>
                            <a href="{{ route('super_admin.companies.impersonate', $company) }}" target="_blank"
                                class="p-2 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 transition"
                                title="Impersonate Owner">
                                <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
    {{ $companies->links() }}
</div>
