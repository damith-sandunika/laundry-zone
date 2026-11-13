<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HRController extends Controller
{
    public function storeAttendance(Request $request, User $staff)
    {
        $request->validate([
            'date'     => 'required|date',
            'clock_in' => 'nullable|date_format:H:i',
            'status'   => 'required|in:present,half_day,late,leave,paid_leave',
        ]);

        $staff->attendances()->updateOrCreate(
            ['date' => $request->date],
            [
                'clock_in' => $request->clock_in ?? now()->format('H:i:s'),
                'status'   => $request->status,
            ]
        );

        return back()->with('success', 'Attendance marked successfully.');
    }

    public function updateAttendance(Request $request, Attendance $attendance)
    {
        $request->validate(['clock_out' => 'required|date_format:H:i']);

        $otMinutes = 0;
        if ($attendance->clock_in && $attendance->user && $attendance->user->standard_working_hours > 0) {
            $clockIn = Carbon::parse($attendance->clock_in);
            $clockOut = Carbon::parse($request->clock_out);
            $workedMinutes = $clockIn->diffInMinutes($clockOut);
            $standardMinutes = $attendance->user->standard_working_hours * 60;
            if ($workedMinutes > $standardMinutes) {
                $otMinutes = $workedMinutes - $standardMinutes;
            }
        }

        $attendance->update([
            'clock_out'  => $request->clock_out,
            'ot_minutes' => $otMinutes,
        ]);

        return back()->with('success', 'Out time marked successfully.');
    }

    public function bulkUpdateAttendance(Request $request, User $staff)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['super_admin', 'branch_admin', 'workshop_admin', 'hr_manager', 'accountant_and_hr_manager'])) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate(['attendance_data' => 'required|string']);
        $data = json_decode($request->attendance_data, true);
        if (!is_array($data)) return back()->with('error', 'Invalid attendance data format.');

        DB::transaction(function () use ($data, $staff) {
            foreach ($data as $dateStr => $dayInfo) {
                $status = $dayInfo['status'] ?? null;
                if (!$status) {
                    $staff->attendances()->where('date', $dateStr)->delete();
                    continue;
                }

                $staff->attendances()->updateOrCreate(
                    ['date' => $dateStr],
                    [
                        'status'         => $status,
                        'clock_in'       => !empty($dayInfo['clock_in']) ? $dayInfo['clock_in'] : null,
                        'clock_out'      => !empty($dayInfo['clock_out']) ? $dayInfo['clock_out'] : null,
                        'normal_minutes' => $dayInfo['normal_minutes'] ?? 0,
                        'ot_minutes'     => $dayInfo['ot_minutes'] ?? 0,
                    ]
                );
            }
        });

        return back()->with('success', 'Monthly attendance updated successfully.');
    }

    public function storeLeave(Request $request, User $staff)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'type'       => 'required|in:casual,sick',
            'reason'     => 'required|string',
        ]);

        $status = auth()->user()->hasAdminAccess() ? 'approved' : 'pending';

        DB::transaction(function () use ($staff, $request, $status) {
            $staff->leaves()->create([
                'start_date'  => $request->start_date,
                'end_date'    => $request->end_date,
                'type'        => $request->type,
                'reason'      => $request->reason,
                'status'      => $status,
                'approved_by' => $status === 'approved' ? auth()->id() : null,
            ]);

            if ($status === 'approved') {
                $staff->attendances()->updateOrCreate(
                    ['date' => $request->start_date],
                    ['status' => 'absent']
                );
            }
        });

        return back()->with('success', $status === 'approved' ? 'Leave marked and approved successfully.' : 'Leave requested successfully.');
    }

    public function updateLeave(Request $request, Leave $leave)
    {
        if (!auth()->user()->hasAdminAccess()) abort(403);
        $request->validate(['status' => 'required|in:approved,rejected']);

        DB::transaction(function () use ($leave, $request) {
            $leave->update([
                'status'      => $request->status,
                'approved_by' => auth()->id(),
            ]);

            if ($request->status === 'approved') {
                $leave->user->attendances()->updateOrCreate(
                    ['date' => $leave->start_date],
                    ['status' => 'absent']
                );
            }
        });

        return back()->with('success', 'Leave status updated.');
    }
}
