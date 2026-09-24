<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StorePackageRequest;
use App\Http\Requests\Settings\UpdatePackageRequest;
use App\Models\Package;
use App\Models\PackageService;
use App\Models\Setting;

class PackageSettingsController extends Controller
{
    protected function authorizeConfigManagement()
    {
        $isCentralized = Setting::get('centralized_management', '1') == '1';
        $isGlobalManager = auth()->user()->canManageGlobalConfig();
        $isSuperAdmin = auth()->user()->role === 'super_admin';
        $canManage = ($isCentralized && $isGlobalManager) || (!$isCentralized && !$isSuperAdmin);
        if (!$canManage) abort(403, 'Unauthorized config management mode.');
    }

    public function storePackage(StorePackageRequest $request)
    {
        $this->authorizeConfigManagement();
        $branchId = Setting::get('centralized_management', '1') == '1' ? null : $this->getActiveBranchId();

        $package = Package::create([
            'branch_id'    => $branchId,
            'name'         => $request->name,
            'price'        => $request->price,
            'payment_type' => $request->payment_type,
            'is_active'    => true,
        ]);

        foreach ($request->services as $serviceId => $limit) {
            if ($limit > 0) {
                PackageService::create([
                    'package_id'      => $package->id,
                    'service_type_id' => $serviceId,
                    'quantity_limit'  => $limit,
                ]);
            }
        }
        return redirect()->back()->with('success', 'Package created successfully.');
    }

    public function updatePackage(UpdatePackageRequest $request, Package $package)
    {
        $this->authorizeConfigManagement();
        $package->update([
            'name'         => $request->name,
            'price'        => $request->price,
            'payment_type' => $request->payment_type,
        ]);

        PackageService::where('package_id', $package->id)->delete();
        foreach ($request->services as $serviceId => $limit) {
            if ($limit > 0) {
                PackageService::create([
                    'package_id'      => $package->id,
                    'service_type_id' => $serviceId,
                    'quantity_limit'  => $limit,
                ]);
            }
        }
        return redirect()->back()->with('success', 'Package updated successfully.');
    }

    public function destroyPackage(Package $package)
    {
        $this->authorizeConfigManagement();
        $package->delete();
        return redirect()->back()->with('success', 'Package deleted successfully.');
    }
}
