<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h2 class="ph-title">Online Bookings</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Manage customer pickup & delivery requests</p>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div style="display:flex;align-items:center;gap:10px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;padding:14px 18px;margin-bottom:24px;">
            <svg width="16" height="16" fill="none" stroke="#16a34a" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span style="font-size:13px;font-weight:600;color:#15803d;">{{ session('success') }}</span>
        </div>
    @endif

    <div x-data="{ statusModalOpen: false, selectedBooking: null, newStatus: '' }" style="display:flex;flex-direction:column;gap:20px;">

        <div style="background:white;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 4px rgba(0,0,0,0.04);overflow:hidden;">
            <div style="padding:18px 24px;border-bottom:1px solid #f8fafc;display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:14px;font-weight:700;color:#0f172a;">All Pickup & Delivery Requests</span>
            </div>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f8fafc;">
                            <th style="padding:10px 24px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:left;">Date Requested</th>
                            <th style="padding:10px 24px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:left;">Customer Details</th>
                            <th style="padding:10px 24px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:left;">Service Details</th>
                            <th style="padding:10px 24px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:left;">Status</th>
                            <th style="padding:10px 24px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                        <tr style="border-top:1px solid #f8fafc;transition:background 0.1s;"
                            onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                            <td style="padding:14px 24px;font-size:12.5px;color:#64748b;line-height:1.4;">
                                {{ $booking->created_at->format('M d, Y h:i A') }}<br>
                                <span style="font-size:11px;font-weight:700;color:#2563eb;">Prefers: {{ $booking->preferred_date ? $booking->preferred_date->format('M d, Y') : 'Anytime' }}</span>
                            </td>
                            <td style="padding:14px 24px;line-height:1.4;">
                                <p style="font-size:13.5px;font-weight:700;color:#0f172a;margin:0 0 2px;">{{ $booking->customer_name }}</p>
                                <p style="font-size:11.5px;color:#64748b;margin:0 0 1px;">{{ $booking->customer_phone }}</p>
                                <p style="font-size:11.5px;color:#94a3b8;margin:0;">{{ $booking->customer_address }}</p>
                            </td>
                            <td style="padding:14px 24px;font-size:13px;font-weight:500;color:#374151;line-height:1.4;">
                                {{ $booking->service_description }}
                            </td>
                            <td style="padding:14px 24px;">
                                @php
                                    $colors = [
                                        'pending' => ['#d97706','#fffbeb','#fef3c7'],
                                        'confirmed' => ['#2563eb','#eff6ff','#dbeafe'],
                                        'completed' => ['#16a34a','#f0fdf4','#bbf7d0'],
                                        'cancelled' => ['#dc2626','#fef2f2','#fecaca']
                                    ];
                                    $c = $colors[$booking->status] ?? ['#475569','#f8fafc','#e2e8f0'];
                                @endphp
                                <span style="font-size:11px;font-weight:700;color:{{ $c[0] }};background:{{ $c[1] }};border:1px solid {{ $c[2] }};padding:3px 8px;border-radius:6px;text-transform:uppercase;letter-spacing:0.06em;">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td style="padding:14px 24px;text-align:center;">
                                <button @click="selectedBooking = {{ $booking }}; newStatus = '{{ $booking->status }}'; statusModalOpen = true" 
                                    style="padding:6px 12px;background:#eff6ff;color:#2563eb;font-size:11.5px;font-weight:700;border:1px solid #dbeafe;border-radius:8px;cursor:pointer;transition:all 0.15s;"
                                    onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                                    Update Status
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="padding:40px;text-align:center;font-size:13px;color:#94a3b8;font-weight:500;">No online bookings found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Status Update Modal -->
        <div x-show="statusModalOpen" style="display:none;position:fixed;inset:0;z-index:999;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(3px);padding:20px;">
            <div style="background:white;border-radius:20px;border:1px solid #e2e8f0;width:100%;max-width:480px;box-shadow:0 10px 30px rgba(0,0,0,0.1);overflow:hidden;" @click.away="statusModalOpen = false">
                <form x-bind:action="'/online-bookings/' + selectedBooking?.id + '/status'" method="POST" style="margin:0;">
                    @csrf @method('PUT')
                    <div style="padding:24px 30px;">
                        <h3 style="font-size:18px;font-weight:900;color:#0f172a;margin:0 0 20px;letter-spacing:-0.02em;" x-text="'Update Booking from ' + selectedBooking?.customer_name"></h3>
                        
                        <div>
                            <label style="display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Status</label>
                            <select name="status" x-model="newStatus" style="width:100%;padding:12px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13.5px;font-weight:500;color:#0f172a;background:#f8fafc;outline:none;">
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed (Pickup Scheduled)</option>
                                <option value="completed">Completed (Order Created)</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div style="padding:16px 30px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:10px;">
                        <button type="button" @click="statusModalOpen = false" style="padding:10px 16px;background:white;color:#64748b;font-size:13px;font-weight:600;border-radius:10px;border:1.5px solid #e2e8f0;cursor:pointer;">
                            Cancel
                        </button>
                        <button type="submit" style="padding:10px 18px;background:#1d4ed8;color:white;font-size:13px;font-weight:700;border-radius:10px;border:none;cursor:pointer;box-shadow:0 2px 8px rgba(29,78,216,0.25);">
                            Save Status
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>

