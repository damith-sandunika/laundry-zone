<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Global Authorization Gates
        Gate::define('is-super-admin', function ($user) {
            return $user->role === 'super_admin';
        });

        Gate::define('is-admin', function ($user) {
            return in_array($user->role, ['super_admin', 'admin', 'branch_admin']);
        });

        Gate::define('is-workshop-admin', function ($user) {
            return $user->role === 'workshop_admin' || ($user->role === 'staff' && $user->branch && $user->branch->type === 'workshop');
        });

        Gate::define('manage-users', function ($user) {
            return in_array($user->role, ['super_admin', 'admin']);
        });

        Gate::define('manage-branches', function ($user) {
            return $user->role === 'super_admin';
        });

        // Register policies that don't match auto-discovery
        Gate::policy(\App\Models\User::class, \App\Policies\StaffPolicy::class);
    }
}

