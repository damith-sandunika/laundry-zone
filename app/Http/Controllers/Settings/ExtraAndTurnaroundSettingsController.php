<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreExtraRequest;
use App\Http\Requests\Settings\StoreTurnaroundTypeRequest;
use App\Http\Requests\Settings\UpdateExtraRequest;
use App\Http\Requests\Settings\UpdateTurnaroundTypeRequest;
use App\Models\Extra;
use App\Models\Setting;
use App\Models\TurnaroundType;

class ExtraAndTurnaroundSettingsController extends Controller
{
    protected function authorizeConfigManagement()
    {
        $isCentralized = Setting::get('centralized_management', '1') == '1';
        $isGlobalManager = auth()->user()->canManageGlobalConfig();
        $isSuperAdmin = auth()->user()->role === 'super_admin';
        if (!(($isCentralized && $isGlobalManager) || (!$isCentralized && !$isSuperAdmin))) {
            abort(403, 'Unauthorized config management mode.');
        }
    }

    public function storeExtra(StoreExtraRequest $request)
    {
        $this->authorizeConfigManagement();
        $branchId = Setting::get('centralized_management', '1') == '1' ? null : $this->getActiveBranchId();

        Extra::create([
            'branch_id'  => $branchId,
            'name'       => $request->name,
            'price_type' => $request->price_type,
            'price'      => $request->price,
            'is_active'  => true,
        ]);
        return redirect()->back()->with('success', 'Extra added successfully.');
    }

    public function updateExtra(UpdateExtraRequest $request, Extra $extra)
    {
        $this->authorizeConfigManagement();
        $extra->update($request->only(['name', 'price_type', 'price', 'is_active']));
        return redirect()->back()->with('success', 'Extra updated successfully.');
    }

    public function destroyExtra(Extra $extra)
    {
        $this->authorizeConfigManagement();
        $extra->delete();
        return redirect()->back()->with('success', 'Extra deleted successfully.');
    }

    public function storeTurnaroundType(StoreTurnaroundTypeRequest $request)
    {
        $this->authorizeConfigManagement();
        if ($request->has('is_default')) {
            TurnaroundType::query()->update(['is_default' => false]);
        }
        $branchId = Setting::get('centralized_management', '1') == '1' ? null : $this->getActiveBranchId();

        TurnaroundType::create([
            'branch_id'    => $branchId,
            'name'         => $request->name,
            'markup_type'  => $request->markup_type,
            'markup_value' => $request->markup_value,
            'is_default'   => $request->has('is_default'),
            'is_active'    => true,
        ]);
        return redirect()->back()->with('success', 'Turnaround type added successfully.');
    }

    public function updateTurnaroundType(UpdateTurnaroundTypeRequest $request, TurnaroundType $turnaroundType)
    {
        $this->authorizeConfigManagement();
        if ($request->has('is_default') && $request->is_default) {
            TurnaroundType::where('id', '!=', $turnaroundType->id)->update(['is_default' => false]);
        }
        $turnaroundType->update([
            'name'         => $request->name,
            'markup_type'  => $request->markup_type,
            'markup_value' => $request->markup_value,
            'is_default'   => $request->has('is_default'),
            'is_active'    => $request->has('is_active'),
        ]);
        return redirect()->back()->with('success', 'Turnaround type updated successfully.');
    }

    public function destroyTurnaroundType(TurnaroundType $turnaroundType)
    {
        $this->authorizeConfigManagement();
        if (strtolower($turnaroundType->name) === 'standard') {
            return redirect()->back()->with('error', 'The Standard turnaround type cannot be deleted.');
        }
        $turnaroundType->delete();
        return redirect()->back()->with('success', 'Turnaround type deleted successfully.');
    }
}

