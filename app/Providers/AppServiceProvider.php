<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;

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
        Model::preventLazyLoading(! app()->isProduction());

        // ── API Rate Limiting ────────────────────────────────────────────────
        // Public /api/bookings — 60 requests per minute per IP
        RateLimiter::for('api-public', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        // Protected /api/orders, /api/customers — 300 requests per minute per IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(300)->by($request->ip());
        });

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

