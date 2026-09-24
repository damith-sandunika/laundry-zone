    <!-- Horizontal Status Timeline -->
    @if($order->payment_approved || $order->requested_advance == 0)
    <div class="bg-white rounded-lg sm:rounded-xl border border-slate-100 shadow-sm p-4 sm:p-6 lg:p-8 mb-6 overflow-hidden">
        <h3 class="font-bold text-slate-800 mb-8 flex items-center gap-2"><i class="fa-solid fa-bars-progress text-blue-500"></i> Order Progress</h3>
        
        @php
            if ($order->is_delivery_order) {
                $statuses = [
                    'pending'          => ['label' => 'Pending Approval', 'icon' => 'fa-clock', 'color' => 'yellow'],
                    'confirmed'        => ['label' => 'Confirmed', 'icon' => 'fa-clipboard-check', 'color' => 'blue'],
                    'picked_up'        => ['label' => 'Picked Up', 'icon' => 'fa-truck-pickup', 'color' => 'blue'],
                    'processing'       => ['label' => 'Processing', 'icon' => 'fa-spinner', 'color' => 'blue'],
                    'ready'            => ['label' => 'Ready to Deliver', 'icon' => 'fa-box-open', 'color' => 'blue'],
                    'out_for_delivery' => ['label' => 'Out for Delivery', 'icon' => 'fa-motorcycle', 'color' => 'blue'],
                    'delivered'        => ['label' => 'Delivered', 'icon' => 'fa-truck-fast', 'color' => 'purple'],
                    'completed'        => ['label' => 'Completed', 'icon' => 'fa-check-double', 'color' => 'green']
                ];
            } else {
                $statuses = [
                    'pending'    => ['label' => 'Pending Approval', 'icon' => 'fa-clock', 'color' => 'yellow'],
                    'confirmed'  => ['label' => 'Confirmed', 'icon' => 'fa-clipboard-check', 'color' => 'blue'],
                    'received'   => ['label' => 'Order Placed', 'icon' => 'fa-clipboard-list', 'color' => 'blue'],
                    'processing' => ['label' => 'Processing', 'icon' => 'fa-spinner', 'color' => 'blue'],
                    'ready'      => ['label' => 'Job Done', 'icon' => 'fa-box-open', 'color' => 'blue'],
                    'completed'  => ['label' => 'Completed', 'icon' => 'fa-check-double', 'color' => 'green']
                ];
            }
            
            $actualStatus = $order->status;
            
            if ($order->is_delivery_order) {
                if ($actualStatus === 'pending') {
                    $actualStatus = 'pending';
                } elseif ($actualStatus === 'confirmed') {
                    $actualStatus = 'confirmed';
                } elseif (in_array($actualStatus, ['picked_up', 'received', 'dispatched_to_workshop', 'received_at_workshop'])) {
                    $actualStatus = 'picked_up';
                } elseif (in_array($actualStatus, ['washing', 'drying', 'ironing', 'folding', 'edited', 'processing']) || ($order->payment_approved && in_array($order->status, ['dispatched_to_workshop', 'received_at_workshop']))) {
                    $actualStatus = 'processing';
                } elseif (in_array($actualStatus, ['returned_from_workshop', 'done', 'completed_at_workshop', 'returned_to_branch'])) {
                    $actualStatus = 'ready';
                }
            } else {
                if ($actualStatus === 'pending') {
                    $actualStatus = 'pending';
                } elseif ($actualStatus === 'confirmed') {
                    $actualStatus = 'confirmed';
                } elseif (in_array($actualStatus, ['received', 'dispatched_to_workshop', 'received_at_workshop'])) {
                    $actualStatus = 'received';
                } elseif (in_array($actualStatus, ['washing', 'drying', 'ironing', 'folding', 'edited', 'processing']) || ($order->payment_approved && in_array($order->status, ['dispatched_to_workshop', 'received_at_workshop']))) {
                    $actualStatus = 'processing';
                } elseif (in_array($actualStatus, ['returned_from_workshop', 'done', 'completed_at_workshop', 'returned_to_branch'])) {
                    $actualStatus = 'ready';
                }
            }
            
            if ($actualStatus === 'delivered' && $order->payment_status === 'paid') {
                $actualStatus = 'completed';
            } elseif ($actualStatus === 'delivered' && $order->payment_status !== 'paid' && !$order->is_delivery_order) {
                $actualStatus = 'ready'; 
            }

            if ($order->status === 'cancelled') {
                $statuses = ['cancelled' => ['label' => 'Order Cancelled', 'icon' => 'fa-xmark', 'color' => 'red']];
                $actualStatus = 'cancelled';
            }
            
            $currentStatusIndex = array_search($actualStatus, array_keys($statuses));
            if ($currentStatusIndex === false) $currentStatusIndex = 0;
            
            $totalSteps = count($statuses);
            $currentIndex = 0;
        @endphp

        <div class="relative mt-4">
            <div class="absolute left-0 top-6 w-full h-1 bg-slate-100 rounded-full z-0 hidden sm:block"></div>
            
            <div class="absolute left-0 top-6 h-1 bg-blue-500 rounded-full z-0 transition-all duration-500 hidden sm:block" 
                 style="width: {{ $totalSteps > 1 ? ($currentStatusIndex / ($totalSteps - 1)) * 100 : 100 }}%"></div>

            <div class="flex flex-col sm:flex-row justify-between relative z-10 gap-6 sm:gap-0">
                @foreach($statuses as $key => $details)
                    @php
                        $isCompleted = $currentIndex <= $currentStatusIndex;
                        $isCurrent = $currentIndex === $currentStatusIndex;
                        
                        $colorClasses = [
                            'yellow' => ['bg' => 'bg-yellow-500', 'text' => 'text-yellow-600', 'border' => 'border-yellow-200'],
                            'blue'   => ['bg' => 'bg-blue-500', 'text' => 'text-blue-600', 'border' => 'border-blue-200'],
                            'green'  => ['bg' => 'bg-green-500', 'text' => 'text-green-600', 'border' => 'border-green-200'],
                            'purple' => ['bg' => 'bg-purple-500', 'text' => 'text-purple-600', 'border' => 'border-purple-200'],
                            'red'    => ['bg' => 'bg-red-500', 'text' => 'text-red-600', 'border' => 'border-red-200'],
                        ];
                        $color = $colorClasses[$details['color']];
                    @endphp
                    
                    <div class="flex flex-row sm:flex-col items-center sm:text-center gap-4 sm:gap-3 flex-1 relative">
                        @if($currentIndex < $totalSteps - 1)
                            <div class="absolute left-6 top-12 bottom-[-24px] w-0.5 bg-slate-100 sm:hidden z-0 {{ $isCompleted && $currentIndex < $currentStatusIndex ? '!bg-blue-500' : '' }}"></div>
                        @endif

                        <div class="w-12 h-12 rounded-full flex items-center justify-center border-4 border-white shadow-sm shrink-0 relative z-10 transition-colors duration-300
                            {{ $isCompleted ? $color['bg'] . ' text-white' : 'bg-slate-100 text-slate-400' }}
                            {{ $isCurrent ? 'ring-4 ring-blue-50/50 scale-110' : '' }}">
                            <i class="fa-solid {{ $details['icon'] }} text-lg {{ $isCurrent ? 'fa-bounce' : '' }}"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold {{ $isCompleted ? 'text-slate-800' : 'text-slate-400' }}">{{ $details['label'] }}</h4>
                            @if($isCurrent)
                                <p class="text-[10px] font-bold uppercase tracking-wider text-blue-600 mt-1 bg-blue-50 inline-block px-2 py-0.5 rounded-full hidden sm:inline-block">Current</p>
                            @endif
                        </div>
                    </div>
                    
                    @php $currentIndex++; @endphp
                @endforeach
            </div>
        </div>
        </div>
    </div>
    @endif
