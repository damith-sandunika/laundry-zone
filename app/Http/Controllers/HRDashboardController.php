<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HRDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Only super_admin should see this overall dashboard
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Unauthorized access');
        }

        $branchId = $request->query('branch_id');
        $today = now()->format('Y-m-d');
        $month = now()->month;
        $year = now()->year;

        // Base Query for Staff
        $staffQuery = \App\Models\User::whereIn('role', ['admin', 'branch_admin', 'staff', 'rider', 'workshop_admin'])
            ->where('is_active', true);

        if ($branchId) {
            $staffQuery->where('branch_id', $branchId);
        }

        $staffIds = $staffQuery->pluck('id');
        $totalStaff = $staffIds->count();

        // Today's Attendance
        $attendancesToday = \App\Models\Attendance::whereIn('user_id', $staffIds)
            ->whereDate('date', $today)
            ->get();

        $presentCount = $attendancesToday->where('status', 'present')->count();
        $lateCount = $attendancesToday->where('status', 'late')->count();
        $halfDayCount = $attendancesToday->where('status', 'half_day')->count();

        // Today's Leaves
        $leavesToday = \App\Models\Leave::whereIn('user_id', $staffIds)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->count();

        $notMarkedCount = $totalStaff - ($presentCount + $lateCount + $halfDayCount + $leavesToday);

        // Pending Leaves
        $pendingLeaves = \App\Models\Leave::with('user.branch')
            ->whereIn('user_id', $staffIds)
            ->where('status', 'pending')
            ->latest('start_date')
            ->get();

        // Advances this month
        $advancesThisMonth = \App\Models\SalaryAdvance::whereIn('user_id', $staffIds)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('amount');

        $branches = \App\Models\Branch::all();

        // Recent Activity
        $recentAttendances = \App\Models\Attendance::with('user.branch')
            ->whereIn('user_id', $staffIds)
            ->latest('created_at')
            ->take(5)
            ->get();

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
            'branchId'
        ));
    }
}
