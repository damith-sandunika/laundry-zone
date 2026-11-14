                <!-- Add Package Modal -->
                <div x-show="packageModalOpen" class="fixed inset-0 z-[9999] overflow-y-auto" style="display: none;">
                    <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
                        <div x-show="packageModalOpen" x-transition.opacity class="fixed inset-0 transition-opacity"
                            @click="packageModalOpen = false">
                            <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm"></div>
                        </div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                        <div x-show="packageModalOpen" x-transition
                            class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-gray-100">
                            <form action="{{ route('settings.storePackage') }}" method="POST">
                                @csrf
                                <div class="bg-white px-6 pt-6 pb-6">
                                    <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                        Create New Package
                                    </h3>

                                    <div class="space-y-5">
                                        <div>
                                            <label
                                                class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Package
                                                Name <span class="text-red-500">*</span></label>
                                            <input type="text" name="name" required
                                                placeholder="e.g. Premium Monthly Plan"
                                                class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label
                                                    class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Package
                                                    Price (Rs) <span class="text-red-500">*</span></label>
                                                <input type="number" step="0.01" name="price" required
                                                    placeholder="0.00"
                                                    class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Payment
                                                    Type</label>
                                                <select name="payment_type"
                                                    class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50 font-semibold">
                                                    <option value="prepaid">Pre-paid (Pay upfront)</option>
                                                    <option value="postpaid">Post-paid (Pay later)</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="border-t border-gray-100 pt-5">
                                            <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-3">
                                                Included Services & Limits</h4>
                                            <p class="text-[11px] text-gray-500 mb-3">Set the quantity limit for
                                                 services included in this package. Leave as 0 if the service is not
                                                 included.</p>

                                            <div class="space-y-3 max-h-64 overflow-y-auto custom-scrollbar pr-2">
                                                @foreach($serviceCategories as $category)
                                                    @if($category->services->count() > 0)
                                                        <div
                                                            class="bg-gray-50/50 rounded-xl border border-gray-100 overflow-hidden">
                                                            <div class="bg-gray-100/50 px-3 py-2 border-b border-gray-100">
                                                                <h5
                                                                    class="text-[10px] font-black text-gray-700 uppercase tracking-wider">
                                                                    {{ $category->name }}
                                                                </h5>
                                                            </div>
                                                            <div class="p-1 space-y-1">
                                                                @foreach($category->services as $service)
                                                                    <div
                                                                        class="flex items-center justify-between p-2 hover:bg-white rounded-lg transition-colors border border-transparent hover:border-gray-200">
                                                                        <span
                                                                            class="text-sm font-semibold text-gray-700">{{ $service->name }}
                                                                            <span
                                                                                class="text-[10px] text-gray-400 font-normal ml-1">({{ $service->price_type === 'per_kg' ? 'KG' : ($service->price_type === 'per_sqft' ? 'SqFt' : ($service->price_type === 'per_pair' ? 'Pairs' : 'Items')) }})</span></span>
                                                                        <input type="number" step="any"
                                                                            name="services[{{ $service->id }}]" placeholder="0"
                                                                            class="w-24 text-sm border-gray-200 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-center font-bold">
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50/80 px-6 py-4 flex justify-end gap-3 border-t border-gray-100">
                                    <button type="button" @click="packageModalOpen = false"
                                        class="px-5 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-sm">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:shadow-lg hover:shadow-blue-500/30 transition-all transform hover:-translate-y-0.5">
                                        Save Package
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
