<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <h2 class="ph-title">Global Branches</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Manage all laundry branches across the network</p>
            </div>
            <button @click="$dispatch('open-add-branch')" class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition-colors flex items-center gap-2">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                Add Branch
            </button>
        </div>
    </x-slot>

    @if(session('success'))
        <div style="display:flex;align-items:center;gap:10px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;padding:14px 18px;margin-bottom:24px;">
            <svg width="16" height="16" fill="none" stroke="#16a34a" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span style="font-size:13px;font-weight:600;color:#15803d;">{{ session('success') }}</span>
        </div>
    @endif

    <div x-data="{ addModalOpen: false, editModalOpen: false, selectedBranch: null }" @open-add-branch.window="addModalOpen = true" style="display:flex;flex-direction:column;gap:20px;">

        <div class="bg-white rounded-md border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Branch Name</th>
                        <th class="px-6 py-3 font-semibold">Contact Info</th>
                        <th class="px-6 py-3 font-semibold">Location</th>
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
                                        <img src="{{ Storage::url($branch->logo_path) }}" alt="{{ $branch->name }}" class="w-8 h-8 rounded-full object-cover border border-gray-200">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                                            {{ substr($branch->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-bold text-gray-900">{{ $branch->name }}</p>
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
                            <td class="px-6 py-4 text-center">
                                @if($branch->is_active)
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-green-100 text-green-700 uppercase tracking-wider">Active</span>
                                @else
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-red-100 text-red-700 uppercase tracking-wider">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center space-x-2">
                                <button 
                                    @click="selectedBranch = {{ json_encode($branch) }}; editModalOpen = true"
                                    class="text-xs font-semibold px-2.5 py-1 bg-gray-50 border border-gray-200 text-gray-600 rounded hover:bg-gray-100">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('branches.destroy', $branch) }}" onsubmit="return confirm('Deactivate this branch?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold px-2.5 py-1 bg-red-50 border border-red-200 text-red-600 rounded hover:bg-red-100">
                                        Disable
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        <!-- Add Branch Modal -->
        <div x-show="addModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
                <div x-show="addModalOpen" x-transition.opacity class="fixed inset-0 transition-opacity" @click="addModalOpen = false">
                    <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="addModalOpen" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-gray-100">
                    <form action="{{ route('branches.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="bg-white px-6 pt-6 pb-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-6">Register New Branch</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Branch Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" required class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Phone</label>
                                        <input type="text" name="phone" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">WhatsApp Number</label>
                                        <input type="text" name="whatsapp_number" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Physical Address</label>
                                    <input type="text" name="address" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Tagline</label>
                                    <input type="text" name="tagline" placeholder="e.g. Premium Laundry Services" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Branch Logo</label>
                                    <input type="file" name="logo" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors">
                                </div>
                                <div class="flex items-center gap-2 mt-4">
                                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                    <label class="text-sm font-semibold text-gray-700">Set as Active Branch</label>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50/80 px-6 py-4 flex justify-end gap-3 border-t border-gray-100">
                            <button type="button" @click="addModalOpen = false" class="px-5 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-sm">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl text-sm font-bold hover:shadow-lg hover:shadow-blue-500/30 transition-all duration-200">
                                Save Branch
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Branch Modal -->
        <div x-show="editModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
                <div x-show="editModalOpen" x-transition.opacity class="fixed inset-0 transition-opacity" @click="editModalOpen = false">
                    <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="editModalOpen" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-gray-100">
                    <form :action="'{{ url('/branches') }}/' + selectedBranch?.id" method="POST" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <div class="bg-white px-6 pt-6 pb-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-6">Edit Branch Details</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Branch Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" :value="selectedBranch?.name" required class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Phone</label>
                                        <input type="text" name="phone" :value="selectedBranch?.phone" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">WhatsApp Number</label>
                                        <input type="text" name="whatsapp_number" :value="selectedBranch?.whatsapp_number" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Physical Address</label>
                                    <input type="text" name="address" :value="selectedBranch?.address" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Tagline</label>
                                    <input type="text" name="tagline" :value="selectedBranch?.tagline" class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Update Logo (Optional)</label>
                                    <input type="file" name="logo" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors">
                                </div>
                                <div class="flex items-center gap-2 mt-4">
                                    <input type="checkbox" name="is_active" value="1" :checked="selectedBranch?.is_active" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                    <label class="text-sm font-semibold text-gray-700">Active Branch</label>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50/80 px-6 py-4 flex justify-end gap-3 border-t border-gray-100">
                            <button type="button" @click="editModalOpen = false" class="px-5 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-sm">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl text-sm font-bold hover:shadow-lg hover:shadow-blue-500/30 transition-all duration-200">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
