<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        
        // Fetch active orders (not delivered or cancelled)
        $activeOrders = $customer->orders()
            ->with(['branch', 'items.service'])
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->latest()
            ->get();

        // Fetch order history (delivered or cancelled)
        $orderHistory = $customer->orders()
            ->with(['branch'])
            ->whereIn('status', ['delivered', 'cancelled'])
            ->latest()
            ->take(5)
            ->get();

        // Calculate stats
        $totalOrders = $customer->orders()->count();
        $totalSpent = $customer->orders()->where('payment_status', 'paid')->sum('total_amount');
        $activeCount = $activeOrders->count();

        return view('customer.dashboard.index', compact('customer', 'activeOrders', 'orderHistory', 'totalOrders', 'totalSpent', 'activeCount'));
    }

    public function orders()
    {
        $customer = Auth::guard('customer')->user();
        $orders = $customer->orders()
            ->with(['branch', 'items.service'])
            ->latest()
            ->paginate(10);
            
        return view('customer.dashboard.orders', compact('customer', 'orders'));
    }
}
