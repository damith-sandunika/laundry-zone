            <div style="display: block;">

                <!-- Global Delivery Settings -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-8 p-6">
                    <h3 class="text-sm font-semibold text-gray-800 mb-4 border-b pb-2">Global Delivery Settings</h3>
                    <form action="{{ route('settings.updateDeliverySettings') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Minimum Delivery
                                    Cost (Rs.)</label>
                                <input type="number" step="0.01" name="minimum_delivery_cost"
                                    value="{{ $minimumDeliveryCost }}"
                                    class="w-full text-sm border-gray-300 rounded-md p-2">
                                <p class="text-xs text-gray-500 mt-1">If the calculated cost is lower than this, this
                                    amount will be charged.</p>
                            </div>
                        </div>



                        <div class="mt-4 flex justify-end">
                            <button type="submit"
                                class="bg-blue-600 text-white px-4 py-2 rounded-md font-bold text-sm hover:bg-blue-700">Save
                                Settings</button>
                        </div>
                    </form>
                </div>

                <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
                    <h3 class="text-sm font-semibold text-gray-800">Delivery Ranges & Cost Calculation</h3>
                    <button onclick="document.getElementById('add-delivery-range-modal').style.display='flex'"
                        class="px-4 py-2 bg-slate-800 text-white text-xs font-semibold rounded hover:bg-slate-900 transition-colors">
                        + Add Delivery Range
                    </button>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-8">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">
                                    Distance Range (km)</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">
                                    Delivery Cost (Rs.)</th>
                                <th class="px-6 py-3 text-right font-bold text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($deliveryRanges ?? [] as $range)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-800">
                                        {{ $range->min_distance_km }} - {{ $range->max_distance_km }} km
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-blue-600">
                                        Rs. {{ number_format($range->cost, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <form method="POST" action="{{ route('settings.deleteDeliveryRange', $range) }}"
                                            onsubmit="return confirm('Are you sure you want to delete this range?')"
                                            class="inline-block m-0">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="text-xs font-semibold text-red-600 hover:text-red-800 px-2 py-1 bg-red-50 rounded">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-6 text-center text-gray-500 font-medium">No delivery
                                        ranges set.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
