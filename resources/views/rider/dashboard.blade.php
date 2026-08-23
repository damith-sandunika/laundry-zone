<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div>
                <h2 class="ph-title">Rider Dashboard</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Overview of your daily tasks and active deliveries</p>
            </div>
            <a href="{{ route('rider.tasks') }}"
                style="display:flex;align-items:center;gap:7px;padding:10px 18px;background:#1d4ed8;color:white;font-size:13px;font-weight:700;border-radius:10px;text-decoration:none;box-shadow:0 2px 10px rgba(29,78,216,0.3);transition:all 0.15s;"
                onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='#1d4ed8'">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
                </svg>
                View All Tasks
            </a>
        </div>
    </x-slot>

    @if(session('success'))
        <div style="margin-bottom:16px;padding:12px 18px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;font-size:13px;font-weight:700;color:#16a34a;">
            ✓ {{ session('success') }}
        </div>
    @endif

    <!-- STAT CARDS -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
        <!-- Active Tasks -->
        <div class="flex flex-col items-center justify-center p-4 md:px-6 md:py-5 rounded-xl border-2 border-orange-500 bg-orange-50 shadow-sm">
            <div class="bg-orange-600 text-white w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <span class="text-xl md:text-2xl font-black mt-2 text-orange-700">{{ $activeTasks->count() }}</span>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-1">Active Tasks</span>
        </div>

        <!-- Completed Today -->
        <div class="flex flex-col items-center justify-center p-4 md:px-6 md:py-5 rounded-xl border-2 border-green-500 bg-green-50 shadow-sm">
            <div class="bg-green-600 text-white w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <span class="text-xl md:text-2xl font-black mt-2 text-green-700">{{ $completedToday }}</span>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-1">Completed Today</span>
        </div>

        <!-- Total Deliveries -->
        <div class="flex flex-col items-center justify-center p-4 md:px-6 md:py-5 rounded-xl border-2 border-blue-500 bg-blue-50 shadow-sm">
            <div class="bg-blue-600 text-white w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <span class="text-xl md:text-2xl font-black mt-2 text-blue-700">{{ $totalCompleted }}</span>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-1">Total Deliveries</span>
        </div>
    </div>

    <!-- TABLE SECTION FOR ACTIVE TASKS -->
    <div style="background:white;border-radius:16px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);overflow:hidden;display:flex;flex-direction:column;gap:0;">
        <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:24px;background:#f8fafc;">
            <div>
                <h3 style="font-size:16px;font-weight:900;color:#0f172a;margin:0;">Current Active Tasks</h3>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;text-align:left;font-size:12px;color:#334155;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Task ID & Type</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Customer</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Location</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Status</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeTasks as $task)
                        <tr style="background:white;border-bottom:1px solid #f1f5f9;transition:all 0.1s;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                            <td style="padding:12px 20px;">
                                <div style="font-size:13px;font-weight:800;color:#1d4ed8;">#{{ str_pad($task->id, 5, '0', STR_PAD_LEFT) }}</div>
                                <div style="margin-top:4px;">
                                    <span style="font-size:10px;font-weight:800;padding:2px 6px;border-radius:4px;border:1px solid #cbd5e1;background:#f8fafc;color:#475569;text-transform:uppercase;">
                                        {{ $task->task_type === 'pickup' ? 'Pickup' : 'Delivery' }}
                                    </span>
                                </div>
                            </td>
                            <td style="padding:12px 20px;">
                                <div style="font-weight:700;color:#0f172a;">{{ $task->appointment->customer->name ?? 'Unknown' }}</div>
                                <div style="font-size:11px;color:#64748b;font-weight:600;">{{ $task->appointment->contact_phone ?? '—' }}</div>
                            </td>
                            <td style="padding:12px 20px;font-weight:600;color:#334155;max-width:200px;">
                                <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ $task->task_type === 'pickup' ? $task->pickup_location : $task->dropoff_location }}
                                </div>
                            </td>
                            <td style="padding:12px 20px;">
                                <span style="display:inline-block;font-size:11px;font-weight:800;padding:4px 9px;border-radius:6px;text-transform:uppercase;letter-spacing:0.04em;
                                    {{ $task->status === 'assigned' ? 'background:#fef3c7;color:#d97706;border:1px solid #fde68a;' : '' }}
                                    {{ in_array($task->status, ['accepted','on_way','arrived']) ? 'background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;' : '' }}
                                ">
                                    {{ $task->statusLabel() }}
                                </span>
                            </td>
                            <td style="padding:12px 20px;text-align:right;">
                                <div style="display:inline-flex;gap:6px;align-items:center;justify-content:flex-end;">
                                    
                                    @php
                                        $mapQ = urlencode($task->task_type === 'pickup' ? $task->pickup_location : $task->dropoff_location);
                                        if ($task->task_type === 'pickup' && $task->appointment->latitude && $task->appointment->longitude) {
                                            $mapQ = $task->appointment->latitude . ',' . $task->appointment->longitude;
                                        }
                                    @endphp
                                    
                                    <!-- Location Button -->
                                    <a href="https://maps.google.com/?q={{ $mapQ }}" target="_blank"
                                        style="padding:5px 10px;background:white;color:#0f172a;border:1.5px solid #cbd5e1;border-radius:8px;font-size:11.5px;font-weight:800;text-decoration:none;transition:all 0.15s;box-shadow:0 1px 2px rgba(0,0,0,0.05);"
                                        onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                        Location
                                    </a>

                                    <!-- Action Buttons depending on status -->
                                    <!-- Simplified Action Buttons -->
                                    @if($task->task_type === 'pickup')
                                        <a href="{{ route('rider.createOrder', $task->id) }}" style="padding:5px 10px;background:#16a34a;color:white;text-decoration:none;border-radius:8px;font-size:11.5px;font-weight:800;box-shadow:0 2px 4px rgba(22,163,74,0.3);">
                                            Pick Order
                                        </a>
                                    @else
                                        <button onclick="updateTask({{ $task->id }}, 'complete')" style="padding:5px 10px;background:#16a34a;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(22,163,74,0.3);">
                                            Deliver Order
                                        </button>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:40px;text-align:center;color:#94a3b8;font-size:13px;font-weight:500;">
                                No active tasks found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-slot name="scripts">
    <script>
    function updateTask(taskId, action) {
        fetch(`/rider/tasks/${taskId}/${action}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Failed to update task.');
            }
        });
    }
    </script>
    </x-slot>
</x-app-layout>
