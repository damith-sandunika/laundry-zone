<x-app-layout>
    <x-slot name="header">
        <div class="ph-title">📝 Create Order</div>
        <div class="ph-sub">{{ $task->appointment->customer->name ?? 'Customer' }}</div>
    </x-slot>

    <div class="max-w-md mx-auto">
        <form action="{{ route('rider.storeOrder', $task->id) }}" method="POST">
            @csrf
            
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 mb-6">
                <div class="text-sm font-bold text-slate-800 mb-4">Select Services & Items</div>
                
                <div id="items-container" class="space-y-4">
                    @if(isset($cartData) && count($cartData) > 0)
                        @foreach($cartData as $index => $item)
                            <div class="item-row flex items-center gap-3">
                                <select name="items[{{ $index }}][service_id]" required class="flex-1 rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    <option value="">Select Service</option>
                                    @foreach($categories as $category)
                                        <optgroup label="{{ $category->name }}">
                                            @foreach($category->services as $service)
                                                <option value="{{ $service->id }}" data-price="{{ $service->base_price }}" {{ $service->id == $item['service']['id'] ? 'selected' : '' }}>
                                                    {{ $service->name }} (Rs.{{ $service->base_price }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <input type="number" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] }}" min="1" required class="w-20 rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                                <button type="button" class="btn-remove-item text-red-500 hover:bg-red-50 p-2 rounded-lg transition" {{ count($cartData) == 1 ? 'style=display:none;' : '' }}>🗑️</button>
                            </div>
                        @endforeach
                    @else
                        <!-- Default first item -->
                        <div class="item-row flex items-center gap-3">
                            <select name="items[0][service_id]" required class="flex-1 rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="">Select Service</option>
                                @foreach($categories as $category)
                                    <optgroup label="{{ $category->name }}">
                                        @foreach($category->services as $service)
                                            <option value="{{ $service->id }}" data-price="{{ $service->base_price }}">
                                                {{ $service->name }} (Rs.{{ $service->base_price }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <input type="number" name="items[0][quantity]" value="1" min="1" required class="w-20 rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                            <button type="button" class="btn-remove-item text-red-500 hover:bg-red-50 p-2 rounded-lg transition" style="display:none;">🗑️</button>
                        </div>
                    @endif
                </div>

                <button type="button" id="btn-add-item" class="mt-4 w-full py-2 border-2 border-dashed border-slate-200 text-slate-500 font-bold rounded-xl hover:bg-slate-50 transition text-sm">
                    + Add Another Item
                </button>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 mb-6">
                <label class="block text-sm font-bold text-slate-800 mb-2">Notes / Instructions</label>
                <textarea name="notes" rows="2" class="w-full rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="Any specific customer requests?"></textarea>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-black py-4 rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-700 transition">
                Create Order & Notify Customer
            </button>
        </form>
    </div>

    <x-slot name="scripts">
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const container = document.getElementById('items-container');
                const btnAdd = document.getElementById('btn-add-item');
                let itemCount = {{ isset($cartData) && count($cartData) > 0 ? count($cartData) : 1 }};

                // Attach event listener to existing remove buttons
                const existingRemoveBtns = container.querySelectorAll('.btn-remove-item');
                existingRemoveBtns.forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.target.closest('.item-row').remove();
                    });
                });

                btnAdd.addEventListener('click', function() {
                    const firstRow = container.querySelector('.item-row');
                    const newRow = firstRow.cloneNode(true);
                    
                    // Update names
                    const select = newRow.querySelector('select');
                    const input = newRow.querySelector('input');
                    select.name = `items[${itemCount}][service_id]`;
                    select.value = '';
                    input.name = `items[${itemCount}][quantity]`;
                    input.value = 1;
                    
                    // Show remove button
                    const btnRemove = newRow.querySelector('.btn-remove-item');
                    btnRemove.style.display = 'block';
                    btnRemove.addEventListener('click', function() {
                        newRow.remove();
                    });

                    container.appendChild(newRow);
                    itemCount++;
                });
            });
        </script>
    </x-slot>
</x-app-layout>


