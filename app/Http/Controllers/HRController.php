<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HRController extends Controller
{
    public function storeAttendance(Request $request, \App\Models\User $staff)
    {
        $request->validate([
            'date' => 'required|date',
            'clock_in' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,half_day,late',
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

    public function updateAttendance(Request $request, \App\Models\Attendance $attendance)
    {
        $request->validate([
            'clock_out' => 'required|date_format:H:i',
        ]);

        $attendance->update([
            'clock_out' => $request->clock_out,
        ]);

        return back()->with('success', 'Out time marked successfully.');
    }

    public function storeLeave(Request $request, \App\Models\User $staff)
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

    public function updateLeave(Request $request, \App\Models\Leave $leave)
    {
        // Only admins can approve/reject
        if (!auth()->user()->hasAdminAccess()) {
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

    public function storeAdvance(Request $request, \App\Models\User $staff)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'reason' => 'nullable|string',
        ]);

        $staff->salaryAdvances()->create([
            'amount' => $request->amount,
            'date' => $request->date,
            'reason' => $request->reason,
        ]);

        return back()->with('success', 'Salary advance recorded.');
    }
}
