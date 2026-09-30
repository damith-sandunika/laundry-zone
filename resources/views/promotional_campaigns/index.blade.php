<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="ph-title text-indigo-600 flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                    Promotional Campaigns
                </h2>
                <p class="text-[13px] text-slate-500 font-medium m-0">Send bulk SMS, Email, and WhatsApp offers to your customers</p>
            </div>
            
            <a href="{{ route('campaigns.create') }}" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 shadow-md hover:shadow-indigo-500/30 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New Campaign
            </a>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 bg-emerald-50 text-emerald-700 p-4 rounded-xl border border-emerald-100 font-bold text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 text-red-700 p-4 rounded-xl border border-red-100 font-bold text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-[20px] border border-slate-200/60 shadow-sm overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Campaign Name</th>
                        <th class="px-6 py-4 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Audience</th>
                        <th class="px-6 py-4 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider text-center">Channel</th>
                        <th class="px-6 py-4 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-4 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider text-center">Recipients</th>
                        <th class="px-6 py-4 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider text-right">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($campaigns as $campaign)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-black text-slate-800 text-[14px]">{{ $campaign->name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($campaign->audience_type === 'all')
                                    <span class="px-2 py-1 bg-blue-50 text-blue-600 font-bold text-[11px] rounded uppercase">All Customers</span>
                                @elseif($campaign->audience_type === 'birthday')
                                    <span class="px-2 py-1 bg-pink-50 text-pink-600 font-bold text-[11px] rounded uppercase">Birthdays Today</span>
                                @elseif($campaign->audience_type === 'inactive')
                                    <span class="px-2 py-1 bg-slate-100 text-slate-600 font-bold text-[11px] rounded uppercase">Inactive Customers</span>
                                @elseif($campaign->audience_type === 'branch_specific')
                                    <span class="px-2 py-1 bg-indigo-50 text-indigo-600 font-bold text-[11px] rounded uppercase">Branch: {{ $campaign->branch->name ?? 'Unknown' }}</span>
                                @elseif($campaign->audience_type === 'top_customers')
                                    <span class="px-2 py-1 bg-amber-50 text-amber-600 font-bold text-[11px] rounded uppercase">Top Customers</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="text-[12px] font-bold text-slate-600 uppercase">{{ $campaign->channel }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($campaign->status === 'completed')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[11px] font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Completed
                                    </span>
                                @elseif($campaign->status === 'processing')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-50 border border-blue-200 text-blue-700 text-[11px] font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span> Processing
                                    </span>
                                @elseif($campaign->status === 'failed')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-50 border border-red-200 text-red-700 text-[11px] font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Failed
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-700 text-[11px] font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="font-black text-[14px] text-slate-800">{{ $campaign->recipients_count }}</div>
                                @if($campaign->status === 'completed' || $campaign->status === 'processing')
                                    <div class="text-[10px] font-bold text-emerald-600">{{ $campaign->sent_count }} sent</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="text-[12px] font-bold text-slate-600">{{ $campaign->created_at->format('M d, Y') }}</div>
                                <div class="text-[10px] font-semibold text-slate-400">{{ $campaign->created_at->format('h:i A') }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-slate-400 font-bold text-sm">No promotional campaigns found. Create one to send offers.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-4 bg-slate-50 border-t border-slate-100">
            {{ $campaigns->links() }}
        </div>
    </div>
</x-app-layout>
