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
            'clock_out' => 'nullable|date_format:H:i|after_or_equal:clock_in',
            'status' => 'required|in:present,half_day,late',
        ]);

        $staff->attendances()->updateOrCreate(
            ['date' => $request->date],
            [
                'clock_in' => $request->clock_in,
                'clock_out' => $request->clock_out,
                'status' => $request->status,
            ]
        );

        return back()->with('success', 'Attendance marked successfully.');
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

        $staff->leaves()->create([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'type' => $request->type,
            'reason' => $request->reason,
            'status' => $status,
            'approved_by' => $status === 'approved' ? auth()->id() : null,
        ]);

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
