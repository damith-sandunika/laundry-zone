<?php

namespace App\Http\Controllers;

use App\Models\Central\Tenant;
use App\Models\Setting;
use App\Services\Tenancy\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PwaManifestController extends Controller
{
    /**
     * Generate dynamic PWA manifest tailored to the active tenant/company.
     */
    public function manifest(Request $request): JsonResponse
    {
        $tenant = TenantManager::getTenant();

        // 1. Determine Company Name & App Name
        $defaultName = 'Laundry Zone';
        $companyName = $defaultName;
        $shortName = $defaultName;

        if ($tenant && !empty($tenant->name)) {
            $companyName = $tenant->name;
            $shortName = mb_substr($tenant->name, 0, 15);
        } else {
            try {
                $settingName = Setting::get('company_name');
                if (!empty($settingName)) {
                    $companyName = $settingName;
                    $shortName = mb_substr($settingName, 0, 15);
                }
            } catch (\Throwable $e) {
                // Landlord context or db not ready fallback
            }
        }

        // 2. Determine App Icon
        $defaultLogo = asset('images/laundry_main_logo.png');
        $iconSrc = $defaultLogo;

        if ($tenant && !empty($tenant->logo_path)) {
            $iconSrc = asset($tenant->logo_path);
        } else {
            try {
                $settingLogo = Setting::get('company_logo');
                if (!empty($settingLogo)) {
                    $iconSrc = asset($settingLogo);
                }
            } catch (\Throwable $e) {
                // Fallback to default
            }
        }

        // 3. Construct Manifest
        $manifest = [
            'name' => $companyName,
            'short_name' => $shortName,
            'description' => "{$companyName} - Smart Laundry Management System",
            'start_url' => url('/login'),
            'scope' => url('/'),
            'display' => 'standalone',
            'background_color' => '#0f172a',
            'theme_color' => '#1d4ed8',
            'orientation' => 'portrait-primary',
            'icons' => [
                [
                    'src' => $iconSrc,
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
                ],
                [
                    'src' => $iconSrc,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
                ],
            ],
        ];

        return response()->json($manifest, 200, [
            'Content-Type' => 'application/manifest+json; charset=utf-8',
            'Cache-Control' => 'no-cache, private',
        ]);
    }
}

