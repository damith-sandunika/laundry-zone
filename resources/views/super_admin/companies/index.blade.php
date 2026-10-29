@extends('super_admin.layouts.app', ['title' => 'Companies'])

@section('content')
<div class="space-y-6">
    <!-- Signature Title Section -->
    @component('super_admin.layouts.partials.title-section', [
        'title' => 'Companies',
        'subtitle' => 'Manage registered laundry businesses, provision databases, and configure tenant settings'
    ])
        <a href="{{ route('super_admin.companies.create') }}"
            class="flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[11px] sm:text-[13px] font-bold rounded-xl shadow-sm transition-colors h-9 sm:h-10 whitespace-nowrap">
            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add Company & Provision DB
        </a>
    @endcomponent

    <!-- Filters & Search Bar (Light Theme) -->
    <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('super_admin.companies.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, subdomain, domain, email, phone..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 placeholder-slate-400 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3.5 top-3 text-xs"></i>
            </div>

            <div>
                <select name="status" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div>
                <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Companies Card -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden">
        @if($companies->isEmpty())
            <div class="p-12 text-center text-slate-400">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 mx-auto flex items-center justify-center text-slate-400 mb-3">
                    <i class="fa-solid fa-building text-2xl"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800">No companies found</h3>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Get started by creating a new company. Its dedicated MySQL database will be provisioned automatically!</p>
                <div class="mt-4">
                    <a href="{{ route('super_admin.companies.create') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition">
                        Add New Company
                    </a>
                </div>
            </div>
        @else
            @include('super_admin.companies.partials.table')
        @endif
    </div>
</div>
@endsection
