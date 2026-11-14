<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <div>
                <h2 class="text-2xl font-black text-gray-800 tracking-tight">HR & Payroll Dashboard</h2>
                <p class="text-sm font-medium text-gray-500 mt-1">Company-wide Staff Performance, Working Hours & Salary Overview</p>
            </div>
            
            <form method="GET" action="{{ route('hr.dashboard') }}" class="flex flex-wrap items-center gap-2">
                <select name="branch_id" onchange="this.form.submit()" class="text-sm border-gray-200 rounded-xl focus:ring-blue-500 font-semibold bg-white py-2 px-3">
                    <option value="">🌍 All Branches (Global)</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                <select name="month" onchange="this.form.submit()" class="text-sm border-gray-200 rounded-xl focus:ring-blue-500 font-semibold bg-white py-2 px-3">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 10)) }}
                        </option>
                    @endforeach
                </select>
                <select name="year" onchange="this.form.submit()" class="text-sm border-gray-200 rounded-xl focus:ring-blue-500 font-semibold bg-white py-2 px-3">
                    @foreach(range(now()->year - 2, now()->year) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>
                <a href="{{ route('hr.dashboard.pdf', ['branch_id' => $branchId, 'month' => $month, 'year' => $year]) }}" 
                   target="_blank"
                   class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-sm shadow-blue-500/30 flex items-center gap-2 transition">
                    📄 Export PDF
                </a>
            </form>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        @include('hr.partials.dashboard_metrics')
        @include('hr.partials.dashboard_table')
    </div>
</x-app-layout>
