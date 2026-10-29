<?php

use App\Http\Controllers\SuperAdmin\AuthController;
use App\Http\Controllers\SuperAdmin\CompanyController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\DatabaseController;
use App\Http\Controllers\SuperAdmin\OwnerController;
use App\Http\Controllers\SuperAdmin\PaymentController;
use App\Http\Controllers\SuperAdmin\SettingController;
use Illuminate\Support\Facades\Route;

// ─── Super Admin Authentication ───
Route::prefix('super-admin')->name('super_admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ─── Protected Platform Admin Routes ───
    Route::middleware(['super_admin'])->group(function () {
        // Dashboard
        Route::get('/', fn() => redirect()->route('super_admin.dashboard'));
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Companies (Tenants)
        Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
        Route::get('/companies/create', [CompanyController::class, 'create'])->name('companies.create');
        Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
        Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
        Route::get('/companies/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
        Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
        Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
        Route::get('/companies/{company}/impersonate', [CompanyController::class, 'impersonate'])->name('companies.impersonate');

        // Company Owners
        Route::get('/owners', [OwnerController::class, 'index'])->name('owners.index');
        Route::put('/owners/{owner}', [OwnerController::class, 'update'])->name('owners.update');
        Route::post('/owners/{owner}/reset-password', [OwnerController::class, 'resetPassword'])->name('owners.resetPassword');

        // Tenant Databases
        Route::get('/databases', [DatabaseController::class, 'index'])->name('databases.index');
        Route::post('/databases/migrate-all', [DatabaseController::class, 'migrateAll'])->name('databases.migrateAll');
        Route::post('/databases/{company}/migrate', [DatabaseController::class, 'migrate'])->name('databases.migrate');
        Route::get('/databases/{company}/tables', [DatabaseController::class, 'tables'])->name('databases.tables');

        // Payments & Billing
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::put('/payments/{subscription}/status', [PaymentController::class, 'updateStatus'])->name('payments.updateStatus');
        Route::get('/payments/{subscription}/invoice', [PaymentController::class, 'invoice'])->name('payments.invoice');

        // Platform Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('/settings/download-nginx', [SettingController::class, 'downloadNginx'])->name('settings.downloadNginx');
    });
});
