<?php

namespace App\Actions\Staff;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CreateStaffAction
{
    public function execute(array $data, ?int $activeBranchId): User
    {
        $role = $data['role'];
        $currentUserRole = auth()->user()->role ?? '';

        if (in_array($role, ['super_admin', 'hr_manager', 'accountant', 'accountant_and_hr_manager']) && $currentUserRole !== 'super_admin') {
            $role = 'staff';
        }

        $branchId = Gate::allows('manage-users') ? ($data['branch_id'] ?? null) : $activeBranchId;
        if (in_array($role, ['super_admin', 'hr_manager', 'accountant', 'accountant_and_hr_manager'])) {
            $branchId = null;
        }

        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'role' => $role,
            'branch_id' => $branchId,
            'password' => bcrypt($data['password']),
            'is_active' => true,
        ];

        if (Gate::allows('is-super-admin')) {
            $userData['can_manage_global_config'] = !empty($data['can_manage_global_config']);
        }

        return DB::transaction(function () use ($userData, $data) {
            $user = User::create($userData);

            $user->profile()->create([
                'nic' => $data['nic'] ?? null,
                'dob' => $data['dob'] ?? null,
                'address' => $data['address'] ?? null,
                'emergency_contact' => $data['emergency_contact'] ?? null,
                'vehicle_number' => $data['vehicle_number'] ?? null,
                'casual_leaves' => $data['casual_leaves'] ?? 0,
                'standard_working_hours' => $data['standard_working_hours'] ?? 8.0,
                'ot_rate_per_hour' => $data['ot_rate_per_hour'] ?? 0,
                'hourly_rate' => $data['hourly_rate'] ?? 0,
                'basic_salary' => $data['basic_salary'] ?? 0,
            ]);

            return $user;
        });
    }
}

