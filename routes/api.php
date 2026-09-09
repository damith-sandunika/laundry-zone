<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingsController;
use App\Http\Controllers\Api\CustomersController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| PUBLIC  (no key needed — share the link directly):
|   GET /api/bookings               All bookings flat JSON array
|   GET /api/bookings/{id}          Single booking detail
|
| PROTECTED (requires X-API-Key header or ?api_key= param):
|   GET /api/orders                 All orders with customer info (paginated)
|   GET /api/orders/delivery        Delivery orders only
|   GET /api/orders/walkin          Walk-in orders only
|   GET /api/orders/{id}            Single order full detail
|   GET /api/customers              Customer list with contacts
|   GET /api/customers/{id}         Single customer detail
|
| Common query params: branch_id, status, from, to, per_page, page
|
*/

// ── PUBLIC — no API key required ─────────────────────────────────────────────
Route::middleware('api')->group(function () {
    Route::get('/bookings',      [BookingsController::class, 'index']);
    Route::get('/bookings/{id}', [BookingsController::class, 'show']);
});

// ── PROTECTED — requires X-API-Key header ────────────────────────────────────
Route::middleware(['api', 'api.key'])->group(function () {

    // Orders (with customer details, paginated)
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

    // Customers
    Route::get('/customers',      [CustomersController::class, 'index']);
    Route::get('/customers/{id}', [CustomersController::class, 'show']);
});
