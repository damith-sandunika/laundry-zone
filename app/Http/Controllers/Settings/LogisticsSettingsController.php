<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreCubbyRequest;
use App\Http\Requests\Settings\UpdateCubbyRequest;
use App\Http\Requests\Settings\StoreDeliveryRangeRequest;
use App\Http\Requests\Settings\StorePickupTimePeriodRequest;
use App\Http\Requests\Settings\UpdateDeliverySettingsRequest;
use App\Models\Branch;
use App\Models\Cubby;
use App\Models\Setting;


class LogisticsSettingsController extends Controller
{
    protected function authorizeConfigManagement()
    {
        $isCentralized = Setting::get('centralized_management', '1') == '1';
        $isGlobalManager = auth()->user()->canManageGlobalConfig();
        $isSuperAdmin = auth()->user()->role === 'super_admin';
        $canManage = ($isCentralized && $isGlobalManager) || (!$isCentralized && !$isSuperAdmin);
        if (!$canManage) abort(403, 'Unauthorized config management mode.');
    }

    // ─── Cubbies ───

    public function storeCubby(StoreCubbyRequest $request)
    {
        Cubby::create([
            'branch_id' => $this->getActiveBranchId(),
            'name'      => $request->name,
            'status'    => 'empty',
        ]);
        return redirect()->back()->with('success', 'Storage location added.');
    }

    public function updateCubby(UpdateCubbyRequest $request, Cubby $cubby)
    {
        $cubby->update(['name' => $request->name]);
        return redirect()->back()->with('success', 'Storage location updated.');
    }

    public function destroyCubby(Cubby $cubby)
    {
        $cubby->delete();
        return redirect()->back()->with('success', 'Storage location deleted.');
    }

    // ─── Delivery Settings ───

    public function updateDeliverySettings(UpdateDeliverySettingsRequest $request)
    {
        $this->authorizeConfigManagement();

        Setting::set('minimum_delivery_cost', $request->minimum_delivery_cost);

        if ($request->has('workshops')) {
            foreach ($request->workshops as $workshopId => $data) {
                $workshop = Branch::workshops()->find($workshopId);
                if ($workshop) {
                    $workshop->update(['latitude' => $data['latitude'], 'longitude' => $data['longitude']]);
                }
            }
        }

        return redirect()->back()->with('success', 'Delivery settings updated successfully.');
    }

    public function storeDeliveryRange(StoreDeliveryRangeRequest $request)
    {
        $this->authorizeConfigManagement();
        $branchId = Setting::get('centralized_management', '1') == '1' ? null : $this->getActiveBranchId();

        \App\Models\DeliveryRange::create([
            'branch_id'       => $branchId,
            'min_distance_km' => $request->min_distance_km,
            'max_distance_km' => $request->max_distance_km,
            'cost'            => $request->cost,
        ]);
        return redirect()->back()->with('success', 'Delivery Range added successfully.');
    }

    public function deleteDeliveryRange(\App\Models\DeliveryRange $deliveryRange)
    {
        $this->authorizeConfigManagement();
        $deliveryRange->delete();
        return redirect()->back()->with('success', 'Delivery Range deleted successfully.');
    }

    // ─── Pickup Time Periods ───

    public function storePickupTimePeriod(StorePickupTimePeriodRequest $request)
    {
        $this->authorizeConfigManagement();
        $branchId = Setting::get('centralized_management', '1') == '1' ? null : $this->getActiveBranchId();

        \App\Models\PickupTimePeriod::create([
            'branch_id'  => $branchId,
            'label'      => $request->label,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
            'is_active'  => true,
        ]);
        return redirect()->back()->with('success', 'Pickup time period added successfully.');
    }

    public function deletePickupTimePeriod(\App\Models\PickupTimePeriod $pickupTimePeriod)
    {
        $this->authorizeConfigManagement();
        $pickupTimePeriod->delete();
        return redirect()->back()->with('success', 'Pickup time period deleted successfully.');
    }
}
