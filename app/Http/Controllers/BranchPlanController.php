<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BranchMonthlyPlan;
use App\Models\Branch;

class BranchPlanController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['super_admin', 'branch_admin', 'workshop_admin', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $branches = $user->isSuperAdmin() ? Branch::where('is_active', true)->get() : collect();
        $firstBranch = Branch::where('is_active', true)->first() ?? Branch::first();

        $branchId = $user->isSuperAdmin() 
            ? $request->input('branch_id', $user->branch_id) 
            : $user->branch_id;

        $selectedBranch = $branchId ? Branch::find($branchId) : null;
        if (!$selectedBranch) {
            $selectedBranch = $firstBranch;
            $branchId = $firstBranch?->id;
        }

        if (!$selectedBranch) {
            return redirect()->back()->with('error', 'No branches found in system.');
        }

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $plan = BranchMonthlyPlan::firstOrCreate(
            ['branch_id' => $selectedBranch->id, 'year' => $year, 'month' => $month],
            ['plan_data' => []]
        );

        return view('branch.month_plan', compact('plan', 'month', 'year', 'branchId', 'branches', 'selectedBranch', 'user'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['super_admin', 'branch_admin', 'workshop_admin', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'plan_data' => 'required|string',
            'default_in_time' => 'nullable|date_format:H:i',
            'default_out_time' => 'nullable|date_format:H:i',
        ]);

        $branchId = $request->branch_id;
        // Only super admins can update other branches
        if (!$user->isSuperAdmin() && $user->branch_id != $branchId) {
            abort(403, 'Unauthorized to modify this branch.');
        }

        $planData = json_decode($request->plan_data, true);
        if (!is_array($planData)) {
            $planData = [];
        }

        $plan = BranchMonthlyPlan::updateOrCreate(
            ['branch_id' => $branchId, 'year' => $request->year, 'month' => $request->month],
            [
                'plan_data' => $planData,
                'default_in_time' => $request->default_in_time,
                'default_out_time' => $request->default_out_time,
            ]
        );

        return redirect()->back()->with('success', 'Monthly plan saved successfully.');
    }
}
