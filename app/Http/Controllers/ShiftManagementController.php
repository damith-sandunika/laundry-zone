<?php

namespace App\Http\Controllers;

use App\Actions\Shift\CloseShiftAction;
use App\Actions\Shift\OpenShiftAction;
use App\Models\Attendance;
use App\Models\BranchLog;
use App\Models\BranchReopenRequest;
use App\Models\RegisterSession;
use App\Models\Scopes\BranchScope;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShiftManagementController extends Controller
{
    public function openShift(Request $request, OpenShiftAction $action)
    {
        $request->validate([
            'opening_cash'   => 'required|numeric|min:0',
            'assigned_staff' => 'nullable|array',
        ]);

        $branchId = $this->getActiveBranchId();
        $existing = RegisterSession::withoutGlobalScope(BranchScope::class)
            ->where('branch_id', $branchId)->where('status', 'open')->first();

        if ($existing) {
            return $this->dashboardRedirect()->with('info', 'Branch shift is already open.');
        }

        $today = now()->format('Y-m-d');
        $requireApproval = Setting::get('require_superadmin_approval_to_reopen_shift', '1') == '1';
        $closedSessionsCount = RegisterSession::withoutGlobalScope(BranchScope::class)
            ->where('branch_id', $branchId)->where('status', 'closed')->whereDate('closed_at', $today)->count();

        if ($requireApproval && $closedSessionsCount > 0) {
            $approved = BranchReopenRequest::where('branch_id', $branchId)
                ->where('request_date', $today)->where('status', 'approved')->latest()->first();

            if (!$approved) {
                return redirect()->back()->with('error', 'Daily shift was already closed. Superadmin approval is required.');
            }

            $sessionsAfter = RegisterSession::withoutGlobalScope(BranchScope::class)
                ->where('branch_id', $branchId)->where('opened_at', '>=', $approved->reviewed_at)->count();
            if ($sessionsAfter > 0) {
                return redirect()->back()->with('error', 'Reopen approval already consumed. A new approval is required.');
            }
        }

        try {
            $action->execute($branchId, (float) $request->opening_cash, $request->assigned_staff, auth()->id());
            return $this->dashboardRedirect()->with('success', 'Branch shift opened successfully.');
        } catch (\Exception $e) {
            Log::error('openShift error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Shift open failed: ' . $e->getMessage());
        }
    }

    public function updateAttendance(Request $request)
    {
        $request->validate(['assigned_staff' => 'required|array']);
        $branchId = $this->getActiveBranchId();
        $activeSession = RegisterSession::withoutGlobalScope(BranchScope::class)
            ->where('branch_id', $branchId)->where('status', 'open')->first();

        if (!$activeSession) return redirect()->back()->with('error', 'No active shift found.');

        DB::transaction(function () use ($request, $activeSession, $branchId) {
            foreach ($request->assigned_staff as $key => $data) {
                $staffId = $data['staff_id'] ?? null;
                if (!$staffId) continue;
                $isAssigned = !empty($data['is_assigned']);

                if (str_starts_with($key, 'new_')) {
                    Attendance::create([
                        'user_id' => $staffId, 'date' => now()->toDateString(),
                        'status' => 'present', 'register_session_id' => $activeSession->id, 'is_assigned' => $isAssigned,
                    ]);
                } else {
                    $att = Attendance::find($key);
                    if ($att && $att->register_session_id === $activeSession->id) {
                        $att->update(['is_assigned' => $isAssigned]);
                    }
                }
            }

            BranchLog::create([
                'branch_id' => $branchId, 'user_id' => auth()->id(),
                'action' => 'attendance_updated', 'description' => 'Staff attendance updated during shift.',
            ]);
        });

        return redirect()->back()->with('success', 'Staff attendance updated successfully.');
    }

    public function closeShift(Request $request, CloseShiftAction $action)
    {
        $branchId = $this->getActiveBranchId();
        $session = RegisterSession::withoutGlobalScope(BranchScope::class)
            ->where('branch_id', $branchId)->where('status', 'open')->first();

        if (!$session) return redirect()->route('dashboard')->with('error', 'No active branch shift found.');

        $finalCash = $action->execute($session, $branchId, auth()->id());
        return $this->dashboardRedirect()->with('success', 'Shift closed. Final Cash: Rs.' . number_format($finalCash, 2) . '.');
    }

    private function dashboardRedirect()
    {
        $user = auth()->user();
        if ($user->role === 'workshop_admin' || ($user->role === 'staff' && $user->branch && $user->branch->type === 'workshop')) {
            return redirect()->route('workshop.dashboard');
        }
        return redirect()->route('dashboard');
    }
}
