<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\WorkshopDispatch;
use App\Services\CommunicationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOrderNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order;
    public $status;
    public $tries = 3;

    public function __construct(Order $order, string $status)
    {
        $this->order = $order;
        $this->status = $status;
    }

    public function handle(): void
    {
        try {
            $commService = new CommunicationService();
            $dispatch = WorkshopDispatch::where('order_id', $this->order->id)->first();
            $isCollection = false;
            
            if (! $this->order->is_delivery_order && $dispatch && $dispatch->branch_id !== $dispatch->workshop_branch_id) {
                $isCollection = true;
            }

            if ($this->status === 'done' && $isCollection) {
                $commService->sendCollectionOrderReady($this->order);
            } elseif ($this->status === 'delivered' || $this->status === 'completed') {
                if ($this->order->is_delivery_order) {
                    $commService->sendDeliveryCompleted($this->order);
                } elseif ($isCollection) {
                    $commService->sendCollectionOrderCompleted($this->order);
                } else {
                    $commService->sendPosWalkinCompleted($this->order);
                }
            }
        } catch (\Exception $e) {
            Log::error('SendOrderNotificationJob failed: ' . $e->getMessage());
            $this->fail($e);
        }
    }
}
