                        <!-- Subcategories -->
                        @if($category->children->count() > 0)
                            <div class="bg-slate-50/50 p-3 border-b border-gray-100">
                                <h5 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 pl-2">
                                    Subcategories</h5>
                                <div class="subcategory-sortable space-y-2 pl-4" data-parent-id="{{ $category->id }}">
                                    @foreach($category->children as $child)
                                        <div x-data="{ subExpanded: false }" class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm category-item" data-id="{{ $child->id }}">
                                            <div class="flex justify-between items-center p-3 border-b border-slate-100 bg-slate-50/80">
                                                <!-- Left Side: Hamburger, Toggle, Image, Name -->
                                                <div class="flex items-center gap-2">
                                                    <!-- Hamburger -->
                                                    <div class="cursor-move category-handle text-slate-400 hover:text-slate-600" title="Drag to reorder">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                                        </svg>
                                                    </div>
                                                    <!-- Toggle Arrow -->
                                                    <button @click="subExpanded = !subExpanded" class="text-slate-400 hover:text-slate-600 transition-transform duration-200" :class="subExpanded ? 'rotate-180' : ''">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                        </svg>
                                                    </button>
                                                    <!-- Image -->
                                                    @if($child->image_path)
                                                        <img src="{{ asset($child->image_path) }}" alt="{{ $child->name }}"
                                                            class="w-5 h-5 object-cover rounded-md border border-gray-200">
                                                    @endif
                                                    <h5 class="font-bold text-slate-700 text-sm">{{ $child->name }}</h5>
                                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider {{ $child->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                        {{ $child->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </div>
                                                
                                                <!-- Right Side: Actions -->
                                                <div class="flex gap-2">
                                                    <button
                                                        onclick="openEditCategoryModal({{ $child->id }}, '{{ addslashes($child->name) }}', {{ $child->is_active ? 1 : 0 }}, {{ $child->parent_id ?? 'null' }}, {{ $child->is_visible_on_website ? 1 : 0 }})"
                                                        class="text-[10px] font-semibold text-blue-600 hover:text-blue-800 px-1.5 py-0.5 bg-blue-50 rounded">Edit</button>
                                                    <form method="POST"
                                                        action="{{ route('settings.destroyServiceCategory', $child) }}"
                                                        onsubmit="return confirm('Are you sure?')" class="m-0">
                                                        @csrf @method('DELETE')
                                                        <button type="submit"
                                                            class="text-[10px] font-semibold text-red-600 hover:text-red-800 px-1.5 py-0.5 bg-red-50 rounded">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                            
                                            <div x-show="subExpanded" x-collapse>
                                                <div class="services-sortable" data-category-id="{{ $child->id }}">
                                                    @forelse($child->services as $service)
                                                        <div class="service-item flex justify-between items-center p-2.5 border-b border-slate-50 bg-white hover:bg-slate-50 transition-colors"
                                                            data-id="{{ $service->id }}">
                                                            <div class="flex items-center gap-3">
                                                                <!-- Hamburger for Service -->
                                                                <div class="cursor-move service-handle text-slate-300 hover:text-slate-500">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                                                    </svg>
                                                                </div>
                                                                <div>
                                                                    <span class="font-bold text-slate-700 text-[13px]">{{ $service->name }}</span>
                                                                    <span class="text-[11px] text-slate-500 ml-2">Rs.{{ number_format($service->base_price, 2) }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="flex gap-2">
                                                                <button
                                                                    onclick="openEditServiceModal({{ $service->id }}, '{{ addslashes($service->name) }}', {{ $service->base_price }}, '{{ $service->price_type }}', {{ $service->is_active ? 1 : 0 }}, '{{ $service->color ?? 'blue' }}', {{ $service->category_id ?? 'null' }}, {{ $service->is_visible_on_website ? 1 : 0 }}, '{{ addslashes($service->item_code ?? '') }}')"
                                                                    class="text-[10px] font-semibold text-blue-600 hover:text-blue-800 px-1.5 py-0.5 bg-blue-50 rounded">Edit</button>
                                                                <form method="POST"
                                                                    action="{{ route('settings.destroyService', $service) }}"
                                                                    onsubmit="return confirm('Delete this service?')" class="m-0">
                                                                    @csrf @method('DELETE')
                                                                    <button type="submit"
                                                                        class="text-[10px] font-semibold text-red-600 hover:text-red-800 px-1.5 py-0.5 bg-red-50 rounded">Delete</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <div class="p-2 text-center text-[11px] text-slate-400 font-medium bg-slate-50/30 service-empty-state">
                                                            No services in this subcategory.
                                                        </div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
