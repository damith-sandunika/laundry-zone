        <!-- Worked Days Breakdown Log -->
        @if(isset($attendances) && $attendances->count() > 0)
        <div class="worked-days-box">
            <div class="worked-days-title">Daily Attendance & Work Log</div>
            <table class="days-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Status</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th class="text-right">Worked Hours</th>
                        <th class="text-right">OT Hours</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $att)
                    <tr>
                        <td><strong>{{ \Carbon\Carbon::parse($att->date)->format('M d, Y') }}</strong></td>
                        <td>{{ \Carbon\Carbon::parse($att->date)->format('l') }}</td>
                        <td>
                            @if($att->status === 'present')
                                <span style="color:#16a34a; font-weight:700;">🟢 Present</span>
                            @elseif($att->status === 'paid_leave')
                                <span style="color:#059669; font-weight:700;">🟢 Paid Leave</span>
                            @elseif($att->status === 'leave')
                                <span style="color:#ea580c; font-weight:700;">🟠 Unpaid Leave</span>
                            @else
                                <span style="color:#64748b;">{{ ucfirst($att->status) }}</span>
                            @endif
                        </td>
                        <td>{{ $att->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('h:i A') : '-' }}</td>
                        <td>{{ $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('h:i A') : '-' }}</td>
                        <td class="text-right font-mono">
                            @if($att->normal_minutes > 0)
                                {{ \App\Services\PayrollCalculator::formatHoursAndMinutes($att->normal_minutes, true) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right font-mono text-indigo">
                            @if($att->ot_minutes > 0)
                                + {{ \App\Services\PayrollCalculator::formatHoursAndMinutes($att->ot_minutes, true) }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f8fafc; font-weight: 800; border-top: 2px solid #e2e8f0;">
                        <td colspan="5" class="text-right" style="font-size: 11px; text-transform: uppercase; color: #475569; padding: 10px 12px;">
                            <strong>Month Total Hours:</strong>
                        </td>
                        <td class="text-right font-mono" style="color: #1e3a8a; font-size: 12px; padding: 10px 12px;">
                            <strong>{{ $payroll->total_regular_hours_formatted }}</strong>
                        </td>
                        <td class="text-right font-mono" style="color: #4f46e5; font-size: 12px; padding: 10px 12px;">
                            <strong>+ {{ $payroll->ot_hours_formatted }}</strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
