<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id', 'category', 'description', 'amount', 'expense_date', 'receipt_image', 'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
