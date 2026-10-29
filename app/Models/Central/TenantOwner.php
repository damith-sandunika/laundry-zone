<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantOwner extends Model
{
    use HasFactory;

    protected $connection = 'landlord';

    protected $table = 'tenant_owners';

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'initial_password',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
