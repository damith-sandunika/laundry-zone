<?php

namespace App\Actions\Shift;

use App\Models\Attendance;
use App\Models\BranchLog;
use App\Models\RegisterSession;
use App\Models\Scopes\BranchScope;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OpenShiftAction
{
    public function execute(int $branchId, float $openingCash, ?array $assignedStaff, int $userId): RegisterSession
    {
        return DB::transaction(function () use ($branchId, $openingCash, $assignedStaff, $userId) {
            $session = RegisterSession::create([
                'branch_id'    => $branchId,
                'user_id'      => $userId,
                'opened_at'    => now(),
                'opening_cash' => $openingCash,
                'status'       => 'open',
            ]);

            $presentCount = 0;
            if (!empty($assignedStaff)) {
                foreach ($assignedStaff as $staffId => $data) {
                    $user = User::find($staffId);
                    if ($user && $user->branch_id == $branchId) {
                        $isAssigned = !empty($data['is_assigned']);
                        Attendance::create([
                            'user_id'             => $user->id,
                            'date'                => now()->toDateString(),
                            'status'              => 'present',
                            'register_session_id' => $session->id,
                            'is_assigned'         => $isAssigned,
                        ]);
                        $presentCount++;
                    }
                }
            }

            $logDesc = "Shift opened. Opening Cash: Rs. " . number_format($openingCash, 2);
            if ($presentCount > 0) {
                $logDesc .= " | Staff marked present: {$presentCount}";
            }

            BranchLog::create([
                'branch_id'   => $branchId,
                'user_id'     => $userId,
                'action'      => 'shift_opened',
                'description' => $logDesc,
            ]);

            session()->forget("branch_shift_open_{$branchId}");
            return $session;
        });
    }
}
