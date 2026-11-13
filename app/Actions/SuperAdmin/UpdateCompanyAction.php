<?php

namespace App\Actions\SuperAdmin;

use App\Models\Central\Tenant;
use App\Models\Setting;
use App\Services\Tenancy\TenantManager;
use Illuminate\Support\Facades\Cache;

class UpdateCompanyAction
{
    public function execute(Tenant $company, array $data, $logoFile = null): Tenant
    {
        TenantManager::resetToLandlord();

        $updatePayload = [
            'name' => $data['name'],
            'custom_domain' => $data['custom_domain'] ?? null,
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'plan' => $data['plan'],
            'plan_price' => $data['plan_price'] ?? null,
            'delivery_price' => $data['delivery_price'] ?? null,
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
            'sms_gateway' => $data['sms_gateway'] ?? null,
            'sms_mask' => $data['sms_mask'] ?? null,
            'sms_api_url' => $data['sms_api_url'] ?? null,
            'sms_api_key' => $data['sms_api_key'] ?? null,
            'mail_from_name' => $data['mail_from_name'] ?? null,
            'mail_from_address' => $data['mail_from_address'] ?? null,
            'is_delivery_enabled' => !empty($data['is_delivery_enabled']),
        ];

        if ($logoFile && $logoFile->isValid()) {
            $filename = 'tenant_' . $company->subdomain . '_' . time() . '.' . $logoFile->getClientOriginalExtension();
            $logoFile->move(public_path('uploads/tenants'), $filename);
            $updatePayload['logo_path'] = 'uploads/tenants/' . $filename;
        }

        $company->update($updatePayload);

        // Sync settings into the tenant database
        try {
            TenantManager::setTenant($company);
            Setting::set('company_name', $company->name);
            Setting::set('delivery_enabled', $company->is_delivery_enabled ? '1' : '0');

            if (!empty($company->sms_mask)) Setting::set('sms_mask', $company->sms_mask);
            if (!empty($company->sms_gateway)) Setting::set('sms_gateway', $company->sms_gateway);
            if (!empty($company->sms_api_url)) Setting::set('sms_api_url', $company->sms_api_url);
            if (!empty($company->sms_api_key)) {
                Setting::set('sms_api_key', $company->sms_api_key);
                Setting::set('is_sms_enabled', 'true');
            }
            if (!empty($company->mail_from_name)) Setting::set('mail_from_name', $company->mail_from_name);
            if (!empty($company->mail_from_address)) {
                Setting::set('mail_from_address', $company->mail_from_address);
                Setting::set('is_email_enabled', 'true');
            }
            if (!empty($updatePayload['logo_path'])) {
                Setting::set('company_logo', $updatePayload['logo_path']);
            }
            Cache::forget('global_comm_settings');
            TenantManager::resetToLandlord();
        } catch (\Throwable $e) {
            TenantManager::resetToLandlord();
        }

        return $company;
    }
}

