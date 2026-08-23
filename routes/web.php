<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerSiteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\HRController;
use App\Http\Controllers\HRDashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OnlineBookingController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PaymentSettingsController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RiderController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\WhatsappTemplateController;
use App\Http\Controllers\WorkshopController;
use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Route;

// ─── App Redirect ───
Route::redirect('/app', '/stafflogin');
Route::redirect('/login', '/stafflogin');

// ─── Public Booking Routes (Customer Site) ───
Route::get('/', [CustomerSiteController::class, 'home'])->name('home');

// ─── Customer Auth & Dashboard Routes ───
Route::get('/customer/login', [\App\Http\Controllers\Customer\AuthController::class, 'showLogin'])->name('customer.login');
Route::post('/customer/login', [\App\Http\Controllers\Customer\AuthController::class, 'login'])->name('customer.login.submit');
Route::get('/customer/register', [\App\Http\Controllers\Customer\AuthController::class, 'showRegister'])->name('customer.register');
Route::post('/customer/register', [\App\Http\Controllers\Customer\AuthController::class, 'register'])->name('customer.register.submit');
Route::post('/customer/logout', [\App\Http\Controllers\Customer\AuthController::class, 'logout'])->name('customer.logout');

Route::middleware('auth:customer')->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Customer\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders', [\App\Http\Controllers\Customer\DashboardController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}', [\App\Http\Controllers\Customer\DashboardController::class, 'showOrder'])->name('orders.show');
    Route::get('/orders/{order}/pay', [\App\Http\Controllers\Customer\DashboardController::class, 'pay'])->name('orders.pay');
    Route::post('/orders/{order}/pay', [\App\Http\Controllers\Customer\DashboardController::class, 'processPayment'])->name('orders.pay.process');
    
    // Appointments
    Route::get('/appointments/{appointment}', [\App\Http\Controllers\Customer\DashboardController::class, 'showAppointment'])->name('appointments.show');
    
    // Customer Booking
    Route::get('/book', [\App\Http\Controllers\CustomerSiteController::class, 'booking'])->name('booking');
    Route::post('/book', [\App\Http\Controllers\CustomerSiteController::class, 'store'])->name('booking.store');
    Route::get('/book/success/{appointment}', [\App\Http\Controllers\CustomerSiteController::class, 'success'])->name('booking.success');
});

// ─── Public Order Tracking (QR Code link) ───
Route::get('/orders/{order}/track', function (Order $order) {
    return view('orders.track', compact('order'));
})->name('orders.track');

// ─── Public QR Code Generator ───
Route::get('/qr/{content}', function (string $content) {
    $service = new WhatsAppService;
    $path = $service->generateQrCodeImage(base64_decode($content), 'qr');

    return response()->file($path, ['Content-Type' => 'image/png']);
})->name('qr.generate');

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/shifts/open', [DashboardController::class, 'openShift'])->name('shifts.open');
    Route::post('/shifts/close', [DashboardController::class, 'closeShift'])->name('shifts.close');

    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::post('/pos', [POSController::class, 'store'])->name('pos.store');

    Route::post('/customers/pos', [CustomerController::class, 'store'])->name('customers.store.pos');
    Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');
    Route::resource('customers', CustomerController::class);

    Route::post('/orders/{order}/update', [OrderController::class, 'updateOrder'])->name('orders.updateOrder');
    Route::post('/orders/bulk-status', [OrderController::class, 'bulkStatus'])->name('orders.bulkStatus');
    Route::get('/orders/{order}/print', [OrderController::class, 'printBill'])->name('orders.print');
    Route::get('/orders/{order}/chit', [OrderController::class, 'printChit'])->name('orders.printChit');
    Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::post('/orders/{order}/reverse', [OrderController::class, 'reverseOrder'])->name('orders.reverseOrder');
    Route::post('/orders/{order}/credit-payment', [OrderController::class, 'collectCreditPayment'])->name('orders.collectCreditPayment');
    // Workshop dispatch from order
    Route::post('/orders/{order}/dispatch-workshop', [WorkshopController::class, 'dispatch'])->name('orders.dispatchWorkshop');
    Route::post('/orders/workshop/bulk-dispatch', [WorkshopController::class, 'bulkDispatch'])->name('orders.bulkDispatchWorkshop');
    Route::get('/orders/{order}/workshop-tracking', [WorkshopController::class, 'tracking'])->name('orders.workshopTracking');
    Route::resource('orders', OrderController::class)->only(['index', 'show']);

    Route::post('/packages/{customerPackage}/mark-paid', [PackageController::class, 'markPaid'])->name('packages.markPaid');
    Route::get('/packages/{customerPackage}/print', [PackageController::class, 'printReceipt'])->name('packages.print');
    Route::resource('packages', PackageController::class)->except(['create', 'edit', 'update']);

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/bulk-restock', [InventoryController::class, 'bulkStockIn'])->name('inventory.bulkStockIn');
    Route::post('/inventory/bulk-stock-out', [InventoryController::class, 'bulkStockOut'])->name('inventory.bulkStockOut');
    Route::post('/inventory/{item}/stock-out', [InventoryController::class, 'stockOut'])->name('inventory.stockOut');
    Route::delete('/inventory/{item}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

    Route::resource('payment_methods', \App\Http\Controllers\PaymentMethodController::class)->only(['store', 'update', 'destroy']);

    Route::resource('expenses', ExpenseController::class)->except(['create', 'show']);

    Route::get('/online-bookings', [OnlineBookingController::class, 'index'])->name('online_bookings.index');
    Route::post('/online-bookings/{booking}/status', [OnlineBookingController::class, 'updateStatus'])->name('online_bookings.updateStatus');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ─── RIDER ROUTES ───
    Route::middleware(['rider'])->prefix('rider')->name('rider.')->group(function () {
        Route::get('/dashboard', [RiderController::class, 'dashboard'])->name('dashboard');
        Route::get('/tasks', [RiderController::class, 'tasks'])->name('tasks');
        Route::post('/tasks/{task}/accept', [RiderController::class, 'accept'])->name('tasks.accept');
        Route::post('/tasks/{task}/on-way', [RiderController::class, 'onWay'])->name('tasks.onWay');
        Route::post('/tasks/{task}/arrived', [RiderController::class, 'arrived'])->name('tasks.arrived');
        Route::post('/tasks/{task}/complete', [RiderController::class, 'complete'])->name('tasks.complete');
        Route::post('/tasks/{task}/location', [RiderController::class, 'updateLocation'])->name('tasks.location');
        Route::get('/tasks/{task}/create-order', [RiderController::class, 'createOrder'])->name('createOrder');
        Route::post('/tasks/{task}/store-order', [RiderController::class, 'storeOrder'])->name('storeOrder');
    });

    // ─── WORKSHOP ROUTES ───
    Route::middleware(['workshop'])->prefix('workshop')->name('workshop.')->group(function () {
        Route::get('/dashboard', [WorkshopController::class, 'dashboard'])->name('dashboard');
        Route::get('/history', [WorkshopController::class, 'history'])->name('history');
        Route::post('/dispatches/{dispatch}/receive', [WorkshopController::class, 'receive'])->name('receive');
        Route::post('/dispatches/{dispatch}/start-processing', [WorkshopController::class, 'startProcessing'])->name('startProcessing');
        Route::post('/dispatches/{dispatch}/complete', [WorkshopController::class, 'complete'])->name('complete');
        Route::post('/dispatches/{dispatch}/return', [WorkshopController::class, 'returnToBranch'])->name('return');
        Route::post('/bulk-status', [WorkshopController::class, 'bulkStatus'])->name('bulkStatus');
    });

    // ─── ADMIN ONLY ROUTES ───
    Route::middleware(['admin'])->group(function () {
        Route::resource('branches', BranchController::class)->except(['create', 'show', 'edit']);

        // ─── Delivery Management (Branch Admin) ───
        Route::get('/delivery', [DeliveryController::class, 'index'])->name('delivery.index');
        Route::get('/delivery/{appointment}/confirm', [DeliveryController::class, 'confirm'])->name('delivery.confirm');
        Route::post('/delivery/{appointment}/confirm-action', [DeliveryController::class, 'confirmAction'])->name('delivery.confirmAction');
        Route::post('/delivery/{appointment}/cancel', [DeliveryController::class, 'cancel'])->name('delivery.cancel');
        Route::post('/delivery/{appointment}/assign-rider', [DeliveryController::class, 'assignRider'])->name('delivery.assignRider');
        Route::post('/delivery/{appointment}/at-branch', [DeliveryController::class, 'markAtBranch'])->name('delivery.atBranch');
        Route::post('/delivery/{appointment}/assign-delivery-rider', [DeliveryController::class, 'assignDeliveryRider'])->name('delivery.assignDeliveryRider');
        Route::post('/delivery/{appointment}/collect-cash', [DeliveryController::class, 'collectCash'])->name('delivery.collectCash');
        Route::post('/workshop/dispatches/{dispatch}/received-at-branch', [WorkshopController::class, 'receivedAtBranch'])->name('workshop.receivedAtBranch');

        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.updateGeneral');

        // Payment Settings
        Route::get('/settings/payment-methods', [PaymentSettingsController::class, 'index'])->name('settings.payment-methods.index');
        Route::post('/settings/payment-methods', [PaymentSettingsController::class, 'update'])->name('settings.payment-methods.update');

        // WhatsApp Proxy Routes (Server-ready)
        Route::get('/settings/whatsapp/status', [SettingsController::class, 'whatsappStatus'])->name('whatsapp.status');
        Route::post('/settings/whatsapp/logout', [SettingsController::class, 'whatsappLogout'])->name('whatsapp.logout');
        Route::post('/settings/whatsapp/reset', [SettingsController::class, 'whatsappReset'])->name('whatsapp.reset');

        // Super Admin Only Settings Routes
        Route::middleware(['role:super_admin'])->group(function () {
            Route::post('/settings/services', [SettingsController::class, 'storeService'])->name('settings.storeService');
            Route::put('/settings/services/{service}', [SettingsController::class, 'updateService'])->name('settings.updateService');
            Route::delete('/settings/services/{service}', [SettingsController::class, 'destroyService'])->name('settings.destroyService');

            Route::post('/settings/service-categories', [SettingsController::class, 'storeServiceCategory'])->name('settings.storeServiceCategory');
            Route::put('/settings/service-categories/{category}', [SettingsController::class, 'updateServiceCategory'])->name('settings.updateServiceCategory');
            Route::delete('/settings/service-categories/{category}', [SettingsController::class, 'destroyServiceCategory'])->name('settings.destroyServiceCategory');

            Route::post('/settings/packages', [SettingsController::class, 'storePackage'])->name('settings.storePackage');
            Route::put('/settings/packages/{package}', [SettingsController::class, 'updatePackage'])->name('settings.updatePackage');
            Route::delete('/settings/packages/{package}', [SettingsController::class, 'destroyPackage'])->name('settings.destroyPackage');
        });

        Route::post('/settings/cubbies', [SettingsController::class, 'storeCubby'])->name('settings.storeCubby');
        Route::put('/settings/cubbies/{cubby}', [SettingsController::class, 'updateCubby'])->name('settings.updateCubby');
        Route::delete('/settings/cubbies/{cubby}', [SettingsController::class, 'destroyCubby'])->name('settings.destroyCubby');

        Route::post('/settings/inventory', [SettingsController::class, 'storeInventory'])->name('settings.storeInventory');
        Route::post('/settings/inventory/{item}/stock-in', [SettingsController::class, 'stockIn'])->name('settings.stockIn');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
        Route::get('/reports/close-shift', [ReportController::class, 'closeShiftReport'])->name('reports.closeShift');
        Route::post('/reports/packages/{customerPackage}/approve-payment', [ReportController::class, 'approvePackagePayment'])->name('reports.approvePackagePayment');

        Route::post('/staff/pay-salary', [StaffController::class, 'paySalary'])->name('staff.paySalary');
        Route::resource('staff', StaffController::class);

        // HR Routes
        Route::get('/hr/dashboard', [HRDashboardController::class, 'index'])->name('hr.dashboard');
        Route::post('/hr/{staff}/attendance', [HRController::class, 'storeAttendance'])->name('hr.attendance.store');
        Route::put('/hr/attendance/{attendance}', [HRController::class, 'updateAttendance'])->name('hr.attendance.update');
        Route::post('/hr/{staff}/leaves', [HRController::class, 'storeLeave'])->name('hr.leaves.store');
        Route::put('/hr/leaves/{leave}', [HRController::class, 'updateLeave'])->name('hr.leaves.update');
        Route::post('/hr/{staff}/advances', [HRController::class, 'storeAdvance'])->name('hr.advances.store');

        Route::get('/whatsapp-templates', [WhatsappTemplateController::class, 'index'])->name('whatsapp_templates.index');
        Route::put('/whatsapp-templates/{whatsappTemplate}', [WhatsappTemplateController::class, 'update'])->name('whatsapp_templates.update');
    });
});

require __DIR__.'/auth.php';
