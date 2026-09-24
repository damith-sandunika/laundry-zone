        <!-- Net Payable Salary Box -->
        <div class="net-salary-box">
            <div>
                <div class="net-label">Payable Balance</div>
                <div class="net-sub">Net Remaining Payable Salary (After Advances) for {{ date('F Y', mktime(0, 0, 0, $payroll->month, 10, $payroll->year)) }}</div>
            </div>
            <div class="net-amount">Rs. {{ number_format($payroll->net_salary, 2) }}</div>
        </div>
