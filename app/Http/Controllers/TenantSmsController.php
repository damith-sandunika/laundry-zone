<?php

namespace App\Http\Controllers;

use App\Models\Central\PlatformSmsPackage;
use App\Models\Central\Tenant;
use App\Models\Central\TenantSmsCredit;
use App\Models\SmsLog;
use App\Services\Tenancy\TenantManager;
use Illuminate\Http\Request;

class TenantSmsController extends Controller
{
    /**
     * Display SMS Sender Logs & Activity.
     */
    public function sender(Request $request)
    {
        $tenant = TenantManager::getTenant();

        $query = SmsLog::with('order');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('phone', 'like', "%{$s}%")
                    ->orWhere('message', 'like', "%{$s}%")
                    ->orWhere('type', 'like', "%{$s}%");
            });
        }

        $logs = $query->latest()->paginate(20)->withQueryString();

        // Counts
        $totalSent = SmsLog::where('status', 'sent')->count();
        $totalFailed = SmsLog::where('status', 'failed')->count();
        $totalPending = SmsLog::where('status', 'pending')->count();
        $totalMessages = SmsLog::count();

        // Fetch remaining SMS balance from Central Landlord
        $centralTenant = $tenant ? Tenant::find($tenant->id) : null;
        $smsBalance = $centralTenant ? $centralTenant->sms_balance : 0;
        $smsRate = $centralTenant ? $centralTenant->sms_rate : 2.00;

        return view('sms.sender', compact(
            'logs',
            'totalSent',
            'totalFailed',
            'totalPending',
            'totalMessages',
            'smsBalance',
            'smsRate',
            'centralTenant'
        ));
    }

    /**
     * Display SMS Payment History & Bundles.
     */
    public function payments()
    {
        $tenant = TenantManager::getTenant();
        $centralTenant = $tenant ? Tenant::find($tenant->id) : null;

        $smsBalance = $centralTenant ? $centralTenant->sms_balance : 0;
        $smsRate = $centralTenant ? $centralTenant->sms_rate : 2.00;

        // Fetch credit purchase history from central DB
        $credits = $tenant 
            ? TenantSmsCredit::where('tenant_id', $tenant->id)->orderByDesc('created_at')->get() 
            : collect();

        $totalPurchased = $credits->where('status', 'completed')->sum('sms_count');
        $totalSpent = $credits->where('status', 'completed')->sum('amount_paid');

        // Fetch dynamic platform SMS packages created by SaaS Admin
        $packages = PlatformSmsPackage::where('is_active', true)->orderBy('order_index')->get();

        return view('sms.payments', compact(
            'centralTenant',
            'smsBalance',
            'smsRate',
            'credits',
            'totalPurchased',
            'totalSpent',
            'packages'
        ));
    }
}

