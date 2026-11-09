@extends('super_admin.layouts.app', ['title' => 'SMS Packages & Top-Ups'])

@section('content')
<div class="space-y-6" x-data="{ createPkgModal: false, editPkgModal: false, topUpModal: false, activePkg: {} }">
    <!-- Signature Title Section -->
    @component('super_admin.layouts.partials.title-section', [
        'title' => 'SMS Packages & Top-Ups',
        'subtitle' => 'Configure platform SMS bundles, pricing rates, and credit tenant balances'
    ])
        <div class="flex items-center gap-2">
            <button @click="topUpModal = true"
                class="flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] sm:text-[13px] font-bold rounded-xl shadow-sm transition-colors h-9 sm:h-10 whitespace-nowrap">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Issue SMS Credit
            </button>
            <button @click="createPkgModal = true"
                class="flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[11px] sm:text-[13px] font-bold rounded-xl shadow-sm transition-colors h-9 sm:h-10 whitespace-nowrap">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Create New Package
            </button>
        </div>
    @endcomponent

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="text-xs text-slate-400 font-bold uppercase tracking-wider">Total SMS Issued</div>
            <div class="text-2xl font-black text-indigo-600 mt-2">{{ number_format($totalSmsIssued) }} SMS</div>
            <div class="text-[11px] text-indigo-600 font-semibold mt-1">Total volume credited to tenants</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="text-xs text-slate-400 font-bold uppercase tracking-wider">SMS Top-Up Revenue</div>
            <div class="text-2xl font-black text-emerald-600 mt-2">Rs. {{ number_format($totalRevenue, 0) }}</div>
            <div class="text-[11px] text-emerald-600 font-semibold mt-1">Collected from SMS bundle sales</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="text-xs text-slate-400 font-bold uppercase tracking-wider">Active SMS Tenants</div>
            <div class="text-2xl font-black text-blue-600 mt-2">{{ $activeTenantsWithSms }} Companies</div>
            <div class="text-[11px] text-blue-600 font-semibold mt-1">Currently have an active SMS balance</div>
        </div>
    </div>

    <!-- Active SMS Packages Grid -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-black text-slate-900">Platform SMS Bundles</h3>
                <p class="text-xs text-slate-500 font-medium">Packages available for tenant companies to view and request</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($packages as $pkg)
                <div class="relative bg-white rounded-2xl border {{ $pkg->is_featured ? 'border-blue-500 shadow-md ring-2 ring-blue-500/20' : 'border-slate-200/80 shadow-sm' }} p-6 flex flex-col justify-between">
                    @if($pkg->badge)
                        <span class="absolute -top-3 left-6 px-3 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-full {{ $pkg->is_featured ? 'bg-blue-600 text-white' : 'bg-slate-800 text-white' }}">
                            {{ $pkg->badge }}
                        </span>
                    @endif

                    <div>
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="text-lg font-black text-slate-900">{{ $pkg->name }}</h4>
                                <div class="flex items-baseline gap-1 mt-2">
                                    <span class="text-2xl font-black text-slate-900">Rs. {{ number_format($pkg->price, 0) }}</span>
                                    <span class="text-xs font-semibold text-slate-400">/ bundle</span>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 text-[11px] font-bold rounded-lg {{ $pkg->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                {{ $pkg->is_active ? 'Active' : 'Disabled' }}
                            </span>
                        </div>

                        <div class="mt-4 p-3 bg-slate-50 rounded-xl flex items-center justify-between">
                            <div>
                                <div class="text-xs text-slate-400 font-bold uppercase">SMS Credits</div>
                                <div class="text-base font-black text-indigo-600">{{ number_format($pkg->sms_count) }} SMS</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-slate-400 font-bold uppercase">Unit Rate</div>
                                <div class="text-sm font-bold text-slate-700">Rs. {{ number_format($pkg->rate_per_sms, 2) }}/sms</div>
                            </div>
                        </div>

                        @if(!empty($pkg->features))
                            <ul class="mt-4 space-y-2 text-xs text-slate-600">
                                @foreach($pkg->features as $feat)
                                    <li class="flex items-center gap-2 font-medium">
                                        <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        {{ $feat }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center gap-2">
                        <button type="button" @click="activePkg = {{ json_encode($pkg) }}; editPkgModal = true"
                            class="flex-1 py-2 px-3 text-center text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                            Edit Package
                        </button>
                        <form action="{{ route('super_admin.sms_packages.destroy', $pkg) }}" method="POST" onsubmit="return confirm('Delete this package?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 rounded-xl hover:bg-rose-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-3 p-8 text-center bg-white rounded-2xl border border-slate-200/80 text-slate-400 text-sm">
                    No SMS packages found. Click <strong>Create New Package</strong> above to create one.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Tenant SMS Credits History -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
            <div>
                <h3 class="text-sm font-black text-slate-800">Tenant SMS Top-Up History</h3>
                <p class="text-[11px] font-semibold text-slate-500">Log of SMS bundles credited directly to tenant companies</p>
            </div>
            
            <form method="GET" action="{{ route('super_admin.sms_packages.index') }}" class="flex items-center gap-2">
                <select name="tenant_id" onchange="this.form.submit()" class="text-xs font-semibold rounded-xl border-slate-200 bg-white px-3 py-1.5 focus:ring-blue-500">
                    <option value="">All Companies</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}" {{ request('tenant_id') == $t->id ? 'selected' : '' }}>
                            {{ $t->name }} (Balance: {{ $t->sms_balance }} SMS)
                        </option>
                    @endforeach
                </select>
                @if(request('tenant_id'))
                    <a href="{{ route('super_admin.sms_packages.index') }}" class="text-xs text-slate-500 hover:text-slate-800">Clear</a>
                @endif
            </form>
        </div>

        @if($smsCredits->isEmpty())
            <div class="p-12 text-center text-slate-400 text-sm">
                No SMS top-ups recorded yet. Click <strong>Issue SMS Credit</strong> to top up a company's balance.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
                        <tr>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Company</th>
                            <th class="px-6 py-3">SMS Volume</th>
                            <th class="px-6 py-3">Rate</th>
                            <th class="px-6 py-3">Total Paid</th>
                            <th class="px-6 py-3">Method & Ref</th>
                            <th class="px-6 py-3">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($smsCredits as $credit)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500">
                                    {{ $credit->created_at->format('M d, Y h:i A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $credit->tenant->name ?? 'Unknown Company' }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">Current Balance: {{ $credit->tenant->sms_balance ?? 0 }} SMS</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black bg-indigo-50 text-indigo-700">
                                        +{{ number_format($credit->sms_count) }} SMS
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-600">
                                    Rs. {{ number_format($credit->rate_per_sms, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-black text-emerald-600">
                                    Rs. {{ number_format($credit->amount_paid, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-600">
                                    <span class="font-semibold">{{ $credit->payment_method ?? 'N/A' }}</span>
                                    @if($credit->payment_reference)
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $credit->payment_reference }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500 max-w-xs truncate">
                                    {{ $credit->notes ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-100">
                {{ $smsCredits->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: Create Package -->
    <div x-show="createPkgModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 border border-slate-100" @click.away="createPkgModal = false">
            <h3 class="text-base font-black text-slate-900 mb-4">Create New SMS Package</h3>
            
            <form action="{{ route('super_admin.sms_packages.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Package Name</label>
                    <input type="text" name="name" required placeholder="e.g. Standard Pack" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">SMS Count</label>
                        <input type="number" name="sms_count" required placeholder="1000" min="1" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Total Price (Rs.)</label>
                        <input type="number" step="0.01" name="price" required placeholder="1800.00" min="0" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Rate per SMS (Rs.)</label>
                        <input type="number" step="0.01" name="rate_per_sms" placeholder="Optional (auto)" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Badge</label>
                        <input type="text" name="badge" placeholder="e.g. Popular" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Features (One per line)</label>
                    <textarea name="features" rows="3" placeholder="Instant Delivery&#10;No Expiry&#10;Priority Routing" class="w-full text-xs font-semibold rounded-xl border-slate-200"></textarea>
                </div>

                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" class="rounded text-blue-600 focus:ring-blue-500">
                        <span class="text-xs font-bold text-slate-700">Highlight / Featured</span>
                    </label>
                    <div>
                        <input type="number" name="order_index" placeholder="Order (0)" class="w-24 text-xs font-semibold rounded-xl border-slate-200">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="createPkgModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl">Create Package</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Package -->
    <div x-show="editPkgModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 border border-slate-100" @click.away="editPkgModal = false">
            <h3 class="text-base font-black text-slate-900 mb-4">Edit SMS Package</h3>
            
            <form :action="'{{ url('super-admin/sms-packages') }}/' + activePkg.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Package Name</label>
                    <input type="text" name="name" :value="activePkg.name" required class="w-full text-xs font-semibold rounded-xl border-slate-200">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">SMS Count</label>
                        <input type="number" name="sms_count" :value="activePkg.sms_count" required min="1" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Total Price (Rs.)</label>
                        <input type="number" step="0.01" name="price" :value="activePkg.price" required min="0" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Rate per SMS (Rs.)</label>
                        <input type="number" step="0.01" name="rate_per_sms" :value="activePkg.rate_per_sms" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Badge</label>
                        <input type="text" name="badge" :value="activePkg.badge" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Features (One per line)</label>
                    <textarea name="features" rows="3" :value="(activePkg.features || []).join('\n')" class="w-full text-xs font-semibold rounded-xl border-slate-200"></textarea>
                </div>

                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" :checked="activePkg.is_featured" class="rounded text-blue-600 focus:ring-blue-500">
                        <span class="text-xs font-bold text-slate-700">Featured</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" :checked="activePkg.is_active" class="rounded text-emerald-600 focus:ring-emerald-500">
                        <span class="text-xs font-bold text-slate-700">Active</span>
                    </label>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="editPkgModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Issue Top-Up Credit -->
    <div x-show="topUpModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 border border-slate-100" @click.away="topUpModal = false">
            <h3 class="text-base font-black text-slate-900 mb-4">Issue SMS Credits to Tenant</h3>
            
            <form action="{{ route('super_admin.sms_credits.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Select Tenant Company</label>
                    <select name="tenant_id" required class="w-full text-xs font-semibold rounded-xl border-slate-200">
                        <option value="">-- Choose Company --</option>
                        @foreach($tenants as $t)
                            <option value="{{ $t->id }}">
                                {{ $t->name }} (Balance: {{ $t->sms_balance }} SMS | Default Rate: Rs. {{ number_format($t->sms_rate, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">SMS Count</label>
                        <input type="number" name="sms_count" required placeholder="e.g. 1000" min="1" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Rate per SMS (Rs.)</label>
                        <input type="number" step="0.01" name="rate_per_sms" placeholder="e.g. 2.00" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Amount Paid (Rs.)</label>
                        <input type="number" step="0.01" name="amount_paid" placeholder="Leave empty for auto" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Payment Method</label>
                        <select name="payment_method" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cash">Cash</option>
                            <option value="Online Gateway">Online Gateway</option>
                            <option value="Complimentary">Complimentary / Free</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Payment Reference</label>
                    <input type="text" name="payment_reference" placeholder="e.g. SLIP-998822" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" placeholder="Bundle top-up details..." class="w-full text-xs font-semibold rounded-xl border-slate-200"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="topUpModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl">Credit SMS Volume</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

