<?php

use App\Http\Controllers\HRController;
use App\Http\Controllers\SalaryAdvanceAndAdjustmentController;
use App\Http\Controllers\HRDashboardController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BranchPlanController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\Workshop\WorkshopDispatchController;
use App\Http\Controllers\Reports\MainReportController;
use App\Http\Controllers\Reports\SalesReportController;
use App\Http\Controllers\Reports\ServiceReportController;
use App\Http\Controllers\Reports\CustomerReportController;
use App\Http\Controllers\Reports\ShiftReportController;
use App\Http\Controllers\Reports\PendingOrdersReportController;
use App\Http\Controllers\Reports\CreditReportController;
use App\Http\Controllers\Reports\PendingPaymentsReportController;
use App\Http\Controllers\BranchLogController;
use App\Http\Controllers\BranchReopenRequestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['admin'])->group(function () {
    // HR Routes
    Route::get('/hr/dashboard', [HRDashboardController::class, 'index'])->name('hr.dashboard');
    Route::get('/hr/dashboard/pdf', [HRDashboardController::class, 'exportPdf'])->name('hr.dashboard.pdf');
    Route::post('/hr/{staff}/attendance', [HRController::class, 'storeAttendance'])->name('hr.attendance.store');
    Route::post('/hr/{staff}/attendance/bulk', [HRController::class, 'bulkUpdateAttendance'])->name('hr.attendance.bulkUpdate');
    Route::put('/hr/attendance/{attendance}', [HRController::class, 'updateAttendance'])->name('hr.attendance.update');
    Route::post('/hr/{staff}/leaves', [HRController::class, 'storeLeave'])->name('hr.leaves.store');
    Route::put('/hr/leaves/{leave}', [HRController::class, 'updateLeave'])->name('hr.leaves.update');
    Route::post('/hr/{staff}/advances', [SalaryAdvanceAndAdjustmentController::class, 'storeAdvance'])->name('hr.advances.store');
    Route::put('/hr/advances/{advance}', [SalaryAdvanceAndAdjustmentController::class, 'updateAdvance'])->name('hr.advances.update');
    Route::delete('/hr/advances/{advance}', [SalaryAdvanceAndAdjustmentController::class, 'destroyAdvance'])->name('hr.advances.destroy');
    Route::post('/hr/{staff}/adjustments', [SalaryAdvanceAndAdjustmentController::class, 'storeAdjustment'])->name('hr.adjustments.store');
    Route::delete('/hr/adjustments/{adjustment}', [SalaryAdvanceAndAdjustmentController::class, 'destroyAdjustment'])->name('hr.adjustments.destroy');
    Route::get('/hr/{staff}/payslip', [StaffController::class, 'downloadPayslip'])->name('hr.payslip.download');

    // Branches
    Route::resource('branches', BranchController::class)->except(['show']);
    Route::get('/branch/month-plan', [BranchPlanController::class, 'index'])->name('branch.month_plan');
    Route::post('/branch/month-plan', [BranchPlanController::class, 'store'])->name('branch.month_plan.store');

    // Branch Logs & Shift Re-open Requests
    Route::get('/branch-logs', [BranchLogController::class, 'index'])->name('superadmin.branch-logs');
    Route::redirect('/reopen-requests', '/branch-logs?tab=reopen_requests')->name('superadmin.reopen-requests');
    Route::post('/reopen-requests/{reopenRequest}/approve', [BranchReopenRequestController::class, 'approve'])->name('superadmin.reopen-requests.approve');
    Route::post('/reopen-requests/{reopenRequest}/reject', [BranchReopenRequestController::class, 'reject'])->name('superadmin.reopen-requests.reject');

    Route::post('/workshop/dispatches/{dispatch}/received-at-branch', [WorkshopDispatchController::class, 'receivedAtBranch'])->name('workshop.receivedAtBranch');
    Route::post('/staff/pay-salary', [StaffController::class, 'paySalary'])->name('staff.paySalary');
    Route::put('/staff/salaries/{expense}', [StaffController::class, 'updateSalary'])->name('staff.salaries.update');
    Route::delete('/staff/salaries/{expense}', [StaffController::class, 'destroySalary'])->name('staff.salaries.destroy');
    Route::resource('staff', StaffController::class);

    // Reports Routes (Protected by shift.open)
    Route::middleware(['shift.open'])->group(function () {
        Route::get('/reports', [MainReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/pdf', [MainReportController::class, 'exportPdf'])->name('reports.pdf');
        Route::get('/reports/pending-orders', [PendingOrdersReportController::class, 'index'])->name('reports.pending-orders');
        Route::get('/reports/active-orders-pdf', [PendingOrdersReportController::class, 'exportPdf'])->name('reports.active-orders-pdf');

        Route::get('/reports/credit-orders', [CreditReportController::class, 'index'])->name('reports.credit-orders');
        Route::get('/reports/credit-orders/pdf', [CreditReportController::class, 'exportPdf'])->name('reports.credit-orders.pdf');
        Route::get('/reports/credit-orders/customer/{customerId}', [CreditReportController::class, 'customerOrders'])->name('reports.credit-orders.customer');

        Route::get('/reports/pending-payments', [PendingPaymentsReportController::class, 'index'])->name('reports.pending-payments');
        Route::get('/reports/pending-payments/pdf', [PendingPaymentsReportController::class, 'exportPdf'])->name('reports.pending-payments.pdf');

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

