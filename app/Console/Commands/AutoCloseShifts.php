<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RegisterSession;
use App\Models\WhatsappLog;
use App\Models\WhatsappTemplate;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoCloseShifts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shifts:autoclose';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically close any open branch shifts at the end of the day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now()->format('H:i');

        $openSessions = RegisterSession::where('status', 'open')
            ->whereHas('branch', function ($query) use ($now) {
                $query->where('auto_close_time', 'like', $now.'%');
            })
            ->get();

        if ($openSessions->isEmpty()) {
            return;
        }

        foreach ($openSessions as $session) {
            $branchId = $session->branch_id;

            $salesCollected = Order::where('branch_id', $branchId)
                ->where('created_at', '>=', $session->opened_at)
                ->sum('paid_amount');

            $expensesLogged = Expense::where('branch_id', $branchId)
                ->where('created_at', '>=', $session->opened_at)
                ->sum('amount');

            $finalCash = $session->opening_cash + $salesCollected - $expensesLogged;

            $session->update([
                'closing_cash' => $finalCash,
                'closed_at' => now(),
                'status' => 'closed',
                'closed_by' => null, // Auto closed
            ]);

            // WhatsApp End-of-Day Summary
            $branch = Branch::find($branchId);
            if ($branch && $branch->whatsapp_number) {
                $orderCount = Order::where('branch_id', $branchId)
                    ->where('created_at', '>=', $session->opened_at)->count();
                $deliveredCount = Order::where('branch_id', $branchId)
                    ->where('status', 'delivered')
                    ->where('created_at', '>=', $session->opened_at)->count();
                $pendingCount = Order::where('branch_id', $branchId)
                    ->whereNotIn('status', ['delivered', 'cancelled'])
                    ->where('created_at', '>=', $session->opened_at)->count();

                $openedAt = Carbon::parse($session->opened_at);
                $shiftDate = $openedAt->format('d M Y');
                $openedTime = $openedAt->format('h:i A');
                $closedTime = now()->format('h:i A');

                $inventoryItems = InventoryItem::where('branch_id', $branchId)->get();
                $stockLines = '';
                foreach ($inventoryItems as $inv) {
                    $stockLines .= "   • {$inv->name}: {$inv->current_stock} {$inv->unit} remaining\n";
                }

                $serviceBreakdown = OrderItem::select('service_types.name', \DB::raw('SUM(order_items.quantity) as total_qty'), \DB::raw('SUM(order_items.total_price) as total_revenue'))
                    ->join('service_types', 'service_types.id', '=', 'order_items.service_type_id')
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->where('orders.branch_id', $branchId)
                    ->where('orders.created_at', '>=', $session->opened_at)
                    ->groupBy('service_types.name')
                    ->get();

                $sbText = '';
                if ($serviceBreakdown->count() > 0) {
                    $sbText .= "👕 *Services Sold:*\n";
                    foreach ($serviceBreakdown as $sb) {
                        $sbText .= "   • {$sb->name}: {$sb->total_qty} (Rs.".number_format($sb->total_revenue, 2).")\n";
                    }
                    $sbText .= '━━━━━━━━━━━━━━━━━━━━';
                }

                $inventoryConsumed = InventoryTransaction::select('inventory_items.name', 'inventory_items.unit', \DB::raw('SUM(inventory_transactions.quantity) as total_used'))
                    ->join('inventory_items', 'inventory_items.id', '=', 'inventory_transactions.inventory_item_id')
                    ->where('inventory_items.branch_id', $branchId)
                    ->where('inventory_transactions.type', 'out')
                    ->where('inventory_transactions.created_at', '>=', $session->opened_at)
                    ->groupBy('inventory_items.name', 'inventory_items.unit')
                    ->get();

                $icText = '';
                if ($inventoryConsumed->count() > 0) {
                    $icText .= "📦 *Inventory Consumed:*\n";
                    foreach ($inventoryConsumed as $ic) {
                        $icText .= "   • {$ic->name}: {$ic->total_used} {$ic->unit}\n";
                    }
                    $icText .= '━━━━━━━━━━━━━━━━━━━━';
                }

                $srText = '';
                if ($stockLines) {
                    $srText .= "📊 *Stock Remaining:*\n";
                    $srText .= $stockLines;
                }

                $template = WhatsappTemplate::where('type', 'shift_close')->first();
                $summary = $template ? $template->content : "*[BranchName] - End of Day Report* 🔴\n━━━━━━━━━━━━━━━━━━━━\n📅 Date: [Date]\n🕐 Shift: [ShiftTime]\n━━━━━━━━━━━━━━━━━━━━\n📦 Total Orders: [TotalOrders]\n✅ Delivered: [DeliveredCount]\n⏳ Pending: [PendingCount]\n━━━━━━━━━━━━━━━━━━━━\n[ServiceBreakdown]\n💰 Sales Collected: Rs.[SalesCollected]\n📤 Expenses: Rs.[Expenses]\n🏦 Opening Balance: Rs.[OpeningBalance]\n💵 *Final Cash Count: Rs.[FinalCash]*\n[InventoryConsumed]\n[StockRemaining]\n━━━━━━━━━━━━━━━━━━━━\n_Powered by SmartLaundry System_";

                $summary = str_replace('[BranchName]', $branch->name ?? 'SmartLaundry', $summary);
                $summary = str_replace('[Date]', $shiftDate, $summary);
                $summary = str_replace('[ShiftTime]', "{$openedTime} → {$closedTime}", $summary);
                $summary = str_replace('[TotalOrders]', $orderCount, $summary);
                $summary = str_replace('[DeliveredCount]', $deliveredCount, $summary);
                $summary = str_replace('[PendingCount]', $pendingCount, $summary);
                $summary = str_replace('[ServiceBreakdown]', $sbText, $summary);
                $summary = str_replace('[SalesCollected]', number_format($salesCollected, 2), $summary);
                $summary = str_replace('[Expenses]', number_format($expensesLogged, 2), $summary);
                $summary = str_replace('[OpeningBalance]', number_format($session->opening_cash, 2), $summary);
                $summary = str_replace('[FinalCash]', number_format($finalCash, 2), $summary);
                $summary = str_replace('[InventoryConsumed]', $icText, $summary);
                $summary = str_replace('[StockRemaining]', $srText, $summary);

                $summary = preg_replace("/\n\s*\n/", "\n", $summary);

                WhatsappLog::create([
                    'order_id' => null,
                    'customer_id' => null,
                    'phone' => $branch->whatsapp_number,
                    'message_type' => 'shift_close',
                    'message' => "AUTOMATIC CLOSE:\n".$summary,
                    'status' => 'pending',
                ]);
            }
        }

        $this->info(count($openSessions).' shifts auto-closed.');
    }
}
