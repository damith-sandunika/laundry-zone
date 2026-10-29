@extends('super_admin.layouts.app', ['title' => 'SaaS Dashboard'])

@section('content')
<div class="space-y-6">
    <!-- Signature Title Section with Geometric Diamond Motif -->
    @component('super_admin.layouts.partials.title-section', [
        'title' => 'Dashboard',
        'subtitle' => "Today's updates and key metrics for Multi-tenant SaaS Cluster"
    ])
        <div class="flex items-center justify-center gap-1.5 px-3 sm:px-4 py-2 bg-emerald-50 border border-emerald-200 rounded-xl h-9 sm:h-10 shadow-sm flex-1 sm:flex-none">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            <span class="text-[10px] sm:text-xs font-bold text-emerald-700 tracking-wide uppercase whitespace-nowrap">Cluster Active</span>
        </div>

        <a href="{{ route('super_admin.companies.create') }}"
            class="flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[11px] sm:text-[13px] font-bold rounded-xl shadow-sm transition-colors h-9 sm:h-10 whitespace-nowrap">
            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add Company
        </a>

        <a href="{{ route('super_admin.databases.index') }}"
            class="flex items-center justify-center px-4 py-2 bg-white text-slate-600 text-[11px] sm:text-[13px] font-bold border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-slate-800 transition-colors h-9 sm:h-10 shadow-sm whitespace-nowrap">
            <svg class="w-4 h-4 mr-1.5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
            Databases
        </a>
    @endcomponent

    <!-- 1. Metrics Cards Partial -->
    @include('super_admin.dashboard.stats')

    <!-- 2. Recent Companies Table Partial -->
    @include('super_admin.dashboard.recent-companies')

    <!-- 3. Engine Diagnostics & Quick Actions Partial -->
    @include('super_admin.dashboard.system-specs')
</div>
@endsection
