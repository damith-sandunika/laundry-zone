            <div x-show="activeTab === 'turnaround_types'" style="display: none;">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-semibold text-gray-800">Turnaround Types (e.g., Normal, Express)</h3>
                    <button onclick="document.getElementById('add-turnaround-type-modal').style.display='flex'"
                        class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors">
                        + Add Type
                    </button>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @forelse($turnaroundTypes ?? [] as $tt)
                        <div x-data="{ editing: false, newName: '{{ addslashes($tt->name) }}', newMarkupType: '{{ $tt->markup_type }}', newMarkupValue: {{ $tt->markup_value }}, newIsDefault: {{ $tt->is_default ? 1 : 0 }}, newIsActive: {{ $tt->is_active ? 1 : 0 }} }"
                            class="p-4 border rounded-md text-center relative {{ $tt->is_active ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }}">
                            {{-- Edit/Delete buttons: only super admin can modify global records --}}
                            @php $canEdit = in_array(auth()->user()->role, ['super_admin', 'workshop_admin']) || !is_null($tt->branch_id); @endphp
                            <div class="absolute top-1 right-1 flex gap-1">
                                @if($canEdit)
                                <button type="button" @click="editing = true"
                                    class="text-blue-500 hover:text-blue-700 p-1 rounded hover:bg-blue-50 transition-colors"
                                    title="Edit">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <form action="{{ route('settings.destroyTurnaroundType', $tt) }}" method="POST"
                                    onsubmit="return confirm('Are you sure?');">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="text-red-400 hover:text-red-600 p-1 rounded hover:bg-red-50 transition-colors"
                                        title="Delete">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>

                            <div x-show="!editing">
                                <h4 class="font-bold text-gray-800 mt-2">
                                    {{ $tt->name }}
                                    @if($tt->is_default)
                                        <span
                                            class="inline-flex items-center justify-center px-2 py-0.5 ml-1 text-[10px] font-bold leading-none text-white bg-blue-600 rounded-full">DEFAULT</span>
                                    @endif
                                </h4>
                                <p class="text-xs text-gray-500 mt-1">Extra:
                                    {{ $tt->markup_type == 'percentage' ? $tt->markup_value . '%' : 'Rs. ' . number_format($tt->markup_value, 2) }}
                                </p>
                                @if(is_null($tt->branch_id))
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded mt-1 inline-block bg-purple-100 text-purple-700 border border-purple-200">🌐 Global</span>
                                @endif
                                <span
                                    class="text-xs font-semibold px-2 py-0.5 rounded mt-1 inline-block {{ $tt->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700' }}">
                                    {{ $tt->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            <div x-show="editing" class="mt-4 text-left" style="display:none;">
                                <form action="{{ route('settings.updateTurnaroundType', $tt) }}" method="POST"
                                    class="flex flex-col gap-2">
                                    @csrf @method('PUT')
                                    <div>
                                        <label
                                            class="text-[10px] text-gray-600 font-bold uppercase mb-0.5 block">Name</label>
                                        <input type="text" name="name" x-model="newName"
                                            class="w-full text-xs border-gray-300 rounded px-2 py-1" required>
                                    </div>
                                    <div>
                                        <label class="text-[10px] text-gray-600 font-bold uppercase mb-0.5 block">Markup
                                            Type</label>
                                        <select name="markup_type" x-model="newMarkupType"
                                            class="w-full text-xs border-gray-300 rounded px-2 py-1">
                                            <option value="percentage">Percentage (%)</option>
                                            <option value="flat">Flat Amount (Rs)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-[10px] text-gray-600 font-bold uppercase mb-0.5 block">Markup
                                            Value</label>
                                        <input type="number" step="0.01" name="markup_value" x-model="newMarkupValue"
                                            class="w-full text-xs border-gray-300 rounded px-2 py-1" required>
                                    </div>
                                    <div>
                                        <label
                                            class="text-[10px] text-gray-600 font-bold uppercase mb-0.5 block">Status</label>
                                        <select name="is_active" x-model="newIsActive"
                                            class="w-full text-xs border-gray-300 rounded px-2 py-1">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <input type="checkbox" name="is_default" value="1" x-model="newIsDefault"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-3 h-3">
                                        <label class="text-[10px] text-gray-600 font-bold">Set as Default</label>
                                    </div>
                                    <div class="flex gap-1 justify-center mt-2">
                                        <button type="submit"
                                            class="bg-blue-600 hover:bg-blue-700 text-white text-[10px] px-3 py-1.5 rounded font-bold">Save</button>
                                        <button type="button" @click="editing = false"
                                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-[10px] px-3 py-1.5 rounded font-bold">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full p-4 border rounded-md text-center bg-gray-50 border-gray-200">
                            <span class="text-gray-500 text-sm font-medium">No turnaround types added yet.</span>
                        </div>
                    @endforelse
                </div>
            </div>
