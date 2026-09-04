<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class PaymentSettingsController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Only Super Admin can manage payment methods.');

        $methods = [
            (object) [
                'id' => 'bank_transfer',
                'name' => 'Bank Transfer',
                'type' => 'bank_transfer',
                'is_active' => Setting::get('payment_bank_transfer_active', '1') == '1',
            ],
            (object) [
                'id' => 'online_payment',
                'name' => 'Online Payment',
                'type' => 'card_transfer',
                'is_active' => Setting::get('payment_online_active', '0') == '1',
            ],
            (object) [
                'id' => 'cash_on_delivery',
                'name' => 'Cash on Delivery',
                'type' => 'cash_on_delivery',
                'is_active' => Setting::get('payment_cod_active', '1') == '1',
            ],
        ];
        
        $bankDetails = [
            'bank_name' => Setting::get('bank_name', ''),
            'bank_account_name' => Setting::get('bank_account_name', ''),
            'bank_account_number' => Setting::get('bank_account_number', ''),
            'bank_branch' => Setting::get('bank_branch', ''),
        ];

        $gatewayDetails = [
            'gateway_merchant_id' => Setting::get('gateway_merchant_id', ''),
            'gateway_secret_key' => Setting::get('gateway_secret_key', ''),
        ];

        return view('settings.payment-methods', compact('methods', 'bankDetails', 'gatewayDetails'));
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
            'gateway_merchant_id' => 'nullable|string|max:255',
            'gateway_secret_key' => 'nullable|string|max:255',
        ]);

        // Update active statuses in Settings
        $activeIds = $request->input('is_active', []);
        
        Setting::set('payment_bank_transfer_active', isset($activeIds['bank_transfer']) ? '1' : '0');
        Setting::set('payment_online_active', isset($activeIds['online_payment']) ? '1' : '0');
        Setting::set('payment_cod_active', isset($activeIds['cash_on_delivery']) ? '1' : '0');

        // Save bank details
        Setting::set('bank_name', $request->input('bank_name'));
        Setting::set('bank_account_name', $request->input('bank_account_name'));
        Setting::set('bank_account_number', $request->input('bank_account_number'));
        Setting::set('bank_branch', $request->input('bank_branch'));

        // Save gateway details
        Setting::set('gateway_merchant_id', $request->input('gateway_merchant_id'));
        Setting::set('gateway_secret_key', $request->input('gateway_secret_key'));

        return redirect()->route('settings.index')->with('success', 'Payment methods updated successfully.');
    }
}

