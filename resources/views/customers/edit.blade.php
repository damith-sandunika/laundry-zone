<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs text-gray-400 mb-1"><a href="{{ route('customers.show', $customer) }}" class="hover:text-blue-600">← {{ $customer->name }}</a></p>
            <h2 class="text-xl font-bold text-gray-800">Edit Customer</h2>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-lg font-bold text-gray-800">Customer Details</h3>
                <p class="text-sm text-gray-500">Update the customer's contact information and address.</p>
            </div>
            
            <form action="{{ route('customers.update', $customer) }}" method="POST" class="p-6 space-y-6">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $customer->name) }}" required
                            class="w-full text-sm bg-gray-50 border-gray-200 rounded-lg focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Phone Number <span class="text-red-500">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" required
                            class="w-full text-sm bg-gray-50 border-gray-200 rounded-lg focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Residential Address</label>
                    <textarea name="address" rows="3"
                        class="w-full text-sm bg-gray-50 border-gray-200 rounded-lg focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">{{ old('address', $customer->address) }}</textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('customers.show', $customer) }}" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-bold rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-all shadow-sm">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition-all shadow-sm shadow-blue-600/30">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
