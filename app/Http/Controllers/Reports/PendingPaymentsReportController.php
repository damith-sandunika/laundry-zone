<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Actions\Reports\GetPendingPaymentsAction;

class PendingPaymentsReportController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $branchId = $isSuperAdmin ? $request->input('branch_id') : $user->branch_id;
        
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        
        $action = new GetPendingPaymentsAction();
        $query = $action->execute($branchId, $startDate, $endDate);

        $orders = $query->paginate(15)->withQueryString();
        
        $totalsQuery = clone $query;
        $totalsQuery->getQuery()->orders = null;
        
        $totalPendingAmount = $totalsQuery->sum(DB::raw('total - COALESCE(paid_amount, 0)'));
        $totalPendingCount = $totalsQuery->count();

        $branches = $isSuperAdmin ? Branch::all() : collect();
        
        return view('reports.pending_payments', compact('orders', 'branches', 'branchId', 'startDate', 'endDate', 'isSuperAdmin', 'totalPendingAmount', 'totalPendingCount'));
    }
    
    public function exportPdf(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $branchId = $isSuperAdmin ? $request->input('branch_id') : $user->branch_id;
        
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $action = new GetPendingPaymentsAction();
        $query = $action->execute($branchId, $startDate, $endDate);

        // Add 31 days limit for PDF to prevent OOM
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        if ($start->diffInDays($end) > 31) {
            return back()->with('error', 'PDF generation is limited to a maximum of 31 days to ensure system stability. Please select a smaller date range.');
        }

        // Use cursor for memory efficient processing
        $orders = collect();
        foreach ($query->cursor() as $order) {
            $orders->push($order);
        }

        $branch = $branchId ? Branch::find($branchId) : null;
        
        Log::info("User ID {$user->id} generated Pending Payments PDF Report from {$startDate} to {$endDate}");
        
        return view('reports.pending_payments_pdf', compact('orders', 'startDate', 'endDate', 'branch', 'isSuperAdmin'));
    }
}
