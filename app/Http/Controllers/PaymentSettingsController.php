<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\Setting;
use Illuminate\Http\Request;

class PaymentSettingsController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Only Super Admin can manage payment methods.');

        $methods = PaymentMethod::all();
        
        $bankDetails = [
            'bank_name' => Setting::get('bank_name', ''),
            'bank_account_name' => Setting::get('bank_account_name', ''),
            'bank_account_number' => Setting::get('bank_account_number', ''),
            'bank_branch' => Setting::get('bank_branch', ''),
        ];

        return view('settings.payment-methods', compact('methods', 'bankDetails'));
    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Only Super Admin can manage payment methods.');

        $request->validate([
            'is_active' => 'array',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'bank_branch' => 'nullable|string|max:255',
        ]);

        // Update active statuses
        $activeIds = $request->input('is_active', []);
        PaymentMethod::query()->update(['is_active' => false]);
        if (!empty($activeIds)) {
            PaymentMethod::whereIn('id', array_keys($activeIds))->update(['is_active' => true]);
        }

        // Save bank details
        Setting::set('bank_name', $request->input('bank_name'));
        Setting::set('bank_account_name', $request->input('bank_account_name'));
        Setting::set('bank_account_number', $request->input('bank_account_number'));
        Setting::set('bank_branch', $request->input('bank_branch'));

        return redirect()->route('settings.payment-methods.index')->with('success', 'Payment methods updated successfully.');
    }
}
