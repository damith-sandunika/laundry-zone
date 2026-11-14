                                <!-- Edit Package Modal -->
                                <div x-show="editPackageModalOpen_{{ $package->id }}"
                                    class="fixed inset-0 z-[9999] overflow-y-auto" style="display: none;">
                                    <div
                                        class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
                                        <div x-show="editPackageModalOpen_{{ $package->id }}" x-transition.opacity
                                            class="fixed inset-0 transition-opacity"
                                            @click="editPackageModalOpen_{{ $package->id }} = false">
                                            <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm"></div>
                                        </div>
                                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                        <div x-show="editPackageModalOpen_{{ $package->id }}" x-transition
                                            class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-gray-100">
                                            <form action="{{ route('settings.updatePackage', $package) }}" method="POST">
                                                @csrf @method('PUT')
                                                <div class="bg-white px-6 pt-6 pb-6">
                                                    <h3
                                                        class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                        Edit Package
                                                    </h3>
                                                    <div class="space-y-5">
                                                        <div class="text-left">
                                                            <label
                                                                class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Package
                                                                Name <span class="text-red-500">*</span></label>
                                                            <input type="text" name="name" value="{{ $package->name }}"
                                                                required
                                                                class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                                        </div>
                                                        <div class="grid grid-cols-2 gap-4 text-left">
                                                            <div>
                                                                <label
                                                                    class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Package
                                                                    Price (Rs) <span class="text-red-500">*</span></label>
                                                                <input type="number" step="0.01" name="price"
                                                                    value="{{ $package->price }}" required
                                                                    class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                                            </div>
                                                            <div>
                                                                <label
                                                                    class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Payment
                                                                    Type</label>
                                                                <select name="payment_type"
                                                                    class="w-full text-sm border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50 font-semibold">
                                                                    <option value="prepaid" {{ $package->payment_type === 'prepaid' ? 'selected' : '' }}>Pre-paid (Pay upfront)</option>
                                                                    <option value="postpaid" {{ $package->payment_type === 'postpaid' ? 'selected' : '' }}>Post-paid (Pay later)</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="border-t border-gray-100 pt-5 text-left">
                                                            <h4
                                                                class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-3">
                                                                Included Services & Limits</h4>
                                                            <div
                                                                class="space-y-3 max-h-64 overflow-y-auto custom-scrollbar pr-2">
                                                                @foreach($serviceCategories as $category)
                                                                    @if($category->services->count() > 0)
                                                                        <div
                                                                            class="bg-gray-50/50 rounded-xl border border-gray-100 overflow-hidden">
                                                                            <div
                                                                                class="bg-gray-100/50 px-3 py-2 border-b border-gray-100">
                                                                                <h5
                                                                                    class="text-[10px] font-black text-gray-700 uppercase tracking-wider">
                                                                                    {{ $category->name }}
                                                                                </h5>
                                                                            </div>
                                                                            <div class="p-1 space-y-1">
                                                                                @foreach($category->services as $service)
                                                                                    @php $qty = $package->services->where('service_type_id', $service->id)->first()->quantity_limit ?? 0; @endphp
                                                                                    <div
                                                                                        class="flex items-center justify-between p-2 hover:bg-white rounded-lg transition-colors border border-transparent hover:border-gray-200">
                                                                                        <span
                                                                                            class="text-sm font-semibold text-gray-700">{{ $service->name }}
                                                                                            <span
                                                                                                class="text-[10px] text-gray-400 font-normal ml-1">({{ $service->price_type === 'per_kg' ? 'KG' : ($service->price_type === 'per_sqft' ? 'SqFt' : ($service->price_type === 'per_pair' ? 'Pairs' : 'Items')) }})</span></span>
                                                                                        <input type="number" step="any"
                                                                                            name="services[{{ $service->id }}]"
                                                                                            value="{{ $qty }}"
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
                                                <div
                                                    class="bg-gray-50/80 px-6 py-4 flex justify-end gap-3 border-t border-gray-100">
                                                    <button type="button"
                                                        @click="editPackageModalOpen_{{ $package->id }} = false"
                                                        class="px-5 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-sm">Cancel</button>
                                                    <button type="submit"
                                                        class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:shadow-lg hover:shadow-blue-500/30 transition-all transform hover:-translate-y-0.5">Save
                                                        Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
