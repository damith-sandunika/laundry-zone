<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Central\PlatformSmsPackage;
use App\Models\Central\Tenant;
use App\Models\Central\TenantSmsCredit;
use App\Services\Tenancy\TenantManager;
use Illuminate\Http\Request;

class SmsPackageController extends Controller
{
    /**
     * Display SMS Packages and Top-Up History.
     */
    public function index(Request $request)
    {
        TenantManager::resetToLandlord();

        $packages = PlatformSmsPackage::orderBy('order_index')->get();
        $tenants = Tenant::where('status', 'active')->orderBy('name')->get();

        $creditsQuery = TenantSmsCredit::with('tenant')->latest();

        if ($request->filled('tenant_id')) {
            $creditsQuery->where('tenant_id', $request->tenant_id);
        }

        $smsCredits = $creditsQuery->paginate(15)->withQueryString();

        $totalSmsIssued = TenantSmsCredit::sum('sms_count');
        $totalRevenue = TenantSmsCredit::sum('amount_paid');
        $activeTenantsWithSms = Tenant::where('sms_balance', '>', 0)->count();

        return view('super_admin.sms.index', compact(
            'packages',
            'tenants',
            'smsCredits',
            'totalSmsIssued',
            'totalRevenue',
            'activeTenantsWithSms'
        ));
    }

    /**
     * Store a new SMS package.
     */
    public function storePackage(Request $request)
    {
        TenantManager::resetToLandlord();

        $request->validate([
            'name' => 'required|string|max:100',
            'sms_count' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'rate_per_sms' => 'nullable|numeric|min:0',
            'badge' => 'nullable|string|max:50',
            'is_featured' => 'nullable|boolean',
            'features' => 'nullable|string',
            'order_index' => 'nullable|integer',
        ]);

        $features = $request->filled('features')
            ? array_filter(array_map('trim', explode("\n", $request->features)))
            : [];

        $rate = $request->filled('rate_per_sms') 
            ? (float)$request->rate_per_sms 
            : round((float)$request->price / (int)$request->sms_count, 2);

        PlatformSmsPackage::create([
            'name' => $request->name,
            'sms_count' => (int)$request->sms_count,
            'price' => (float)$request->price,
            'rate_per_sms' => $rate,
            'badge' => $request->badge,
            'is_featured' => $request->boolean('is_featured'),
            'features' => $features,
            'is_active' => true,
            'order_index' => (int)($request->order_index ?? 0),
        ]);

        return back()->with('success', 'SMS Package created successfully.');
    }

    /**
     * Update an existing SMS package.
     */
    public function updatePackage(Request $request, PlatformSmsPackage $package)
    {
        TenantManager::resetToLandlord();

        $request->validate([
            'name' => 'required|string|max:100',
            'sms_count' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'rate_per_sms' => 'nullable|numeric|min:0',
            'badge' => 'nullable|string|max:50',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'features' => 'nullable|string',
            'order_index' => 'nullable|integer',
        ]);

        $features = $request->filled('features')
            ? array_filter(array_map('trim', explode("\n", $request->features)))
            : [];

        $rate = $request->filled('rate_per_sms') 
            ? (float)$request->rate_per_sms 
            : round((float)$request->price / (int)$request->sms_count, 2);

        $package->update([
            'name' => $request->name,
            'sms_count' => (int)$request->sms_count,
            'price' => (float)$request->price,
            'rate_per_sms' => $rate,
            'badge' => $request->badge,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $package->is_active,
            'features' => $features,
            'order_index' => (int)($request->order_index ?? 0),
        ]);

        return back()->with('success', 'SMS Package updated successfully.');
    }

    /**
     * Delete an SMS package.
     */
    public function destroyPackage(PlatformSmsPackage $package)
    {
        TenantManager::resetToLandlord();
        $package->delete();

        return back()->with('success', 'SMS Package removed successfully.');
    }

    /**
     * Issue Top-Up Credit to Tenant Company.
     */
    public function storeCredit(Request $request)
    {
        TenantManager::resetToLandlord();

        $request->validate([
            'tenant_id' => 'required|exists:landlord.tenants,id',
            'sms_count' => 'required|integer|min:1',
            'rate_per_sms' => 'nullable|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:100',
            'payment_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $tenant = Tenant::findOrFail($request->tenant_id);
        $rate = $request->filled('rate_per_sms') ? (float)$request->rate_per_sms : (float)$tenant->sms_rate;
        $count = (int)$request->sms_count;
        $amountPaid = $request->filled('amount_paid') ? (float)$request->amount_paid : ($count * $rate);

        TenantSmsCredit::create([
            'tenant_id' => $tenant->id,
            'sms_count' => $count,
            'rate_per_sms' => $rate,
            'amount_paid' => $amountPaid,
            'payment_method' => $request->payment_method ?? 'Bank Transfer',
            'payment_reference' => $request->payment_reference,
            'status' => 'completed',
            'notes' => $request->notes ?? 'SMS Bundle Top-Up by SaaS Admin',
        ]);

        $tenant->increment('sms_balance', $count);
        if ($request->filled('rate_per_sms')) {
            $tenant->sms_rate = $rate;
            $tenant->save();
        }

        return back()->with('success', "Successfully credited {$count} SMS to {$tenant->name}. New Balance: {$tenant->sms_balance} SMS.");
    }
}

