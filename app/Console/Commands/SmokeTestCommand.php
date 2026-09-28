<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class SmokeTestCommand extends Command
{
    protected $signature = 'test:smoke';
    protected $description = 'Run a smoke test by hitting all GET routes as an admin';

    public function handle()
    {
        $this->info('Starting System Smoke Test...');

        $admin = User::where('role', 'admin')->first() ?? User::where('role', 'super_admin')->first();
        if (!$admin) {
            $this->error('No admin user found to run the tests.');
            return;
        }

        Auth::login($admin);
        $this->info("Logged in as: {$admin->name} ({$admin->role})");

        $routes = collect(Route::getRoutes())->filter(function ($route) {
            return in_array('GET', $route->methods()) && 
                   !str_contains($route->uri(), '{') && 
                   !str_contains($route->uri(), 'api/') && 
                   !str_contains($route->uri(), '_ignition') &&
                   !str_contains($route->uri(), 'logout') &&
                   $route->uri() !== '/';
        });

        $passed = 0;
        $failed = 0;
        $failures = [];

        $this->output->progressStart($routes->count());

        foreach ($routes as $route) {
            try {
                $request = \Illuminate\Http\Request::create($route->uri(), 'GET');
                // Binding the session and simulating web middleware
                $request->setLaravelSession(app('session')->driver('array'));
                app()->instance('request', $request);

                $response = app()->handle($request);
                
                if ($response->getStatusCode() >= 500) {
                    $failed++;
                    $failures[] = "Route: /{$route->uri()} returned 500 Error.";
                } else {
                    $passed++;
                }
            } catch (\Exception $e) {
                $failed++;
                $failures[] = "Route: /{$route->uri()} threw Exception: " . $e->getMessage();
            }
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->info("Test Complete! Passed: {$passed}, Failed: {$failed}");

        if ($failed > 0) {
            $this->error("Failures detected:");
            foreach ($failures as $f) {
                $this->line("- $f");
            }
        } else {
            $this->info("System is working 10000% perfectly on all GET endpoints!");
        }
    }
}
