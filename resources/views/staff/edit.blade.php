<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs text-gray-400 mb-1"><a href="{{ route('staff.index') }}" class="hover:text-blue-600">← Staff</a></p>
            <h2 class="text-xl font-bold text-gray-800">Edit Staff — {{ $staff->name }}</h2>
        </div>
    </x-slot>

    <div class="max-w-lg">
        <div class="bg-white rounded-lg border border-gray-100 shadow-sm p-6">
            <form action="{{ route('staff.update', $staff) }}" method="POST" class="space-y-4">
                @csrf @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $staff->name) }}" required
                        class="w-full text-sm border-gray-200 rounded focus:border-blue-500 focus:ring-blue-500">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $staff->phone) }}"
                        class="w-full text-sm border-gray-200 rounded focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Role *</label>
                    <select name="role" class="w-full text-sm border-gray-200 rounded focus:border-blue-500 focus:ring-blue-500">
                        <option value="staff" {{ $staff->role === 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="admin" {{ $staff->role === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="super_admin" {{ $staff->role === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Status *</label>
                    <select name="is_active" class="w-full text-sm border-gray-200 rounded focus:border-blue-500 focus:ring-blue-500">
                        <option value="1" {{ $staff->is_active ? 'selected' : '' }}>✅ Active</option>
                        <option value="0" {{ !$staff->is_active ? 'selected' : '' }}>🚫 Inactive / Suspended</option>
                    </select>
                </div>

                <div class="divider border-t border-gray-100 pt-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Change Password (optional)</p>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">New Password</label>
                            <input type="password" name="password"
                                class="w-full text-sm border-gray-200 rounded focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Leave blank to keep current">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Confirm Password</label>
                            <input type="password" name="password_confirmation"
                                class="w-full text-sm border-gray-200 rounded focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded transition-colors">
                        Save Changes
                    </button>
                    <a href="{{ route('staff.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-xs font-semibold rounded hover:bg-gray-200 transition-colors">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
