@extends('super_admin.layouts.app', ['title' => 'Tables - ' . $company->database_name])

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pt-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-brand-400 mb-1">
                <a href="{{ route('super_admin.databases.index') }}" class="hover:underline">&larr; Back to Databases</a>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Tables in {{ $company->database_name }}</h1>
            <p class="text-sm text-slate-400 mt-1">Company: <strong class="text-white">{{ $company->name }}</strong> ({{ count($tables) }} total tables provisioned)</p>
        </div>

        <div>
            <form action="{{ route('super_admin.databases.migrate', $company) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs transition">
                    Run Migrations Now
                </button>
            </form>
        </div>
    </div>

    <!-- Tables List -->
    <div class="rounded-2xl bg-slate-900/80 border border-slate-800/80 overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/70 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-5 py-3">#</th>
                        <th class="px-4 py-3">Table Name</th>
                        <th class="px-4 py-3">Row Count</th>
                        <th class="px-4 py-3">Size (KB)</th>
                        <th class="px-5 py-3 text-right">Last Updated</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-mono">
                    @foreach($tables as $idx => $row)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="px-5 py-3 text-slate-500">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3 font-bold text-white">{{ $row['table_name'] ?? $row['TABLE_NAME'] }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ number_format($row['table_rows'] ?? $row['TABLE_ROWS'] ?? 0) }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ $row['size_kb'] ?? 0 }} KB</td>
                            <td class="px-5 py-3 text-right text-slate-400 font-sans">{{ $row['update_time'] ?? $row['UPDATE_TIME'] ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
