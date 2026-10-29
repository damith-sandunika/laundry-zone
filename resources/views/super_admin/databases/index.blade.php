@extends('super_admin.layouts.app', ['title' => 'Tenant Databases'])

@section('content')
<div class="space-y-6">
    <!-- Signature Title Section -->
    @component('super_admin.layouts.partials.title-section', [
        'title' => 'Tenant Databases',
        'subtitle' => 'Multi-tenant database cluster health, table counts, and schema migration runner'
    ])
        <form action="{{ route('super_admin.databases.migrateAll') }}" method="POST"
            onsubmit="return confirm('Run migrations across ALL active tenant databases now?');" class="m-0">
            @csrf
            <button type="submit"
                class="flex items-center justify-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-[11px] sm:text-[13px] font-bold rounded-xl shadow-sm transition-colors h-9 sm:h-10 whitespace-nowrap">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Migrate All Databases
            </button>
        </form>
    @endcomponent

    <!-- Cluster Overview Cards (Light Theme) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="text-xs text-slate-400 font-bold uppercase tracking-wider">Total Databases</div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ count($dbData) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Database-per-tenant isolated schemas</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="text-xs text-slate-400 font-bold uppercase tracking-wider">Online Cluster Status</div>
            <div class="text-2xl font-black text-emerald-600 mt-2">
                {{ collect($dbData)->where('status', 'online')->count() }} / {{ count($dbData) }} Online
            </div>
            <div class="text-[11px] text-emerald-600 font-semibold mt-1">All connections responding</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="text-xs text-slate-400 font-bold uppercase tracking-wider">Central Landlord Database</div>
            <div class="text-sm font-mono font-bold text-purple-700 mt-2">{{ config('database.connections.landlord.database', 'laundry_zone_saas_central') }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Platform management & tenant registry</div>
        </div>
    </div>

    <!-- Database Cluster Table -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden">
        @if(empty($dbData))
            <div class="p-12 text-center text-slate-400 text-sm">
                No tenant databases found. Databases are auto-generated when you register a new company!
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
                        <tr>
                            <th class="px-5 py-3.5">Database Name</th>
                            <th class="px-4 py-3.5">Assigned Company</th>
                            <th class="px-4 py-3.5">Connection Status</th>
                            <th class="px-4 py-3.5">Tables Count</th>
                            <th class="px-4 py-3.5">Storage Size</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($dbData as $item)
                            @php $t = $item['tenant']; @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-5 py-4">
                                    <div class="font-mono text-sm font-bold text-purple-700">{{ $t->database_name }}</div>
                                    <div class="text-xs text-slate-400 font-mono">Host: {{ $t->db_host ?: '127.0.0.1' }}:{{ $t->db_port ?: '3306' }}</div>
                                </td>
                                <td class="px-4 py-4">
                                    <a href="{{ route('super_admin.companies.show', $t) }}" class="font-bold text-slate-900 hover:text-blue-600 block">
                                        {{ $t->name }}
                                    </a>
                                    <a href="{{ $t->url }}" target="_blank" class="text-xs font-mono text-blue-600 hover:underline">
                                        {{ parse_url($t->url, PHP_URL_HOST) ?? ($t->subdomain . '.' . \App\Services\Tenancy\TenantManager::getCentralBaseDomain()) }}
                                    </a>
                                </td>
                                <td class="px-4 py-4">
                                    @if($item['status'] === 'online')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            <span>Online</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span>Offline</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <span class="text-xs font-bold text-slate-800">{{ $item['tables'] }} tables</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="text-xs font-bold text-slate-800">{{ $item['size_mb'] }} MB</span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('super_admin.databases.tables', $t) }}"
                                            class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                                            View Tables
                                        </a>
                                        <form action="{{ route('super_admin.databases.migrate', $t) }}" method="POST"
                                            onsubmit="return confirm('Run database migrations for {{ $t->name }} now?');">
                                            @csrf
                                            <button type="submit"
                                                class="px-3 py-1.5 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-bold transition">
                                                Migrate
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
