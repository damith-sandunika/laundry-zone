<x-app-layout>
<div class="px-3 lg:px-8 py-6 lg:py-8 w-full max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Promotions</h1>
            <p class="text-sm font-medium text-slate-500 mt-1">Manage homepage slider promotions and images.</p>
        </div>
        <button onclick="document.getElementById('add-modal').classList.remove('hidden')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-sm transition-all text-sm">
            + Add New
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl font-medium text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl font-medium text-sm">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="py-3 px-4 font-bold text-slate-900 text-xs uppercase tracking-wider w-10"></th>
                    <th class="py-3 px-4 font-bold text-slate-900 text-xs uppercase tracking-wider">Image</th>
                    <th class="py-3 px-4 font-bold text-slate-900 text-xs uppercase tracking-wider">Title</th>
                    <th class="py-3 px-4 font-bold text-slate-900 text-xs uppercase tracking-wider text-center">Status</th>
                    <th class="py-3 px-4 font-bold text-slate-900 text-xs uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="sortable-list">
                @forelse($promotions as $promo)
                    <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors" data-id="{{ $promo->id }}">
                        <td class="py-3 px-4 cursor-move text-slate-300 hover:text-slate-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
                        </td>
                        <td class="py-3 px-4">
                            <img src="{{ Str::startsWith($promo->image_path, 'http') ? $promo->image_path : asset('storage/' . $promo->image_path) }}" class="h-12 w-auto object-cover rounded-lg border border-slate-200">
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-800 text-sm">
                            {{ $promo->title }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <form action="{{ route('promotions.update', $promo->id) }}" method="POST">
                                @csrf @method('PUT')
                                <button type="submit" name="is_active" value="{{ $promo->is_active ? '0' : '1' }}" class="text-xs font-bold px-2 py-1 rounded-md {{ $promo->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $promo->is_active ? 'Active' : 'Hidden' }}
                                </button>
                            </form>
                        </td>
                        <td class="py-3 px-4 text-right flex justify-end gap-2">
                            <button onclick="openEditModal({{ $promo->id }}, '{{ addslashes($promo->title) }}', '{{ $promo->category_id }}')" class="p-1.5 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                            <form action="{{ route('promotions.destroy', $promo->id) }}" method="POST" onsubmit="return confirm('Delete this promotion?');">
                                @csrf @method('DELETE')
                                <button class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-400 font-medium text-sm">No promotions found. Add one above.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div id="add-modal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden relative">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-lg">Add Promotion</h3>
            <button onclick="document.getElementById('add-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        <form action="{{ route('promotions.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Internal Title (Not shown on site)</label>
                <input type="text" name="title" required class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
            </div>
            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Link to Category (Optional)</label>
                <select name="category_id" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <option value="">None</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-6">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Image</label>
                <input type="file" name="image" required accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('add-modal').classList.add('hidden')" class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-sm text-sm">Save Promotion</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden relative">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-lg">Edit Promotion</h3>
            <button onclick="document.getElementById('edit-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        <form id="edit-form" method="POST" class="p-6">
            @csrf @method('PUT')
            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Internal Title</label>
                <input type="text" id="edit-title" name="title" required class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
            </div>
            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Link to Category (Optional)</label>
                <select id="edit-category-id" name="category_id" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <option value="">None</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')" class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-sm text-sm">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    function openEditModal(id, title, categoryId) {
        document.getElementById('edit-form').action = `/promotions/${id}`;
        document.getElementById('edit-title').value = title;
        document.getElementById('edit-category-id').value = categoryId || '';
        document.getElementById('edit-modal').classList.remove('hidden');
    }

    document.addEventListener('DOMContentLoaded', function () {
        var el = document.getElementById('sortable-list');
        if(el) {
            Sortable.create(el, {
                animation: 150,
                handle: '.cursor-move',
                ghostClass: 'bg-slate-50',
                onEnd: function () {
                    var order = [];
                    el.querySelectorAll('tr').forEach(function(row, index) {
                        order.push({
                            id: row.getAttribute('data-id'),
                            sequence: index
                        });
                    });

                    fetch('{{ route("promotions.reorder") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ order: order })
                    }).then(res => res.json()).then(data => {
                        if(data.success) {
                            console.log('Reordered successfully');
                        }
                    });
                }
            });
        }
    });
</script>
</x-app-layout>


