            <!-- STEP: Delivery & Payment -->
            <div x-show="currentView === 'delivery'" style="display:none;" x-transition.opacity.duration.300ms>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8 relative">
                    <!-- Left Column: Delivery Details -->
                    <div class="space-y-6 sm:space-y-8">

                    <!-- Requested Delivery Time Card -->
                    <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6 lg:p-8">
                        <h3 class="text-lg sm:text-xl font-bold text-slate-800 mb-2 flex items-center gap-3">
                            <span class="bg-blue-50 text-blue-500 w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl flex items-center justify-center"><i class="fa-solid fa-clock"></i></span>
                            Requested Delivery Time
                        </h3>
                        <p class="text-sm text-slate-500 mb-6 font-medium">When would you like to receive your clean items back? (Optional)</p>
                        <div class="grid grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1">Preferred Date</label>
                                <input type="date" name="delivery_date" x-model="deliveryDate" class="w-full px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-500 font-semibold" min="{{ date('Y-m-d') }}">
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1">Preferred Time Slot</label>
                                <select name="delivery_time_period_id" x-model="deliveryTimePeriodId" class="w-full px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-500 font-semibold">
                                    <option value="">Select time slot...</option>
                                    <template x-for="period in branchPickupTimes" :key="period.id">
                                        <option :value="period.id" x-text="formatTime(period.start_time) + ' - ' + formatTime(period.end_time)"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Section -->
                    <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6 lg:p-8">
                        <h3 class="text-lg sm:text-xl font-bold text-slate-800 mb-2 flex items-center gap-3">
                            <span class="bg-emerald-50 text-emerald-500 w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl flex items-center justify-center"><i class="fa-solid fa-box-open"></i></span>
                            Return Delivery
                        </h3>
                        <p class="text-sm text-slate-500 mb-6 font-medium">How would you like to receive your clean laundry?</p>

                        <div class="grid grid-cols-2 gap-3 sm:gap-4 mb-6">
                            <label class="relative cursor-pointer h-full">
                                <input type="radio" name="delivery_preference" value="deliver_to_address" class="custom-radio sr-only" x-model="deliveryPref">
                                <div class="p-3 sm:p-4 border-2 border-slate-200 rounded-lg sm:rounded-xl bg-slate-50 transition-all flex flex-col sm:flex-row items-center gap-2 sm:gap-3 h-full">
                                    <div class="w-4 h-4 sm:w-5 sm:h-5 rounded-full border-2 border-slate-300 flex items-center justify-center shrink-0 bg-white">
                                        <div class="radio-dot w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full"></div>
                                    </div>
                                    <div class="text-center sm:text-left">
                                        <div class="font-bold text-xs sm:text-sm text-slate-800 leading-tight">Deliver to me</div>
                                        <div class="text-[10px] sm:text-[11px] font-bold text-slate-500 uppercase mt-0.5 hidden sm:block">Rider brings it back</div>
                                    </div>
                                </div>
                            </label>
                            <label class="relative cursor-pointer h-full">
                                <input type="radio" name="delivery_preference" value="pickup_from_branch" class="custom-radio sr-only" x-model="deliveryPref">
                                <div class="p-3 sm:p-4 border-2 border-slate-200 rounded-lg sm:rounded-xl bg-slate-50 transition-all flex flex-col sm:flex-row items-center gap-2 sm:gap-3 h-full">
                                    <div class="w-4 h-4 sm:w-5 sm:h-5 rounded-full border-2 border-slate-300 flex items-center justify-center shrink-0 bg-white">
                                        <div class="radio-dot w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full"></div>
                                    </div>
                                    <div class="text-center sm:text-left">
                                        <div class="font-bold text-xs sm:text-sm text-slate-800 leading-tight">I will pick up</div>
                                        <div class="text-[10px] sm:text-[11px] font-bold text-slate-500 uppercase mt-0.5 hidden sm:block">Collect from branch</div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <div x-show="deliveryPref === 'deliver_to_address'" x-collapse>
                            <label class="flex items-center gap-3 cursor-pointer p-4 bg-blue-50 border border-blue-100 rounded-xl mb-4">
                                <input type="checkbox" name="same_as_pickup" value="1" x-model="sameAsPickup" class="w-5 h-5 text-blue-600 rounded border-blue-200 focus:ring-blue-500 bg-white">
                                <span class="font-bold text-sm text-blue-800">Delivery address is same as pickup</span>
                            </label>

                            <div x-show="!sameAsPickup" x-collapse>
                                <div class="mb-4">
                                    <div class="flex justify-between items-center mb-3">
                                        <label class="block text-sm font-bold text-slate-700 flex items-center gap-2">
                                            <span class="w-6 h-6 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-xs"><i class="fa-solid fa-map-pin"></i></span>
                                            Pin your delivery location
                                        </label>
                                    </div>
                                    <!-- Delivery Map Wrapper -->
                                    <div class="relative rounded-xl overflow-hidden border border-slate-200 shadow-lg">
                                        <!-- Centered Pin -->
                                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-full z-[400] pointer-events-none flex flex-col items-center">
                                            <div :class="isOutOfRange ? 'bg-red-600 border-red-700' : 'bg-slate-900 border-slate-700'"
                                                class="text-white text-[11px] font-bold px-2.5 py-1.5 rounded-lg mb-1 shadow-xl whitespace-nowrap border flex flex-col items-center gap-0.5 transition-colors">
                                                <template x-if="!isOutOfRange">
                                                    <div class="flex flex-col items-center gap-0.5">
                                                        <span x-show="deliveryDistance > 0" class="text-[9px] font-bold opacity-80 flex items-center gap-1">
                                                            <i class="fa-solid fa-route text-blue-300"></i>
                                                            <span x-text="deliveryDistance"></span> km road distance
                                                        </span>
                                                    </div>
                                                </template>
                                                <template x-if="isOutOfRange">
                                                    <span class="animate-pulse"><i class="fa-solid fa-triangle-exclamation text-yellow-300"></i> Out of delivery range</span>
                                                </template>
                                            </div>
                                            <div class="map-pulse">
                                                <i :class="isOutOfRange ? 'text-red-500' : 'text-emerald-500'"
                                                    class="fa-solid fa-location-dot text-4xl drop-shadow-xl transition-colors"></i>
                                            </div>
                                        </div>
                                        <!-- Map -->
                                        <div id="delivery_map" class="map-container"></div>
                                        <!-- Bottom Info Bar -->
                                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-slate-900/90 to-transparent p-3 z-[300] pointer-events-none">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center shrink-0">
                                                        <i class="fa-solid fa-house text-white text-[10px]"></i>
                                                    </div>
                                                    <div>
                                                        <div class="text-white text-[10px] font-bold opacity-70">Delivery Location</div>
                                                        <div class="text-white text-xs font-bold">Move pin to set location</div>
                                                    </div>
                                                </div>
                                                <div x-show="deliveryDistance > 0" class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-lg px-2.5 py-1.5">
                                                    <i class="fa-solid fa-route text-amber-300 text-xs"></i>
                                                    <span class="text-white text-xs font-bold"><span x-text="deliveryDistance"></span> km</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="delivery_lat" id="delivery_lat">
                                    <input type="hidden" name="delivery_lng" id="delivery_lng">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1">Full Delivery Address</label>
                                    <textarea name="delivery_address" rows="2" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500" placeholder="House no, Street name, City"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    </div>

                    <!-- Right Column: Summary & Payment -->
                    <div class="space-y-6 sm:space-y-8">
                        <!-- Payment Summary Card -->
                        <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-lg sm:rounded-xl shadow-lg border border-slate-700 p-5 sm:p-8">
                            <div class="flex items-center gap-3 mb-4 sm:mb-5">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-600 rounded-lg sm:rounded-xl flex items-center justify-center text-white text-lg sm:text-xl shadow-lg">
                                    <i class="fa-solid fa-receipt"></i>
                                </div>
                                <div>
                                    <h3 class="text-base sm:text-lg font-bold text-white">Order Summary</h3>
                                    <p class="text-xs sm:text-sm text-slate-400 font-medium">Estimated costs — final bill after inspection</p>
                                </div>
                            </div>

                            <div class="space-y-3 mb-4">
                                <div class="flex justify-between items-center py-2.5 border-b border-slate-700">
                                    <span class="text-slate-300 text-sm font-bold flex items-center gap-2">
                                        <i class="fa-solid fa-shirt text-blue-400 text-xs"></i> Services (Estimate)
                                    </span>
                                    <span class="text-white text-sm font-bold">LKR <span x-text="calculatedGrandTotal.toFixed(2)"></span></span>
                                </div>

                                <div x-show="estimatedDeliveryFee > 0" class="flex justify-between items-center py-2.5 border-b border-slate-700">
                                    <span class="text-slate-300 text-sm font-bold flex items-center gap-2">
                                        <i class="fa-solid fa-truck text-emerald-400 text-xs"></i> Delivery Fee
                                    </span>
                                    <span class="text-white text-sm font-bold">LKR <span x-text="estimatedDeliveryFee.toFixed(2)"></span></span>
                                </div>

                                <div x-show="pickupDistance > 0" class="flex justify-between items-center py-1.5 text-slate-400 text-xs">
                                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-route"></i> Pickup distance</span>
                                    <span class="font-bold"><span x-text="pickupDistance"></span> km from branch</span>
                                </div>
                            </div>

                            <div class="border-t border-slate-700 my-4 sm:my-5"></div>

                            <div class="bg-blue-600 rounded-lg sm:rounded-xl p-3 sm:p-4 flex justify-between items-center">
                                <span class="text-blue-100 font-semibold text-sm sm:text-base">Total to Pay</span>
                                <span class="text-white font-bold text-lg sm:text-xl">LKR <span x-text="totalPrice.toFixed(2)"></span></span>
                            </div>

                            <p class="text-[11px] text-slate-500 text-center mt-4">
                                <i class="fa-solid fa-circle-info mr-1"></i>
                                Payment collected by rider after workshop inspection. WhatsApp notification will be sent.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 sm:mt-10 flex flex-col-reverse sm:flex-row justify-between items-center gap-4 border-t border-slate-200 pt-6 sm:pt-8">
                    <button type="button" @click="goToStep(step - 1)" class="text-slate-500 hover:text-slate-800 font-bold flex items-center gap-2 px-4 py-2 text-sm sm:text-base">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </button>
                    <button type="button" @click="submitBooking()" :disabled="isOutOfRange || isPlacingOrder" class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-6 py-3 sm:px-8 sm:py-4 rounded-lg sm:rounded-xl font-bold transition-all shadow-lg shadow-blue-600/30 flex items-center justify-center gap-2 text-base sm:text-lg cursor-pointer min-w-[200px] w-full sm:w-auto">
                        <span x-show="!isPlacingOrder">Confirm Booking <i class="fa-solid fa-check-circle"></i></span>
                        <i x-show="isPlacingOrder" class="fa-solid fa-spinner fa-spin text-lg sm:text-xl"></i>
                    </button>
                </div>