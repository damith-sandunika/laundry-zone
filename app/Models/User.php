<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use BelongsToBranch, SoftDeletes;
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'phone', 'email_verified_at', 'password', 'role', 
        'can_manage_global_config', 'branch_id', 'is_active', 'remember_token'
    ];

    public function profile()
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'can_manage_global_config' => 'boolean',
        ];
    }

    public function canManageGlobalConfig()
    {
        return in_array($this->role, ['owner', 'super_admin']) || $this->can_manage_global_config;
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'created_by');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function salaryAdvances()
    {
        return $this->hasMany(SalaryAdvance::class);
    }

    public function salaryAdjustments()
    {
        return $this->hasMany(SalaryAdjustment::class);
    }

    // ─── Role Helpers ───
    public function isOwner(): bool
    {
        return in_array($this->role, ['owner', 'super_admin']);
    }

    public function isSuperAdmin(): bool
    {
        return in_array($this->role, ['owner', 'super_admin']);
    }

    public function isBranchAdmin(): bool
    {
        return $this->role === 'branch_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['owner', 'super_admin', 'branch_admin']);
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isRider(): bool
    {
        return false;
    }

    public function isWorkshopAdmin(): bool
    {
        return $this->role === 'workshop_admin';
    }

    // Has admin-level access (owner, super_admin or branch_admin)
    public function hasAdminAccess(): bool
    {
        return in_array($this->role, ['owner', 'super_admin', 'branch_admin']);
    }

    // Dashboard redirect based on role
    public function dashboardRoute(): string
    {
        return match ($this->role) {
            'owner', 'super_admin' => 'dashboard',
            'branch_admin',
            'staff' => 'dashboard',
            'workshop_admin' => 'workshop.dashboard',
            default => 'dashboard',
        };
    }

    public function canDeleteOrders(): bool
    {
        if ($this->isOwner()) {
            return true;
        }

        if (in_array($this->role, ['branch_admin', 'workshop_admin'])) {
            return \App\Models\Setting::get('allow_branch_admins_delete_orders', '0') === '1';
        }

        return false;
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            'owner' => 'Company Owner',
            'super_admin' => 'Company Owner',
            'branch_admin' => 'Branch Admin',
            'staff' => 'Staff',
            'workshop_admin' => 'Workshop Admin',
            default => ucfirst($this->role),
        };
    }
}

