<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs text-gray-400 mb-1"><a href="{{ route('customers.show', $customer) }}" class="hover:text-blue-600">← {{ $customer->name }}</a></p>
            <h2 class="text-xl font-bold text-gray-800">Edit Customer</h2>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        {{-- Customer Details --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-lg font-bold text-gray-800">Customer Details</h3>
                <p class="text-sm text-gray-500">Update the customer's contact information and address.</p>
            </div>
            
            <form action="{{ route('customers.update', $customer) }}" method="POST" class="p-6 space-y-6">
                @csrf @method('PUT')

                @if(session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-700 text-sm font-semibold px-4 py-3 rounded-xl">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 text-sm font-semibold px-4 py-3 rounded-xl">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
                        </ul>
                    </div>
                @endif

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
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}"
                        class="w-full text-sm bg-gray-50 border-gray-200 rounded-lg focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Residential Address</label>
                    <textarea name="address" rows="3"
                        class="w-full text-sm bg-gray-50 border-gray-200 rounded-lg focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">{{ old('address', $customer->address) }}</textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('customers.show', $customer) }}" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-bold rounded-lg hover:bg-gray-50 transition-all shadow-sm">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition-all shadow-sm shadow-blue-600/30">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        {{-- Change Password --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-amber-50/50">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Change Password
                </h3>
                <p class="text-sm text-gray-500 mt-1">Set a new password for this customer's online account. Leave blank to keep current password.</p>
            </div>

            <form action="{{ route('customers.update', $customer) }}" method="POST" class="p-6 space-y-6">
                @csrf @method('PUT')
                {{-- Pass existing values so they don't get wiped --}}
                <input type="hidden" name="name" value="{{ $customer->name }}">
                <input type="hidden" name="phone" value="{{ $customer->phone }}">
                <input type="hidden" name="address" value="{{ $customer->address }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">New Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" minlength="6" placeholder="Min. 6 characters"
                            class="w-full text-sm bg-gray-50 border-gray-200 rounded-lg focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-200 transition-all">
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Confirm New Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" minlength="6" placeholder="Re-enter new password"
                            class="w-full text-sm bg-gray-50 border-gray-200 rounded-lg focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-200 transition-all">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold rounded-lg transition-all shadow-sm shadow-amber-500/30 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
