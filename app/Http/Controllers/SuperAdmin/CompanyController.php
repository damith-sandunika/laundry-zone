<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Central\PlatformSetting;
use App\Models\Central\Tenant;
use App\Models\Central\TenantOwner;
use App\Models\Setting;
use App\Models\User;
use App\Services\Tenancy\TenantManager;
use App\Services\Tenancy\TenantProvisioner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    protected TenantProvisioner $provisioner;

    public function __construct(TenantProvisioner $provisioner)
    {
        $this->provisioner = $provisioner;
    }

    /**
     * Display listing of companies (Tenants).
     */
    public function index(Request $request)
    {
        TenantManager::resetToLandlord();

        $query = Tenant::with(['latestOwner', 'subscriptions']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('subdomain', 'like', "%{$s}%")
                    ->orWhere('custom_domain', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('delivery')) {
            $query->where('is_delivery_enabled', $request->delivery === '1');
        }

        $companies = $query->latest()->paginate(15)->withQueryString();

        $defaultBasePrice = PlatformSetting::get('default_base_price', '15000.00');
        $defaultDeliveryPrice = PlatformSetting::get('default_delivery_price', '5000.00');
        $centralDomain = TenantManager::getCentralBaseDomain();

        return view('super_admin.companies.index', compact('companies', 'defaultBasePrice', 'defaultDeliveryPrice', 'centralDomain'));
    }

    /**
     * Show form for creating a new company.
     */
    public function create()
    {
        TenantManager::resetToLandlord();

        $defaultBasePrice = PlatformSetting::get('default_base_price', '15000.00');
        $defaultDeliveryPrice = PlatformSetting::get('default_delivery_price', '5000.00');
        $centralDomain = TenantManager::getCentralBaseDomain();
        $dbPrefix = PlatformSetting::get('db_prefix', env('TENANT_DB_PREFIX', 'lz_tenant_'));

        return view('super_admin.companies.create', compact('defaultBasePrice', 'defaultDeliveryPrice', 'centralDomain', 'dbPrefix'));
    }

    /**
     * Store a new company & auto-provision dedicated database.
     */
    public function store(Request $request)
    {
        TenantManager::resetToLandlord();

        $rules = [
            'name' => 'required|string|max:255',
            'subdomain' => 'required|alpha_dash|max:64|unique:landlord.tenants,subdomain',
            'custom_domain' => 'nullable|string|max:255|unique:landlord.tenants,custom_domain',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:4096',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_delivery_enabled' => 'nullable|boolean',
            'plan' => 'required|string|in:Basic,Standard,Premium,Enterprise',
            'plan_price' => 'nullable|numeric|min:0',
            'delivery_price' => 'nullable|numeric|min:0',
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|max:255',
            'owner_phone' => 'nullable|string|max:50',
            'owner_password' => 'required|string|min:6',
        ];

        // 1. Interactive AJAX / JSON Flow (Progressive Provisioning)
        if ($request->expectsJson() || $request->ajax()) {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            try {
                $tenant = $this->provisioner->provision($request->all(), $request->file('logo'));
                $owner = $tenant->latestOwner;

                return response()->json([
                    'success' => true,
                    'message' => "Company '{$tenant->name}' has been created and provisioned successfully!",
                    'tenant' => [
                        'id' => $tenant->id,
                        'name' => $tenant->name,
                        'subdomain' => $tenant->subdomain,
                        'database_name' => $tenant->database_name,
                        'url' => $tenant->url,
                        'staff_login_url' => $tenant->staff_login_url,
                        'owner_name' => $owner?->name ?? $request->owner_name,
                        'owner_email' => $owner?->email ?? $request->owner_email,
                        'owner_password' => $owner?->initial_password ?? $request->owner_password,
                    ],
                    'redirect_url' => route('super_admin.companies.index'),
                ]);
            } catch (\Throwable $e) {
                report($e);
                return response()->json([
                    'success' => false,
                    'message' => 'Provisioning failed: ' . $e->getMessage(),
                ], 500);
            }
        }

        // 2. Standard Form Submit Fallback
        $request->validate($rules);

        try {
            $tenant = $this->provisioner->provision($request->all(), $request->file('logo'));

            return redirect()->route('super_admin.companies.index')
                ->with('success', "Company '{$tenant->name}' and database '{$tenant->database_name}' have been created and provisioned successfully!");
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->withErrors(['error' => 'Provisioning failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Display company details.
     */
    public function show(Tenant $company)
    {
        TenantManager::resetToLandlord();

        $company->load(['owners', 'subscriptions']);
        $dbStats = $this->provisioner->testConnection($company);
        $centralDomain = TenantManager::getCentralBaseDomain();

        return view('super_admin.companies.show', compact('company', 'dbStats', 'centralDomain'));
    }

    /**
     * Show form for editing company.
     */
    public function edit(Tenant $company)
    {
        TenantManager::resetToLandlord();

        $company->load('latestOwner');
        $centralDomain = TenantManager::getCentralBaseDomain();

        return view('super_admin.companies.edit', compact('company', 'centralDomain'));
    }

    /**
     * Update company details.
     */
    public function update(Request $request, Tenant $company)
    {
        TenantManager::resetToLandlord();

        $request->validate([
            'name' => 'required|string|max:255',
            'custom_domain' => 'nullable|string|max:255|unique:landlord.tenants,custom_domain,' . $company->id,
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:4096',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_delivery_enabled' => 'nullable|boolean',
            'plan' => 'required|string|in:Basic,Standard,Premium,Enterprise',
            'plan_price' => 'nullable|numeric|min:0',
            'delivery_price' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,suspended',
            'notes' => 'nullable|string',
        ]);

        $data = $request->only([
            'name', 'custom_domain', 'address', 'phone', 'email',
            'plan', 'plan_price', 'delivery_price', 'status', 'notes'
        ]);

        $data['is_delivery_enabled'] = $request->boolean('is_delivery_enabled');

        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $logo = $request->file('logo');
            $filename = 'tenant_' . $company->subdomain . '_' . time() . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('uploads/tenants'), $filename);
            $data['logo_path'] = 'uploads/tenants/' . $filename;
        }

        $company->update($data);

        // Sync settings into the tenant database
        try {
            TenantManager::setTenant($company);
            Setting::set('company_name', $company->name);
            Setting::set('delivery_enabled', $company->is_delivery_enabled ? '1' : '0');
            if (!empty($data['logo_path'])) {
                Setting::set('company_logo', $data['logo_path']);
            }
            TenantManager::resetToLandlord();
        } catch (\Throwable $e) {
            TenantManager::resetToLandlord();
        }

        return redirect()->route('super_admin.companies.index')
            ->with('success', "Company '{$company->name}' updated successfully.");
    }

    /**
     * Toggle Delivery Module for a company.
     */
    public function toggleDelivery(Tenant $company)
    {
        TenantManager::resetToLandlord();

        $newState = !$company->is_delivery_enabled;
        $company->is_delivery_enabled = $newState;
        $company->save();

        // Update setting in tenant database
        try {
            TenantManager::setTenant($company);
            Setting::set('delivery_enabled', $newState ? '1' : '0');
            TenantManager::resetToLandlord();
        } catch (\Throwable $e) {
            TenantManager::resetToLandlord();
        }

        $statusText = $newState ? 'enabled' : 'disabled';
        return back()->with('success', "Delivery Module has been {$statusText} for {$company->name}. Pricing adjusted accordingly.");
    }

    /**
     * Impersonate / Direct login as Company Owner.
     */
    public function impersonate(Tenant $company)
    {
        TenantManager::resetToLandlord();

        $owner = $company->latestOwner;
        if (!$owner) {
            return back()->withErrors(['error' => 'No owner registered for this company.']);
        }

        // Switch to tenant DB and login as owner
        TenantManager::setTenant($company);
        $user = User::where('email', $owner->email)->first() ?? User::whereIn('role', ['owner', 'super_admin'])->first();

        if (!$user) {
            TenantManager::resetToLandlord();
            return back()->withErrors(['error' => 'Owner user record not found in tenant database.']);
        }

        Auth::guard('web')->login($user);
        session(['active_tenant_subdomain' => $company->subdomain]);

        // Redirect to tenant dashboard
        return redirect()->route('dashboard')->with('success', "Logged in as Company Owner ({$user->name}) for {$company->name}.");
    }

    /**
     * Delete / Suspend Company.
     */
    public function destroy(Tenant $company)
    {
        TenantManager::resetToLandlord();

        $name = $company->name;
        $company->delete();

        return redirect()->route('super_admin.companies.index')
            ->with('success', "Company '{$name}' has been removed from active platform tenants.");
    }
}
