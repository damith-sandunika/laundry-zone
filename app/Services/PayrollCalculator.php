<?php

namespace App\Services;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\SalaryAdvance;
use App\Models\SalaryAdjustment;
use Carbon\Carbon;

class PayrollCalculator
{
    public static function calculate(User $staff, int $month, int $year): object
    {
        $profile = $staff->profile;

        $hourlyRate = (float) ($profile->hourly_rate ?? 0);
        $standardHours = (float) ($profile->standard_working_hours ?? 8.0);
        $otRatePerHour = (float) ($profile->ot_rate_per_hour ?? 0);
        $monthlyAllowedLeaves = (int) ($profile->casual_leaves ?? 2);

        // Fetch Month's Attendances
        $attendances = Attendance::where('user_id', $staff->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        // Fetch Branch Monthly Plan rules if configured
        $branchPlan = \App\Models\BranchMonthlyPlan::where('branch_id', $staff->branch_id)
            ->where('year', $year)
            ->where('month', $month)
            ->first();
        $planData = $branchPlan ? ($branchPlan->plan_data ?? []) : [];

        // Present days, paid leave days, unpaid leave days
        $presentDays = $attendances->where('status', 'present')->count();
        $paidLeaveDays = $attendances->where('status', 'paid_leave')->count();
        $unpaidAttendanceLeaveDays = $attendances->where('status', 'leave')->count();
        
        // Count leaves from Leave model
        $approvedLeaves = Leave::where('user_id', $staff->id)
            ->where('status', 'approved')
            ->where(function ($q) use ($month, $year) {
                $q->whereMonth('start_date', $month)->whereYear('start_date', $year)
                    ->orWhere(function ($q2) use ($month, $year) {
                        $q2->whereMonth('end_date', $month)->whereYear('end_date', $year);
                    });
            })->get();

        $leaveDaysFromModel = 0;
        foreach ($approvedLeaves as $l) {
            $start = Carbon::parse($l->start_date);
            $end = Carbon::parse($l->end_date);
            $leaveDaysFromModel += $end->diffInDays($start) + 1;
        }

        $unpaidLeaveDays = max($leaveDaysFromModel, $unpaidAttendanceLeaveDays);

        // Only explicitly marked 'paid_leave' days are paid (capped by monthly allowed quota)
        $approvedPaidLeaves = min($paidLeaveDays, $monthlyAllowedLeaves);
        $paidLeaveMinutes = $approvedPaidLeaves * $standardHours * 60;

        // Working Hours Calculation
        $totalRegularMinutes = 0;
        $totalOtMinutes = $attendances->sum('ot_minutes');

        foreach ($attendances->where('status', 'present') as $att) {
            $dateStr = Carbon::parse($att->date)->format('Y-m-d');
            $dayConfig = $planData[$dateStr] ?? ['type' => 'normal'];

            $dayRequiredHours = $standardHours;
            $isFullyOt = false;

            if (($dayConfig['type'] ?? 'normal') === 'special') {
                if (($dayConfig['special_type'] ?? '') === 'fully_ot') {
                    $dayRequiredHours = 0;
                    $isFullyOt = true;
                } else {
                    $dayRequiredHours = isset($dayConfig['normal_hours']) && $dayConfig['normal_hours'] !== ''
                        ? (float) $dayConfig['normal_hours'] 
                        : $standardHours;
                }
            } elseif (($dayConfig['type'] ?? 'normal') === 'holiday') {
                $dayRequiredHours = 0;
                $isFullyOt = true;
            }

            $workedMinutes = 0;
            if ($att->normal_minutes > 0) {
                $workedMinutes = $att->normal_minutes;
            } elseif ($att->clock_in && $att->clock_out) {
                $cIn = Carbon::parse($att->clock_in);
                $cOut = Carbon::parse($att->clock_out);
                $workedMinutes = max(0, $cIn->diffInMinutes($cOut) - $att->ot_minutes);
            } else {
                // If present but clock_out not marked yet, assume full required hours for that day
                $workedMinutes = $dayRequiredHours * 60;
            }

            $dayWorkedHours = $workedMinutes / 60;

            if ($isFullyOt) {
                if ($att->ot_minutes == 0) {
                    $totalOtMinutes += $workedMinutes;
                }
            } else {
                if ($dayWorkedHours > $dayRequiredHours) {
                    $totalRegularMinutes += ($dayRequiredHours * 60);
                    if ($att->ot_minutes == 0) {
                        $totalOtMinutes += ($dayWorkedHours - $dayRequiredHours) * 60;
                    }
                } else {
                    $totalRegularMinutes += $workedMinutes;
                }
            }
        }

        $totalRegularMinutesForPay = $totalRegularMinutes + $paidLeaveMinutes;
        
        // Base Pay (Regular Hours + Paid Leave Hours)
        $basePay = ($totalRegularMinutesForPay / 60) * $hourlyRate;

        // OT Salary
        $otAmount = ($totalOtMinutes / 60) * $otRatePerHour;

        // Allowances & Manual Deductions
        $adjustments = SalaryAdjustment::where('user_id', $staff->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        $allowances = (float) $adjustments->where('type', 'allowance')->sum('amount');
        $manualDeductions = (float) $adjustments->where('type', 'deduction')->sum('amount');

        // Advances
        $advances = (float) SalaryAdvance::where('user_id', $staff->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('amount');

        // Gross Salary
        $grossSalary = max(0, $basePay + $otAmount + $allowances - $manualDeductions);

        // Fetch already paid salaries for this month to subtract from payable balance
        $monthName = date('F Y', mktime(0, 0, 0, $month, 10, $year));
        $alreadyPaid = (float) \App\Models\Expense::where('category', 'salary')
            ->where('description', 'like', "%{$staff->name}%")
            ->where('description', 'like', "%{$monthName}%")
            ->sum('amount');

        // Net Salary (Payable Balance)
        $netSalary = max(0, $grossSalary - $advances - $alreadyPaid);

        return (object) [
            'month' => $month,
            'year' => $year,
            'hourly_rate' => $hourlyRate,
            'standard_hours' => $standardHours,
            'ot_rate_per_hour' => $otRatePerHour,
            'monthly_allowed_leaves' => $monthlyAllowedLeaves,
            'present_days' => $presentDays,
            'leave_days' => $paidLeaveDays + $unpaidAttendanceLeaveDays,
            'approved_paid_leaves' => $approvedPaidLeaves,
            'total_regular_minutes' => $totalRegularMinutes,
            'total_regular_hours' => round($totalRegularMinutes / 60, 2),
            'total_regular_hours_formatted' => self::formatHoursAndMinutes($totalRegularMinutes, true),
            'paid_leave_minutes' => $paidLeaveMinutes,
            'paid_leave_hours_formatted' => self::formatHoursAndMinutes($paidLeaveMinutes, true),
            'total_regular_minutes_for_pay' => $totalRegularMinutesForPay,
            'total_regular_hours_for_pay_formatted' => self::formatHoursAndMinutes($totalRegularMinutesForPay, true),
            'base_pay' => round($basePay, 2),
            'total_ot_minutes' => $totalOtMinutes,
            'ot_hours' => round($totalOtMinutes / 60, 2),
            'ot_hours_formatted' => self::formatHoursAndMinutes($totalOtMinutes, true),
            'ot_amount' => round($otAmount, 2),
            'allowances' => $allowances,
            'manual_deductions' => $manualDeductions,
            'gross_salary' => round($grossSalary, 2),
            'advances' => $advances,
            'already_paid' => $alreadyPaid,
            'net_salary' => round($netSalary, 2),
            'adjustments' => $adjustments,
        ];
    }

    public static function formatHoursAndMinutes(float|int $value, bool $isMinutes = false): string
    {
        $totalMinutes = $isMinutes ? (int) round($value) : (int) round($value * 60);
        if ($totalMinutes <= 0) return '0h';
        $h = intdiv($totalMinutes, 60);
        $m = $totalMinutes % 60;
        if ($h > 0 && $m > 0) return "{$h}h {$m}m";
        if ($h > 0) return "{$h}h";
        return "{$m}m";
    }
}
