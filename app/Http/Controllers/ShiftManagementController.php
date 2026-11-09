<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\BranchLog;
use App\Models\BranchReopenRequest;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\RegisterSession;
use App\Models\Scopes\BranchScope;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShiftManagementController extends Controller
{
    public function openShift(Request $request)
    {
        $request->validate([
            'opening_cash'   => 'required|numeric|min:0',
            'assigned_staff' => 'nullable|array',
        ]);

        $branchId = $this->getActiveBranchId();

        Log::info('openShift: user=' . auth()->id() . ' branch=' . $branchId);

        $existing = RegisterSession::withoutGlobalScope(BranchScope::class)
            ->where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        if ($existing) {
            Log::info('openShift: session already open id=' . $existing->id);
            return $this->dashboardRedirect()->with('info', 'Branch shift is already open.');
        }

        $today = now()->format('Y-m-d');
        $requireApprovalSetting = Setting::get('require_superadmin_approval_to_reopen_shift', '1') == '1';
        $closedSessionsTodayCount = RegisterSession::withoutGlobalScope(BranchScope::class)
            ->where('branch_id', $branchId)
            ->where('status', 'closed')
            ->whereDate('closed_at', $today)
            ->count();

        if ($requireApprovalSetting && $closedSessionsTodayCount > 0) {
            $approvedRequest = BranchReopenRequest::where('branch_id', $branchId)
                ->where('request_date', $today)
                ->where('status', 'approved')
                ->latest()
                ->first();

            $canReopen = false;
            if ($approvedRequest) {
                $sessionsAfterApproval = RegisterSession::withoutGlobalScope(BranchScope::class)
                    ->where('branch_id', $branchId)
                    ->where('opened_at', '>=', $approvedRequest->reviewed_at)
                    ->count();

                if ($sessionsAfterApproval === 0) {
                    $canReopen = true;
                }
            }

            if (!$canReopen) {
                return $this->dashboardRedirect()->with('error', 'Today\'s shift was previously closed. Re-opening shift on the same day requires Super Admin approval.');
            }
        }

        try {
            DB::transaction(function () use ($branchId, $request) {
                $newSession = RegisterSession::create([
                    'branch_id'    => $branchId,
                    'user_id'      => auth()->id(),
                    'opening_cash' => $request->opening_cash,
                    'opened_at'    => now(),
                    'status'       => 'open',
                ]);

                Log::info('openShift: created session id=' . $newSession->id . ' branch=' . $branchId);

                Branch::where('id', $branchId)->update(['cash_balance' => $request->opening_cash]);

                $assignedStaffDesc = [];
                if ($request->has('assigned_staff')) {
                    foreach ($request->assigned_staff as $staffId) {
                        Attendance::create([
                            'user_id'             => $staffId,
                            'date'                => now()->toDateString(),
                            'register_session_id' => $newSession->id,
                            'is_assigned'         => true,
                            'status'              => 'present',
                        ]);

                        $staff = User::find($staffId);
                        if ($staff) {
                            $assignedStaffDesc[] = $staff->name;
                        }
                    }
                }

                $logDesc = 'Shift opened with Rs. ' . number_format($request->opening_cash, 2);
                if (!empty($assignedStaffDesc)) {
                    $logDesc .= '. Staff Assigned: ' . implode(', ', $assignedStaffDesc);
                }

                BranchLog::create([
                    'branch_id'   => $branchId,
                    'user_id'     => auth()->id(),
                    'action'      => 'shift_opened',
                    'description' => $logDesc,
                ]);

                session()->forget("branch_shift_open_{$branchId}");
            });

            return $this->dashboardRedirect()->with('success', 'Branch shift opened successfully.');

        } catch (\Exception $e) {
            Log::error('openShift error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Shift open failed: ' . $e->getMessage());
        }
    }

    public function updateAttendance(Request $request)
    {
        $request->validate([
            'assigned_staff' => 'required|array',
        ]);

        $branchId = $this->getActiveBranchId();
        $activeSession = RegisterSession::withoutGlobalScope(BranchScope::class)
            ->where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        if (!$activeSession) {
            return redirect()->back()->with('error', 'No active shift found.');
        }

        DB::transaction(function () use ($request, $activeSession, $branchId) {
            foreach ($request->assigned_staff as $key => $data) {
                $staffId = $data['staff_id'] ?? null;
                if (!$staffId) continue;

                $isAssigned = !empty($data['is_assigned']);

                if (str_starts_with($key, 'new_')) {
                    Attendance::create([
                        'user_id' => $staffId,
                        'date' => now()->toDateString(),
                        'status' => 'present',
                        'register_session_id' => $activeSession->id,
                        'is_assigned' => $isAssigned,
                    ]);
                } else {
                    $attendance = Attendance::find($key);
                    if ($attendance && $attendance->register_session_id === $activeSession->id) {
                        $attendance->is_assigned = $isAssigned;
                        $attendance->save();
                    }
                }
            }

            BranchLog::create([
                'branch_id' => $branchId,
                'user_id' => auth()->id(),
                'action' => 'attendance_updated',
                'description' => 'Staff attendance updated during shift.',
            ]);
        });

        return redirect()->back()->with('success', 'Staff attendance updated successfully.');
    }

    public function closeShift(Request $request)
    {
        $branchId = $this->getActiveBranchId();
        $session = RegisterSession::withoutGlobalScope(BranchScope::class)
            ->where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        if (! $session) {
            return redirect()->route('dashboard')->with('error', 'No active branch shift found.');
        }

        $cashCollected = PaymentTransaction::whereHas('order', fn ($q) => $q->where('branch_id', $branchId))
            ->where('created_at', '>=', $session->opened_at)
            ->where('payment_method', 'cash')
            ->sum('amount');

        $directCashOrders = Order::where('branch_id', $branchId)
            ->where('created_at', '>=', $session->opened_at)
            ->where('payment_method', 'cash')
            ->whereDoesntHave('paymentTransactions')
            ->sum('paid_amount');

        $cashCollected += $directCashOrders;

        $bankDepositsLogged = \App\Models\BankDeposit::where('branch_id', $branchId)
            ->where('created_at', '>=', $session->opened_at)
            ->sum('amount');

        $finalCash = $session->opening_cash + $cashCollected - $bankDepositsLogged;

        DB::transaction(function () use ($session, $finalCash, $branchId) {
            $session->update([
                'closing_cash' => $finalCash,
                'closed_at'    => now(),
                'status'       => 'closed',
                'closed_by'    => auth()->id(),
            ]);

            Branch::where('id', $branchId)->update(['cash_balance' => $finalCash]);

            BranchLog::create([
                'branch_id'   => $branchId,
                'user_id'     => auth()->id(),
                'action'      => 'shift_closed',
                'description' => 'Shift closed. Final Cash: Rs. ' . number_format($finalCash, 2),
            ]);

            session()->forget("branch_shift_open_{$branchId}");
        });

        return $this->dashboardRedirect()->with('success', 'Shift closed. Final Cash: Rs.' . number_format($finalCash, 2) . '.');
    }

    private function dashboardRedirect()
    {
        $user = auth()->user();
        if (
            $user->role === 'workshop_admin' ||
            ($user->role === 'staff' && $user->branch && $user->branch->type === 'workshop')
        ) {
            return redirect()->route('workshop.dashboard');
        }
        return redirect()->route('dashboard');
    }
}

