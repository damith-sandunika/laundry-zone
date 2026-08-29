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
        'name', 'email', 'phone', 'password', 'role', 'branch_id', 'is_active',
        'nic', 'dob', 'address', 'emergency_contact', 'basic_salary', 'casual_leaves',
        'standard_working_hours', 'ot_rate_per_hour',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'created_by');
    }

    public function deliveryTasks()
    {
        return $this->hasMany(DeliveryTask::class, 'rider_id');
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

    // ─── Role Helpers ───
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isBranchAdmin(): bool
    {
        return $this->role === 'branch_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'branch_admin']);
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isRider(): bool
    {
        return $this->role === 'rider';
    }

    public function isWorkshopAdmin(): bool
    {
        return $this->role === 'workshop_admin';
    }

    // Has admin-level access (super_admin or branch_admin)
    public function hasAdminAccess(): bool
    {
        return in_array($this->role, ['super_admin', 'branch_admin', 'admin']);
    }

    // Dashboard redirect based on role
    public function dashboardRoute(): string
    {
        return match ($this->role) {
            'super_admin' => 'dashboard',
            'branch_admin',
            'admin',
            'staff' => 'dashboard',
            'rider' => 'rider.dashboard',
            'workshop_admin' => 'workshop.dashboard',
            default => 'dashboard',
        };
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Admin',
            'branch_admin' => 'Branch Admin',
            'admin' => 'Admin',
            'staff' => 'Staff',
            'rider' => 'Delivery Rider',
            'workshop_admin' => 'Workshop Admin',
            default => ucfirst($this->role),
        };
    }
}
