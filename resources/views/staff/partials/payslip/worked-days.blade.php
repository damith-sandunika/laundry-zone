        <!-- Month Worked Days & Overtime (OT) Breakdown Table -->
        <div class="worked-days-box">
            <div class="worked-days-header">
                <div class="worked-days-title">
                    <span>📅</span>
                    <span>Daily Attendance, Working Hours & Overtime Log</span>
                </div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b;">
                    Total: <strong style="color: #047857;">{{ $payroll->present_days }} Days Worked</strong> 
                    @if($payroll->total_ot_minutes > 0)
                        | <strong style="color: #4f46e5;">{{ $payroll->ot_hours_formatted }} Overtime</strong>
                    @endif
                </div>
            </div>

            @if(isset($attendances) && $attendances->count() > 0)
            <table class="days-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Date</th>
                        <th style="width: 12%;">Day</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 14%;">In Time</th>
                        <th style="width: 14%;">Out Time</th>
                        <th style="width: 15%; text-align: right;">Regular Work</th>
                        <th style="width: 15%; text-align: right;">Overtime (OT)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $att)
                        @php
                            $cDate = \Carbon\Carbon::parse($att->date);
                            $regMinutes = $att->normal_minutes > 0 ? $att->normal_minutes : 0;
                            if ($regMinutes == 0 && $att->clock_in && $att->clock_out && $att->status === 'present') {
                                $cIn = \Carbon\Carbon::parse($att->clock_in);
                                $cOut = \Carbon\Carbon::parse($att->clock_out);
                                $regMinutes = max(0, $cIn->diffInMinutes($cOut) - $att->ot_minutes);
                            }
                            $otMinutes = (int) ($att->ot_minutes ?? 0);
                        @endphp
                        <tr>
                            <td style="font-weight: 700;">{{ $cDate->format('M d, Y') }}</td>
                            <td style="color: #64748b; font-weight: 600;">{{ $cDate->format('l') }}</td>
                            <td>
                                @if($att->status === 'present')
                                    <span class="badge-status present">Present</span>
                                @elseif($att->status === 'paid_leave')
                                    <span class="badge-status paid_leave">Paid Leave</span>
                                @elseif($att->status === 'leave')
                                    <span class="badge-status leave">Leave</span>
                                @elseif($att->status === 'holiday')
                                    <span class="badge-status holiday">Holiday</span>
                                @else
                                    <span class="badge-status present">{{ ucfirst($att->status) }}</span>
                                @endif
                            </td>
                            <td style="font-family: monospace; font-weight: 600;">
                                {{ $att->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('h:i A') : '-' }}
                            </td>
                            <td style="font-family: monospace; font-weight: 600;">
                                {{ $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('h:i A') : '-' }}
                            </td>
                            <td style="text-align: right; font-family: monospace; font-weight: 700; color: #1e293b;">
                                {{ $regMinutes > 0 ? \App\Services\PayrollCalculator::formatHoursAndMinutes($regMinutes, true) : '-' }}
                            </td>
                            <td style="text-align: right; font-family: monospace; font-weight: 700; color: {{ $otMinutes > 0 ? '#4f46e5' : '#94a3b8' }};">
                                {{ $otMinutes > 0 ? '+' . \App\Services\PayrollCalculator::formatHoursAndMinutes($otMinutes, true) : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f8fafc; font-weight: 800; border-top: 2px solid #e2e8f0;">
                        <td colspan="5" style="text-transform: uppercase; font-size: 10px; color: #475569;">
                            Monthly Total Summary
                        </td>
                        <td style="text-align: right; font-family: monospace; font-size: 12px; color: #1e40af;">
                            {{ $payroll->total_regular_hours_formatted }}
                        </td>
                        <td style="text-align: right; font-family: monospace; font-size: 12px; color: #4f46e5;">
                            {{ $payroll->ot_hours_formatted }}
                        </td>
                    </tr>
                </tfoot>
            </table>
            @else
            <div style="padding: 24px; text-align: center; color: #94a3b8; font-weight: 600;">
                No daily attendance logs recorded for this month.
            </div>
            @endif
        </div>

