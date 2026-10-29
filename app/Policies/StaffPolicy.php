<?php

namespace App\Policies;

use App\Models\User;

class StaffPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if (in_array($user->role, ['owner', 'super_admin'])) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['branch_admin', 'workshop_admin']);
    }

    public function view(User $user, User $model): bool
    {
        return $user->branch_id === $model->branch_id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['branch_admin', 'workshop_admin']);
    }

    public function update(User $user, User $model): bool
    {
        return $user->branch_id === $model->branch_id && $user->role !== 'staff';
    }

    public function delete(User $user, User $model): bool
    {
        return $user->branch_id === $model->branch_id && $user->role !== 'staff';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
