<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800 leading-tight">
            Manage Branch Monthly Plan
        </h2>
    </x-slot>

    <div class="py-12" x-data="branchPlanConfig()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Filters -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <form action="{{ route('branch.month_plan') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                    @if($user->isSuperAdmin())
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Select Branch</label>
                            <select name="branch_id" class="w-full border-gray-200 rounded-lg text-sm font-semibold focus:ring-blue-500">
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Year</label>
                        <select name="year" class="w-full border-gray-200 rounded-lg text-sm font-semibold focus:ring-blue-500">
                            @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Month</label>
                        <select name="month" class="w-full border-gray-200 rounded-lg text-sm font-semibold focus:ring-blue-500">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 10)) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="px-6 py-2.5 bg-gray-800 text-white text-sm font-bold rounded-lg shadow-sm hover:bg-gray-700 transition">
                            Load Plan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Configuration Grid -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">
                            {{ $selectedBranch->name }} - {{ date('F Y', mktime(0, 0, 0, $month, 10, $year)) }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">Configure working rules for each day.</p>
                    </div>
                    <div>
                        <form action="{{ route('branch.month_plan.store') }}" method="POST" @submit="prepareSubmit" class="flex items-end gap-4">
                            @csrf
                            <input type="hidden" name="branch_id" value="{{ $branchId }}">
                            <input type="hidden" name="year" value="{{ $year }}">
                            <input type="hidden" name="month" value="{{ $month }}">
                            <input type="hidden" name="plan_data" x-model="submitData">
                            
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Default In Time</label>
                                <input type="time" name="default_in_time" value="{{ $plan->default_in_time ? \Carbon\Carbon::parse($plan->default_in_time)->format('H:i') : '08:30' }}" class="text-xs border-gray-200 rounded-lg focus:ring-blue-500 py-2">
                            </div>
                            
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Default Out Time</label>
                                <input type="time" name="default_out_time" value="{{ $plan->default_out_time ? \Carbon\Carbon::parse($plan->default_out_time)->format('H:i') : '16:30' }}" class="text-xs border-gray-200 rounded-lg focus:ring-blue-500 py-2">
                            </div>

                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white text-sm font-bold rounded-lg shadow-sm hover:bg-blue-700 shadow-blue-500/30 transition flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Save Month Plan
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase">
                            <tr>
                                <th class="p-4 w-32">Date</th>
                                <th class="p-4 w-48">Day Type</th>
                                <th class="p-4">Rule Configuration</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="(dayData, dateStr) in planData" :key="dateStr">
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="p-4">
                                        <div class="font-bold text-gray-800" x-text="formatDate(dateStr)"></div>
                                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider" x-text="formatDay(dateStr)"></div>
                                    </td>
                                    <td class="p-4">
                                        <select x-model="dayData.type" class="w-full text-sm border-gray-200 rounded-lg focus:ring-blue-500 font-semibold" :class="{
                                            'bg-green-50 text-green-700 border-green-200': dayData.type === 'normal',
                                            'bg-blue-50 text-blue-700 border-blue-200': dayData.type === 'special',
                                            'bg-red-50 text-red-700 border-red-200': dayData.type === 'holiday'
                                        }">
                                            <option value="normal" class="text-gray-800 bg-white">🟢 Normal</option>
                                            <option value="special" class="text-gray-800 bg-white">🔵 Special</option>
                                            <option value="holiday" class="text-gray-800 bg-white">🔴 Holiday</option>
                                        </select>
                                    </td>
                                    <td class="p-4">
                                        <!-- Configuration based on Type -->
                                        <div x-show="dayData.type === 'normal'" class="text-xs font-semibold text-gray-500 flex items-center gap-2">
                                            <span class="px-2 py-1 bg-gray-100 rounded text-gray-600">Standard Working Hours Apply</span>
                                        </div>
                                        
                                        <div x-show="dayData.type === 'holiday'" class="text-xs font-semibold text-red-500 flex items-center gap-2">
                                            <span class="px-2 py-1 bg-red-50 text-red-600 rounded">Branch is Closed (Or Full OT if worked)</span>
                                        </div>
                                        
                                        <div x-show="dayData.type === 'special'" class="flex gap-4 items-center">
                                            <select x-model="dayData.special_type" class="text-sm border-blue-200 bg-blue-50/50 rounded-lg text-blue-800 font-bold focus:ring-blue-500 min-w-[150px]">
                                                <option value="fully_ot">Fully OT Day</option>
                                                <option value="custom">Custom Normal Hours</option>
                                            </select>
                                            
                                            <div x-show="dayData.special_type === 'custom'" class="flex items-center gap-2">
                                                <span class="text-xs font-bold text-gray-600">Normal Hrs:</span>
                                                <input type="number" step="0.5" min="0" x-model="dayData.normal_hours" class="w-20 text-sm border-gray-200 rounded-lg p-1.5 text-center font-bold">
                                                <span class="text-[10px] text-gray-400 leading-tight w-48">Hours worked beyond this will be counted as OT.</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        function branchPlanConfig() {
            // PHP variables injected
            const serverPlanData = @json($plan->plan_data ?? []);
            const year = {{ $year }};
            const month = {{ $month }};
            
            // Build default days for month
            const daysInMonth = new Date(year, month, 0).getDate();
            const planData = {};
            
            for (let i = 1; i <= daysInMonth; i++) {
                const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                // Merge server data if exists, else default
                if (serverPlanData[dateStr]) {
                    planData[dateStr] = serverPlanData[dateStr];
                    // ensure defaults for legacy/empty
                    if(!planData[dateStr].type) planData[dateStr].type = 'normal';
                    if(planData[dateStr].type === 'special' && !planData[dateStr].special_type) {
                        planData[dateStr].special_type = 'fully_ot';
                        planData[dateStr].normal_hours = 0;
                    }
                } else {
                    planData[dateStr] = {
                        type: 'normal',
                        special_type: 'fully_ot',
                        normal_hours: 0
                    };
                }
            }
            
            return {
                planData: planData,
                submitData: '',
                
                formatDate(dateStr) {
                    const d = new Date(dateStr);
                    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                },
                formatDay(dateStr) {
                    const d = new Date(dateStr);
                    return d.toLocaleDateString('en-US', { weekday: 'long' });
                },
                prepareSubmit() {
                    this.submitData = JSON.stringify(this.planData);
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
