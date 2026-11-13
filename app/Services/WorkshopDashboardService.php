<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\InventoryItem;
use App\Models\RegisterSession;
use App\Models\Scopes\BranchScope;
use App\Models\User;
use App\Models\WorkshopDispatch;

class WorkshopDashboardService
{
    protected WorkshopShiftStatsCalculator $statsCalculator;
    protected WorkshopChartAndStatusService $chartAndStatusService;

    public function __construct(
        ?WorkshopShiftStatsCalculator $statsCalculator = null,
        ?WorkshopChartAndStatusService $chartAndStatusService = null
    ) {
        $this->statsCalculator = $statsCalculator ?? new WorkshopShiftStatsCalculator();
        $this->chartAndStatusService = $chartAndStatusService ?? new WorkshopChartAndStatusService();
    }

    public function getDashboardData(int $branchId): array
    {
        $activeSession = RegisterSession::withoutGlobalScope(BranchScope::class)
            ->where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        $suggestedOpeningCash = 0;
        if (!$activeSession) {
            $lastSession = RegisterSession::withoutGlobalScope(BranchScope::class)
                ->where('branch_id', $branchId)
                ->where('status', 'closed')
                ->orderBy('closed_at', 'desc')
                ->first();
            $suggestedOpeningCash = $lastSession ? $lastSession->closing_cash : 0;
        }

        $stats = $this->statsCalculator->calculate($branchId, $activeSession);

        $recentOrders = WorkshopDispatch::with(['order.customer', 'branch'])
            ->join('orders', 'workshop_dispatches.order_id', '=', 'orders.id')
            ->select('workshop_dispatches.*')
            ->where('workshop_branch_id', $branchId)
            ->whereIn('workshop_dispatches.status', ['dispatched_to_workshop', 'received_at_workshop', 'processing'])
            ->orderByRaw('orders.due_date IS NULL')
            ->orderBy('orders.due_date', 'asc')
            ->get();

        $lowStockItems = InventoryItem::whereColumn('current_stock', '<=', 'low_stock_threshold')
            ->where('branch_id', $branchId)->get();
        $allInventory = InventoryItem::where('branch_id', $branchId)->get();

        $currentBranch = Branch::find($branchId);
        $branchName = $currentBranch->name ?? 'Workshop';
        $branchStaff = User::where('branch_id', $branchId)->where('role', 'staff')->get();

        $activeAttendances = $activeSession
            ? Attendance::where('register_session_id', $activeSession->id)->get()->keyBy('user_id')
            : collect();

        $chartData = $this->chartAndStatusService->generateChartData($branchId);
        $reopenData = $this->chartAndStatusService->checkShiftReopenStatus($branchId, $activeSession);

        return array_merge([
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'lowStockItems' => $lowStockItems,
            'allInventory' => $allInventory,
            'activeSession' => $activeSession,
            'suggestedOpeningCash' => $suggestedOpeningCash,
            'branchName' => $branchName,
            'chartData' => $chartData,
            'currentBranch' => $currentBranch,
            'branchStaff' => $branchStaff,
            'activeAttendances' => $activeAttendances,
        ], $reopenData);
    }
}
