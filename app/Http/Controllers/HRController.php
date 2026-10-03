<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Http\Request;

class HRController extends Controller
{
    public function storeAttendance(Request $request, User $staff)
    {
        $request->validate([
            'date' => 'required|date',
            'clock_in' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,half_day,late,leave,paid_leave',
        ]);

        $staff->attendances()->updateOrCreate(
            ['date' => $request->date],
            [
                'clock_in' => $request->clock_in ?? now()->format('H:i:s'),
                'status' => $request->status,
            ]
        );

        return back()->with('success', 'Attendance marked successfully.');
    }

    public function updateAttendance(Request $request, Attendance $attendance)
    {
        $request->validate([
            'clock_out' => 'required|date_format:H:i',
        ]);

        $otMinutes = 0;
        
        // Calculate OT minutes if the attendance has a clock_in and user has standard_working_hours
        if ($attendance->clock_in && $attendance->user && $attendance->user->standard_working_hours > 0) {
            $clockIn = \Carbon\Carbon::parse($attendance->clock_in);
            $clockOut = \Carbon\Carbon::parse($request->clock_out);
            
            // Calculate total minutes worked
            $workedMinutes = $clockIn->diffInMinutes($clockOut);
            
            // Standard minutes
            $standardMinutes = $attendance->user->standard_working_hours * 60;
            
            if ($workedMinutes > $standardMinutes) {
                $otMinutes = $workedMinutes - $standardMinutes;
            }
        }

        $attendance->update([
            'clock_out' => $request->clock_out,
            'ot_minutes' => $otMinutes,
        ]);

        return back()->with('success', 'Out time marked successfully.');
    }

    public function bulkUpdateAttendance(Request $request, User $staff)
    {
        // Must be admin or HR to bulk update
        $user = auth()->user();
        if (!in_array($user->role, ['super_admin', 'branch_admin', 'workshop_admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'attendance_data' => 'required|string',
        ]);

        $data = json_decode($request->attendance_data, true);
        if (!is_array($data)) {
            return back()->with('error', 'Invalid attendance data format.');
        }

        foreach ($data as $dateStr => $dayInfo) {
            $status = $dayInfo['status'] ?? null;
            
            // If status is empty, it means the user cleared it from the UI. Remove the record if it exists.
            if (!$status) {
                $staff->attendances()->where('date', $dateStr)->delete();
                continue;
            }

            $clockIn = !empty($dayInfo['clock_in']) ? $dayInfo['clock_in'] : null;
            $clockOut = !empty($dayInfo['clock_out']) ? $dayInfo['clock_out'] : null;
            $normalMinutes = $dayInfo['normal_minutes'] ?? 0;
            $otMinutes = $dayInfo['ot_minutes'] ?? 0;

            $staff->attendances()->updateOrCreate(
                ['date' => $dateStr],
                [
                    'status' => $status,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'normal_minutes' => $normalMinutes,
                    'ot_minutes' => $otMinutes,
                ]
            );
        }

        return back()->with('success', 'Monthly attendance updated successfully.');
    }

    public function storeLeave(Request $request, User $staff)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|in:casual,sick',
            'reason' => 'required|string',
        ]);

        $status = auth()->user()->hasAdminAccess() ? 'approved' : 'pending';

        $leave = $staff->leaves()->create([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'type' => $request->type,
            'reason' => $request->reason,
            'status' => $status,
            'approved_by' => $status === 'approved' ? auth()->id() : null,
        ]);

        if ($status === 'approved') {
            // Create an 'absent' attendance record for today so the button goes away in the index
            $staff->attendances()->updateOrCreate(
                ['date' => $request->start_date],
                ['status' => 'absent']
            );
        }

        return back()->with('success', $status === 'approved' ? 'Leave marked and approved successfully.' : 'Leave requested successfully.');
    }

    public function updateLeave(Request $request, Leave $leave)
    {
        // Only admins can approve/reject
        if (! auth()->user()->hasAdminAccess()) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $leave->update([
            'status' => $request->status,
            'approved_by' => auth()->id(),
        ]);

        if ($request->status === 'approved') {
            $leave->user->attendances()->updateOrCreate(
                ['date' => $leave->start_date],
                ['status' => 'absent']
            );
        }

        return back()->with('success', 'Leave status updated.');
    }

    public function storeAdvance(Request $request, User $staff)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'reason' => 'nullable|string',
        ]);

        $month = \Carbon\Carbon::parse($request->date)->month;
        $year = \Carbon\Carbon::parse($request->date)->year;

        $payroll = \App\Services\PayrollCalculator::calculate($staff, $month, $year);

        if ($request->amount > $payroll->net_salary) {
            return back()->with('error', 'Advance amount cannot exceed the currently available payable balance (Rs. ' . number_format($payroll->net_salary, 2) . ').');
        }

        $staff->salaryAdvances()->create([
            'amount' => $request->amount,
            'date' => $request->date,
            'reason' => $request->reason,
        ]);

        return back()->with('success', 'Salary advance recorded.');
    }

    public function storeAdjustment(Request $request, User $staff)
    {
        $request->validate([
            'type' => 'required|in:allowance,deduction',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'reason' => 'nullable|string',
        ]);

        $staff->salaryAdjustments()->create([
            'type' => $request->type,
            'amount' => $request->amount,
            'date' => $request->date,
            'reason' => $request->reason,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', ucfirst($request->type) . ' recorded successfully.');
    }

    public function destroyAdjustment(\App\Models\SalaryAdjustment $adjustment)
    {
        $adjustment->delete();
        return back()->with('success', 'Adjustment removed successfully.');
    }
}

