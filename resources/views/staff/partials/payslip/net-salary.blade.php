        <!-- Net Payable Salary Box -->
        <div class="net-salary-box" style="{{ $payroll->net_salary <= 0 ? 'background: linear-gradient(135deg, #15803d 0%, #166534 100%);' : '' }}">
            <div>
                <div class="net-label">{{ $payroll->net_salary <= 0 ? 'Fully Disbursed' : 'Payable Balance' }}</div>
                <div class="net-sub">
                    @if($payroll->net_salary <= 0 && ($payroll->already_paid ?? 0) > 0)
                        Total of Rs. {{ number_format($payroll->already_paid, 2) }} has been fully paid for {{ date('F Y', mktime(0, 0, 0, $payroll->month, 10, $payroll->year)) }}.
                    @else
                        Remaining Payable Balance (Gross Rs. {{ number_format($payroll->gross_salary, 2) }} - Advances Rs. {{ number_format($payroll->advances, 2) }} - Paid Rs. {{ number_format($payroll->already_paid ?? 0, 2) }})
                    @endif
                </div>
            </div>
            <div class="net-amount">
                @if($payroll->net_salary <= 0 && ($payroll->already_paid ?? 0) > 0)
                    Rs. 0.00 <span style="font-size: 14px; opacity: 0.9; font-weight: 700;">(PAID)</span>
                @else
                    Rs. {{ number_format($payroll->net_salary, 2) }}
                @endif
            </div>
        </div>
