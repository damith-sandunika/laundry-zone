<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Central\PlatformSetting;
use App\Services\Tenancy\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SettingController extends Controller
{
    /**
     * Display SaaS Platform settings.
     */
    public function index()
    {
        TenantManager::resetToLandlord();

        $settings = [
            'platform_name' => PlatformSetting::get('platform_name', 'Laundry Zone SaaS Platform'),
            'central_domain' => PlatformSetting::get('central_domain', env('CENTRAL_DOMAIN', 'laundryzone.dsrzone.com')),
            'contact_email' => PlatformSetting::get('contact_email', 'support@laundryzone.dsrzone.com'),
            'contact_phone' => PlatformSetting::get('contact_phone', '+94 11 234 5678'),
            'currency' => PlatformSetting::get('currency', 'LKR'),
            'currency_symbol' => PlatformSetting::get('currency_symbol', 'Rs.'),
            'default_base_price' => PlatformSetting::get('default_base_price', '15000.00'),
            'default_delivery_price' => PlatformSetting::get('default_delivery_price', '5000.00'),
            'db_prefix' => PlatformSetting::get('db_prefix', 'lz_tenant_'),
            'db_default_host' => PlatformSetting::get('db_default_host', '127.0.0.1'),
            'db_default_port' => PlatformSetting::get('db_default_port', '3306'),
            'db_default_username' => PlatformSetting::get('db_default_username', 'root'),
            'server_ip' => PlatformSetting::get('server_ip', '127.0.0.1'),
        ];

        $nginxConfig = $this->generateNginxConfigString($settings['central_domain'], $settings['server_ip']);

        return view('super_admin.settings.index', compact('settings', 'nginxConfig'));
    }

    /**
     * Update Platform settings.
     */
    public function update(Request $request)
    {
        TenantManager::resetToLandlord();

        $fields = [
            'platform_name', 'central_domain', 'contact_email', 'contact_phone',
            'currency', 'currency_symbol', 'default_base_price', 'default_delivery_price',
            'db_prefix', 'db_default_host', 'db_default_port', 'db_default_username',
            'server_ip'
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                PlatformSetting::set($field, $request->input($field));
            }
        }

        return back()->with('success', 'Platform settings saved successfully.');
    }

    /**
     * Download generated Nginx configuration file.
     */
    public function downloadNginx()
    {
        $domain = PlatformSetting::get('central_domain', env('CENTRAL_DOMAIN', 'laundryzone.dsrzone.com'));
        $ip = PlatformSetting::get('server_ip', '127.0.0.1');
        $content = $this->generateNginxConfigString($domain, $ip);

        return Response::make($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="laundryzone_nginx.conf"',
        ]);
    }

    /**
     * Helper to generate production Nginx configuration.
     */
    protected function generateNginxConfigString(string $domain, string $serverIp): string
    {
        return <<<NGINX
# ==============================================================================
# Nginx Configuration for Multi-Tenant Laundry SaaS Platform
# Domain: {$domain}
# Supports: Central Portal, Wildcard Subdomains (*.{$domain}), and Custom Domains
# ==============================================================================

# 1. HTTP Redirect to HTTPS (All traffic)
server {
    listen 80;
    listen [::]:80;
    server_name {$domain} *.{$domain};

    # Let's Encrypt ACME challenge path
    location /.well-known/acme-challenge/ {
        root /var/www/laundryzone/public;
    }

    location / {
        return 301 https://\$host\$request_uri;
    }
}

# 2. Central SaaS Platform & Wildcard Tenant Subdomains
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name {$domain} *.{$domain};

    root /var/www/laundryzone/public;
    index index.php index.html;

    # SSL Certificates (Generated via Certbot wildcard)
    ssl_certificate /etc/letsencrypt/live/{$domain}/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/{$domain}/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    client_max_body_size 64M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    # PHP-FPM FastCGI
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}

# 3. Custom Tenant Domains Block (e.g. clientdomain.com)
server {
    listen 80;
    listen 443 ssl http2;
    server_name ~^(?!.*{$domain}).+$;

    root /var/www/laundryzone/public;
    index index.php;

    client_max_body_size 64M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }
}
NGINX;
    }
}
