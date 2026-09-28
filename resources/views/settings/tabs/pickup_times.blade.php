            <div style="display: block;">
                <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
                    <h3 class="text-sm font-semibold text-gray-800">Pickup Time Periods</h3>
                    <button onclick="document.getElementById('add-pickup-time-modal').style.display='flex'"
                        class="px-4 py-2 bg-slate-800 text-white text-xs font-semibold rounded hover:bg-slate-900 transition-colors">
                        + Add Time Period
                    </button>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-8">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Label
                                </th>
                                <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Time
                                    Range</th>
                                <th class="px-6 py-3 text-right font-bold text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($pickupTimePeriods ?? [] as $period)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-800">
                                        {{ $period->label }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-600">
                                        {{ \Carbon\Carbon::parse($period->start_time)->format('h:i A') }} -
                                        {{ \Carbon\Carbon::parse($period->end_time)->format('h:i A') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <form method="POST" action="{{ route('settings.deletePickupTimePeriod', $period) }}"
                                            onsubmit="return confirm('Are you sure you want to delete this period?')"
                                            class="inline-block m-0">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="text-xs font-semibold text-red-600 hover:text-red-800 px-2 py-1 bg-red-50 rounded">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-6 text-center text-gray-500 font-medium">No pickup time
                                        periods set.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
