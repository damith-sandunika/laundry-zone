<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Central\TenantOwner;
use App\Models\Central\TenantSubscription;
use App\Services\Tenancy\TenantManager;
use App\Services\Tenancy\TenantProvisioner;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(?TenantProvisioner $provisioner = null)
    {
        TenantManager::resetToLandlord();

        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('status', 'active')->count();
        $suspendedTenants = Tenant::where('status', 'suspended')->count();
        $totalOwners = TenantOwner::count();

        // Calculate Monthly Recurring Revenue (MRR)
        $tenants = Tenant::where('status', 'active')->get();
        $totalMrr = $tenants->sum(function ($tenant) {
            return (float) ($tenant->plan_price ?? 15000);
        });

        // Subscriptions summary
        $totalInvoices = TenantSubscription::count();
        $totalPaid = TenantSubscription::where('status', 'paid')->sum('total_amount');
        $pendingInvoices = TenantSubscription::where('status', 'pending')->count();
        $pendingAmount = TenantSubscription::where('status', 'pending')->sum('total_amount');

        // Recent records
        $recentTenants = Tenant::with('latestOwner')->latest()->take(6)->get();
        $recentInvoices = TenantSubscription::with('tenant')->latest()->take(6)->get();

        return view('super_admin.dashboard', compact(
            'totalTenants',
            'activeTenants',
            'suspendedTenants',
            'totalOwners',
            'totalMrr',
            'totalInvoices',
            'totalPaid',
            'pendingInvoices',
            'pendingAmount',
            'recentTenants',
            'recentInvoices'
        ));
    }
}
