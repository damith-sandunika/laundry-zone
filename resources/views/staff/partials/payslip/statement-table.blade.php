        <!-- Detailed Statement Table -->
        <table class="statement-table">
            <thead>
                <tr>
                    <th>Earnings & Deductions Description</th>
                    <th>Rate / Basis</th>
                    <th class="text-right">Earnings (LKR)</th>
                    <th class="text-right">Deductions (LKR)</th>
                </tr>
            </thead>
            <tbody>
                <!-- Regular Pay -->
                <tr>
                    <td><strong>Regular Worked Hours Pay</strong></td>
                    <td>{{ $payroll->total_regular_hours_for_pay_formatted }} @ Rs. {{ number_format($payroll->hourly_rate, 2) }}/hr</td>
                    <td class="text-right font-mono text-blue-600">+ {{ number_format($payroll->base_pay, 2) }}</td>
                    <td class="text-right font-mono">-</td>
                </tr>

                <!-- Overtime Pay -->
                @if($payroll->ot_amount > 0)
                <tr>
                    <td class="text-indigo">Overtime (OT) Earnings</td>
                    <td>{{ $payroll->ot_hours_formatted }} @ Rs. {{ number_format($staff->profile->ot_rate_per_hour ?? 0, 2) }}/hr</td>
                    <td class="text-right font-mono text-indigo">+ {{ number_format($payroll->ot_amount, 2) }}</td>
                    <td class="text-right font-mono">-</td>
                </tr>
                @endif

                <!-- Allowances -->
                @if($payroll->allowances > 0)
                <tr>
                    <td class="text-green">Allowances & Performance Bonuses</td>
                    <td>Ad-hoc Allowances</td>
                    <td class="text-right font-mono text-green">+ {{ number_format($payroll->allowances, 2) }}</td>
                    <td class="text-right font-mono">-</td>
                </tr>
                @endif

                <!-- Manual Deductions -->
                @if($payroll->manual_deductions > 0)
                <tr>
                    <td class="text-red">Manual Salary Deductions / Fines</td>
                    <td>Ad-hoc Deductions</td>
                    <td class="text-right font-mono">-</td>
                    <td class="text-right font-mono text-red">- {{ number_format($payroll->manual_deductions, 2) }}</td>
                </tr>
                @endif

                <!-- Full Total Salary Row -->
                <tr style="background-color: #f8fafc; font-weight: 800; border-top: 2px solid #cbd5e1; border-bottom: 2px solid #cbd5e1;">
                    <td colspan="2" style="font-size: 13px; color: #0f172a;"><strong>FULL TOTAL SALARY (Gross Earnings)</strong></td>
                    <td class="text-right font-mono" style="color: #1e40af; font-size: 14px;">Rs. {{ number_format($payroll->gross_salary, 2) }}</td>
                    <td class="text-right font-mono">-</td>
                </tr>

                <!-- Salary Advances -->
                @if($payroll->advances > 0)
                <tr>
                    <td class="text-purple">Salary Advances Collected</td>
                    <td>Month Advances Deducted</td>
                    <td class="text-right font-mono">-</td>
                    <td class="text-right font-mono text-purple">- {{ number_format($payroll->advances, 2) }}</td>
                </tr>
                @endif
            </tbody>
        </table>
