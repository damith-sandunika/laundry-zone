<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ApiKeyMiddleware;
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
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'    => RoleMiddleware::class,
            'admin'   => AdminMiddleware::class,
            'rider'   => RiderMiddleware::class,
            'workshop'=> WorkshopMiddleware::class,
            'api.key' => ApiKeyMiddleware::class,
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

        $middleware->validateCsrfTokens(except: [
            'sms/callback',
            'payment/webhook',
            'pay/*/webhook',
            ...(env('APP_ENV') === 'testing' ? ['*'] : []),
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
