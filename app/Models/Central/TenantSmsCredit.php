<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSmsCredit extends Model
{
    use HasFactory;

    protected $connection = 'landlord';

    protected $table = 'tenant_sms_credits';

    protected $fillable = [
        'tenant_id',
        'sms_count',
        'rate_per_sms',
        'amount_paid',
        'payment_reference',
        'payment_method',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sms_count' => 'integer',
            'rate_per_sms' => 'decimal:2',
            'amount_paid' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}

