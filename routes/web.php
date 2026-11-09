<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\BranchPlanController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerSiteController;
use App\Http\Controllers\DashboardController;
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
use App\Http\Controllers\Reports\MainReportController;
use App\Http\Controllers\Reports\SalesReportController;
use App\Http\Controllers\Reports\ServiceReportController;
use App\Http\Controllers\Reports\CustomerReportController;
use App\Http\Controllers\Reports\ShiftReportController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SmsTemplateController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\Workshop\WorkshopDashboardController;
use App\Http\Controllers\Workshop\WorkshopOperationsController;
use App\Http\Controllers\Workshop\WorkshopDispatchController;
use App\Http\Controllers\Workshop\WorkshopBillingController;
use App\Http\Controllers\Settings\GeneralSettingsController;
use App\Http\Controllers\Settings\ServiceSettingsController;
use App\Http\Controllers\Settings\LogisticsSettingsController;
use App\Http\Controllers\Settings\PackageSettingsController;
use App\Http\Controllers\Settings\InventorySettingsController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

// ─── Super Admin SaaS Platform Routes ───
require __DIR__ . '/super_admin.php';

// ─── App Redirect ───
Route::redirect('/app', '/stafflogin');
Route::redirect('/login', '/stafflogin');

// ─── Public Payment Link ───
Route::get('/pay/{token}', [\App\Http\Controllers\PaymentLinkController::class, 'show'])->name('payment.link.show');
Route::post('/pay/{token}/bank-transfer', [\App\Http\Controllers\PaymentLinkController::class, 'submitBankTransfer'])->name('payment.link.submit');
Route::post('/pay/{token}/webhook', [\App\Http\Controllers\PaymentLinkController::class, 'webhook'])->name('payment.link.webhook');

// ─── Root → Public Website Home ───
Route::get('/', [CustomerSiteController::class, 'home'])->name('home');

// ─── Public Website Pages ───
Route::get('/about', function () {
    return view()->exists('website.pages.about') ? view('website.pages.about') : redirect()->route('customer.login'); })->name('site.about');
Route::get('/faq', function () {
    return view()->exists('website.pages.faq') ? view('website.pages.faq') : redirect()->route('customer.login'); })->name('site.faq');
Route::get('/how-it-works', function () {
    return view()->exists('website.pages.how-it-works') ? view('website.pages.how-it-works') : redirect()->route('customer.login'); })->name('site.how-it-works');
Route::get('/terms', function () {
    return view()->exists('website.pages.terms') ? view('website.pages.terms') : redirect()->route('customer.login'); })->name('site.terms');
Route::get('/privacy', function () {
    return view()->exists('website.pages.privacy') ? view('website.pages.privacy') : redirect()->route('customer.login'); })->name('site.privacy');
Route::get('/quality', function () {
    return view()->exists('website.pages.quality') ? view('website.pages.quality') : redirect()->route('customer.login'); })->name('site.quality');


// Fix for Windows PHP built-in server symlink 403 error
Route::get('/storage/receipts/{filename}', function ($filename) {
    $path = storage_path('app/public/receipts/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path);
});


// ─── Customer Auth & Dashboard Routes ───
Route::middleware([\App\Http\Middleware\CheckDeliveryEnabled::class])->group(function () {
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

    // AJAX Auth for Wizard — rate limited to prevent brute force
    Route::post('/customer/ajax-login', [\App\Http\Controllers\Customer\AuthController::class, 'ajaxLogin'])->name('customer.ajax.login')->middleware('throttle:10,1');
    Route::post('/customer/ajax-register', [\App\Http\Controllers\Customer\AuthController::class, 'ajaxRegister'])->name('customer.ajax.register')->middleware('throttle:5,1');

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
});

// ─── Public Order Tracking (QR Code link) ───
Route::get('/orders/{order}/track', function (Order $order) {
    return view('orders.track', compact('order'));
})->name('orders.track');



Route::middleware(['auth'])->group(function () {
    Route::get('/notifications/check', [\App\Http\Controllers\NotificationController::class, 'check'])->name('notifications.check');
    Route::post('/notifications/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/accountant/dashboard', [\App\Http\Controllers\AccountantDashboardController::class, 'index'])->name('accountant.dashboard')->middleware(['role:super_admin,accountant,accountant_and_hr_manager']);
    Route::post('/shifts/open', [\App\Http\Controllers\DashboardController::class, 'openShift'])->name('shifts.open');
    Route::post('/shifts/attendance/update', [\App\Http\Controllers\DashboardController::class, 'updateAttendance'])->name('shifts.attendance.update');
    Route::post('/shifts/close', [\App\Http\Controllers\DashboardController::class, 'closeShift'])->name('shifts.close');
    Route::post('/shifts/reopen-request', [\App\Http\Controllers\BranchReopenRequestController::class, 'store'])->name('shifts.reopen.request');

    Route::post('/bank-deposits', [\App\Http\Controllers\BankDepositController::class, 'store'])->name('bank-deposits.store');
    Route::put('/bank-deposits/{bankDeposit}', [\App\Http\Controllers\BankDepositController::class, 'update'])->name('bank-deposits.update');
    Route::delete('/bank-deposits/{bankDeposit}', [\App\Http\Controllers\BankDepositController::class, 'destroy'])->name('bank-deposits.destroy');

    Route::middleware(['shift.open'])->group(function () {
        Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
        Route::post('/pos', [POSController::class, 'store'])->name('pos.store');

        Route::post('/customers/pos', [CustomerController::class, 'store'])->name('customers.store.pos');
        Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');
        Route::get('/customers/{customer}/sales-report/pdf', [CustomerController::class, 'salesReportPdf'])->name('customers.salesReport.pdf');
        Route::post('/customers/{customer}/bulk-credit-payment', [CustomerController::class, 'bulkCreditPayment'])->name('customers.bulkCreditPayment');
        Route::get('/customers/top', [\App\Http\Controllers\TopCustomersController::class, 'index'])->name('customers.top');
        Route::resource('customers', CustomerController::class);

        Route::post('/orders/{order:id}/update', [OrderController::class, 'updateOrder'])->name('orders.updateOrder');
        Route::post('/orders/{order}/corrections', [\App\Http\Controllers\OrderCorrectionController::class, 'store'])->name('orders.corrections.store')->middleware('throttle:10,1');
        Route::post('/corrections/{correction}/approve', [\App\Http\Controllers\OrderCorrectionController::class, 'approve'])->name('corrections.approve')->middleware('throttle:10,1');
        Route::post('/corrections/{correction}/reject', [\App\Http\Controllers\OrderCorrectionController::class, 'reject'])->name('corrections.reject')->middleware('throttle:10,1');
        Route::post('/orders/bulk-status', [OrderController::class, 'bulkStatus'])->name('orders.bulkStatus');
        Route::get('/orders/{order:id}/print', [OrderController::class, 'printBill'])->name('orders.print');
        Route::get('/orders/{order:id}/chit', [OrderController::class, 'printChit'])->name('orders.printChit');
        Route::get('/orders/{order:id}/label', [OrderController::class, 'printLabel'])->name('orders.printLabel');
        Route::get('/orders/{order:id}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
        Route::post('/orders/{order:id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
        Route::post('/orders/{order:id}/request-payment', [OrderController::class, 'requestPayment'])->name('orders.requestPayment');
        Route::post('/orders/{order:id}/credit-payment', [OrderController::class, 'collectCreditPayment'])->name('orders.collectCreditPayment');
        Route::post('/orders/{order:id}/refund-overpayment', [OrderController::class, 'refundOverpayment'])->name('orders.refundOverpayment')->middleware('throttle:10,1');
        Route::post('/orders/{order}/payments/{transaction}/approve', [OrderController::class, 'approvePayment'])->name('orders.payments.approve')->middleware('throttle:10,1');
        Route::post('/orders/{order}/payments/{transaction}/reject', [OrderController::class, 'rejectPayment'])->name('orders.payments.reject')->middleware('throttle:10,1');
        // Workshop dispatch from order
        Route::post('/orders/{order}/dispatch-workshop', [WorkshopDispatchController::class, 'dispatch'])->name('orders.dispatchWorkshop');
        Route::post('/orders/workshop/bulk-dispatch', [WorkshopDispatchController::class, 'bulkDispatch'])->name('orders.bulkDispatchWorkshop');
        // Workshop finalize bill - accessible by branch_admin, workshop_admin, super_admin
        Route::get('/workshop/orders/{order_number}/finalize-bill', [WorkshopBillingController::class, 'finalizeBill'])->name('workshop.finalizeBill')->middleware('admin');

        // Order Management
        Route::get('/orders/history', [OrderController::class, 'history'])->name('orders.history');
        Route::get('/orders/archive', [App\Http\Controllers\OrderArchiveController::class, 'index'])->name('orders.archive');
        Route::get('/orders/search', [OrderController::class, 'search'])->name('orders.search');
        Route::get('/orders/view/{order_number}', [OrderController::class, 'showByNumber'])->name('orders.show');
        Route::resource('orders', OrderController::class)->only(['index', 'edit']);
        Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

        Route::resource('expenses', ExpenseController::class)->except(['create', 'show']);
        Route::post('/petty-cash/topup', [ExpenseController::class, 'topup'])->name('petty_cash.topup');

        Route::get('/online-bookings', [OnlineBookingController::class, 'index'])->name('online_bookings.index');
        Route::post('/online-bookings/{booking}/status', [OnlineBookingController::class, 'updateStatus'])->name('online_bookings.updateStatus');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ─── WORKSHOP ROUTES ───
    Route::middleware(['workshop'])->prefix('workshop')->name('workshop.')->group(function () {
        Route::get('/dashboard', [WorkshopDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/operations', [WorkshopOperationsController::class, 'operations'])->name('operations');
        Route::get('/history', fn() => redirect()->route('orders.history', request()->all()))->name('history');
        Route::post('/dispatches/{dispatch}/receive', [WorkshopOperationsController::class, 'receive'])->name('receive');
        Route::post('/dispatches/{dispatch}/start-processing', [WorkshopOperationsController::class, 'startProcessing'])->name('startProcessing');
        Route::post('/dispatches/{dispatch}/complete', [WorkshopOperationsController::class, 'complete'])->name('complete');
        Route::post('/dispatches/{dispatch}/handover', [WorkshopOperationsController::class, 'handover'])->name('handover');
        Route::post('/dispatches/{dispatch}/return', [WorkshopOperationsController::class, 'returnToBranch'])->name('return');
        Route::post('/dispatches/{dispatch}/request-payment', [WorkshopBillingController::class, 'requestPayment'])->name('requestPayment');
        Route::post('/dispatches/{dispatch}/approve-payment', [WorkshopBillingController::class, 'approvePayment'])->name('approvePayment');
        Route::post('/bulk-status', [WorkshopOperationsController::class, 'bulkStatus'])->name('bulkStatus');
    });


    // ─── INVENTORY (WORKSHOP ADMIN) ───
    Route::middleware(['workshop'])->group(function () {
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/bulk-restock', [InventoryController::class, 'bulkStockIn'])->name('inventory.bulkStockIn');
        Route::post('/inventory/bulk-stock-out', [InventoryController::class, 'bulkStockOut'])->name('inventory.bulkStockOut');
        Route::post('/inventory/{item}/stock-out', [InventoryController::class, 'stockOut'])->name('inventory.stockOut');
        Route::put('/inventory/{item}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::delete('/inventory/{item}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    });

    // ─── ADMIN + WORKSHOP_ADMIN SETTINGS ───
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

        // Removed payment methods and notifications to super-admin group

        // Cubbies
        Route::post('/settings/cubbies', [LogisticsSettingsController::class, 'storeCubby'])->name('settings.storeCubby');
        Route::put('/settings/cubbies/{cubby}', [LogisticsSettingsController::class, 'updateCubby'])->name('settings.updateCubby');
        Route::delete('/settings/cubbies/{cubby}', [LogisticsSettingsController::class, 'destroyCubby'])->name('settings.destroyCubby');

        // Inventory
        Route::post('/settings/inventory', [InventorySettingsController::class, 'storeInventory'])->name('settings.storeInventory');
        Route::post('/settings/inventory/{item}/stock-in', [InventorySettingsController::class, 'stockIn'])->name('settings.stockIn');

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
        Route::post('/settings/extras', [ServiceSettingsController::class, 'storeExtra'])->name('settings.storeExtra');
        Route::put('/settings/extras/{extra}', [ServiceSettingsController::class, 'updateExtra'])->name('settings.updateExtra');
        Route::delete('/settings/extras/{extra}', [ServiceSettingsController::class, 'destroyExtra'])->name('settings.destroyExtra');

        // Turnaround Types
        Route::post('/settings/turnaround-types', [ServiceSettingsController::class, 'storeTurnaroundType'])->name('settings.storeTurnaroundType');
        Route::put('/settings/turnaround-types/{turnaroundType}', [ServiceSettingsController::class, 'updateTurnaroundType'])->name('settings.updateTurnaroundType');
        Route::delete('/settings/turnaround-types/{turnaroundType}', [ServiceSettingsController::class, 'destroyTurnaroundType'])->name('settings.destroyTurnaroundType');

        // Logistics / Pickups
        Route::post('/settings/pickup-time-periods', [LogisticsSettingsController::class, 'storePickupTimePeriod'])->name('settings.storePickupTimePeriod');
        Route::delete('/settings/pickup-time-periods/{pickupTimePeriod}', [LogisticsSettingsController::class, 'deletePickupTimePeriod'])->name('settings.deletePickupTimePeriod');
        Route::post('/settings/delivery-ranges', [LogisticsSettingsController::class, 'storeDeliveryRange'])->name('settings.storeDeliveryRange');
        Route::delete('/settings/delivery-ranges/{deliveryRange}', [LogisticsSettingsController::class, 'deleteDeliveryRange'])->name('settings.deleteDeliveryRange');
        Route::post('/settings/delivery', [LogisticsSettingsController::class, 'updateDeliverySettings'])->name('settings.updateDeliverySettings');

        // Super Admin Only Settings Routes
        Route::middleware(['role:super_admin'])->group(function () {
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

        // HR Routes
        Route::get('/hr/dashboard', [HRDashboardController::class, 'index'])->name('hr.dashboard');
        Route::get('/hr/dashboard/pdf', [HRDashboardController::class, 'exportPdf'])->name('hr.dashboard.pdf');
        Route::post('/hr/{staff}/attendance', [HRController::class, 'storeAttendance'])->name('hr.attendance.store');
        Route::post('/hr/{staff}/attendance/bulk', [HRController::class, 'bulkUpdateAttendance'])->name('hr.attendance.bulkUpdate');
        Route::put('/hr/attendance/{attendance}', [HRController::class, 'updateAttendance'])->name('hr.attendance.update');
        Route::post('/hr/{staff}/leaves', [HRController::class, 'storeLeave'])->name('hr.leaves.store');
        Route::put('/hr/leaves/{leave}', [HRController::class, 'updateLeave'])->name('hr.leaves.update');
        Route::post('/hr/{staff}/advances', [HRController::class, 'storeAdvance'])->name('hr.advances.store');
        Route::put('/hr/advances/{advance}', [HRController::class, 'updateAdvance'])->name('hr.advances.update');
        Route::delete('/hr/advances/{advance}', [HRController::class, 'destroyAdvance'])->name('hr.advances.destroy');
        Route::post('/hr/{staff}/adjustments', [HRController::class, 'storeAdjustment'])->name('hr.adjustments.store');
        Route::delete('/hr/adjustments/{adjustment}', [HRController::class, 'destroyAdjustment'])->name('hr.adjustments.destroy');
        Route::get('/hr/{staff}/payslip', [StaffController::class, 'downloadPayslip'])->name('hr.payslip.download');

        // Branches
        Route::resource('branches', BranchController::class)->except(['show']);
        Route::get('/branch/month-plan', [BranchPlanController::class, 'index'])->name('branch.month_plan');
        Route::post('/branch/month-plan', [BranchPlanController::class, 'store'])->name('branch.month_plan.store');

        // Branch Logs & Shift Re-open Requests
        Route::get('/branch-logs', [\App\Http\Controllers\BranchLogController::class, 'index'])->name('superadmin.branch-logs');
        Route::redirect('/reopen-requests', '/branch-logs?tab=reopen_requests')->name('superadmin.reopen-requests');
        Route::post('/reopen-requests/{reopenRequest}/approve', [\App\Http\Controllers\BranchReopenRequestController::class, 'approve'])->name('superadmin.reopen-requests.approve');
        Route::post('/reopen-requests/{reopenRequest}/reject', [\App\Http\Controllers\BranchReopenRequestController::class, 'reject'])->name('superadmin.reopen-requests.reject');

        Route::post('/workshop/dispatches/{dispatch}/received-at-branch', [WorkshopDispatchController::class, 'receivedAtBranch'])->name('workshop.receivedAtBranch');
        Route::post('/staff/pay-salary', [StaffController::class, 'paySalary'])->name('staff.paySalary');
        Route::put('/staff/salaries/{expense}', [StaffController::class, 'updateSalary'])->name('staff.salaries.update');
        Route::delete('/staff/salaries/{expense}', [StaffController::class, 'destroySalary'])->name('staff.salaries.destroy');
        Route::resource('staff', StaffController::class);

        // ─── Reports Routes (Protected by shift.open) ───
        Route::middleware(['shift.open'])->group(function () {
            Route::get('/reports', [MainReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/pdf', [MainReportController::class, 'exportPdf'])->name('reports.pdf');
            Route::get('/reports/pending-orders', [\App\Http\Controllers\Reports\PendingOrdersReportController::class, 'index'])->name('reports.pending-orders');
            Route::get('/reports/active-orders-pdf', [\App\Http\Controllers\Reports\PendingOrdersReportController::class, 'exportPdf'])->name('reports.active-orders-pdf');

            Route::get('/reports/credit-orders', [\App\Http\Controllers\Reports\CreditReportController::class, 'index'])->name('reports.credit-orders');
            Route::get('/reports/credit-orders/pdf', [\App\Http\Controllers\Reports\CreditReportController::class, 'exportPdf'])->name('reports.credit-orders.pdf');
            Route::get('/reports/credit-orders/customer/{customerId}', [\App\Http\Controllers\Reports\CreditReportController::class, 'customerOrders'])->name('reports.credit-orders.customer');

            Route::get('/reports/pending-payments', [\App\Http\Controllers\Reports\PendingPaymentsReportController::class, 'index'])->name('reports.pending-payments');
            Route::get('/reports/pending-payments/pdf', [\App\Http\Controllers\Reports\PendingPaymentsReportController::class, 'exportPdf'])->name('reports.pending-payments.pdf');

            Route::get('/reports/sales', [SalesReportController::class, 'index'])->name('reports.sales');
            Route::get('/reports/sales/pdf', [SalesReportController::class, 'exportPdf'])->name('reports.sales.pdf');
            Route::get('/reports/sales/csv', [SalesReportController::class, 'exportCsv'])->name('reports.sales.csv');

            Route::get('/reports/services', [ServiceReportController::class, 'index'])->name('reports.services');
            Route::get('/reports/services/pdf', [ServiceReportController::class, 'exportPdf'])->name('reports.services.pdf');

            Route::get('/reports/customers', [CustomerReportController::class, 'index'])->name('reports.customers');
            Route::get('/reports/customers/pdf', [CustomerReportController::class, 'exportPdf'])->name('reports.customers.pdf');

            Route::get('/reports/close-shift', [ShiftReportController::class, 'index'])->name('reports.closeShift');
        });
    });
});

require __DIR__ . '/auth.php';

// SMS Webhook Route
Route::post('/sms/callback', [\App\Http\Controllers\SmsWebhookController::class, 'handleCallback']);

