<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Services\WorkshopDashboardService;
use Illuminate\Http\Request;

class WorkshopDashboardController extends Controller
{
    protected WorkshopDashboardService $dashboardService;

    public function __construct(WorkshopDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function dashboard()
    {
        $branchId = $this->getActiveBranchId();
        $data = $this->dashboardService->getDashboardData($branchId);

        return view('workshop.dashboard', $data);
    }

    public function history(Request $request)
    {
        return redirect()->route('orders.history', $request->query());
    }
}
