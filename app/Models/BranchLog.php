<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchLog extends Model
{
    protected $fillable = [
        'branch_id',
        'user_id',
        'action',
        'description',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

