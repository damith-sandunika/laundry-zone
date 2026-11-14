<?php

namespace App\Services;

use App\Models\BranchReopenRequest;
use App\Models\RegisterSession;
use App\Models\Scopes\BranchScope;
use App\Models\Setting;
use App\Models\WorkshopDispatch;

class WorkshopChartAndStatusService
{
    public function generateChartData(int $branchId): array
    {
        $todayLabels = []; $todayPending = []; $todayDelivered = [];
        for ($h = 0; $h < 8; $h++) {
            $start = now()->startOfDay()->addHours($h * 3);
            $end = $start->copy()->addHours(3);
            $todayLabels[] = $start->format('H:i');
            $q = WorkshopDispatch::where('workshop_branch_id', $branchId)->whereBetween('created_at', [$start, $end]);
            $todayPending[] = (clone $q)->whereIn('status', ['received_at_workshop', 'processing'])->count();
            $todayDelivered[] = (clone $q)->where('status', 'completed_at_workshop')->count();
        }

        $weeklyLabels = []; $weeklyPending = []; $weeklyDelivered = [];
        for ($d = 6; $d >= 0; $d--) {
            $day = now()->subDays($d);
            $weeklyLabels[] = $day->format('D');
            $q = WorkshopDispatch::where('workshop_branch_id', $branchId)->whereDate('created_at', $day->toDateString());
            $weeklyPending[] = (clone $q)->whereIn('status', ['received_at_workshop', 'processing'])->count();
            $weeklyDelivered[] = (clone $q)->where('status', 'completed_at_workshop')->count();
        }

        $monthlyLabels = []; $monthlyPending = []; $monthlyDelivered = [];
        for ($w = 3; $w >= 0; $w--) {
            $start = now()->startOfWeek()->subWeeks($w);
            $end = $start->copy()->endOfWeek();
            $monthlyLabels[] = 'Wk '.$start->format('d/m');
            $q = WorkshopDispatch::where('workshop_branch_id', $branchId)->whereBetween('created_at', [$start, $end]);
            $monthlyPending[] = (clone $q)->whereIn('status', ['received_at_workshop', 'processing'])->count();
            $monthlyDelivered[] = (clone $q)->where('status', 'completed_at_workshop')->count();
        }

        return [
            'today' => ['labels' => $todayLabels, 'pending' => $todayPending, 'delivered' => $todayDelivered],
            'weekly' => ['labels' => $weeklyLabels, 'pending' => $weeklyPending, 'delivered' => $weeklyDelivered],
            'monthly' => ['labels' => $monthlyLabels, 'pending' => $monthlyPending, 'delivered' => $monthlyDelivered],
        ];
    }

    public function checkShiftReopenStatus(int $branchId, ?RegisterSession $activeSession): array
    {
        $today = now()->format('Y-m-d');
        $closedSessionsTodayCount = RegisterSession::withoutGlobalScope(BranchScope::class)
            ->where('branch_id', $branchId)->where('status', 'closed')->whereDate('closed_at', $today)->count();

        $requireApprovalSetting = Setting::get('require_superadmin_approval_to_reopen_shift', '1') == '1';
        $requiresApprovalToOpen = false;
        $pendingReopenRequest = null;
        $hasApprovedReopenRequest = false;

        if ($requireApprovalSetting && $closedSessionsTodayCount > 0 && !$activeSession) {
            $approvedRequest = BranchReopenRequest::where('branch_id', $branchId)
                ->where('request_date', $today)->where('status', 'approved')->latest()->first();

            if ($approvedRequest) {
                $sessionsAfterApproval = RegisterSession::withoutGlobalScope(BranchScope::class)
                    ->where('branch_id', $branchId)->where('opened_at', '>=', $approvedRequest->reviewed_at)->count();
                if ($sessionsAfterApproval === 0) {
                    $hasApprovedReopenRequest = true;
                } else {
                    $requiresApprovalToOpen = true;
                }
            } else {
                $requiresApprovalToOpen = true;
            }

            if ($requiresApprovalToOpen) {
                $pendingReopenRequest = BranchReopenRequest::where('branch_id', $branchId)
                    ->where('request_date', $today)->where('status', 'pending')->latest()->first();
            }
        }

        return [
            'requiresApprovalToOpen' => $requiresApprovalToOpen,
            'pendingReopenRequest' => $pendingReopenRequest,
            'hasApprovedReopenRequest' => $hasApprovedReopenRequest,
            'closedSessionsTodayCount' => $closedSessionsTodayCount,
        ];
    }
}

