<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerSiteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OnlineBookingController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Workshop\WorkshopDashboardController;
use App\Http\Controllers\Workshop\WorkshopOperationsController;
use App\Http\Controllers\Workshop\WorkshopDispatchController;
use App\Http\Controllers\Workshop\WorkshopBillingController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

// ─── Super Admin SaaS Platform Routes ───
require __DIR__ . '/super_admin.php';

// ─── Dynamic Tenant PWA Manifest ───
Route::get('/manifest.json', [\App\Http\Controllers\PwaManifestController::class, 'manifest'])->name('pwa.manifest');

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
Route::get('/about', fn() => view()->exists('website.pages.about') ? view('website.pages.about') : redirect()->route('login'))->name('site.about');
Route::get('/faq', fn() => view()->exists('website.pages.faq') ? view('website.pages.faq') : redirect()->route('login'))->name('site.faq');
Route::get('/how-it-works', fn() => view()->exists('website.pages.how-it-works') ? view('website.pages.how-it-works') : redirect()->route('login'))->name('site.how-it-works');
Route::get('/terms', fn() => view()->exists('website.pages.terms') ? view('website.pages.terms') : redirect()->route('login'))->name('site.terms');
Route::get('/privacy', fn() => view()->exists('website.pages.privacy') ? view('website.pages.privacy') : redirect()->route('login'))->name('site.privacy');
Route::get('/quality', fn() => view()->exists('website.pages.quality') ? view('website.pages.quality') : redirect()->route('login'))->name('site.quality');

Route::get('/storage/receipts/{filename}', function ($filename) {
    $path = storage_path('app/public/receipts/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path);
});

// ─── Customer Auth Disabled (Redirect to Staff Login) ───
Route::get('/customer/login', fn() => redirect()->route('login'))->name('customer.login');
Route::get('/customer/register', fn() => redirect()->route('login'))->name('customer.register');
Route::post('/customer/login', fn() => redirect()->route('login'))->name('customer.login.submit');
Route::post('/customer/register', fn() => redirect()->route('login'))->name('customer.register.submit');
Route::get('/customer/forgot-password', fn() => redirect()->route('login'))->name('customer.password.request');

Route::middleware([\App\Http\Middleware\CheckDeliveryEnabled::class])->group(function () {
    Route::post('/customer/logout', [\App\Http\Controllers\Customer\AuthController::class, 'logout'])->name('customer.logout');
    Route::post('/customer/forgot-password', [\App\Http\Controllers\Customer\ForgotPasswordController::class, 'sendOtp'])->name('customer.password.email');
    Route::get('/customer/verify-otp', [\App\Http\Controllers\Customer\ForgotPasswordController::class, 'showOtpForm'])->name('customer.password.verifyForm');
    Route::post('/customer/verify-otp', [\App\Http\Controllers\Customer\ForgotPasswordController::class, 'verifyOtp'])->name('customer.password.verifyOtp');
    Route::get('/customer/reset-password', [\App\Http\Controllers\Customer\ForgotPasswordController::class, 'showResetForm'])->name('customer.password.resetForm');
    Route::post('/customer/reset-password', [\App\Http\Controllers\Customer\ForgotPasswordController::class, 'resetPassword'])->name('customer.password.update');

    Route::post('/customer/ajax-login', [\App\Http\Controllers\Customer\AuthController::class, 'ajaxLogin'])->name('customer.ajax.login')->middleware('throttle:10,1');
    Route::post('/customer/ajax-register', [\App\Http\Controllers\Customer\AuthController::class, 'ajaxRegister'])->name('customer.ajax.register')->middleware('throttle:5,1');

    Route::get('/customer/book', [CustomerSiteController::class, 'booking'])->name('customer.booking');
    Route::post('/customer/book', [CustomerSiteController::class, 'store'])->name('customer.booking.store');
    Route::get('/customer/book/success/{order:order_number}', [CustomerSiteController::class, 'success'])->name('customer.booking.success');
    Route::get('/customer/branch-services/{branch}', [CustomerSiteController::class, 'getBranchServices'])->name('customer.branch_services');

    Route::middleware('auth:customer')->prefix('customer')->name('customer.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Customer\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/orders', [\App\Http\Controllers\Customer\DashboardController::class, 'orders'])->name('orders');
        Route::get('/orders/{order:order_number}', [\App\Http\Controllers\Customer\DashboardController::class, 'showOrder'])->name('orders.show');
        Route::post('/orders/{order:order_number}/payment', [\App\Http\Controllers\Customer\DashboardController::class, 'submitPayment'])->name('orders.submitPayment');
        Route::get('/orders/{order:order_number}/pay', [\App\Http\Controllers\Customer\DashboardController::class, 'pay'])->name('orders.pay');

        Route::get('/appointments/{appointment}', function (\App\Models\Appointment $appointment) {
            if (!$appointment->order_number) {
                return redirect()->route('customer.booking.success', $appointment->id);
            }
            return app(\App\Http\Controllers\Customer\DashboardController::class)->showAppointment($appointment);
        })->name('appointments.show');

        Route::get('/orders/{order:order_number}/pay-gateway', [\App\Http\Controllers\PaymentController::class, 'checkout'])->name('payment.checkout');
        Route::post('/payment/webhook', [\App\Http\Controllers\PaymentController::class, 'webhook'])->name('payment.webhook');
        Route::get('/orders/{order:order_number}/payment-success', [\App\Http\Controllers\PaymentController::class, 'success'])->name('payment.success');
    });
});

Route::get('/orders/{order}/track', fn(Order $order) => view('orders.track', compact('order')))->name('orders.track');

Route::middleware(['auth'])->group(function () {
    Route::get('/notifications/check', [\App\Http\Controllers\NotificationController::class, 'check'])->name('notifications.check');
    Route::post('/notifications/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/accountant/dashboard', [\App\Http\Controllers\AccountantDashboardController::class, 'index'])->name('accountant.dashboard')->middleware(['role:super_admin,accountant,accountant_and_hr_manager']);
    Route::post('/shifts/open', [DashboardController::class, 'openShift'])->name('shifts.open');
    Route::post('/shifts/attendance/update', [DashboardController::class, 'updateAttendance'])->name('shifts.attendance.update');
    Route::post('/shifts/close', [DashboardController::class, 'closeShift'])->name('shifts.close');
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
        Route::get('/orders/{order:id}/print', [\App\Http\Controllers\OrderPrintController::class, 'printBill'])->name('orders.print');
        Route::get('/orders/{order:id}/chit', [\App\Http\Controllers\OrderPrintController::class, 'printChit'])->name('orders.printChit');
        Route::get('/orders/{order:id}/label', [\App\Http\Controllers\OrderPrintController::class, 'printLabel'])->name('orders.printLabel');
        Route::get('/orders/{order:id}/invoice', [\App\Http\Controllers\OrderPrintController::class, 'invoice'])->name('orders.invoice');
        Route::post('/orders/{order:id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
        Route::post('/orders/{order:id}/request-payment', [OrderController::class, 'requestPayment'])->name('orders.requestPayment');
        Route::post('/orders/{order:id}/credit-payment', [OrderController::class, 'collectCreditPayment'])->name('orders.collectCreditPayment');
        Route::post('/orders/{order:id}/refund-overpayment', [OrderController::class, 'refundOverpayment'])->name('orders.refundOverpayment')->middleware('throttle:10,1');
        Route::post('/orders/{order}/payments/{transaction}/approve', [OrderController::class, 'approvePayment'])->name('orders.payments.approve')->middleware('throttle:10,1');
        Route::post('/orders/{order}/payments/{transaction}/reject', [OrderController::class, 'rejectPayment'])->name('orders.payments.reject')->middleware('throttle:10,1');
        Route::post('/orders/{order}/dispatch-workshop', [WorkshopDispatchController::class, 'dispatch'])->name('orders.dispatchWorkshop');
        Route::post('/orders/workshop/bulk-dispatch', [WorkshopDispatchController::class, 'bulkDispatch'])->name('orders.bulkDispatchWorkshop');
        Route::get('/workshop/orders/{order_number}/finalize-bill', [WorkshopBillingController::class, 'finalizeBill'])->name('workshop.finalizeBill')->middleware('admin');

        Route::get('/orders/history', [\App\Http\Controllers\OrderHistoryController::class, 'index'])->name('orders.history');
        Route::get('/orders/archive', [App\Http\Controllers\OrderArchiveController::class, 'index'])->name('orders.archive');
        Route::get('/orders/search', [\App\Http\Controllers\OrderSearchController::class, 'search'])->name('orders.search');
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

    Route::middleware(['workshop'])->group(function () {
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/bulk-restock', [InventoryController::class, 'bulkStockIn'])->name('inventory.bulkStockIn');
        Route::post('/inventory/bulk-stock-out', [InventoryController::class, 'bulkStockOut'])->name('inventory.bulkStockOut');
        Route::post('/inventory/{item}/stock-out', [InventoryController::class, 'stockOut'])->name('inventory.stockOut');
        Route::put('/inventory/{item}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::delete('/inventory/{item}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    });

    require __DIR__ . '/settings.php';
    require __DIR__ . '/admin_extra.php';
});

require __DIR__ . '/auth.php';

Route::post('/sms/callback', [\App\Http\Controllers\SmsWebhookController::class, 'handleCallback']);
