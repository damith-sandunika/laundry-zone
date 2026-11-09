@extends('super_admin.layouts.app', ['title' => 'Company Owners'])

@section('content')
<div class="space-y-6" x-data="{ resetModal: false, editModal: false, activeOwner: {} }">
    <!-- Signature Title Section -->
    @component('super_admin.layouts.partials.title-section', [
        'title' => 'Tenant Owners',
        'subtitle' => 'Manage master owner accounts and access credentials for all registered companies'
    ])
        <a href="{{ route('super_admin.companies.create') }}"
            class="flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[11px] sm:text-[13px] font-bold rounded-xl shadow-sm transition-colors h-9 sm:h-10 whitespace-nowrap">
            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add New Company & Owner
        </a>
    @endcomponent

    <!-- Owners Table Card -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden">
        @if($owners->isEmpty())
            <div class="p-12 text-center text-slate-400 text-sm">
                No company owners registered yet. When a company is registered, an owner account is created automatically!
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
                        <tr>
                            <th class="px-5 py-3.5">Owner Name</th>
                            <th class="px-4 py-3.5">Assigned Company</th>
                            <th class="px-4 py-3.5">Login Email</th>
                            <th class="px-4 py-3.5">Phone</th>
                            <th class="px-4 py-3.5">Initial Password</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($owners as $owner)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-900">{{ $owner->name }}</div>
                                    <span class="text-[11px] text-blue-600 font-semibold">Tenant Master Admin</span>
                                </td>
                                <td class="px-4 py-4">
                                    @if($owner->tenant)
                                        <a href="{{ route('super_admin.companies.show', $owner->tenant) }}" class="font-bold text-slate-900 hover:text-blue-600 block">
                                            {{ $owner->tenant->name }}
                                        </a>
                                        <div class="text-xs font-mono text-slate-400">{{ $owner->tenant->subdomain }}</div>
                                    @else
                                        <span class="text-slate-400">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 font-mono text-xs text-slate-700">
                                    {{ $owner->email }}
                                </td>
                                <td class="px-4 py-4 text-xs text-slate-500">
                                    {{ $owner->phone ?: 'N/A' }}
                                </td>
                                <td class="px-4 py-4 text-xs font-mono text-amber-700 bg-amber-50/50">
                                    {{ $owner->initial_password ?: '••••••••' }}
                                </td>
                                <td class="px-4 py-4">
                                    @if($owner->is_active)
                                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button @click="activeOwner = {{ json_encode($owner) }}; resetModal = true"
                                            class="px-2.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-bold transition flex items-center gap-1" title="Reset Password">
                                            <i class="fa-solid fa-key text-[10px]"></i>
                                            <span>Reset</span>
                                        </button>
                                        <button @click="activeOwner = {{ json_encode($owner) }}; editModal = true"
                                            class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1" title="Edit Owner">
                                            <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                                            <span>Edit</span>
                                        </button>
                                        <form action="{{ route('super_admin.owners.destroy', $owner) }}" method="POST"
                                            onsubmit="return confirm('⚠️ Are you sure you want to delete owner \'{{ $owner->name }}\'?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition flex items-center gap-1" title="Delete Owner">
                                                <i class="fa-solid fa-trash text-[11px]"></i>
                                                <span>Delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($owners->hasPages())
                <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $owners->links() }}
                </div>
            @endif
        @endif
    </div>

    <!-- MODALS PARTIAL -->
    @include('super_admin.owners.partials.modals')
</div>
@endsection
