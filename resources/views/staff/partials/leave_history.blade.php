                <!-- Leaves -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h3 class="font-bold text-gray-800">Leave History</h3>
                        <button @click="leaveModalOpen = true" class="text-xs font-bold px-3 py-1.5 bg-orange-50 text-orange-600 rounded-lg border border-orange-100 hover:bg-orange-100 transition">
                            Request Leave
                        </button>
                    </div>
                    <div class="p-0 divide-y divide-gray-50">
                        @forelse($staff->leaves()->latest('start_date')->get() as $leave)
                            <div class="p-4 hover:bg-gray-50/50 transition">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <span class="text-[10px] px-2 py-0.5 rounded font-bold uppercase {{ $leave->type === 'sick' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                            {{ ucfirst($leave->type) }} Leave
                                        </span>
                                        <p class="text-xs font-bold text-gray-800 mt-1">
                                            {{ \Carbon\Carbon::parse($leave->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}
                                        </p>
                                    </div>
                                    <span class="text-[10px] px-2 py-0.5 rounded font-bold uppercase {{ $leave->status === 'approved' ? 'bg-green-100 text-green-700' : ($leave->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                        {{ $leave->status }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-gray-500 mb-2">{{ $leave->reason }}</p>
                                @if($leave->status === 'pending' && in_array(auth()->user()->role, ['super_admin', 'branch_admin', 'admin']))
                                    <div class="flex gap-2 mt-2 pt-2 border-t border-gray-100">
                                        <form method="POST" action="{{ route('hr.leaves.update', $leave->id) }}" class="inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="text-[10px] font-bold text-green-600 hover:text-green-700">✅ Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('hr.leaves.update', $leave->id) }}" class="inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="text-[10px] font-bold text-red-600 hover:text-red-700">❌ Reject</button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <p class="text-sm font-semibold text-gray-400">No leaves requested yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
