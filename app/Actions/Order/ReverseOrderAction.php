<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\Cubby;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\DB;

class ReverseOrderAction
{
    public function execute(Order $order, int $userId): void
    {
        DB::transaction(function () use ($order, $userId) {
            $order->status = 'received';
            $order->payment_status = 'pending';
            $order->paid_amount = 0;

            $orderCubby = $order->cubbies()->whereNull('released_at')->first();
            if ($orderCubby) {
                $orderCubby->update(['released_at' => now()]);
                Cubby::where('id', $orderCubby->cubby_id)->update(['status' => 'empty']);
            }

            $order->save();

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'received',
                'changed_by' => $userId,
            ]);
        });
    }
}
