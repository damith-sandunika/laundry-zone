<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSubscription extends Model
{
    use HasFactory;

    protected $connection = 'landlord';

    protected $table = 'tenant_subscriptions';

    protected $fillable = [
        'tenant_id',
        'invoice_number',
        'plan_name',
        'base_amount',
        'delivery_addon_amount',
        'total_amount',
        'currency',
        'billing_cycle',
        'billing_period_start',
        'billing_period_end',
        'status',
        'payment_method',
        'payment_reference',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'base_amount' => 'decimal:2',
            'delivery_addon_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'billing_period_start' => 'date',
            'billing_period_end' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'paid' => [
                'label' => 'Paid',
                'bg' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
                'dot' => 'bg-emerald-400',
            ],
            'pending' => [
                'label' => 'Pending',
                'bg' => 'bg-amber-500/10 text-amber-400 border border-amber-500/20',
                'dot' => 'bg-amber-400',
            ],
            'overdue' => [
                'label' => 'Overdue',
                'bg' => 'bg-rose-500/10 text-rose-400 border border-rose-500/20',
                'dot' => 'bg-rose-400',
            ],
            default => [
                'label' => ucfirst($this->status ?? 'Draft'),
                'bg' => 'bg-slate-500/10 text-slate-400 border border-slate-500/20',
                'dot' => 'bg-slate-400',
            ],
        };
    }
}
