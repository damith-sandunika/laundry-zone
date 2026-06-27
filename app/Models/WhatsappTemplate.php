<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    protected $fillable = ['type', 'name', 'content', 'available_tags'];

    protected $casts = [
        'available_tags' => 'array',
    ];

    public static function generateMessage($type, $order, $customer, $cubbyName = null)
    {
        $template = self::where('type', $type)->first();
        if (!$template) return "";

        $msg = $template->content;
        $msg = str_replace('[CustomerName]', $customer->name ?? '', $msg);
        $msg = str_replace('[OrderNumber]', $order->order_number ?? '', $msg);
        $msg = str_replace('[TotalAmount]', 'Rs.' . number_format($order->total ?? 0, 2), $msg);
        $msg = str_replace('[PickupDate]', $order->due_date ? \Carbon\Carbon::parse($order->due_date)->format('d M Y g:i A') : 'N/A', $msg);
        $msg = str_replace('[PickupTime]', $order->due_date ? \Carbon\Carbon::parse($order->due_date)->format('g:i A') : 'N/A', $msg);
        
        $orderDetailsStr = '';
        if ($order && $order->items) {
            foreach ($order->items as $item) {
                $orderDetailsStr .= "▪️ " . $item->item_description . " (x" . $item->quantity . ")\n";
            }
        }
        $msg = str_replace('[OrderDetails]', trim($orderDetailsStr), $msg);
        
        if ($cubbyName) {
            $msg = str_replace('[CubbetName]', $cubbyName, $msg);
        }

        return $msg;
    }
}
