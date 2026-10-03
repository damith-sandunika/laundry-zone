<div style="display: block;">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h3 class="text-sm font-semibold text-gray-800">Petty Cash Expense Categories</h3>
            <p class="text-xs text-gray-500 mt-1">
                @if(isset($isGlobalMode) && $isGlobalMode)
                    These categories are applied globally across all branches.
                @else
                    These categories apply to your active branch.
                @endif
            </p>
        </div>
        <button onclick="document.getElementById('add-expense-category-modal').style.display='flex'"
            class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
            + Add Category
        </button>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @forelse($categories ?? [] as $category)
            <div x-data="{ editing: false, newName: '{{ addslashes($category->name) }}' }"
                class="p-4 border rounded-xl text-center relative bg-gray-50 border-gray-200">
                
                @php 
                    $canEdit = auth()->user()->role === 'super_admin' 
                            || (auth()->user()->canManageGlobalConfig() && is_null($category->branch_id)) 
                            || $category->branch_id == auth()->user()->branch_id; 
                @endphp
                
                <div class="absolute top-2 right-2 flex gap-1">
                    @if($canEdit && !$category->is_system)
                        <button type="button" @click="editing = true"
                            class="text-blue-500 hover:text-blue-700 p-1.5 rounded-lg hover:bg-blue-100 transition-colors"
                            title="Edit">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>
                        <form action="{{ route('settings.expense_categories.destroy', $category) }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this category?');">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="text-red-400 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-100 transition-colors"
                                title="Delete">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    @endif
                </div>

                <div x-show="!editing">
                    <h4 class="font-bold text-gray-800 mt-4 text-sm capitalize">
                        {{ $category->name }}
                        @if($category->is_system)
                            <span class="block mt-2">
                                <span class="inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-bold leading-none text-gray-600 bg-gray-200 rounded-full">SYSTEM</span>
                            </span>
                        @endif
                    </h4>
                </div>

                <!-- Edit Form -->
                <div x-show="editing" style="display: none;" class="mt-4 text-left">
                    <form action="{{ route('settings.expense_categories.update', $category) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="mb-2">
                            <label class="block text-[10px] font-semibold text-gray-600 mb-1">Name</label>
                            <input type="text" name="name" x-model="newName" required
                                class="w-full text-xs rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-1.5 px-2">
                        </div>
                        <div class="flex gap-2 justify-end mt-3">
                            <button type="button" @click="editing = false; newName = '{{ addslashes($category->name) }}'"
                                class="px-2 py-1 bg-gray-200 text-gray-700 text-xs font-semibold rounded hover:bg-gray-300">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-2 py-1 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full py-8 text-center text-gray-500 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                <svg class="w-8 h-8 mx-auto text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                No expense categories found. Click "+ Add Category" to create one.
            </div>
        @endforelse
    </div>
</div>

<!-- Add Modal -->
<div id="add-expense-category-modal" style="display: none;" class="fixed inset-0 z-[100] items-center justify-center">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="document.getElementById('add-expense-category-modal').style.display='none'"></div>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 relative z-10 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="font-bold text-gray-800">Add Category</h3>
            <button onclick="document.getElementById('add-expense-category-modal').style.display='none'"
                class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition-colors">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-6">
            <form action="{{ route('settings.expense_categories.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Category Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3" placeholder="e.g., Office Supplies">
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('add-expense-category-modal').style.display='none'"
                        class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                        Create Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
