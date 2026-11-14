                <!-- Uncategorized Services -->
                @php
                    $uncategorizedServices = $services->whereNull('category_id');
                @endphp
                @if($uncategorizedServices->count() > 0)
                    <div x-data="{ expanded: false }" class="category-item bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="flex justify-between items-center p-4 border-b border-gray-100 bg-slate-50">
                            <div class="flex items-center gap-3">
                                <button @click="expanded = !expanded" class="text-gray-400 hover:text-gray-600 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <h4 class="font-bold text-gray-800">Uncategorized Services</h4>
                            </div>
                        </div>
                        
                        <div x-show="expanded" x-collapse>
                            <div class="services-sortable">
                                @foreach($uncategorizedServices as $service)
                                    <div class="service-item flex justify-between items-center p-3 border-b border-gray-50 bg-white hover:bg-gray-50 transition-colors"
                                        data-id="{{ $service->id }}">
                                        <div class="flex items-center gap-3">
                                            <div class="cursor-move service-handle text-gray-300 hover:text-gray-500">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                                </svg>
                                            </div>
                                            <div>
                                                <span class="font-bold text-gray-800 text-sm">{{ $service->name }}</span>
                                                <span class="text-xs text-gray-500 ml-2">Rs.{{ number_format($service->base_price, 2) }}
                                                    ({{ $service->price_type === 'per_kg' ? 'Per KG' : ($service->price_type === 'per_sqft' ? 'Per SqFt' : ($service->price_type === 'per_pair' ? 'Per Pair' : 'Per Item')) }})</span>
                                                <span class="ml-2 text-[10px] font-bold uppercase {{ $service->is_active ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $service->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                                @if($service->is_visible_on_website)
                                                    <span class="ml-2 px-1.5 py-0.5 bg-blue-50 text-blue-600 rounded text-[10px] font-bold border border-blue-100">Show in Website</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex gap-2">
                                            <button
                                                onclick="openEditServiceModal({{ $service->id }}, '{{ addslashes($service->name) }}', {{ $service->base_price }}, '{{ $service->price_type }}', {{ $service->is_active ? 1 : 0 }}, '{{ $service->color ?? 'blue' }}', null, {{ $service->is_visible_on_website ? 1 : 0 }}, '{{ addslashes($service->item_code ?? '') }}')"
                                                class="text-xs font-semibold text-blue-600 hover:text-blue-800 px-2 py-1 bg-blue-50 rounded">Edit</button>
                                            <form method="POST" action="{{ route('settings.destroyService', $service) }}"
                                                onsubmit="return confirm('Delete this service?')" class="m-0">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800 px-2 py-1 bg-red-50 rounded">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
                
                @if($serviceCategories->count() === 0 && $uncategorizedServices->count() === 0)
                    <div class="p-10 text-center bg-white rounded-xl border border-gray-200">
                        <p class="text-sm font-bold text-gray-800 mb-1">No services yet</p>
                        <p class="text-xs text-gray-500 font-medium">Click "Add Category" and "Add New Service" to get started.</p>
                    </div>
                @endif
