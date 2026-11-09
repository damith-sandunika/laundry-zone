<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    use HasFactory;

    protected $connection = 'landlord';

    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'subdomain',
        'custom_domain',
        'database_name',
        'db_host',
        'db_port',
        'db_username',
        'db_password',
        'logo_path',
        'address',
        'phone',
        'email',
        'is_delivery_enabled',
        'plan',
        'plan_price',
        'delivery_price',
        'status',
        'notes',
        'sms_gateway',
        'sms_mask',
        'sms_api_url',
        'sms_api_key',
        'sms_balance',
        'sms_rate',
        'mail_from_name',
        'mail_from_address',
        'trial_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'is_delivery_enabled' => 'boolean',
            'plan_price' => 'decimal:2',
            'delivery_price' => 'decimal:2',
            'trial_ends_at' => 'datetime',
        ];
    }

    /**
     * Relationship to tenant owners registered in Central DB.
     */
    public function owners(): HasMany
    {
        return $this->hasMany(TenantOwner::class, 'tenant_id');
    }

    public function latestOwner(): HasOne
    {
        return $this->hasOne(TenantOwner::class, 'tenant_id')->latestOfMany();
    }

    /**
     * Relationship to tenant subscriptions / invoices.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class, 'tenant_id');
    }

    /**
     * Relationship to tenant SMS credit packages.
     */
    public function smsCredits(): HasMany
    {
        return $this->hasMany(TenantSmsCredit::class, 'tenant_id');
    }

    /**
     * Computed full monthly price (Plan Price + Delivery Add-on fee if enabled).
     */
    public function getTotalMonthlyPriceAttribute(): float
    {
        $base = (float) ($this->plan_price ?? 15000);
        $delivery = $this->is_delivery_enabled ? (float) ($this->delivery_price ?? 5000) : 0;
        return $base + $delivery;
    }

    /**
     * Computed full URL based on subdomain or custom domain, dynamically matching current host & port.
     */
    public function getUrlAttribute(): string
    {
        return \App\Services\Tenancy\TenantManager::getTenantUrl($this);
    }

    /**
     * Direct link to Tenant Staff Login.
     */
    public function getStaffLoginUrlAttribute(): string
    {
        return \App\Services\Tenancy\TenantManager::getTenantUrl($this, '/stafflogin');
    }

    /**
     * Status badge styling helper.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'active' => [
                'label' => 'Active',
                'bg' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
                'dot' => 'bg-emerald-400',
            ],
            'suspended' => [
                'label' => 'Suspended',
                'bg' => 'bg-rose-500/10 text-rose-400 border border-rose-500/20',
                'dot' => 'bg-rose-400',
            ],
            default => [
                'label' => ucfirst($this->status ?? 'Inactive'),
                'bg' => 'bg-amber-500/10 text-amber-400 border border-amber-500/20',
                'dot' => 'bg-amber-400',
            ],
        };
    }
}
