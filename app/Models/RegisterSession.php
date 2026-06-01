<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegisterSession extends Model
{
    protected $fillable = [
        'branch_id', 'user_id', 'opening_cash', 'closing_cash', 'opened_at', 'closed_at', 'status', 'closed_by'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
