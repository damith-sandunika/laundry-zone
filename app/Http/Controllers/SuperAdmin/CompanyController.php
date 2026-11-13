<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Actions\SuperAdmin\DestroyCompanyAction;
use App\Actions\SuperAdmin\UpdateCompanyAction;
use App\Http\Controllers\Controller;
use App\Models\Central\PlatformSetting;
use App\Models\Central\Tenant;
use App\Models\Setting;
use App\Models\User;
use App\Services\Tenancy\TenantManager;
use App\Services\Tenancy\TenantProvisioner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    protected TenantProvisioner $provisioner;

    public function __construct(TenantProvisioner $provisioner)
    {
        $this->provisioner = $provisioner;
    }

    public function index(Request $request)
    {
        TenantManager::resetToLandlord();
        $query = Tenant::with(['latestOwner', 'subscriptions']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(fn($q) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('subdomain', 'like', "%{$s}%")
                ->orWhere('custom_domain', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%"));
        }
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('delivery')) $query->where('is_delivery_enabled', $request->delivery === '1');

        $companies = $query->latest()->paginate(15)->withQueryString();
        $defaultBasePrice = PlatformSetting::get('default_base_price', '15000.00');
        $defaultDeliveryPrice = PlatformSetting::get('default_delivery_price', '5000.00');
        $centralDomain = TenantManager::getCentralBaseDomain();

        return view('super_admin.companies.index', compact('companies', 'defaultBasePrice', 'defaultDeliveryPrice', 'centralDomain'));
    }

    public function create()
    {
        TenantManager::resetToLandlord();
        $defaultBasePrice = PlatformSetting::get('default_base_price', '15000.00');
        $defaultDeliveryPrice = PlatformSetting::get('default_delivery_price', '5000.00');
        $centralDomain = TenantManager::getCentralBaseDomain();
        $dbPrefix = PlatformSetting::get('db_prefix', env('TENANT_DB_PREFIX', 'lz_tenant_'));

        return view('super_admin.companies.create', compact('defaultBasePrice', 'defaultDeliveryPrice', 'centralDomain', 'dbPrefix'));
    }

    public function store(Request $request)
    {
        TenantManager::resetToLandlord();
        $rules = [
            'name' => 'required|string|max:255',
            'subdomain' => 'required|alpha_dash|max:64|unique:landlord.tenants,subdomain',
            'custom_domain' => 'nullable|string|max:255|unique:landlord.tenants,custom_domain',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:4096',
            'plan' => 'required|string|in:Basic,Standard,Premium,Enterprise',
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|max:255',
            'owner_password' => 'required|string|min:6',
        ];

        if ($request->expectsJson() || $request->ajax()) {
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }
            try {
                @set_time_limit(300);
                $tenant = $this->provisioner->provision($request->all(), $request->file('logo'));
                $owner = $tenant->latestOwner;
                return response()->json([
                    'success' => true,
                    'message' => "Company '{$tenant->name}' has been created and provisioned successfully!",
                    'tenant' => ['id' => $tenant->id, 'name' => $tenant->name, 'subdomain' => $tenant->subdomain],
                    'redirect_url' => route('super_admin.companies.index'),
                ]);
            } catch (\Throwable $e) {
                report($e);
                return response()->json(['success' => false, 'message' => 'Provisioning failed: ' . $e->getMessage()], 500);
            }
        }

        $request->validate($rules);
        try {
            $tenant = $this->provisioner->provision($request->all(), $request->file('logo'));
            return redirect()->route('super_admin.companies.index')
                ->with('success', "Company '{$tenant->name}' created successfully!");
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->withErrors(['error' => 'Provisioning failed: ' . $e->getMessage()]);
        }
    }

    public function show(Tenant $company)
    {
        TenantManager::resetToLandlord();
        $company->load(['owners', 'subscriptions']);
        $dbStats = $this->provisioner->testConnection($company);
        $centralDomain = TenantManager::getCentralBaseDomain();
        return view('super_admin.companies.show', compact('company', 'dbStats', 'centralDomain'));
    }

    public function edit(Tenant $company)
    {
        TenantManager::resetToLandlord();
        $company->load('latestOwner');
        $centralDomain = TenantManager::getCentralBaseDomain();
        return view('super_admin.companies.edit', compact('company', 'centralDomain'));
    }

    public function update(Request $request, Tenant $company, UpdateCompanyAction $action)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'custom_domain' => 'nullable|string|max:255|unique:landlord.tenants,custom_domain,' . $company->id,
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:4096',
            'plan' => 'required|string|in:Basic,Standard,Premium,Enterprise',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $action->execute($company, $request->all(), $request->file('logo'));

        return redirect()->route('super_admin.companies.index')->with('success', "Company '{$company->name}' updated successfully.");
    }

    public function toggleDelivery(Tenant $company)
    {
        TenantManager::resetToLandlord();
        $newState = !$company->is_delivery_enabled;
        $company->update(['is_delivery_enabled' => $newState]);

        try {
            TenantManager::setTenant($company);
            Setting::set('delivery_enabled', $newState ? '1' : '0');
            TenantManager::resetToLandlord();
        } catch (\Throwable $e) {
            TenantManager::resetToLandlord();
        }

        return back()->with('success', "Delivery Module updated for {$company->name}.");
    }

    public function impersonate(Tenant $company)
    {
        TenantManager::resetToLandlord();
        $owner = $company->latestOwner;
        if (!$owner) return back()->withErrors(['error' => 'No owner registered for this company.']);

        TenantManager::setTenant($company);
        $user = User::where('email', $owner->email)->first() ?? User::whereIn('role', ['owner', 'super_admin'])->first();
        if (!$user) {
            TenantManager::resetToLandlord();
            return back()->withErrors(['error' => 'Owner user record not found in tenant database.']);
        }

        Auth::guard('web')->login($user);
        session(['active_tenant_subdomain' => $company->subdomain]);
        return redirect()->route('dashboard')->with('success', "Logged in as Company Owner ({$user->name}).");
    }

    public function toggleSuspend(Tenant $company)
    {
        TenantManager::resetToLandlord();
        $newStatus = ($company->status === 'suspended') ? 'active' : 'suspended';
        $company->update(['status' => $newStatus]);

        Cache::forget("tenant_resolution:{$company->custom_domain}");
        Cache::forget("tenant_resolution:{$company->subdomain}.localhost");
        return back()->with('success', "Company '{$company->name}' status changed to {$newStatus}.");
    }

    public function destroy(Tenant $company, DestroyCompanyAction $action)
    {
        $res = $action->execute($company);
        return redirect()->route('super_admin.companies.index')
            ->with('success', "Company '{$res['name']}' and its database/records have been completely removed.");
    }
}
