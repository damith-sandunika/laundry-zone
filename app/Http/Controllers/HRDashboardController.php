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
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);
        $today = now()->format('Y-m-d');

        // Base Query for Staff
        $staffQuery = \App\Models\User::whereIn('role', ['admin', 'branch_admin', 'staff', 'rider', 'workshop_admin'])
            ->where('is_active', true);

        if ($branchId) {
            $staffQuery->where('branch_id', $branchId);
        }

        $staff = $staffQuery->with('branch')->get();
        $staffIds = $staff->pluck('id');
        $totalStaff = $staffIds->count();

        // Today's Attendance (for Top Cards)
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

        // Advances for selected month
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

        // Monthly Summary for all staff in table
        $monthlyAttendances = \App\Models\Attendance::whereIn('user_id', $staffIds)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->groupBy('user_id');

        $monthlyLeaves = \App\Models\Leave::whereIn('user_id', $staffIds)
            ->where('status', 'approved')
            ->where(function($q) use ($month, $year) {
                $q->whereMonth('start_date', $month)->whereYear('start_date', $year)
                  ->orWhere(function($q2) use ($month, $year) {
                      $q2->whereMonth('end_date', $month)->whereYear('end_date', $year);
                  });
            })
            ->get()
            ->groupBy('user_id');

        $staffSummary = $staff->map(function($user) use ($monthlyAttendances, $monthlyLeaves, $month, $year) {
            $userAttendances = $monthlyAttendances->get($user->id, collect());
            
            $userLeaves = $monthlyLeaves->get($user->id, collect());
            $leaveDays = 0;
            foreach($userLeaves as $l) {
                // Approximate leave days within this month (simple diff)
                // If a leave spans months, ideally we'd calculate overlapping days. 
                // For simplicity, we just take the total days of leaves that touch this month.
                $start = \Carbon\Carbon::parse($l->start_date);
                $end = \Carbon\Carbon::parse($l->end_date);
                $leaveDays += $end->diffInDays($start) + 1;
            }

            return [
                'id' => $user->id,
                'name' => $user->name,
                'branch' => $user->branch ? $user->branch->name : 'Global',
                'present' => $userAttendances->where('status', 'present')->count(),
                'late' => $userAttendances->where('status', 'late')->count(),
                'half_day' => $userAttendances->where('status', 'half_day')->count(),
                'leave_days' => $leaveDays,
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
