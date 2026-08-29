<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('api')->group(function () {
    // Public API - Bookings
    Route::get('/bookings', [BookingsController::class, 'index']);
    Route::get('/bookings/{id}', [BookingsController::class, 'show']);
});
