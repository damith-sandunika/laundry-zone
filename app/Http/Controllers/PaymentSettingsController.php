<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\PaymentSetting;
use Illuminate\Http\Request;

class PaymentSettingsController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Only Super Admin can manage payment methods.');

        $branches = Branch::regularBranches()->get();
        $selectedBranchId = $request->branch_id ?? ($branches->first()->id ?? 1);

        $settings = PaymentSetting::where('branch_id', $selectedBranchId)->get()->keyBy('method');
        $allMethods = PaymentSetting::allMethods();

        return view('settings.payment-methods', compact('settings', 'allMethods', 'branches', 'selectedBranchId'));
    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Only Super Admin can manage payment methods.');

        $branchId = $request->branch_id;

        $request->validate([
            'methods' => 'required|array',
            'methods.*.is_enabled' => 'nullable|boolean',
            'methods.*.display_name' => 'nullable|string|max:255',
            'methods.*.instructions' => 'nullable|string',
        ]);

        foreach ($request->methods as $method => $data) {
            PaymentSetting::updateOrCreate(
                ['branch_id' => $branchId, 'method' => $method],
                [
                    'is_enabled' => isset($data['is_enabled']) ? true : false,
                    'display_name' => $data['display_name'] ?? PaymentSetting::allMethods()[$method],
                    'instructions' => $data['instructions'] ?? null,
                ]
            );
        }

        return redirect()->back()->with('success', 'Payment methods updated successfully.');
    }
}
