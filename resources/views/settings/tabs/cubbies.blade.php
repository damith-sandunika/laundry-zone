            <div style="display: block;">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-semibold text-gray-800">Cubbets / Racks</h3>
                    <button @click="$dispatch('open-cubby-modal')"
                        class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors">
                        + Add Location
                    </button>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @forelse($cubbies as $cubby)
                        <div x-data="{ editing: false, newName: '{{ addslashes($cubby->name) }}' }"
                            class="p-4 border rounded-md text-center relative bg-green-50 border-green-200">
                            <div class="absolute top-1 right-1 flex gap-1">
                                <button type="button" @click="editing = true"
                                    class="text-blue-500 hover:text-blue-700 p-1 rounded hover:bg-blue-50 transition-colors"
                                    title="Edit">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <form action="{{ route('settings.destroyCubby', $cubby) }}" method="POST"
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
                            </div>

                            <div x-show="!editing">
                                <h4 class="font-bold text-gray-800 mt-2">{{ $cubby->name }}</h4>
                                <span
                                    class="text-xs font-semibold px-2 py-0.5 rounded mt-2 inline-block bg-green-100 text-green-700">
                                    Active
                                </span>
                            </div>

                            <div x-show="editing" class="mt-4" style="display:none;">
                                <form action="{{ route('settings.updateCubby', $cubby) }}" method="POST"
                                    class="flex flex-col gap-2">
                                    @csrf @method('PUT')
                                    <input type="text" name="name" x-model="newName"
                                        class="w-full text-xs border-gray-300 rounded px-2 py-1 text-center" required>
                                    <div class="flex gap-1 justify-center">
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
                            <span class="text-gray-500 text-sm font-medium">No cubbies added yet.</span>
                        </div>
                    @endforelse
                </div>
            </div>
