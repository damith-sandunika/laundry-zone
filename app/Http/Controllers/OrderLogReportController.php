<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\OrderCorrectionRequest;
use Illuminate\Http\Request;

class OrderLogReportController extends Controller
{
    public function index(Request $request)
    {
        $query = OrderCorrectionRequest::with(['order.branch', 'requestedBy', 'approvedBy']);

        // Date Filter
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        
        $query->whereBetween('created_at', [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59'
        ]);

        // Branch Filter
        $branchId = $request->input('branch_id');
        if ($branchId) {
            $query->whereHas('order', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        // Order Number Filter
        if ($request->filled('order_number')) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->order_number . '%');
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20)->appends($request->all());

        $branches = Branch::where('is_active', true)->get();

        return view('reports.order-logs', compact('logs', 'startDate', 'endDate', 'branches', 'branchId'));
    }
}
