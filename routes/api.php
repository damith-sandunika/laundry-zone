<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingsController;
use App\Http\Controllers\Api\CustomersController;

/*
|--------------------------------------------------------------------------
| API Routes  —  ALL routes protected by X-API-Key header
|--------------------------------------------------------------------------
|
| All endpoints require:  X-API-Key: <value from APP_API_KEY in .env>
| Or query param:         ?api_key=<value>
|
|  GET /api/bookings               All orders (paginated)
|  GET /api/bookings/{id}          Single order full detail
|
|  GET /api/orders                 All orders with customer info
|  GET /api/orders/delivery        Delivery orders only
|  GET /api/orders/walkin          Walk-in orders only
|  GET /api/orders/{id}            Single order detail
|
|  GET /api/customers              Customer list with contacts
|  GET /api/customers/{id}         Single customer detail
|
*/

Route::middleware(['api', 'throttle:api', 'api.key'])->group(function () {

    // ── Bookings ──────────────────────────────────────────────────────────────
    Route::get('/bookings',      [BookingsController::class, 'index']);
    Route::get('/bookings/{id}', [BookingsController::class, 'show']);

    // ── Orders ────────────────────────────────────────────────────────────────
    Route::get('/orders', [BookingsController::class, 'indexProtected']);

    Route::get('/orders/delivery', function (\Illuminate\Http\Request $request) {
        $request->merge(['type' => 'delivery']);
        return app(BookingsController::class)->indexProtected($request);
    });

    Route::get('/orders/walkin', function (\Illuminate\Http\Request $request) {
        $request->merge(['type' => 'walkin']);
        return app(BookingsController::class)->indexProtected($request);
    });

    Route::get('/orders/{id}', [BookingsController::class, 'show']);

    // ── Customers ─────────────────────────────────────────────────────────────
    Route::get('/customers',      [CustomersController::class, 'index']);
    Route::get('/customers/{id}', [CustomersController::class, 'show']);

});
