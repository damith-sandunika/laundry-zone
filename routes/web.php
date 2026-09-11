<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\BranchPlanController;
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
use App\Http\Controllers\SmsTemplateController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\WhatsappTemplateController;
use App\Http\Controllers\WorkshopController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

// ─── App Redirect ───
Route::redirect('/app', '/stafflogin');
Route::redirect('/login', '/stafflogin');

// ─── Root → Redirect to Customer Login ───
Route::get('/', function () {
    return redirect()->route('customer.login');
})->name('home');

// Fix for Windows PHP built-in server symlink 403 error
Route::get('/storage/receipts/{filename}', function ($filename) {
    $path = storage_path('app/public/receipts/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path);
});


// ─── Customer Auth & Dashboard Routes ───
Route::get('/customer/login', [\App\Http\Controllers\Customer\AuthController::class, 'showLogin'])->name('customer.login');
Route::post('/customer/login', [\App\Http\Controllers\Customer\AuthController::class, 'login'])->name('customer.login.submit');
Route::get('/customer/register', [\App\Http\Controllers\Customer\AuthController::class, 'showRegister'])->name('customer.register');
Route::post('/customer/register', [\App\Http\Controllers\Customer\AuthController::class, 'register'])->name('customer.register.submit');
Route::post('/customer/logout', [\App\Http\Controllers\Customer\AuthController::class, 'logout'])->name('customer.logout');

// Forgot Password (OTP via Phone)
Route::get('/customer/forgot-password', [\App\Http\Controllers\Customer\ForgotPasswordController::class, 'showPhoneForm'])->name('customer.password.request');
Route::post('/customer/forgot-password', [\App\Http\Controllers\Customer\ForgotPasswordController::class, 'sendOtp'])->name('customer.password.email');
Route::get('/customer/verify-otp', [\App\Http\Controllers\Customer\ForgotPasswordController::class, 'showOtpForm'])->name('customer.password.verifyForm');
Route::post('/customer/verify-otp', [\App\Http\Controllers\Customer\ForgotPasswordController::class, 'verifyOtp'])->name('customer.password.verifyOtp');
Route::get('/customer/reset-password', [\App\Http\Controllers\Customer\ForgotPasswordController::class, 'showResetForm'])->name('customer.password.resetForm');
Route::post('/customer/reset-password', [\App\Http\Controllers\Customer\ForgotPasswordController::class, 'resetPassword'])->name('customer.password.update');

// AJAX Auth for Wizard
Route::post('/customer/ajax-login', [\App\Http\Controllers\Customer\AuthController::class, 'ajaxLogin'])->name('customer.ajax.login');
Route::post('/customer/ajax-register', [\App\Http\Controllers\Customer\AuthController::class, 'ajaxRegister'])->name('customer.ajax.register');

// Public Booking Wizard
Route::get('/customer/book', [\App\Http\Controllers\CustomerSiteController::class, 'booking'])->name('customer.booking');
Route::post('/customer/book', [\App\Http\Controllers\CustomerSiteController::class, 'store'])->name('customer.booking.store');
Route::get('/customer/book/success/{order:order_number}', [\App\Http\Controllers\CustomerSiteController::class, 'success'])->name('customer.booking.success');
Route::get('/customer/branch-services/{branch}', [\App\Http\Controllers\CustomerSiteController::class, 'getBranchServices'])->name('customer.branch_services');

Route::middleware('auth:customer')->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Customer\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders', [\App\Http\Controllers\Customer\DashboardController::class, 'orders'])->name('orders');
    Route::get('/orders/{order:order_number}', [\App\Http\Controllers\Customer\DashboardController::class, 'showOrder'])->name('orders.show');
    Route::post('/orders/{order:order_number}/payment', [\App\Http\Controllers\Customer\DashboardController::class, 'submitPayment'])->name('orders.submitPayment');
    Route::get('/orders/{order:order_number}/pay', [\App\Http\Controllers\Customer\DashboardController::class, 'pay'])->name('orders.pay');
    
    // Appointments
    Route::get('/appointments/{appointment}', function (\App\Models\Appointment $appointment) {
        if (!$appointment->order_number) {
            return redirect()->route('customer.booking.success', $appointment->id);
        }
        return app(\App\Http\Controllers\Customer\DashboardController::class)->showAppointment($appointment);
    })->name('appointments.show');

    // Payment Gateway Routes
    Route::get('/orders/{order:order_number}/pay-gateway', [\App\Http\Controllers\PaymentController::class, 'checkout'])->name('payment.checkout');
    Route::post('/payment/webhook', [\App\Http\Controllers\PaymentController::class, 'webhook'])->name('payment.webhook');
    Route::get('/orders/{order:order_number}/payment-success', [\App\Http\Controllers\PaymentController::class, 'success'])->name('payment.success');
});

// ─── Public Order Tracking (QR Code link) ───
Route::get('/orders/{order}/track', function (Order $order) {
    return view('orders.track', compact('order'));
})->name('orders.track');



Route::middleware(['auth'])->group(function () {
    Route::get('/notifications/check', [\App\Http\Controllers\NotificationController::class, 'check'])->name('notifications.check');
    Route::post('/notifications/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/shifts/open', [\App\Http\Controllers\DashboardController::class, 'openShift'])->name('shifts.open');
    Route::post('/shifts/attendance/update', [\App\Http\Controllers\DashboardController::class, 'updateAttendance'])->name('shifts.attendance.update');
    Route::post('/shifts/close', [\App\Http\Controllers\DashboardController::class, 'closeShift'])->name('shifts.close');

    Route::post('/bank-deposits', [\App\Http\Controllers\BankDepositController::class, 'store'])->name('bank-deposits.store');

    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::post('/pos', [POSController::class, 'store'])->name('pos.store');
    Route::post('/pos/delivery', [POSController::class, 'storeDelivery'])->name('pos.delivery.store');

    Route::post('/customers/pos', [CustomerController::class, 'store'])->name('customers.store.pos');
    Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');
    Route::resource('customers', CustomerController::class);

    Route::post('/orders/{order:id}/update', [OrderController::class, 'updateOrder'])->name('orders.updateOrder');
    Route::post('/orders/{order}/corrections', [\App\Http\Controllers\OrderCorrectionController::class, 'store'])->name('orders.corrections.store');
    Route::post('/corrections/{correction}/approve', [\App\Http\Controllers\OrderCorrectionController::class, 'approve'])->name('corrections.approve');
    Route::post('/corrections/{correction}/reject', [\App\Http\Controllers\OrderCorrectionController::class, 'reject'])->name('corrections.reject');
    Route::post('/orders/bulk-status', [OrderController::class, 'bulkStatus'])->name('orders.bulkStatus');
    Route::get('/orders/{order:id}/print', [OrderController::class, 'printBill'])->name('orders.print');
    Route::get('/orders/{order:id}/chit', [OrderController::class, 'printChit'])->name('orders.printChit');
    Route::get('/orders/{order:id}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::post('/orders/{order:id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::post('/orders/{order:id}/reverse', [OrderController::class, 'reverseOrder'])->name('orders.reverseOrder');
    Route::post('/orders/{order:id}/dispatch-delivery', [OrderController::class, 'dispatchDelivery'])->name('orders.dispatchDelivery');
    Route::post('/orders/{order:id}/request-payment', [OrderController::class, 'requestPayment'])->name('orders.requestPayment');
    Route::post('/orders/{order:id}/credit-payment', [OrderController::class, 'collectCreditPayment'])->name('orders.collectCreditPayment');
    Route::post('/orders/{order}/payments/{transaction}/approve', [OrderController::class, 'approvePayment'])->name('orders.payments.approve');
    Route::post('/orders/{order}/payments/{transaction}/reject', [OrderController::class, 'rejectPayment'])->name('orders.payments.reject');
    // Workshop dispatch from order
    Route::post('/orders/{order}/dispatch-workshop', [WorkshopController::class, 'dispatch'])->name('orders.dispatchWorkshop');
    Route::post('/orders/workshop/bulk-dispatch', [WorkshopController::class, 'bulkDispatch'])->name('orders.bulkDispatchWorkshop');
    // Order Management
    Route::get('/orders/archive', [App\Http\Controllers\OrderArchiveController::class, 'index'])->name('orders.archive');
    Route::get('/orders/search', [OrderController::class, 'search'])->name('orders.search');
    Route::get('/orders/view/{order_number}', [OrderController::class, 'showByNumber'])->name('orders.show');
    Route::resource('orders', OrderController::class)->only(['index', 'edit']);

    Route::post('/packages/{customerPackage}/mark-paid', [PackageController::class, 'markPaid'])->name('packages.markPaid');
    Route::get('/packages/{customerPackage}/print', [PackageController::class, 'printReceipt'])->name('packages.print');
    Route::resource('packages', PackageController::class)->except(['create', 'edit', 'update']);



    // Note: payment_methods are managed via PaymentSettingsController (settings page)

    Route::resource('expenses', ExpenseController::class)->except(['create', 'show']);
    Route::post('/petty-cash/topup', [ExpenseController::class, 'topup'])->name('petty_cash.topup');

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
        Route::get('/operations', [WorkshopController::class, 'operations'])->name('operations');
        Route::get('/history', [WorkshopController::class, 'history'])->name('history');
        Route::post('/dispatches/{dispatch}/receive', [WorkshopController::class, 'receive'])->name('receive');
        Route::post('/dispatches/{dispatch}/start-processing', [WorkshopController::class, 'startProcessing'])->name('startProcessing');
        Route::post('/dispatches/{dispatch}/complete', [WorkshopController::class, 'complete'])->name('complete');
        Route::post('/dispatches/{dispatch}/handover', [WorkshopController::class, 'handover'])->name('handover');
        Route::post('/dispatches/{dispatch}/return', [WorkshopController::class, 'returnToBranch'])->name('return');
        Route::get('/orders/{order_number}/finalize-bill', [WorkshopController::class, 'finalizeBill'])->name('finalizeBill');
        Route::post('/dispatches/{dispatch}/delivery', [WorkshopController::class, 'dispatchDelivery'])->name('dispatchDelivery');
        Route::post('/dispatches/{dispatch}/request-payment', [WorkshopController::class, 'requestPayment'])->name('requestPayment');
        Route::post('/dispatches/{dispatch}/approve-payment', [WorkshopController::class, 'approvePayment'])->name('approvePayment');
        Route::post('/bulk-status', [WorkshopController::class, 'bulkStatus'])->name('bulkStatus');
    });

    // ─── DELIVERY (BRANCH ADMIN & STAFF & WORKSHOP ADMIN) ───
    // Authorization is handled inside DeliveryController
    Route::get('/delivery', [DeliveryController::class, 'index'])->name('delivery.index');
    Route::get('/delivery/{appointment}/confirm', [DeliveryController::class, 'confirm'])->name('delivery.confirm');
    Route::post('/delivery/{appointment}/confirm-action', [DeliveryController::class, 'confirmAction'])->name('delivery.confirmAction');
    Route::post('/delivery/{appointment}/cancel', [DeliveryController::class, 'cancel'])->name('delivery.cancel');
    Route::post('/delivery/{appointment}/assign-rider', [DeliveryController::class, 'assignRider'])->name('delivery.assignRider');
    Route::post('/delivery/{appointment}/at-branch', [DeliveryController::class, 'markAtBranch'])->name('delivery.atBranch');
    Route::post('/delivery/{appointment}/assign-delivery-rider', [DeliveryController::class, 'assignDeliveryRider'])->name('delivery.assignDeliveryRider');
    Route::post('/delivery/{appointment}/collect-cash', [DeliveryController::class, 'collectCash'])->name('delivery.collectCash');

    // ─── INVENTORY (WORKSHOP ADMIN) ───
    Route::middleware(['workshop'])->group(function () {
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/bulk-restock', [InventoryController::class, 'bulkStockIn'])->name('inventory.bulkStockIn');
        Route::post('/inventory/bulk-stock-out', [InventoryController::class, 'bulkStockOut'])->name('inventory.bulkStockOut');
        Route::post('/inventory/{item}/stock-out', [InventoryController::class, 'stockOut'])->name('inventory.stockOut');
        Route::delete('/inventory/{item}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    });

    // ─── ADMIN + WORKSHOP_ADMIN SETTINGS ───
    Route::middleware(['admin'])->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.updateGeneral');

            // Payment Settings
            Route::post('/settings/payment-methods', [\App\Http\Controllers\PaymentSettingsController::class, 'update'])->name('settings.payment-methods.update');

            // Notifications Settings
            Route::post('/settings/notifications', [\App\Http\Controllers\SettingsController::class, 'updateNotifications'])->name('settings.notifications.update');



        Route::post('/settings/cubbies', [SettingsController::class, 'storeCubby'])->name('settings.storeCubby');
        Route::put('/settings/cubbies/{cubby}', [SettingsController::class, 'updateCubby'])->name('settings.updateCubby');
        Route::delete('/settings/cubbies/{cubby}', [SettingsController::class, 'destroyCubby'])->name('settings.destroyCubby');

        Route::post('/settings/inventory', [SettingsController::class, 'storeInventory'])->name('settings.storeInventory');
        Route::post('/settings/inventory/{item}/stock-in', [SettingsController::class, 'stockIn'])->name('settings.stockIn');

        Route::post('/settings/services/reorder', [SettingsController::class, 'reorderServices'])->name('settings.reorderServices');
        Route::post('/settings/services/reorder', [SettingsController::class, 'reorderServices'])->name('settings.reorderServices');
        Route::post('/settings/service-categories/reorder', [SettingsController::class, 'reorderCategories'])->name('settings.reorderCategories');

        // Services, Categories, Packages, Extras, Turnaround, Delivery & Pickup Settings (Branch & Workshop Admins)
        Route::get('/settings/services', [SettingsController::class, 'servicesManager'])->name('settings.services');
        Route::post('/settings/services', [SettingsController::class, 'storeService'])->name('settings.storeService');
        Route::put('/settings/services/{service}', [SettingsController::class, 'updateService'])->name('settings.updateService');
        Route::delete('/settings/services/{service}', [SettingsController::class, 'destroyService'])->name('settings.destroyService');

        Route::post('/settings/service-categories', [SettingsController::class, 'storeServiceCategory'])->name('settings.storeServiceCategory');
        Route::put('/settings/service-categories/{category}', [SettingsController::class, 'updateServiceCategory'])->name('settings.updateServiceCategory');
        Route::delete('/settings/service-categories/{category}', [SettingsController::class, 'destroyServiceCategory'])->name('settings.destroyServiceCategory');

        Route::post('/settings/packages', [SettingsController::class, 'storePackage'])->name('settings.storePackage');
        Route::put('/settings/packages/{package}', [SettingsController::class, 'updatePackage'])->name('settings.updatePackage');
        Route::delete('/settings/packages/{package}', [SettingsController::class, 'destroyPackage'])->name('settings.destroyPackage');

        // Extras
        Route::post('/settings/extras', [SettingsController::class, 'storeExtra'])->name('settings.storeExtra');
        Route::put('/settings/extras/{extra}', [SettingsController::class, 'updateExtra'])->name('settings.updateExtra');
        Route::delete('/settings/extras/{extra}', [SettingsController::class, 'destroyExtra'])->name('settings.destroyExtra');

        // Turnaround Types
        Route::post('/settings/turnaround-types', [SettingsController::class, 'storeTurnaroundType'])->name('settings.storeTurnaroundType');
        Route::put('/settings/turnaround-types/{turnaroundType}', [SettingsController::class, 'updateTurnaroundType'])->name('settings.updateTurnaroundType');
        Route::delete('/settings/turnaround-types/{turnaroundType}', [SettingsController::class, 'destroyTurnaroundType'])->name('settings.destroyTurnaroundType');
        
        Route::post('/settings/delivery-global', [SettingsController::class, 'updateDeliverySettings'])->name('settings.updateDeliverySettings');
        Route::post('/settings/delivery-ranges', [SettingsController::class, 'storeDeliveryRange'])->name('settings.storeDeliveryRange');
        Route::delete('/settings/delivery-ranges/{deliveryRange}', [SettingsController::class, 'deleteDeliveryRange'])->name('settings.deleteDeliveryRange');
        
        Route::post('/settings/pickup-time-periods', [SettingsController::class, 'storePickupTimePeriod'])->name('settings.storePickupTimePeriod');
        Route::delete('/settings/pickup-time-periods/{pickupTimePeriod}', [SettingsController::class, 'deletePickupTimePeriod'])->name('settings.deletePickupTimePeriod');

        // Super Admin Only Settings Routes
        Route::middleware(['role:super_admin'])->group(function () {
            Route::get('/branch-logs', [\App\Http\Controllers\BranchLogController::class, 'index'])->name('superadmin.branch-logs');

            Route::get('/sms-templates', [SmsTemplateController::class, 'index'])->name('sms-templates.index');
            Route::put('/sms-templates/{template}', [SmsTemplateController::class, 'update'])->name('sms-templates.update');
            Route::post('/sms-templates/toggle', [SmsTemplateController::class, 'toggleGlobal'])->name('sms-templates.toggleGlobal');
            Route::post('/sms-templates/seed', [SmsTemplateController::class, 'seedDefault'])->name('sms-templates.seedDefault');

            Route::get('/whatsapp-templates', [WhatsappTemplateController::class, 'index'])->name('whatsapp-templates.index');
            Route::put('/whatsapp-templates/{template}', [WhatsappTemplateController::class, 'update'])->name('whatsapp-templates.update');
            Route::post('/whatsapp-templates/toggle', [WhatsappTemplateController::class, 'toggleGlobal'])->name('whatsapp-templates.toggleGlobal');
            Route::post('/whatsapp-templates/seed', [WhatsappTemplateController::class, 'seedDefault'])->name('whatsapp-templates.seedDefault');

            Route::get('/email-templates', [EmailTemplateController::class, 'index'])->name('email-templates.index');
            Route::put('/email-templates/{template}', [EmailTemplateController::class, 'update'])->name('email-templates.update');
            Route::post('/email-templates/toggle', [EmailTemplateController::class, 'toggleGlobal'])->name('email-templates.toggleGlobal');
            Route::post('/email-templates/seed', [EmailTemplateController::class, 'seedDefault'])->name('email-templates.seedDefault');

            Route::post('/promotions/reorder', [App\Http\Controllers\PromotionController::class, 'reorder'])->name('promotions.reorder');
            Route::resource('promotions', App\Http\Controllers\PromotionController::class)->except(['create', 'show', 'edit']);
        });

        Route::post('/settings/cubbies', [SettingsController::class, 'storeCubby'])->name('settings.storeCubby');
        Route::put('/settings/cubbies/{cubby}', [SettingsController::class, 'updateCubby'])->name('settings.updateCubby');
        Route::delete('/settings/cubbies/{cubby}', [SettingsController::class, 'destroyCubby'])->name('settings.destroyCubby');

        Route::post('/settings/inventory', [SettingsController::class, 'storeInventory'])->name('settings.storeInventory');
        Route::post('/settings/inventory/{item}/stock-in', [SettingsController::class, 'stockIn'])->name('settings.stockIn');

        // HR Routes
        Route::get('/hr/dashboard', [HRDashboardController::class, 'index'])->name('hr.dashboard');
        Route::get('/hr/dashboard/pdf', [HRDashboardController::class, 'exportPdf'])->name('hr.dashboard.pdf');
        Route::post('/hr/{staff}/attendance', [HRController::class, 'storeAttendance'])->name('hr.attendance.store');
        Route::post('/hr/{staff}/attendance/bulk', [HRController::class, 'bulkUpdateAttendance'])->name('hr.attendance.bulkUpdate');
        Route::put('/hr/attendance/{attendance}', [HRController::class, 'updateAttendance'])->name('hr.attendance.update');
        Route::post('/hr/{staff}/leaves', [HRController::class, 'storeLeave'])->name('hr.leaves.store');
        Route::put('/hr/leaves/{leave}', [HRController::class, 'updateLeave'])->name('hr.leaves.update');
        Route::post('/hr/{staff}/advances', [HRController::class, 'storeAdvance'])->name('hr.advances.store');
        Route::post('/hr/{staff}/adjustments', [HRController::class, 'storeAdjustment'])->name('hr.adjustments.store');
        Route::delete('/hr/adjustments/{adjustment}', [HRController::class, 'destroyAdjustment'])->name('hr.adjustments.destroy');
        Route::get('/hr/{staff}/payslip', [StaffController::class, 'downloadPayslip'])->name('hr.payslip.download');

        // Branches
        Route::resource('branches', BranchController::class)->except(['show', 'edit']);
        Route::get('/branch/month-plan', [BranchPlanController::class, 'index'])->name('branch.month_plan');
        Route::post('/branch/month-plan', [BranchPlanController::class, 'store'])->name('branch.month_plan.store');
        Route::post('/workshop/dispatches/{dispatch}/received-at-branch', [WorkshopController::class, 'receivedAtBranch'])->name('workshop.receivedAtBranch');
        Route::post('/staff/pay-salary', [StaffController::class, 'paySalary'])->name('staff.paySalary');
        Route::resource('staff', StaffController::class);

        // Reports Routes (Accessible by admin, branch_admin, workshop_admin, super_admin, staff)
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
        
        Route::get('/reports/sales', [ReportController::class, 'salesReport'])->name('reports.sales');
        Route::get('/reports/sales/pdf', [ReportController::class, 'salesReportPdf'])->name('reports.sales.pdf');
        
        Route::get('/reports/services', [ReportController::class, 'serviceReport'])->name('reports.services');
        Route::get('/reports/services/pdf', [ReportController::class, 'serviceReportPdf'])->name('reports.services.pdf');
        
        Route::get('/reports/customers', [ReportController::class, 'customerReport'])->name('reports.customers');
        Route::get('/reports/customers/pdf', [ReportController::class, 'customerReportPdf'])->name('reports.customers.pdf');

        Route::get('/reports/close-shift', [ReportController::class, 'closeShiftReport'])->name('reports.closeShift');
        Route::post('/reports/packages/{customerPackage}/approve-payment', [ReportController::class, 'approvePackagePayment'])->name('reports.approvePackagePayment');
    });
});

require __DIR__.'/auth.php';

// SMS Webhook Route
Route::post('/sms/callback', [\App\Http\Controllers\SmsWebhookController::class, 'handleCallback']);

