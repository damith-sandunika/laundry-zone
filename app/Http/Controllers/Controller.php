<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Get the active branch ID for the current context.
     * Falls back to the first branch if user has no specific branch.
     * 
     * @return int
     */
    protected function getActiveBranchId()
    {
        return auth()->user()->branch_id ?? \App\Models\Branch::first()->id;
    }
}

