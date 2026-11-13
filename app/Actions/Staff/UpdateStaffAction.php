<?php

namespace App\Actions\Staff;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UpdateStaffAction
{
    public function execute(User $staff, array $data): User
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'is_active' => (bool) $data['is_active'],
        ];

        if (isset($data['branch_id'])) {
            $userData['branch_id'] = $data['branch_id'];
        }

        if (isset($data['role'])) {
            if (in_array($data['role'], ['super_admin', 'hr_manager', 'accountant', 'accountant_and_hr_manager']) && !Gate::allows('is-super-admin')) {
                // Keep existing role
            } else {
                $userData['role'] = $data['role'];
                if (in_array($data['role'], ['super_admin', 'hr_manager', 'accountant', 'accountant_and_hr_manager'])) {
                    $userData['branch_id'] = null;
                }
            }
        }

        if (Gate::allows('is-super-admin') && array_key_exists('can_manage_global_config', $data)) {
            $userData['can_manage_global_config'] = (bool) $data['can_manage_global_config'];
        }

        if (!empty($data['password'])) {
            $userData['password'] = bcrypt($data['password']);
        }

        $profileData = [
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
        ];

        return DB::transaction(function () use ($staff, $userData, $profileData) {
            $staff->update($userData);

            if ($staff->profile) {
                $staff->profile->update($profileData);
            } else {
                $staff->profile()->create($profileData);
            }

            return $staff;
        });
    }
}

