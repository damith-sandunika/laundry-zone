<!-- Status Progress -->
<div
    style="background:white;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.02);padding:16px;">
    <div class="mb-4 px-2">
        <h3 class="text-sm font-black text-slate-800 mb-4">Order Journey</h3>
        <div class="flex flex-col md:flex-row gap-8">
            <div class="flex-1 relative">
                <div class="absolute left-[15px] top-4 bottom-4 w-[2px] bg-slate-100"></div>
                <div class="space-y-5">
                    @php
                        $steps = collect();
                        $history = $order->statusHistory->sortBy('created_at')->values();
                        $dispatch = \App\Models\WorkshopDispatch::where('order_id', $order->id)->first();

                        function getLog($history, $status, $noteMatch = null)
                        {
                            return $history->first(function ($log) use ($status, $noteMatch) {
                                if ($noteMatch && str_contains(strtolower($log->note ?? ''), strtolower($noteMatch))) {
                                    return true;
                                }
                                return $log->status === $status;
                            });
                        }

                        if ($order->is_delivery_order) {
                            // 1. Customer Place Order (Appointment)
                            $steps->push([
                                'label' => 'Order Placed',
                                'icon' => '📅',
                                'time' => $order->deliveryAppointment?->created_at,
                                'user' => $order->customer->name,
                                'active' => true
                            ]);

                            // 2. Order Confirm (Received)
                            $steps->push([
                                'label' => 'Order Confirmed',
                                'icon' => '✅',
                                'time' => $order->created_at,
                                'user' => $order->createdBy?->name ?? 'System',
                                'active' => true
                            ]);

                            // 3. Rider Assign (Task)
                            $pickupTask = \App\Models\DeliveryTask::where('delivery_appointment_id', $order->delivery_appointment_id)->where('task_type', 'pickup')->first();
                            $steps->push([
                                'label' => 'Rider Assigned',
                                'icon' => '🏍️',
                                'time' => $pickupTask?->created_at,
                                'user' => $pickupTask?->rider?->name ?? 'System',
                                'active' => $pickupTask != null || $order->deliveryAppointment?->delivery_method === 'pickme_flash'
                            ]);

                            // 4. Workshop Received
                            $steps->push([
                                'label' => 'Workshop Received',
                                'icon' => '📥',
                                'time' => $dispatch?->received_at_workshop_at,
                                'user' => $dispatch?->receivedBy?->name ?? 'System',
                                'active' => $dispatch?->received_at_workshop_at != null
                            ]);

                            // 5. Payment & Start (Processing)
                            $firstPayment = $order->paymentTransactions()->orderBy('created_at')->first();
                            $processingLog = getLog($history, 'processing', 'processing started');

                            $payLabel = 'Payment & Processing Started';
                            if ($firstPayment) {
                                $payLabel .= ' (Paid Rs.' . number_format($firstPayment->amount, 0) . ' at ' . $firstPayment->created_at->format('H:i') . ')';
                            }

                            $steps->push([
                                'label' => $payLabel,
                                'icon' => '⚙️',
                                'time' => $processingLog?->created_at ?? $firstPayment?->created_at,
                                'user' => $processingLog?->changedBy?->name ?? $firstPayment?->createdBy?->name ?? 'System',
                                'active' => $processingLog != null || $order->status === 'processing' || in_array($order->status, ['returned_from_workshop', 'done', 'delivered'])
                            ]);

                            // 6. Done (Workshop Completed)
                            $doneWorkshopLog = getLog($history, 'done', 'completed at workshop');
                            $steps->push([
                                'label' => 'Done',
                                'icon' => '✨',
                                'time' => $doneWorkshopLog?->created_at ?? $dispatch?->completed_at_workshop_at,
                                'user' => $doneWorkshopLog?->changedBy?->name ?? $dispatch?->completedBy?->name ?? 'System',
                                'active' => $doneWorkshopLog != null || $dispatch?->completed_at_workshop_at != null || in_array($order->status, ['returned_from_workshop', 'done', 'delivered'])
                            ]);

                            // 7. Pay Full Payment & Completed (Delivered)
                            $completedLog = getLog($history, 'completed');
                            $deliveredLog = getLog($history, 'delivered');
                            $finalLog = $completedLog ?? $deliveredLog;

                            $steps->push([
                                'label' => 'Full Payment & Completed',
                                'icon' => '🤝',
                                'time' => $finalLog?->created_at ?? $order->delivered_at,
                                'user' => $finalLog?->changedBy?->name ?? 'System',
                                'active' => in_array($order->status, ['delivered', 'completed'])
                            ]);

                        } else {
                            // WALK-IN ORDER FLOW

                            // 1. Order Received
                            $steps->push([
                                'label' => 'Order Received',
                                'icon' => '📥',
                                'time' => $order->created_at,
                                'user' => $order->createdBy?->name ?? 'System',
                                'active' => true
                            ]);

                            // 2. Transfer to Workshop
                            $dispatchLog = getLog($history, 'dispatched_to_workshop', 'dispatched');
                            $steps->push([
                                'label' => 'Transfer to Workshop',
                                'icon' => '📤',
                                'time' => $dispatchLog?->created_at ?? $dispatch?->dispatched_at,
                                'user' => $dispatchLog?->changedBy?->name ?? $dispatch?->dispatchedBy?->name ?? 'System',
                                'active' => $dispatch != null
                            ]);

                            // 3. Arrive Workshop & Washing (Processing)
                            $arriveLog = getLog($history, 'received_at_workshop');
                            $processingLog = getLog($history, 'processing', 'processing started');
                            $steps->push([
                                'label' => 'Arrive Workshop & Washing',
                                'icon' => '⚙️',
                                'time' => $arriveLog?->created_at ?? $processingLog?->created_at ?? $dispatch?->received_at_workshop_at,
                                'user' => $arriveLog?->changedBy?->name ?? $processingLog?->changedBy?->name ?? $dispatch?->receivedBy?->name ?? 'System',
                                'active' => $arriveLog != null || $processingLog != null || $dispatch?->received_at_workshop_at != null || in_array($order->status, ['processing', 'returned_from_workshop', 'done', 'delivered'])
                            ]);

                            // 4. Done (at Workshop)
                            $doneWorkshopLog = getLog($history, 'done', 'completed at workshop');
                            $steps->push([
                                'label' => 'Done',
                                'icon' => '✨',
                                'time' => $doneWorkshopLog?->created_at ?? $dispatch?->completed_at_workshop_at,
                                'user' => $doneWorkshopLog?->changedBy?->name ?? $dispatch?->completedBy?->name ?? 'System',
                                'active' => $doneWorkshopLog != null || $dispatch?->completed_at_workshop_at != null || in_array($order->status, ['returned_from_workshop', 'done', 'delivered'])
                            ]);

                            // 5. Delivery to Branch (Returning)
                            $returnLog = getLog($history, 'returned_from_workshop', 'dispatched back');
                            $steps->push([
                                'label' => 'Delivery to Branch',
                                'icon' => '🚚',
                                'time' => $returnLog?->created_at ?? $dispatch?->returned_at,
                                'user' => $returnLog?->changedBy?->name ?? $dispatch?->returnedBy?->name ?? 'System',
                                'active' => $returnLog != null || in_array($order->status, ['returned_from_workshop', 'done', 'delivered'])
                            ]);

                            // 6. Received to Branch (Done state)
                            $doneLog = getLog($history, 'done', 'status updated to done');
                            $steps->push([
                                'label' => 'Received to Branch',
                                'icon' => '🏢',
                                'time' => $doneLog?->created_at ?? $dispatch?->received_at_branch_at,
                                'user' => $doneLog?->changedBy?->name ?? $dispatch?->receivedAtBranchBy?->name ?? 'System',
                                'active' => $doneLog != null || in_array($order->status, ['done', 'delivered'])
                            ]);

                            // 7. Delivered to Customer
                            $completedLog = getLog($history, 'completed');
                            $deliveredLog = getLog($history, 'delivered');
                            $finalLog = $completedLog ?? $deliveredLog;

                            $steps->push([
                                'label' => 'Delivered to Customer',
                                'icon' => '🤝',
                                'time' => $finalLog?->created_at ?? $order->delivered_at,
                                'user' => $finalLog?->changedBy?->name ?? 'System',
                                'active' => in_array($order->status, ['delivered', 'completed'])
                            ]);
                        }
                    @endphp

                    @foreach($steps as $step)
                        <div class="flex items-start gap-4 relative {{ !$step['active'] ? 'opacity-40 grayscale' : '' }}">
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center text-sm z-10 flex-shrink-0 shadow-sm {{ $step['active'] ? 'bg-blue-600 text-white border-2 border-white ring-4 ring-blue-50' : 'bg-slate-200 text-slate-500 border-2 border-white' }}">
                                {{ $step['icon'] }}
                            </div>
                            <div class="flex-1 pt-1.5 pb-5">
                                <div class="flex items-baseline justify-between gap-2">
                                    <div
                                        class="text-[13px] font-black {{ $step['active'] ? 'text-slate-800' : 'text-slate-500' }}">
                                        {{ $step['label'] }}
                                    </div>
                                    @if($step['time'])
                                        <div
                                            class="text-[11px] font-bold whitespace-nowrap {{ $step['active'] ? 'text-blue-600' : 'text-slate-400' }}">
                                            {{ \Carbon\Carbon::parse($step['time'])->format('d M, H:i') }}
                                        </div>
                                    @endif
                                </div>
                                @if($step['active'] && $step['user'])
                                    <div class="text-[11px] text-slate-500 font-semibold mt-0.5">By
                                        {{ $step['user'] }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>