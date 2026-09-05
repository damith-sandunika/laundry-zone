<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Leave;
use App\Models\SalaryAdvance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class HRDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Only super_admin should see this overall dashboard
        Gate::authorize('is-super-admin');

        $branchId = $request->query('branch_id');
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);
        $today = now()->format('Y-m-d');

        // Base Query for Staff
        $staffQuery = User::whereIn('role', ['admin', 'branch_admin', 'staff', 'rider', 'workshop_admin'])
            ->where('is_active', true);

        if ($branchId) {
            $staffQuery->where('branch_id', $branchId);
        }

        $staff = $staffQuery->with('branch')->get();
        $staffIds = $staff->pluck('id');
        $totalStaff = $staffIds->count();

        // Today's Attendance (for Top Cards)
        $attendancesToday = Attendance::whereIn('user_id', $staffIds)
            ->whereDate('date', $today)
            ->get();

        $presentCount = $attendancesToday->where('status', 'present')->count();
        $lateCount = $attendancesToday->where('status', 'late')->count();
        $halfDayCount = $attendancesToday->where('status', 'half_day')->count();

        // Today's Leaves
        $leavesToday = Leave::whereIn('user_id', $staffIds)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->count();

        $notMarkedCount = $totalStaff - ($presentCount + $lateCount + $halfDayCount + $leavesToday);

        // Pending Leaves
        $pendingLeaves = Leave::with('user.branch')
            ->whereIn('user_id', $staffIds)
            ->where('status', 'pending')
            ->latest('start_date')
            ->get();

        // Advances for selected month
        $advancesThisMonth = SalaryAdvance::whereIn('user_id', $staffIds)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('amount');

        $branches = Branch::all();

        // Recent Activity
        $recentAttendances = Attendance::with('user.branch')
            ->whereIn('user_id', $staffIds)
            ->latest('created_at')
            ->take(5)
            ->get();

        // Monthly Summary for all staff in table
        $monthlyAttendances = Attendance::whereIn('user_id', $staffIds)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->groupBy('user_id');

        $monthlyLeaves = Leave::whereIn('user_id', $staffIds)
            ->where('status', 'approved')
            ->where(function ($q) use ($month, $year) {
                $q->whereMonth('start_date', $month)->whereYear('start_date', $year)
                    ->orWhere(function ($q2) use ($month, $year) {
                        $q2->whereMonth('end_date', $month)->whereYear('end_date', $year);
                    });
            })
            ->get()
            ->groupBy('user_id');

        $staffSummary = $staff->map(function ($user) use ($monthlyAttendances, $monthlyLeaves, $month, $year) {
            $userAttendances = $monthlyAttendances->get($user->id, collect());

            $userLeaves = $monthlyLeaves->get($user->id, collect());
            $leaveDays = 0;
            foreach ($userLeaves as $l) {
                $start = Carbon::parse($l->start_date);
                $end = Carbon::parse($l->end_date);
                $leaveDays += $end->diffInDays($start) + 1;
            }
            
            // Calculate total OT minutes
            $totalOtMinutes = $userAttendances->sum('ot_minutes');
            
            // OT Amount
            $otRatePerMinute = $user->ot_rate_per_hour / 60;
            $otAmount = $totalOtMinutes * $otRatePerMinute;
            
            // Salary Advances this month for this user
            $advances = \App\Models\SalaryAdvance::where('user_id', $user->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->sum('amount');
                
            // Total Salary (Basic + OT - Advances)
            $basicSalary = $user->basic_salary ?? 0;
            $totalSalary = $basicSalary + $otAmount - $advances;

            return [
                'id' => $user->id,
                'name' => $user->name,
                'branch' => $user->branch ? $user->branch->name : 'Global',
                'present' => $userAttendances->where('status', 'present')->count(),
                'late' => $userAttendances->where('status', 'late')->count(),
                'half_day' => $userAttendances->where('status', 'half_day')->count(),
                'leave_days' => $leaveDays,
                'ot_minutes' => $totalOtMinutes,
                'ot_amount' => $otAmount,
                'basic_salary' => $basicSalary,
                'advances' => $advances,
                'total_salary' => $totalSalary,
            ];
        });

        return view('hr.dashboard', compact(
            'totalStaff',
            'presentCount',
            'lateCount',
            'halfDayCount',
            'leavesToday',
            'notMarkedCount',
            'pendingLeaves',
            'advancesThisMonth',
            'branches',
            'recentAttendances',
            'branchId',
            'month',
            'year',
            'staffSummary'
        ));
    }
}

