            <!-- Notes -->
            @if($order->notes)
                <div class="bg-yellow-50 rounded-lg sm:rounded-xl border border-yellow-100 p-4 sm:p-6">
                    <h3 class="font-bold text-yellow-800 text-sm mb-2"><i class="fa-solid fa-note-sticky mr-1"></i> Order Notes</h3>
                    <p class="text-xs sm:text-sm text-yellow-700 font-medium">{{ $order->notes }}</p>
                </div>
            @endif
