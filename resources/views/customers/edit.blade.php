<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs text-gray-400 mb-1"><a href="{{ route('customers.show', $customer) }}" class="hover:text-blue-600">← {{ $customer->name }}</a></p>
            <h2 class="text-xl font-bold text-gray-800">Edit Customer</h2>
        </div>
    </x-slot>

    <div class="max-w-lg">
        <div class="bg-white rounded-lg border border-gray-100 shadow-sm p-6">
            <form action="{{ route('customers.update', $customer) }}" method="POST" class="space-y-4">
                @csrf @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" required
                        class="w-full text-sm border-gray-200 rounded focus:border-blue-500 focus:ring-blue-500">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Phone *</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" required
                        class="w-full text-sm border-gray-200 rounded focus:border-blue-500 focus:ring-blue-500">
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Address</label>
                    <textarea name="address" rows="2"
                        class="w-full text-sm border-gray-200 rounded focus:border-blue-500 focus:ring-blue-500">{{ old('address', $customer->address) }}</textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded transition-colors">
                        Save Changes
                    </button>
                    <a href="{{ route('customers.show', $customer) }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-xs font-semibold rounded hover:bg-gray-200 transition-colors">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
