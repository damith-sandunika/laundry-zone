<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {{ $staff->name }} ({{ date('F Y', mktime(0, 0, 0, $payroll->month, 10, $payroll->year)) }})</title>
    @include('staff.partials.payslip.styles')
</head>
<body>

    @include('staff.partials.payslip.header')

        @include('staff.partials.payslip.info-grid')

        @include('staff.partials.payslip.statement-table')

        @include('staff.partials.payslip.net-salary')

        @include('staff.partials.payslip.worked-days')

        @include('staff.partials.payslip.footer')
    </div>

</body>
</html>
