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
        $branchId = auth()->user()->branch_id;
        if ($branchId && \App\Models\Branch::where('id', $branchId)->exists()) {
            return $branchId;
        }
        return \App\Models\Branch::first()->id ?? null;
    }
}

