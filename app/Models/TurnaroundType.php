<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TurnaroundType extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'markup_type',
        'markup_value',
        'is_default',
        'is_active',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}

