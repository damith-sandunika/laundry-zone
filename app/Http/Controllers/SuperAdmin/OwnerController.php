<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Central\TenantOwner;
use App\Models\User;
use App\Services\Tenancy\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OwnerController extends Controller
{
    /**
     * List all company owners across tenants.
     */
    public function index(Request $request)
    {
        TenantManager::resetToLandlord();

        $query = TenantOwner::with('tenant');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhereHas('tenant', function ($tq) use ($s) {
                        $tq->where('name', 'like', "%{$s}%");
                    });
            });
        }

        $owners = $query->latest()->paginate(15)->withQueryString();

        return view('super_admin.owners.index', compact('owners'));
    }

    /**
     * Update Owner information.
     */
    public function update(Request $request, TenantOwner $owner)
    {
        TenantManager::resetToLandlord();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'required|boolean',
        ]);

        $owner->update($request->only('name', 'email', 'phone', 'is_active'));

        // Sync with tenant database user
        try {
            $tenant = $owner->tenant;
            if ($tenant) {
                TenantManager::setTenant($tenant);
                $user = User::where('email', $owner->email)->first();
                if ($user) {
                    $user->update([
                        'name' => $owner->name,
                        'phone' => $owner->phone,
                        'is_active' => $owner->is_active,
                    ]);
                }
                TenantManager::resetToLandlord();
            }
        } catch (\Throwable $e) {
            TenantManager::resetToLandlord();
        }

        return back()->with('success', "Owner '{$owner->name}' details updated successfully.");
    }

    /**
     * Reset Company Owner Password.
     */
    public function resetPassword(Request $request, TenantOwner $owner)
    {
        TenantManager::resetToLandlord();

        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $newPassword = $request->password;
        $owner->initial_password = $newPassword;
        $owner->save();

        // Update password in the tenant database
        try {
            $tenant = $owner->tenant;
            if ($tenant) {
                TenantManager::setTenant($tenant);
                $user = User::where('email', $owner->email)->first() ?? User::whereIn('role', ['owner', 'super_admin'])->first();
                if ($user) {
                    $user->password = Hash::make($newPassword);
                    $user->save();
                }
                TenantManager::resetToLandlord();
            }
        } catch (\Throwable $e) {
            TenantManager::resetToLandlord();
            return back()->withErrors(['error' => 'Failed to update password in tenant database: ' . $e->getMessage()]);
        }

        return back()->with('success', "Password for owner '{$owner->name}' has been reset successfully.");
    }
}
