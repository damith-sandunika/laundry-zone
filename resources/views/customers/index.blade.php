<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h2 class="ph-title">Customer Directory</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Manage customer accounts and details</p>
            </div>
        </div>
    </x-slot>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <!-- Filters / Actions -->
        <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-white">
            <div class="flex-1 max-w-sm">
                <input type="text" placeholder="Search customers..." class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 shadow-sm">
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Name</th>
                        <th class="px-6 py-3 font-semibold">Phone</th>
                        <th class="px-6 py-3 font-semibold">Loyalty Points</th>
                        <th class="px-6 py-3 font-semibold">Address</th>
                        <th class="px-6 py-3 font-semibold text-center">Status</th>
                        <th class="px-6 py-3 font-semibold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $customer->name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $customer->phone }}</td>
                            <td class="px-6 py-4 font-semibold text-blue-600">{{ $customer->loyalty_points }} pts</td>
                            <td class="px-6 py-4 text-gray-500">{{ $customer->address ?? '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($customer->is_active)
                                    <span class="px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-700">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-700">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center" style="display:flex;justify-content:center;gap:8px;">
                                <a href="{{ route('customers.show', $customer) }}" style="font-size:11px;font-weight:700;padding:5px 12px;background:white;color:#1d4ed8;border:1.5px solid #bfdbfe;border-radius:7px;text-decoration:none;transition:background 0.1s;box-shadow:0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='white'">View</a>
                                <a href="{{ route('customers.edit', $customer) }}" style="font-size:11px;font-weight:700;padding:5px 12px;background:#eff6ff;color:#1d4ed8;border:1.5px solid #bfdbfe;border-radius:7px;text-decoration:none;transition:background 0.1s;" onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">Edit</a>
                                @if((auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin' || auth()->user()->role === 'branch_admin') && $customer->orders_count === 0)
                                <form method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm('Delete this customer?')" style="margin:0;">
                                    @csrf @method('DELETE')
                                    <button type="submit" style="font-size:11px;font-weight:700;padding:5px 12px;background:#fef2f2;color:#dc2626;border:1.5px solid #fecaca;border-radius:7px;cursor:pointer;transition:background 0.1s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">Delete</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">No customers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>


