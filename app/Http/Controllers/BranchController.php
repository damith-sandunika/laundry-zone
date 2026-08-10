<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BranchController extends Controller
{
    public function index()
    {
        // Only super_admin can manage branches
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Unauthorized action.');
        }

        $branches = Branch::all();
        return view('branches.index', compact('branches'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'whatsapp_number' => 'nullable|string|max:50',
            'tagline' => 'nullable|string|max:255',
            'logo_size' => 'nullable|integer|min:20|max:500',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->only(['name', 'address', 'phone', 'whatsapp_number', 'tagline', 'logo_size']);
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('branch_logos', 'public');
            $data['logo_path'] = $path;
        }

        Branch::create($data);

        return redirect()->route('branches.index')->with('success', 'Branch created successfully.');
    }

    public function update(Request $request, Branch $branch)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'whatsapp_number' => 'nullable|string|max:50',
            'tagline' => 'nullable|string|max:255',
            'logo_size' => 'nullable|integer|min:20|max:500',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->only(['name', 'address', 'phone', 'whatsapp_number', 'tagline', 'logo_size']);
        
        // Ensure is_active defaults to false if not present, true if present
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($branch->logo_path) {
                Storage::disk('public')->delete($branch->logo_path);
            }
            $path = $request->file('logo')->store('branch_logos', 'public');
            $data['logo_path'] = $path;
        }

        $branch->update($data);

        return redirect()->route('branches.index')->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403);
        }

        // We probably shouldn't hard delete, maybe just deactivate, but let's soft delete or deactivate.
        // The migration doesn't have SoftDeletes, so we'll just set is_active = false
        $branch->update(['is_active' => false]);

        return redirect()->route('branches.index')->with('success', 'Branch deactivated successfully.');
    }
}
