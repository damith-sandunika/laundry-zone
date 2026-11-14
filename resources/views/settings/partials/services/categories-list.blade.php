            <div id="categories-sortable" class="space-y-4">
                @foreach($mainCategories as $category)
                    <div x-data="{ expanded: false }" class="category-item bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
                        data-id="{{ $category->id }}">
                        
                        <div class="flex justify-between items-center p-4 border-b border-gray-100 bg-slate-50">
                            <!-- Left side: Hamburger, toggle arrow, image, name -->
                            <div class="flex items-center gap-3">
                                <!-- Hamburger -->
                                <div class="cursor-move category-handle text-gray-400 hover:text-gray-600" title="Drag to reorder">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                    </svg>
                                </div>
                                <!-- Toggle Arrow -->
                                <button @click="expanded = !expanded" class="text-gray-400 hover:text-gray-600 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <!-- Image -->
                                @if($category->image_path)
                                    <img src="{{ asset($category->image_path) }}" alt="{{ $category->name }}"
                                        class="w-6 h-6 object-cover rounded-md border border-gray-200">
                                @endif
                                <!-- Name & Status -->
                                <h4 class="font-bold text-gray-800">{{ $category->name }}</h4>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $category->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            
                            <!-- Right side: Actions -->
                            <div class="flex gap-2">
                                <button
                                    onclick="openEditCategoryModal({{ $category->id }}, '{{ addslashes($category->name) }}', {{ $category->is_active ? 1 : 0 }}, {{ $category->parent_id ?? 'null' }}, {{ $category->is_visible_on_website ? 1 : 0 }})"
                                    class="text-xs font-semibold text-blue-600 hover:text-blue-800 px-2 py-1 bg-blue-50 rounded">Edit</button>
                                <form method="POST" action="{{ route('settings.destroyServiceCategory', $category) }}"
                                    onsubmit="return confirm('Are you sure you want to delete this category?')"
                                    class="m-0">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="text-xs font-semibold text-red-600 hover:text-red-800 px-2 py-1 bg-red-50 rounded">Delete</button>
                                </form>
                            </div>
                        </div>

                        <!-- Expandable Content -->
                        <div x-show="expanded" x-collapse>
                            @include('settings.partials.services.categories_subcategories')

                            <!-- Main Category Services -->
                            <div class="services-sortable" data-category-id="{{ $category->id }}">
                                @forelse($category->services as $service)
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
                                                onclick="openEditServiceModal({{ $service->id }}, '{{ addslashes($service->name) }}', {{ $service->base_price }}, '{{ $service->price_type }}', {{ $service->is_active ? 1 : 0 }}, '{{ $service->color ?? 'blue' }}', {{ $service->category_id ?? 'null' }}, {{ $service->is_visible_on_website ? 1 : 0 }}, '{{ addslashes($service->item_code ?? '') }}')"
                                                class="text-xs font-semibold text-blue-600 hover:text-blue-800 px-2 py-1 bg-blue-50 rounded">Edit</button>
                                            <form method="POST" action="{{ route('settings.destroyService', $service) }}"
                                                onsubmit="return confirm('Delete this service?')" class="m-0">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800 px-2 py-1 bg-red-50 rounded">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-xs text-gray-400 font-medium bg-gray-50/50 service-empty-state">
                                        No services in this category.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
                
                @include('settings.partials.services.categories_uncategorized')
            </div>
