<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Models\Setting;
use Illuminate\Http\Request;

class ExpenseSettingsController extends Controller
{
    public function index()
    {
        $branchId = $this->getActiveBranchId();
        $isGlobalMode = Setting::get('centralized_management', '1') == '1' && auth()->user()->canManageGlobalConfig();
        
        $categories = ExpenseCategory::where(function ($q) use ($branchId, $isGlobalMode) {
            if (!$isGlobalMode) {
                $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
            } else {
                $q->whereNull('branch_id');
            }
        })->orderBy('is_system', 'desc')->orderBy('name')->get();
        
        return view('settings.pages.expense_categories', compact('categories', 'isGlobalMode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $isGlobalMode = Setting::get('centralized_management', '1') == '1' && auth()->user()->canManageGlobalConfig();
        $branchId = $isGlobalMode ? null : $this->getActiveBranchId();

        ExpenseCategory::create([
            'name' => $request->name,
            'is_system' => false,
            'branch_id' => $branchId,
        ]);

        return redirect()->back()->with('success', 'Expense category created successfully.');
    }

    public function update(Request $request, ExpenseCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        if ($category->is_system) {
            return redirect()->back()->withErrors(['error' => 'System categories cannot be edited.']);
        }

        $isGlobalMode = Setting::get('centralized_management', '1') == '1' && auth()->user()->canManageGlobalConfig();
        $branchId = $isGlobalMode ? null : $this->getActiveBranchId();

        if ($category->branch_id !== $branchId) {
            return redirect()->back()->withErrors(['error' => 'Unauthorized action.']);
        }

        $category->update([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Expense category updated successfully.');
    }

    public function destroy(ExpenseCategory $category)
    {
        if ($category->is_system) {
            return redirect()->back()->withErrors(['error' => 'System categories cannot be deleted.']);
        }

        $isGlobalMode = Setting::get('centralized_management', '1') == '1' && auth()->user()->canManageGlobalConfig();
        $branchId = $isGlobalMode ? null : $this->getActiveBranchId();

        if ($category->branch_id !== $branchId) {
            return redirect()->back()->withErrors(['error' => 'Unauthorized action.']);
        }

        $category->delete();

        return redirect()->back()->with('success', 'Expense category deleted successfully.');
    }
}
