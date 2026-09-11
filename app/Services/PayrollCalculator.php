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

        $basicSalary = (float) ($profile->basic_salary ?? 0);
        $dailyRate = (float) ($profile->daily_rate ?? 0);
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
        $totalLeaveDays = $paidLeaveDays + $unpaidLeaveDays;

        // Excess Leave Deduction applies only to unpaid leaves exceeding allowed quota
        $excessLeaves = max(0, $unpaidLeaveDays - $monthlyAllowedLeaves);
        $excessLeaveDeduction = $excessLeaves * $dailyRate;

        // Working Hours & Short Hours Calculation
        $totalWorkedMinutes = 0;
        $totalOtMinutes = $attendances->sum('ot_minutes');
        $shortHours = 0;

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

            $totalWorkedMinutes += $workedMinutes;
            $dayWorkedHours = $workedMinutes / 60;

            if ($isFullyOt) {
                if ($att->ot_minutes == 0) {
                    $totalOtMinutes += $workedMinutes;
                }
            } else {
                // Short hours deduction if worked less than required hours for this day
                if ($dayWorkedHours < $dayRequiredHours) {
                    $shortHours += ($dayRequiredHours - $dayWorkedHours);
                }
                // Automatic OT calculation if worked more than required hours for this day and ot_minutes wasn't manually set
                elseif ($dayWorkedHours > $dayRequiredHours && $att->ot_minutes == 0) {
                    $extraMinutes = ($dayWorkedHours - $dayRequiredHours) * 60;
                    $totalOtMinutes += $extraMinutes;
                }
            }
        }

        $shortHoursDeduction = $shortHours * $hourlyRate;

        // Total Basic Salary after Leave & Short-hour Deductions
        $adjustedBasicSalary = max(0, $basicSalary - $excessLeaveDeduction - $shortHoursDeduction);

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

        // Final Net Payable Salary
        $netSalary = max(0, $adjustedBasicSalary + $otAmount + $allowances - $manualDeductions - $advances);

        return (object) [
            'month' => $month,
            'year' => $year,
            'basic_salary' => $basicSalary,
            'daily_rate' => $dailyRate,
            'hourly_rate' => $hourlyRate,
            'standard_hours' => $standardHours,
            'monthly_allowed_leaves' => $monthlyAllowedLeaves,
            'present_days' => $presentDays,
            'paid_leave_days' => $paidLeaveDays,
            'unpaid_leave_days' => $unpaidLeaveDays,
            'leave_days' => $totalLeaveDays,
            'excess_leaves' => $excessLeaves,
            'excess_leave_deduction' => $excessLeaveDeduction,
            'total_worked_hours' => round($totalWorkedMinutes / 60, 2),
            'short_hours' => round($shortHours, 2),
            'short_hours_deduction' => round($shortHoursDeduction, 2),
            'adjusted_basic_salary' => round($adjustedBasicSalary, 2),
            'total_ot_minutes' => $totalOtMinutes,
            'ot_hours' => round($totalOtMinutes / 60, 2),
            'ot_amount' => round($otAmount, 2),
            'allowances' => $allowances,
            'manual_deductions' => $manualDeductions,
            'advances' => $advances,
            'net_salary' => round($netSalary, 2),
            'adjustments' => $adjustments,
        ];
    }
}
