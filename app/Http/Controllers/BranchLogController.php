<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BranchLogController extends Controller
{
    public function index(Request $request)
    {
        // Only super_admin should access this (enforced via middleware in routes)
        
        $query = \App\Models\BranchLog::with(['branch', 'user'])->latest();

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->paginate(30)->withQueryString();
        $branches = \App\Models\Branch::all();

        return view('superadmin.branch-logs', compact('logs', 'branches'));
    }
}
