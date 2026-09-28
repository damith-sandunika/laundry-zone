<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <h2 class="ph-title">Global Branches</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Manage all laundry branches across the network</p>
            </div>
            <a href="{{ route('branches.create') }}" class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition-colors flex items-center gap-2">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                Add Branch
            </a>
        </div>
    </x-slot>

    @if(session('success'))
        <div style="display:flex;align-items:center;gap:10px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;padding:14px 18px;margin-bottom:24px;">
            <svg width="16" height="16" fill="none" stroke="#16a34a" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span style="font-size:13px;font-weight:600;color:#15803d;">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div style="display:flex;align-items:center;gap:10px;background:#fef2f2;border:1.5px solid #fca5a5;border-radius:12px;padding:14px 18px;margin-bottom:24px;">
            <svg width="16" height="16" fill="none" stroke="#dc2626" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span style="font-size:13px;font-weight:600;color:#b91c1c;">{{ session('error') }}</span>
        </div>
    @endif

    <div style="display:flex;flex-direction:column;gap:20px;">

        <div class="bg-white rounded-md border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Branch Name</th>
                        <th class="px-6 py-3 font-semibold">Contact Info</th>
                        <th class="px-6 py-3 font-semibold">Location</th>
                        <th class="px-6 py-3 font-semibold">Coordinates</th>
                        <th class="px-6 py-3 font-semibold text-center">Status</th>
                        <th class="px-6 py-3 font-semibold text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($branches as $branch)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($branch->logo_path)
                                        <img src="{{ str_starts_with($branch->logo_path, 'http') ? $branch->logo_path : asset($branch->logo_path) }}" alt="{{ $branch->name }}" class="w-8 h-8 rounded-full object-cover border border-gray-200">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                                            {{ substr($branch->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="font-bold text-gray-900">{{ $branch->name }}</p>
                                            @if($branch->type === 'workshop')
                                                <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-purple-100 text-purple-700 uppercase">Workshop</span>
                                            @else
                                                <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-blue-100 text-blue-700 uppercase">Branch</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500">{{ $branch->tagline ?? 'No Tagline' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-gray-700">📞 {{ $branch->phone ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500">💬 {{ $branch->whatsapp_number ?? 'N/A' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-gray-600 max-w-[200px] truncate" title="{{ $branch->address }}">{{ $branch->address ?? 'No Address' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                @if($branch->latitude && $branch->longitude)
                                    <a href="https://www.google.com/maps?q={{ $branch->latitude }},{{ $branch->longitude }}" target="_blank"
                                        class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 hover:text-blue-800 bg-blue-50 border border-blue-100 rounded-lg px-2 py-1 transition-colors">
                                        <i class="fa-solid fa-location-dot text-blue-500"></i>
                                        {{ number_format($branch->latitude, 4) }}, {{ number_format($branch->longitude, 4) }}
                                    </a>
                                @else
                                    <span class="text-[10px] text-red-400 font-bold flex items-center gap-1">
                                        <i class="fa-solid fa-triangle-exclamation"></i> No coordinates
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($branch->is_active)
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-green-100 text-green-700 uppercase tracking-wider">Active</span>
                                @else
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-red-100 text-red-700 uppercase tracking-wider">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center space-x-2">
                                <a href="{{ route('branches.edit', $branch) }}"
                                    class="text-xs font-semibold px-2.5 py-1 bg-gray-50 border border-gray-200 text-gray-600 rounded hover:bg-gray-100">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        </div>

    </div>
</x-app-layout>


