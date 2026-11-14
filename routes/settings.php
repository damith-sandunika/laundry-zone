<?php

use App\Http\Controllers\Settings\GeneralSettingsController;
use App\Http\Controllers\Settings\ServiceSettingsController;
use App\Http\Controllers\Settings\ExtraAndTurnaroundSettingsController;
use App\Http\Controllers\Settings\LogisticsSettingsController;
use App\Http\Controllers\Settings\PackageSettingsController;
use App\Http\Controllers\Settings\InventorySettingsController;
use App\Http\Controllers\PaymentSettingsController;
use App\Http\Controllers\SmsTemplateController;
use App\Http\Controllers\EmailTemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['admin'])->group(function () {
    Route::get('/settings/general', [GeneralSettingsController::class, 'general'])->name('settings.general');
    Route::get('/settings/extras', [GeneralSettingsController::class, 'extras'])->name('settings.extras');
    Route::get('/settings/turnarounds', [GeneralSettingsController::class, 'turnaroundTypes'])->name('settings.turnarounds');
    Route::get('/settings/cubbies', [GeneralSettingsController::class, 'cubbies'])->name('settings.cubbies');
    Route::get('/settings/delivery', [GeneralSettingsController::class, 'delivery'])->name('settings.delivery');
    Route::get('/settings/pickup-times', [GeneralSettingsController::class, 'pickupTimes'])->name('settings.pickup-times');
    Route::get('/settings/expense-categories', [\App\Http\Controllers\Settings\ExpenseSettingsController::class, 'index'])->name('settings.expense_categories');
    Route::post('/settings/expense-categories', [\App\Http\Controllers\Settings\ExpenseSettingsController::class, 'store'])->name('settings.expense_categories.store');
    Route::put('/settings/expense-categories/{category}', [\App\Http\Controllers\Settings\ExpenseSettingsController::class, 'update'])->name('settings.expense_categories.update');
    Route::delete('/settings/expense-categories/{category}', [\App\Http\Controllers\Settings\ExpenseSettingsController::class, 'destroy'])->name('settings.expense_categories.destroy');

    Route::redirect('/settings', '/settings/general')->name('settings.index');
    Route::post('/settings/general', [GeneralSettingsController::class, 'updateGeneral'])->name('settings.updateGeneral');

    // Cubbies
    Route::post('/settings/cubbies', [LogisticsSettingsController::class, 'storeCubby'])->name('settings.storeCubby');
    Route::put('/settings/cubbies/{cubby}', [LogisticsSettingsController::class, 'updateCubby'])->name('settings.updateCubby');
    Route::delete('/settings/cubbies/{cubby}', [LogisticsSettingsController::class, 'destroyCubby'])->name('settings.destroyCubby');

    // Inventory
    Route::post('/settings/inventory', [InventorySettingsController::class, 'storeInventory'])->name('settings.storeInventory');
    Route::post('/settings/inventory/{item}/stock-in', [InventorySettingsController::class, 'stockIn'])->name('settings.stockIn');

    // Packages Settings
    Route::post('/settings/packages', [PackageSettingsController::class, 'storePackage'])->name('settings.storePackage');
    Route::put('/settings/packages/{package}', [PackageSettingsController::class, 'updatePackage'])->name('settings.updatePackage');
    Route::delete('/settings/packages/{package}', [PackageSettingsController::class, 'destroyPackage'])->name('settings.destroyPackage');

    // Service Reordering
    Route::post('/settings/services/reorder', [ServiceSettingsController::class, 'reorderServices'])->name('settings.reorderServices');
    Route::post('/settings/service-categories/reorder', [ServiceSettingsController::class, 'reorderCategories'])->name('settings.reorderCategories');

    // Services & Categories
    Route::get('/settings/services/print', [ServiceSettingsController::class, 'printPriceList'])->name('settings.services.print');
    Route::get('/settings/services', [ServiceSettingsController::class, 'servicesManager'])->name('settings.services');
    Route::post('/settings/services', [ServiceSettingsController::class, 'storeService'])->name('settings.storeService');
    Route::put('/settings/services/{service}', [ServiceSettingsController::class, 'updateService'])->name('settings.updateService');
    Route::delete('/settings/services/{service}', [ServiceSettingsController::class, 'destroyService'])->name('settings.destroyService');

    Route::post('/settings/service-categories', [ServiceSettingsController::class, 'storeServiceCategory'])->name('settings.storeServiceCategory');
    Route::put('/settings/service-categories/{category}', [ServiceSettingsController::class, 'updateServiceCategory'])->name('settings.updateServiceCategory');
    Route::delete('/settings/service-categories/{category}', [ServiceSettingsController::class, 'destroyServiceCategory'])->name('settings.destroyServiceCategory');

    // Extras
    Route::post('/settings/extras', [ExtraAndTurnaroundSettingsController::class, 'storeExtra'])->name('settings.storeExtra');
    Route::put('/settings/extras/{extra}', [ExtraAndTurnaroundSettingsController::class, 'updateExtra'])->name('settings.updateExtra');
    Route::delete('/settings/extras/{extra}', [ExtraAndTurnaroundSettingsController::class, 'destroyExtra'])->name('settings.destroyExtra');

    // Turnaround Types
    Route::post('/settings/turnaround-types', [ExtraAndTurnaroundSettingsController::class, 'storeTurnaroundType'])->name('settings.storeTurnaroundType');
    Route::put('/settings/turnaround-types/{turnaroundType}', [ExtraAndTurnaroundSettingsController::class, 'updateTurnaroundType'])->name('settings.updateTurnaroundType');
    Route::delete('/settings/turnaround-types/{turnaroundType}', [ExtraAndTurnaroundSettingsController::class, 'destroyTurnaroundType'])->name('settings.destroyTurnaroundType');

    // Logistics / Pickups
    Route::post('/settings/pickup-time-periods', [LogisticsSettingsController::class, 'storePickupTimePeriod'])->name('settings.storePickupTimePeriod');
    Route::delete('/settings/pickup-time-periods/{pickupTimePeriod}', [LogisticsSettingsController::class, 'deletePickupTimePeriod'])->name('settings.deletePickupTimePeriod');
    Route::post('/settings/delivery-ranges', [LogisticsSettingsController::class, 'storeDeliveryRange'])->name('settings.storeDeliveryRange');
    Route::delete('/settings/delivery-ranges/{deliveryRange}', [LogisticsSettingsController::class, 'deleteDeliveryRange'])->name('settings.deleteDeliveryRange');
    Route::post('/settings/delivery', [LogisticsSettingsController::class, 'updateDeliverySettings'])->name('settings.updateDeliverySettings');

    // Super Admin Only Settings Routes
    Route::middleware(['role:super_admin'])->group(function () {
        Route::get('/billing', [\App\Http\Controllers\TenantBillingController::class, 'index'])->name('billing.index');

        // Document & Print Layout Customizer
        Route::get('/document-layouts', [\App\Http\Controllers\TenantPrintLayoutController::class, 'index'])->name('tenant.layouts.index');
        Route::get('/document-layouts/bill', [\App\Http\Controllers\TenantPrintLayoutController::class, 'billPage'])->name('tenant.layouts.bill');
        Route::get('/document-layouts/receipt', [\App\Http\Controllers\TenantPrintLayoutController::class, 'receiptPage'])->name('tenant.layouts.receipt');
        Route::get('/document-layouts/invoice', [\App\Http\Controllers\TenantPrintLayoutController::class, 'invoicePage'])->name('tenant.layouts.invoice');
        Route::post('/document-layouts', [\App\Http\Controllers\TenantPrintLayoutController::class, 'update'])->name('tenant.layouts.update');
        Route::post('/document-layouts/reset', [\App\Http\Controllers\TenantPrintLayoutController::class, 'reset'])->name('tenant.layouts.reset');

        // SMS Messages Sender & Bundle Payments
        Route::get('/sms/sender', [\App\Http\Controllers\TenantSmsController::class, 'sender'])->name('sms.sender');
        Route::get('/sms/payments', [\App\Http\Controllers\TenantSmsController::class, 'payments'])->name('sms.payments');

        Route::get('/settings/payment-methods', [GeneralSettingsController::class, 'paymentMethods'])->name('settings.payment-methods');
        Route::post('/settings/payment-methods', [PaymentSettingsController::class, 'update'])->name('settings.payment-methods.update');

        Route::get('/settings/notifications', [GeneralSettingsController::class, 'notifications'])->name('settings.notifications');
        Route::post('/settings/notifications', [GeneralSettingsController::class, 'updateNotifications'])->name('settings.notifications.update');

        Route::get('/branch-logs', [\App\Http\Controllers\BranchLogController::class, 'index'])->name('superadmin.branch-logs');
        Route::get('/orders-change-log', [\App\Http\Controllers\OrderLogReportController::class, 'index'])->name('superadmin.order-logs');

        Route::get('/sms-templates', [SmsTemplateController::class, 'index'])->name('sms-templates.index');
        Route::put('/sms-templates/{template}', [SmsTemplateController::class, 'update'])->name('sms-templates.update');
        Route::post('/sms-templates/toggle', [SmsTemplateController::class, 'toggleGlobal'])->name('sms-templates.toggleGlobal');
        Route::post('/sms-templates/seed', [SmsTemplateController::class, 'seedDefault'])->name('sms-templates.seedDefault');

        Route::get('/email-templates', [EmailTemplateController::class, 'index'])->name('email-templates.index');
        Route::put('/email-templates/{template}', [EmailTemplateController::class, 'update'])->name('email-templates.update');
        Route::post('/email-templates/toggle', [EmailTemplateController::class, 'toggleGlobal'])->name('email-templates.toggleGlobal');
        Route::post('/email-templates/seed', [EmailTemplateController::class, 'seedDefault'])->name('email-templates.seedDefault');
    });
});

