<?php

namespace App\Services\Tenancy;

use App\Models\Central\Tenant;
use App\Models\Central\PlatformSetting;

class TenantUrlService
{
    public static function getCentralBaseDomain(): string
    {
        $configuredDomain = env('CENTRAL_DOMAIN') ?: PlatformSetting::get('central_domain');
        if (!empty($configuredDomain)) {
            return trim($configuredDomain);
        }

        if (app()->bound('request') && request()->hasHeader('host')) {
            $host = request()->getHost();

            if ($host === 'localhost' || $host === '127.0.0.1' || str_ends_with($host, '.localhost')) {
                return 'localhost';
            }

            if (str_ends_with($host, '.test')) {
                $parts = explode('.', $host);
                if (count($parts) >= 2) {
                    return implode('.', array_slice($parts, -2));
                }
                return 'laundryzone.test';
            }

            return $host;
        }

        return 'localhost';
    }

    public static function getTenantUrl(Tenant $tenant, string $path = ''): string
    {
        $hasRequest = app()->bound('request') && request()->hasHeader('host');
        $scheme = ($hasRequest && request()->isSecure()) ? 'https://' : 'http://';
        $port = $hasRequest ? request()->getPort() : null;
        $portSuffix = ($port && !in_array($port, [80, 443])) ? ":{$port}" : '';

        if (!empty($tenant->custom_domain)) {
            $url = $scheme . $tenant->custom_domain . $portSuffix;
        } else {
            $baseDomain = static::getCentralBaseDomain();
            $url = $scheme . $tenant->subdomain . '.' . $baseDomain . $portSuffix;
        }

        if ($path) {
            $url = rtrim($url, '/') . '/' . ltrim($path, '/');
        }

        return $url;
    }
}

