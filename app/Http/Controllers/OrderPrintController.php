<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Central\TenantPrintLayout;
use App\Models\Order;
use App\Services\Tenancy\TenantManager;
use Illuminate\Http\Request;

class OrderPrintController extends Controller
{
    public function printBill(Order $order)
    {
        $order->load(['customer', 'items.serviceType.category.parent', 'items.turnaroundType', 'items.extras.extra']);
        $branch = Branch::find($order->branch_id);
        $mainBranch = Branch::orderBy('id')->first();

        $tenant = TenantManager::getTenant();
        $layout = TenantPrintLayout::getForTenant($tenant?->id, 'bill');

        return view('orders.print', compact('order', 'branch', 'mainBranch', 'layout'));
    }

    public function printChit(Order $order)
    {
        $order->load(['customer', 'items.serviceType.category.parent', 'items.turnaroundType', 'items.extras.extra']);

        $tenant = TenantManager::getTenant();
        $layout = TenantPrintLayout::getForTenant($tenant?->id, 'receipt');

        return view('orders.chit', compact('order', 'layout'));
    }

    public function printLabel(Order $order)
    {
        $order->load(['customer', 'items.serviceType.category.parent', 'items.turnaroundType', 'items.extras.extra', 'deliveryAppointment']);
        $branch = Branch::find($order->branch_id);
        $mainBranch = Branch::orderBy('id')->first();

        return view('orders.label', compact('order', 'branch', 'mainBranch'));
    }

    public function invoice(Order $order)
    {
        $order->load(['customer', 'items.serviceType.category.parent', 'items.turnaroundType', 'items.extras.extra', 'branch']);
        $mainBranch = Branch::orderBy('id')->first();

        $tenant = TenantManager::getTenant();
        $layout = TenantPrintLayout::getForTenant($tenant?->id, 'invoice');

        return view('orders.invoice', compact('order', 'mainBranch', 'layout'));
    }
}


