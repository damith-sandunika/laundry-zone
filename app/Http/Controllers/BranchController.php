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
            'type' => 'required|in:branch,workshop',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'whatsapp_number' => 'nullable|string|max:50',
            'tagline' => 'nullable|string|max:255',
            'logo_size' => 'nullable|integer|min:20|max:500',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'branch_profit_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $data = $request->only(['name', 'type', 'address', 'phone', 'whatsapp_number', 'tagline', 'logo_size', 'latitude', 'longitude']);
        $data['is_active'] = $request->has('is_active');
        $data['is_delivery_enabled'] = $request->has('is_delivery_enabled');
        $data['branch_profit_percentage'] = $request->input('branch_profit_percentage', 0);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            if (env('CLOUDINARY_URL')) {
                try {
                    $cloudinary = new \Cloudinary\Cloudinary(env('CLOUDINARY_URL'));
                    $result = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                        'folder' => '365_laundry/branch_logos',
                        'format' => 'webp',
                        'quality' => 'auto'
                    ]);
                    $data['logo_path'] = $result['secure_url'];
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Cloudinary Upload Failed in Branch: ' . $e->getMessage());
                    $data['logo_path'] = $file->store('branch_logos', 'public');
                }
            } else {
                $data['logo_path'] = $file->store('branch_logos', 'public');
            }
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
            'type' => 'required|in:branch,workshop',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'whatsapp_number' => 'nullable|string|max:50',
            'tagline' => 'nullable|string|max:255',
            'logo_size' => 'nullable|integer|min:20|max:500',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'branch_profit_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $data = $request->only(['name', 'type', 'address', 'phone', 'whatsapp_number', 'tagline', 'logo_size', 'latitude', 'longitude']);

        // Ensure is_active defaults to false if not present, true if present
        $data['is_active'] = $request->has('is_active');
        $data['is_delivery_enabled'] = $request->has('is_delivery_enabled');
        $data['branch_profit_percentage'] = $request->input('branch_profit_percentage', 0);

        if ($request->hasFile('logo')) {
            // Delete old logo if exists and not from cloudinary
            if ($branch->logo_path && !str_contains($branch->logo_path, 'cloudinary')) {
                Storage::disk('public')->delete($branch->logo_path);
            }
            
            $file = $request->file('logo');
            if (env('CLOUDINARY_URL')) {
                try {
                    $cloudinary = new \Cloudinary\Cloudinary(env('CLOUDINARY_URL'));
                    $result = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                        'folder' => '365_laundry/branch_logos',
                        'format' => 'webp',
                        'quality' => 'auto'
                    ]);
                    $data['logo_path'] = $result['secure_url'];
                } catch (\Exception $e) {
                    $data['logo_path'] = $file->store('branch_logos', 'public');
                }
            } else {
                $data['logo_path'] = $file->store('branch_logos', 'public');
            }
        }

        $branch->update($data);

        return redirect()->route('branches.index')->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403);
        }

        try {
            $branch->delete();
            return redirect()->route('branches.index')->with('success', 'Branch deleted successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Cannot delete because it has linked users/orders
            return redirect()->route('branches.index')->with('error', 'Cannot delete this branch because it has existing orders, users, or data linked to it. Please disable it instead.');
        }
    }
}
