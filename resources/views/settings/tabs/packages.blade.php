            @if($canManageConfig)
            <!-- PACKAGES TAB -->
            <div style="display: block;" x-data="{ packageModalOpen: false }">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-semibold text-gray-800">Subscription Packages</h3>
                    <button @click="packageModalOpen = true"
                        class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition-colors">
                        + Add New Package
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($packages as $package)
                        <div
                            class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col relative group">
                            <div class="p-5 flex-1">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-gray-900 text-lg">{{ $package->name }}</h4>
                                    <span
                                        class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider rounded-md {{ $package->payment_type === 'prepaid' ? 'bg-green-100 text-green-700' : 'bg-purple-100 text-purple-700' }}">
                                        {{ $package->payment_type }}
                                    </span>
                                </div>
                                <div class="text-2xl font-black text-blue-600 mb-4">
                                    Rs.{{ number_format($package->price, 2) }}
                                </div>

                                <div class="space-y-2 mb-4">
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Included
                                        Services</p>
                                    @foreach($package->services as $ps)
                                        <div class="flex justify-between items-center text-sm border-b border-gray-50 pb-1">
                                            <span class="text-gray-700 font-medium flex items-center gap-2">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                                                {{ $ps->serviceType->name }}
                                            </span>
                                            <span
                                                class="font-bold text-gray-900">{{ number_format($ps->quantity_limit, 0) }}</span>
                                        </div>
                                    @endforeach
                                </div>


                            </div>

                            <div class="bg-gray-50 p-3 border-t border-gray-100 flex justify-end gap-2"
                                x-data="{ editPackageModalOpen_{{ $package->id }}: false }">
                                <button type="button" @click="editPackageModalOpen_{{ $package->id }} = true"
                                    class="text-xs font-bold text-blue-600 hover:bg-blue-50 px-3 py-1.5 rounded transition-colors">
                                    Edit
                                </button>
                                <form action="{{ route('settings.destroyPackage', $package) }}" method="POST"
                                    onsubmit="return confirm('Delete this package?');">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="text-xs font-bold text-red-600 hover:bg-red-50 px-3 py-1.5 rounded transition-colors">
                                        Delete
                                    </button>
                                </form>

                                @include('settings.tabs.packages.edit-modal')
                            </div>
                        </div>
                    @empty
                        <div
                            class="col-span-full py-10 text-center text-gray-500 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                            <p class="mb-2">No packages defined.</p>
                            <button @click="packageModalOpen = true"
                                class="text-sm text-blue-600 font-bold hover:underline">Create your first package</button>
                        </div>
                    @endforelse
                </div>

                @include('settings.tabs.packages.add-modal')
            </div>
            @endif
