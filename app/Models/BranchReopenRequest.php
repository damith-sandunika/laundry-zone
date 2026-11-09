<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;

class BranchReopenRequest extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'requested_by',
        'reason',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'request_date',
    ];

    protected $casts = [
        'request_date' => 'date',
        'reviewed_at'  => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
