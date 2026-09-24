        <!-- Top Profile Card -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col md:flex-row">
            <div class="bg-gradient-to-br from-blue-600 to-indigo-700 p-8 text-white flex flex-col items-center justify-center md:w-1/3">
                <div class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center text-4xl font-black mb-4 shadow-inner">
                    {{ strtoupper(substr($staff->name, 0, 1)) }}
                </div>
                <h2 class="text-2xl font-black text-center leading-tight mb-1">{{ $staff->name }}</h2>
                <span class="px-3 py-1 bg-white/20 text-white text-xs font-bold rounded-full mb-3 shadow-sm border border-white/30">
                    {{ ucfirst($staff->role) }}
                </span>
                <p class="text-blue-100 text-sm font-medium text-center">
                    {{ $staff->branch ? $staff->branch->name : 'Global / Main Branch' }}
                </p>
                @if(!$staff->is_active)
                    <span class="mt-4 px-3 py-1 bg-red-500/20 text-red-100 text-xs font-bold rounded-full shadow-sm border border-red-500/30">
                        🚫 Suspended Account
                    </span>
                @endif
            </div>

            <div class="p-8 md:w-2/3 grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8 bg-gray-50/30">
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Email Address</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $staff->email }}</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Phone Number</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $staff->phone ?? 'Not Provided' }}</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">NIC Number</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $staff->profile->nic ?? 'Not Provided' }}</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Vehicle Number</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $staff->profile->vehicle_number ?? 'Not Provided' }}</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Date of Birth</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $staff->profile?->dob ? \Carbon\Carbon::parse($staff->profile->dob)->format('M d, Y') : 'Not Provided' }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Residential Address</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $staff->profile->address ?? 'Not Provided' }}</p>
                </div>
                <div class="md:col-span-2 pt-4 border-t border-gray-100 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-[10px] uppercase font-bold text-red-400 mb-1 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Emergency Contact
                        </p>
                        <p class="text-sm font-bold text-gray-800">{{ $staff->profile->emergency_contact ?? 'Not Provided' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-blue-500 mb-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Monthly Free Leaves
                        </p>
                        @php
                            $monthlyAllowed = $payroll->monthly_allowed_leaves ?? ($staff->profile->casual_leaves ?? 2);
                            $unpaidTaken = $payroll->unpaid_leave_days ?? 0;
                            $freeRemaining = max(0, $monthlyAllowed - $unpaidTaken);
                            $excessTaken = $payroll->excess_leaves ?? 0;
                        @endphp
                        
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-xl font-black {{ $freeRemaining == 0 ? 'text-amber-600' : 'text-gray-800' }}">
                                {{ $freeRemaining }} / {{ $monthlyAllowed }}
                            </span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase">Days Free</span>
                        </div>

                        @if($excessTaken > 0)
                            <div class="mt-1 inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                ⚠️ {{ $excessTaken }} Excess Day{{ $excessTaken > 1 ? 's' : '' }} (-Rs. {{ number_format($payroll->excess_leave_deduction, 2) }})
                            </div>
                        @elseif($unpaidTaken > 0)
                            <div class="mt-1 text-[10px] font-bold text-emerald-600">
                                ✓ {{ $unpaidTaken }} Day{{ $unpaidTaken > 1 ? 's' : '' }} used within free limit
                            </div>
                        @else
                            <div class="mt-1 text-[10px] font-semibold text-gray-400">
                                Full quota available
                            </div>
                        @endif
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-500 mb-1">Hourly Rate</p>
                        <p class="text-lg font-black text-gray-800">Rs. {{ number_format($staff->profile->hourly_rate ?? 0, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-green-500 mb-1">Net Salary ({{ date('M') }})</p>
                        <p class="text-lg font-black text-green-600">Rs. {{ number_format($payroll->net_salary, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>
