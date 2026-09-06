<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchMonthlyPlan extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'plan_data' => 'array',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
