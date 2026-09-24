<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateGeneralSettingsRequest;
use App\Models\Branch;
use App\Models\Setting;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\Cubby;
use App\Models\InventoryItem;
use App\Models\Package;

class GeneralSettingsController extends Controller
{
    /**
     * The main settings index page (only shows general settings to non-super-admins).
     * Kept here because it loads data for ALL tabs.
     */
    public function index()
    {
        $branchId = $this->getActiveBranchId();
        $branch = Branch::find($branchId);
        $isGlobalMode = Setting::get('centralized_management', '1') == '1' && auth()->user()->canManageGlobalConfig();

        $serviceCategories = ServiceCategory::where(function ($q) use ($branchId, $isGlobalMode) {
            if (!$isGlobalMode) $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
        })->with(['children.services' => function ($q) use ($branchId, $isGlobalMode) {
            if (!$isGlobalMode) $q->where(fn($sq) => $sq->whereNull('branch_id')->orWhere('branch_id', $branchId));
            $q->orderBy('order_index');
        }, 'services' => function ($q) use ($branchId, $isGlobalMode) {
            if (!$isGlobalMode) $q->where(fn($sq) => $sq->whereNull('branch_id')->orWhere('branch_id', $branchId));
            $q->orderBy('order_index');
        }])->orderBy('order_index')->get();

        $mainCategories = $serviceCategories->whereNull('parent_id');

        $services = ServiceType::where(function ($q) use ($branchId, $isGlobalMode) {
            if (!$isGlobalMode) $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
        })->with(['inventoryItems', 'category'])->orderBy('order_index')->get();

        $turnaroundTypes = \App\Models\TurnaroundType::where(function ($q) use ($branchId, $isGlobalMode) {
            if (!$isGlobalMode) $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
        })->orderBy('is_default', 'desc')->orderBy('name')->get();

        $extras = \App\Models\Extra::where(function ($q) use ($branchId, $isGlobalMode) {
            if (!$isGlobalMode) $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
        })->orderBy('name')->get();

        $cubbies = Cubby::where('branch_id', $branchId)->get();
        $inventoryItems = InventoryItem::where('branch_id', $branchId)->get();

        $packages = Package::where(function ($q) use ($branchId, $isGlobalMode) {
            if (!$isGlobalMode) $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
        })->with('services.serviceType')->get();

        $deliveryRanges = \App\Models\DeliveryRange::where(function ($q) use ($branchId, $isGlobalMode) {
            if (!$isGlobalMode) $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
        })->get();

        $paymentMethods = collect();
        if (Setting::get('payment_bank_transfer_active', '1') == '1') {
            $paymentMethods->push((object)['id' => 'bank_transfer', 'name' => 'Bank Transfer', 'type' => 'bank_transfer']);
        }
        if (Setting::get('payment_online_active', '0') == '1') {
            $paymentMethods->push((object)['id' => 'online_payment', 'name' => 'Online Payment', 'type' => 'card_transfer']);
        }

        $pickupTimePeriods = \App\Models\PickupTimePeriod::where(function ($q) use ($branchId, $isGlobalMode) {
            if (!$isGlobalMode) $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
        })->orderBy('start_time')->get();

        $workshops = Branch::workshops()->get();
        $minimumDeliveryCost = Setting::get('minimum_delivery_cost', 0);

        $paymentMethodsList = [
            (object)['id' => 'bank_transfer', 'name' => 'Bank Transfer', 'type' => 'bank_transfer', 'is_active' => Setting::get('payment_bank_transfer_active', '1') == '1'],
            (object)['id' => 'online_payment', 'name' => 'Online Payment', 'type' => 'card_transfer', 'is_active' => Setting::get('payment_online_active', '0') == '1'],
        ];

        $bankDetails = [
            'bank_name'           => Setting::get('bank_name', ''),
            'bank_account_name'   => Setting::get('bank_account_name', ''),
            'bank_account_number' => Setting::get('bank_account_number', ''),
            'bank_branch'         => Setting::get('bank_branch', ''),
        ];

        $gatewayDetails = [
            'gateway_merchant_id' => Setting::get('gateway_merchant_id', ''),
            'gateway_secret_key'  => Setting::get('gateway_secret_key', ''),
        ];

        return view('settings.index', compact(
            'branch', 'serviceCategories', 'mainCategories', 'services', 'cubbies',
            'inventoryItems', 'packages', 'paymentMethods', 'deliveryRanges',
            'pickupTimePeriods', 'workshops', 'minimumDeliveryCost',
            'paymentMethodsList', 'bankDetails', 'gatewayDetails',
            'turnaroundTypes', 'extras'
        ));
    }

    public function updateGeneral(UpdateGeneralSettingsRequest $request)
    {
        $user = auth()->user();

        if ($user->role === 'super_admin') {
            Setting::set('centralized_management', $request->has('centralized_management') ? '1' : '0');
            Setting::set('auto_print_label', $request->has('auto_print_label') ? '1' : '0');
            Setting::set('auto_print_pos_chit', $request->has('auto_print_pos_chit') ? '1' : '0');
            Setting::set('site_landing_page', $request->input('site_landing_page', ''));
            if ($request->has('company_name')) Setting::set('company_name', $request->company_name);
            if ($request->has('auto_close_time')) Branch::query()->update(['auto_close_time' => $request->auto_close_time]);
        } else {
            $branchId = $this->getActiveBranchId();
            $branch = Branch::find($branchId);
            $data = $request->only(['name', 'address', 'latitude', 'longitude', 'phone', 'whatsapp_number', 'tagline', 'logo_size']);
            if ($request->hasFile('logo')) {
                $data['logo_path'] = app(\App\Actions\UploadImageAction::class)->execute($request->file('logo'), 'logos', 'logo');
            }
            if ($branch) $branch->update($data);
        }

        return redirect()->back()->with('success', 'General settings saved.');
    }

    public function updateNotifications(\Illuminate\Http\Request $request)
    {
        if (!\Illuminate\Support\Facades\Gate::allows('is-super-admin')) abort(403);

        Setting::set('is_sms_enabled', $request->has('is_sms_enabled') ? 'true' : 'false');
        Setting::set('is_whatsapp_enabled', $request->has('is_whatsapp_enabled') ? 'true' : 'false');
        Setting::set('is_email_enabled', $request->has('is_email_enabled') ? 'true' : 'false');

        // Clear cached comm settings so changes take effect immediately
        \Illuminate\Support\Facades\Cache::forget('global_comm_settings');

        return redirect()->back()->with('success', 'Notification settings saved successfully.');
    }

    public function whatsappStatus()
    {
        $branchId = $this->getActiveBranchId();
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get("http://127.0.0.1:3005/api/whatsapp/status/{$branchId}");
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['connected' => false, 'qr' => null, 'error' => 'Service offline']);
        }
    }

    public function whatsappLogout()
    {
        $branchId = $this->getActiveBranchId();
        try {
            $response = \Illuminate\Support\Facades\Http::post("http://127.0.0.1:3005/api/whatsapp/logout/{$branchId}");
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Service offline']);
        }
    }

    public function whatsappReset()
    {
        $branchId = $this->getActiveBranchId();
        try {
            $response = \Illuminate\Support\Facades\Http::post("http://127.0.0.1:3005/api/whatsapp/start/{$branchId}");
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Service offline']);
        }
    }
}
