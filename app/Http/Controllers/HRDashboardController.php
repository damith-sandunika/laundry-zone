<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use App\Services\PayrollCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class HRDashboardController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('is-super-admin');

        $branchId = $request->query('branch_id');
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        // Base Query for Active Staff
        $staffQuery = User::whereIn('role', ['admin', 'branch_admin', 'staff', 'rider', 'workshop_admin'])
            ->where('is_active', true);

        if ($branchId) {
            $staffQuery->where('branch_id', $branchId);
        }

        $staff = $staffQuery->with(['branch', 'profile'])->orderBy('name', 'asc')->get();
        $totalStaff = $staff->count();

        $staffSummary = $staff->map(function ($user) use ($month, $year) {
            $p = PayrollCalculator::calculate($user, $month, $year);
            return [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->roleLabel(),
                'branch' => $user->branch ? $user->branch->name : 'Global',
                'present_days' => $p->present_days,
                'leave_days' => $p->leave_days,
                'total_worked_hours' => $p->total_worked_hours,
                'day_hours' => max(0, round($p->total_worked_hours - $p->ot_hours, 2)),
                'ot_hours' => $p->ot_hours,
                'adjusted_basic_salary' => $p->adjusted_basic_salary,
                'ot_amount' => $p->ot_amount,
                'net_adjustments' => round($p->allowances - $p->manual_deductions - $p->advances, 2),
                'allowances' => $p->allowances,
                'manual_deductions' => $p->manual_deductions,
                'gross_salary' => $p->gross_salary,
                'advances' => $p->advances,
                'net_salary' => $p->net_salary,
            ];
        });

        // Metrics totals for top cards
        $totalSalaryPay = $staffSummary->sum('net_salary');
        $totalOtPay = $staffSummary->sum('ot_amount');
        $totalWorkingHours = $staffSummary->sum('total_worked_hours');

        $branches = Branch::all();

        return view('hr.dashboard', compact(
            'totalStaff',
            'totalSalaryPay',
            'totalOtPay',
            'totalWorkingHours',
            'branches',
            'branchId',
            'month',
            'year',
            'staffSummary'
        ));
    }

    public function exportPdf(Request $request)
    {
        Gate::authorize('is-super-admin');

        $branchId = $request->query('branch_id');
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $staffQuery = User::whereIn('role', ['admin', 'branch_admin', 'staff', 'rider', 'workshop_admin'])
            ->where('is_active', true);

        if ($branchId) {
            $staffQuery->where('branch_id', $branchId);
        }

        $staff = $staffQuery->with(['branch', 'profile'])->orderBy('name', 'asc')->get();

        $staffSummary = $staff->map(function ($user) use ($month, $year) {
            $p = PayrollCalculator::calculate($user, $month, $year);
            return [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->roleLabel(),
                'branch' => $user->branch ? $user->branch->name : 'Global',
                'present_days' => $p->present_days,
                'leave_days' => $p->leave_days,
                'total_worked_hours' => $p->total_worked_hours,
                'day_hours' => max(0, round($p->total_worked_hours - $p->ot_hours, 2)),
                'ot_hours' => $p->ot_hours,
                'adjusted_basic_salary' => $p->adjusted_basic_salary,
                'ot_amount' => $p->ot_amount,
                'net_adjustments' => round($p->allowances - $p->manual_deductions - $p->advances, 2),
                'gross_salary' => $p->gross_salary,
                'net_salary' => $p->net_salary,
            ];
        });

        $currentBranch = $branchId ? Branch::find($branchId) : null;

        return view('hr.summary_pdf', compact('staffSummary', 'month', 'year', 'currentBranch'));
    }
}
