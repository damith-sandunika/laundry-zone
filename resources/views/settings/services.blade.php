<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <h2 class="ph-title">Services & Categories</h2>
                <p class="text-sm text-gray-500 mt-1">Manage all laundry services and categories here.</p>
            </div>
            <div class="flex gap-2">
                <button onclick="document.getElementById('add-category-modal').style.display='flex'"
                    class="px-4 py-2 bg-slate-800 text-white text-xs font-semibold rounded hover:bg-slate-900 transition-colors">
                    + Add Category
                </button>
                <button onclick="document.getElementById('add-service-modal').style.display='flex'"
                    class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition-colors">
                    + Add New Service
                </button>
            </div>
        </div>
    </x-slot>

    <div class="p-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden p-6">
            
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
                                    onclick="openEditCategoryModal({{ $category->id }}, '{{ addslashes($category->name) }}', {{ $category->is_active ? 1 : 0 }}, {{ $category->parent_id ?? 'null' }})"
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
                                                            onclick="openEditCategoryModal({{ $child->id }}, '{{ addslashes($child->name) }}', {{ $child->is_active ? 1 : 0 }}, {{ $child->parent_id ?? 'null' }})"
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
                                                    ({{ $service->price_type === 'per_kg' ? 'Per KG' : 'Per Item' }})</span>
                                                <span class="ml-2 text-[10px] font-bold uppercase {{ $service->is_active ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $service->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                                @if($service->is_visible_on_website)
                                                    <span class="ml-2 px-1.5 py-0.5 bg-blue-50 text-blue-600 rounded text-[10px] font-bold border border-blue-100">ðŸŒ Website</span>
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
                                                    ({{ $service->price_type === 'per_kg' ? 'Per KG' : 'Per Item' }})</span>
                                                <span class="ml-2 text-[10px] font-bold uppercase {{ $service->is_active ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $service->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                                @if($service->is_visible_on_website)
                                                    <span class="ml-2 px-1.5 py-0.5 bg-blue-50 text-blue-600 rounded text-[10px] font-bold border border-blue-100">ðŸŒ Website</span>
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
            </div>

        </div>
    </div>    <!-- Add Service Modal -->
    <div id="add-service-modal" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);">
        <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:480px;overflow:hidden;" onclick="event.stopPropagation()">
            <div style="padding:24px 28px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h3 style="font-size:17px;font-weight:900;color:#0f172a;margin:0 0 3px;">Add New Service</h3>
                    <p style="font-size:12px;color:#94a3b8;margin:0;font-weight:500;">Define a new laundry service type</p>
                </div>
                <button onclick="document.getElementById('add-service-modal').style.display='none'" style="width:32px;height:32px;background:#f1f5f9;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('settings.storeService') }}" style="padding:24px 28px;display:flex;flex-direction:column;gap:16px;max-height:80vh;overflow-y:auto;">
                @csrf
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Category</label>
                    <select name="category_id" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;">
                        <option value="">Uncategorized</option>
                        @foreach($serviceCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Service Name</label>
                    <input type="text" name="name" required placeholder="e.g. Wash & Iron" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Item Code (Optional)</label>
                    <input type="text" name="item_code" placeholder="e.g. WI-01" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Base Price (Rs.)</label>
                        <input type="number" name="base_price" required min="0" step="0.01" placeholder="0.00" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Price Type</label>
                        <select name="price_type" required style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;">
                            <option value="per_item">Per Item</option>
                            <option value="per_kg">Per KG</option>
                        </select>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <input type="checkbox" name="is_visible_on_website" id="add-website-visible" value="1" checked style="width:16px;height:16px;accent-color:#1d4ed8;cursor:pointer;">
                    <label for="add-website-visible" style="font-size:12px;font-weight:700;color:#374151;cursor:pointer;">Visible on Website</label>
                </div>
                <div style="display:flex;gap:10px;margin-top:4px;">
                    <button type="button" onclick="document.getElementById('add-service-modal').style.display='none'" style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                    <button type="submit" style="flex:1;padding:11px;background:#1d4ed8;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;">Save Service</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Service Modal -->
    <div id="edit-service-modal" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);">
        <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:480px;overflow:hidden;" onclick="event.stopPropagation()">
            <div style="padding:24px 28px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h3 style="font-size:17px;font-weight:900;color:#0f172a;margin:0 0 3px;">Edit Service</h3>
                </div>
                <button onclick="document.getElementById('edit-service-modal').style.display='none'" style="width:32px;height:32px;background:#f1f5f9;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form id="edit-service-form" method="POST" style="padding:24px 28px;display:flex;flex-direction:column;gap:16px;max-height:80vh;overflow-y:auto;">
                @csrf @method('PUT')
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Category</label>
                    <select id="edit-category-id" name="category_id" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;">
                        <option value="">Uncategorized</option>
                        @foreach($serviceCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Service Name</label>
                    <input type="text" id="edit-name" name="name" required style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Item Code</label>
                    <input type="text" id="edit-item-code" name="item_code" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Base Price</label>
                        <input type="number" id="edit-price" name="base_price" required min="0" step="0.01" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Price Type</label>
                        <select id="edit-price-type" name="price_type" required style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;">
                            <option value="per_item">Per Item</option>
                            <option value="per_kg">Per KG</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Status</label>
                    <select id="edit-active" name="is_active" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <input type="checkbox" name="is_visible_on_website" id="edit-website-visible" value="1" style="width:16px;height:16px;accent-color:#1d4ed8;cursor:pointer;">
                    <label for="edit-website-visible" style="font-size:12px;font-weight:700;color:#374151;cursor:pointer;">Visible on Website</label>
                </div>
                <div style="display:flex;gap:10px;margin-top:4px;">
                    <button type="button" onclick="document.getElementById('edit-service-modal').style.display='none'" style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                    <button type="submit" style="flex:1;padding:11px;background:#1d4ed8;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;">Update Service</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Add Category Modal -->
    <div id="add-category-modal" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);">
        <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:480px;overflow:hidden;" onclick="event.stopPropagation()">
            <div style="padding:24px 28px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h3 style="font-size:17px;font-weight:900;color:#0f172a;margin:0 0 3px;">Add Category</h3>
                    <p style="font-size:12px;color:#94a3b8;margin:0;font-weight:500;">Create a new service category</p>
                </div>
                <button onclick="document.getElementById('add-category-modal').style.display='none'" style="width:32px;height:32px;background:#f1f5f9;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('settings.storeServiceCategory') }}" enctype="multipart/form-data" style="padding:24px 28px;display:flex;flex-direction:column;gap:16px;">
                @csrf
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Parent Category (Optional)</label>
                    <select name="parent_id" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;">
                        <option value="">None (Main Category)</option>
                        @foreach($mainCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Category Name</label>
                    <input type="text" name="name" required placeholder="e.g. Dry Cleaning" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Category Image (Optional)</label>
                    <input type="file" name="image" accept="image/*" style="width:100%;font-size:13px;color:#64748b;background:#f8fafc;padding:10px;border-radius:8px;border:1px dashed #cbd5e1;cursor:pointer;">
                </div>
                <div style="display:flex;gap:10px;margin-top:4px;">
                    <button type="button" onclick="document.getElementById('add-category-modal').style.display='none'" style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                    <button type="submit" style="flex:1;padding:11px;background:#1d4ed8;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;">Save Category</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div id="edit-category-modal" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);">
        <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:480px;overflow:hidden;" onclick="event.stopPropagation()">
            <div style="padding:24px 28px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h3 style="font-size:17px;font-weight:900;color:#0f172a;margin:0 0 3px;">Edit Category</h3>
                </div>
                <button onclick="document.getElementById('edit-category-modal').style.display='none'" style="width:32px;height:32px;background:#f1f5f9;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form id="edit-category-form" method="POST" enctype="multipart/form-data" style="padding:24px 28px;display:flex;flex-direction:column;gap:16px;">
                @csrf @method('PUT')
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Parent Category (Optional)</label>
                    <select id="edit-cat-parent-id" name="parent_id" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;">
                        <option value="">None (Main Category)</option>
                        @foreach($mainCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Category Name</label>
                    <input type="text" id="edit-cat-name" name="name" required style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Update Image (Optional)</label>
                    <input type="file" name="image" accept="image/*" style="width:100%;font-size:13px;color:#64748b;background:#f8fafc;padding:10px;border-radius:8px;border:1px dashed #cbd5e1;cursor:pointer;">
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Status</label>
                    <select id="edit-cat-active" name="is_active" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div style="display:flex;gap:10px;margin-top:4px;">
                    <button type="button" onclick="document.getElementById('edit-category-modal').style.display='none'" style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                    <button type="submit" style="flex:1;padding:11px;background:#1d4ed8;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;">Update Category</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        function openEditServiceModal(id, name, price, priceType, isActive, color, categoryId, isVisible, itemCode) {
            document.getElementById('edit-service-form').action = `/settings/services/${id}`;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-price').value = price;
            document.getElementById('edit-price-type').value = priceType;
            document.getElementById('edit-active').value = isActive;
            document.getElementById('edit-category-id').value = categoryId || '';
            document.getElementById('edit-website-visible').checked = isVisible == 1;
            document.getElementById('edit-item-code').value = itemCode || '';
            document.getElementById('edit-service-modal').style.display = 'flex';
        }

        function openEditCategoryModal(id, name, isActive, parentId) {
            document.getElementById('edit-category-form').action = `/settings/service-categories/${id}`;
            document.getElementById('edit-cat-name').value = name;
            document.getElementById('edit-cat-active').value = isActive;
            document.getElementById('edit-cat-parent-id').value = parentId || '';
            document.getElementById('edit-category-modal').style.display = 'flex';
        }

        // Close modals on outside click
        document.getElementById('add-service-modal').addEventListener('click', function(e) {
            if (e.target === this) this.style.display = 'none';
        });
        document.getElementById('edit-service-modal').addEventListener('click', function(e) {
            if (e.target === this) this.style.display = 'none';
        });
        document.getElementById('add-category-modal').addEventListener('click', function(e) {
            if (e.target === this) this.style.display = 'none';
        });
        document.getElementById('edit-category-modal').addEventListener('click', function(e) {
            if (e.target === this) this.style.display = 'none';
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Sort Main Categories
            const categoriesSortable = document.getElementById('categories-sortable');
            if (categoriesSortable) {
                new Sortable(categoriesSortable, {
                    handle: '.category-handle',
                    animation: 150,
                    ghostClass: 'bg-slate-100',
                    onEnd: function (evt) {
                        const order = Array.from(categoriesSortable.children).map(item => item.dataset.id);
                        fetch('{{ route("settings.reorderCategories") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ ordered_ids: order })
                        });
                    }
                });
            }

            // Sort Services within Categories
            document.querySelectorAll('.services-sortable').forEach(function(el) {
                new Sortable(el, {
                    handle: '.service-handle',
                    animation: 150,
                    ghostClass: 'bg-slate-100',
                    group: 'services', // allows dragging services between categories
                    onEnd: function (evt) {
                        const itemEl = evt.item;
                        const newCategoryEl = evt.to;
                        const newCategoryId = newCategoryEl.dataset.categoryId || null;
                        
                        const order = Array.from(newCategoryEl.children)
                            .filter(item => item.classList.contains('service-item'))
                            .map(item => item.dataset.id);
                        const serviceId = itemEl.dataset.id;

                        fetch('{{ route("settings.reorderServices") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ 
                                ordered_ids: order,
                                category_id: newCategoryId
                            })
                        });
                    }
                });
            });

            // Sort Subcategories within each Main Category
            document.querySelectorAll('.subcategory-sortable').forEach(function(el) {
                new Sortable(el, {
                    handle: '.category-handle',
                    animation: 150,
                    ghostClass: 'bg-blue-50',
                    onEnd: function (evt) {
                        const order = Array.from(el.children).map(item => item.dataset.id).filter(Boolean);
                        fetch('{{ route("settings.reorderCategories") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ ordered_ids: order })
                        });
                    }
                });
            });
        });
        function showServiceMoveToast() {
            let toast = document.getElementById('service-move-toast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'service-move-toast';
                toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;background:#1d4ed8;color:white;padding:12px 20px;border-radius:12px;font-size:13px;font-weight:700;box-shadow:0 8px 24px rgba(29,78,216,0.4);display:flex;align-items:center;gap:8px;transform:translateY(80px);transition:transform 0.3s ease;';
                toast.innerHTML = '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Service moved & saved';
                document.body.appendChild(toast);
            }
            setTimeout(function() { toast.style.transform = 'translateY(0)'; }, 10);
            clearTimeout(toast._timeout);
            toast._timeout = setTimeout(function() { toast.style.transform = 'translateY(80px)'; }, 2500);
        }
    </script>

</x-app-layout>