<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Actions\Reports\GetCreditOrdersAction;

class CreditReportController extends Controller
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
        
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        
        $action = new GetCreditOrdersAction();
        $query = $action->execute($branchId, $startDate, $endDate);

        $customers = $query->paginate(15)->withQueryString();
        
        $branches = $isSuperAdmin ? Branch::all() : collect();
        
        return view('reports.credit_orders', compact('customers', 'branches', 'branchId', 'startDate', 'endDate', 'isSuperAdmin'));
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
        
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $action = new GetCreditOrdersAction();
        $query = $action->execute($branchId, $startDate, $endDate, false);

        // Add 31 days limit for PDF to prevent OOM
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        if ($start->diffInDays($end) > 31) {
            return back()->with('error', 'PDF generation is limited to a maximum of 31 days to ensure system stability. Please select a smaller date range.');
        }

        // Use cursor for memory efficient processing
        $ordersCollection = collect();
        foreach ($query->cursor() as $order) {
            $ordersCollection->push($order);
        }
        
        $ordersByCustomer = $ordersCollection->groupBy(function($order) {
            return $order->customer->name ?? 'Unknown Customer';
        });

        $branch = $branchId ? Branch::find($branchId) : null;
        
        Log::info("User ID {$user->id} generated Credit Orders PDF Report from {$startDate} to {$endDate}");
        
        return view('reports.credit_orders_pdf', compact('ordersByCustomer', 'startDate', 'endDate', 'branch', 'isSuperAdmin'));
    }
    
    public function customerOrders(Request $request, $customerId)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $branchId = $isSuperAdmin ? $request->input('branch_id') : $user->branch_id;
        
        $query = Order::with('branch')
            ->where('customer_id', $customerId)
            ->whereColumn('paid_amount', '<', 'total')
            ->where('payment_method', 'credit')
            ->where('status', '!=', 'cancelled')
            ->orderBy('created_at', 'desc');
            
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        
        $orders = $query->get();
        
        $html = view('reports.partials.credit_orders_list', compact('orders'))->render();
        return response()->json(['html' => $html]);
    }
}
