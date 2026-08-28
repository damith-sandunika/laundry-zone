<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\RiderMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\WorkshopMiddleware;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'admin' => AdminMiddleware::class,
            'rider' => RiderMiddleware::class,
            'workshop' => WorkshopMiddleware::class,
        ]);
        
        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            if ($request->is('customer/*') || $request->is('book')) {
                return '/customer/login';
            }
            return '/stafflogin';
        });

        $middleware->redirectUsersTo(function (\Illuminate\Http\Request $request) {
            if (Auth::guard('customer')->check() && $request->is('customer/*')) {
                return '/customer/dashboard';
            }
            return '/dashboard';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
