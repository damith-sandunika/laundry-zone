<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OnlineBookingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\WhatsappTemplateController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Public Booking Routes
Route::get('/book', [\App\Http\Controllers\BookingController::class, 'page'])->name('booking.page');
Route::post('/book', [\App\Http\Controllers\BookingController::class, 'store'])->name('booking.store');
Route::get('/book/success', [\App\Http\Controllers\BookingController::class, 'success'])->name('booking.success');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/shifts/open', [DashboardController::class, 'openShift'])->name('shifts.open');
    Route::post('/shifts/close', [DashboardController::class, 'closeShift'])->name('shifts.close');

    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::post('/pos', [POSController::class, 'store'])->name('pos.store');

    Route::post('/customers/pos', [CustomerController::class, 'store'])->name('customers.store.pos');
    Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');
    Route::resource('customers', CustomerController::class);

    Route::post('/orders/bulk-status', [OrderController::class, 'bulkStatus'])->name('orders.bulkStatus');
    Route::get('/orders/{order}/print', [OrderController::class, 'printBill'])->name('orders.print');
    Route::get('/orders/{order}/chit', [OrderController::class, 'printChit'])->name('orders.printChit');
    Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::post('/orders/{order}/reverse', [OrderController::class, 'reverseOrder'])->name('orders.reverseOrder');
    Route::post('/orders/{order}/credit-payment', [OrderController::class, 'collectCreditPayment'])->name('orders.collectCreditPayment');
    Route::resource('orders', OrderController::class)->only(['index', 'show']);

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/bulk-restock', [InventoryController::class, 'bulkStockIn'])->name('inventory.bulkStockIn');
    Route::delete('/inventory/{item}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

    Route::resource('expenses', ExpenseController::class)->except(['create', 'show']);

    Route::get('/online-bookings', [OnlineBookingController::class, 'index'])->name('online_bookings.index');
    Route::post('/online-bookings/{booking}/status', [OnlineBookingController::class, 'updateStatus'])->name('online_bookings.updateStatus');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ADMIN ONLY ROUTES
    Route::middleware(['admin'])->group(function () {
        Route::resource('branches', \App\Http\Controllers\BranchController::class)->except(['create', 'show', 'edit']);

        Route::get('/services', [SettingsController::class, 'servicesIndex'])->name('services.index');
        
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.updateGeneral');
        
        // WhatsApp Proxy Routes (Server-ready)
        Route::get('/settings/whatsapp/status', [SettingsController::class, 'whatsappStatus'])->name('whatsapp.status');
        Route::post('/settings/whatsapp/logout', [SettingsController::class, 'whatsappLogout'])->name('whatsapp.logout');
        Route::post('/settings/whatsapp/reset', [SettingsController::class, 'whatsappReset'])->name('whatsapp.reset');

        Route::post('/settings/services', [SettingsController::class, 'storeService'])->name('settings.storeService');
        Route::put('/settings/services/{service}', [SettingsController::class, 'updateService'])->name('settings.updateService');
        Route::delete('/settings/services/{service}', [SettingsController::class, 'destroyService'])->name('settings.destroyService');
        
        Route::post('/settings/cubbies', [SettingsController::class, 'storeCubby'])->name('settings.storeCubby');
        Route::put('/settings/cubbies/{cubby}', [SettingsController::class, 'updateCubby'])->name('settings.updateCubby');
        Route::delete('/settings/cubbies/{cubby}', [SettingsController::class, 'destroyCubby'])->name('settings.destroyCubby');
        
        Route::post('/settings/inventory', [SettingsController::class, 'storeInventory'])->name('settings.storeInventory');
        Route::post('/settings/inventory/{item}/stock-in', [SettingsController::class, 'stockIn'])->name('settings.stockIn');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
        Route::get('/reports/close-shift', [ReportController::class, 'closeShiftReport'])->name('reports.closeShift');

        Route::post('/staff/pay-salary', [StaffController::class, 'paySalary'])->name('staff.paySalary');
        Route::resource('staff', StaffController::class);

        Route::get('/whatsapp-templates', [WhatsappTemplateController::class, 'index'])->name('whatsapp_templates.index');
        Route::put('/whatsapp-templates/{whatsappTemplate}', [WhatsappTemplateController::class, 'update'])->name('whatsapp_templates.update');
    });
});

require __DIR__.'/auth.php';
