<?php

namespace App\Http\Controllers;

use App\Models\Central\Tenant;
use App\Models\Central\TenantSubscription;
use App\Services\Tenancy\TenantManager;
use Illuminate\Http\Request;

class TenantBillingController extends Controller
{
    /**
     * Display Company Owner / Super Admin Subscription & Billing History.
     */
    public function index()
    {
        $tenant = TenantManager::getTenant() ?? (app()->bound('tenant') ? app('tenant') : null);

        if (!$tenant) {
            return redirect()->route('dashboard')->with('error', 'Tenant context not found.');
        }

        // Fetch subscriptions from Landlord connection
        $subscriptions = TenantSubscription::where('tenant_id', $tenant->id)
            ->orderByDesc('billing_period_start')
            ->get();

        $totalPaid = $subscriptions->where('status', 'paid')->sum('total_amount');
        $outstandingBalance = $subscriptions->whereIn('status', ['pending', 'overdue'])->sum('total_amount');
        $latestSubscription = $subscriptions->first();

        return view('billing.index', compact(
            'tenant',
            'subscriptions',
            'totalPaid',
            'outstandingBalance',
            'latestSubscription'
        ));
    }
}

