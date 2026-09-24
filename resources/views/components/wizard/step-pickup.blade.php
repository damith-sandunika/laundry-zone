            <!-- STEP: Pickup Details -->
            <div x-show="currentView === 'pickup'" style="display:none;" x-transition.opacity.duration.300ms>
                <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6 lg:p-8">
                    <h3 class="text-lg sm:text-xl font-bold text-slate-800 mb-2 flex items-center gap-3">
                        <span class="bg-amber-50 text-amber-500 w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl flex items-center justify-center"><i class="fa-solid fa-truck-pickup"></i></span>
                        Pickup Details
                    </h3>
                    <p class="text-sm text-slate-500 mb-6 font-medium">When and where should we pick up your laundry?</p>

                    <div class="grid grid-cols-2 gap-3 sm:gap-4 mb-6">
                        <div>
                            <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1">Pickup Scheduled Date</label>
                            <input type="date" name="pickup_date" id="pickup_date_input" x-model="pickupDate" class="w-full px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-500 font-semibold" min="{{ date('Y-m-d') }}">
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1">Pickup Time</label>
                            <select name="pickup_time_period_id" id="pickup_time_input" class="w-full px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-500 font-semibold">
                                <option value="">Select a time slot...</option>
                                <template x-for="period in branchPickupTimes" :key="period.id">
                                    <option :value="period.id" x-text="(period.label || '') + ' (' + formatTime(period.start_time) + ' - ' + formatTime(period.end_time) + ')'"></option>
                                </template>
                                <template x-if="branchPickupTimes.length === 0">
                                    <option disabled value="">No time slots available for this branch</option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-3">
                            <label class="block text-sm font-bold text-slate-700 flex items-center gap-2">
                                <span class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs"><i class="fa-solid fa-map-pin"></i></span>
                                Pin your pickup location
                            </label>
                            <button type="button" @click="findMe('pickup')" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm shadow-blue-500/30">
                                <i class="fa-solid fa-location-crosshairs"></i> Find Me
                            </button>
                        </div>
                        
                        <!-- Map Wrapper -->
                        <div class="relative rounded-xl overflow-hidden border border-slate-200 shadow-lg">
                            
                            <!-- Centered Pin -->
                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-full z-[400] pointer-events-none flex flex-col items-center">
                                <div :class="isOutOfRange ? 'bg-red-600 border-red-700' : 'bg-slate-900 border-slate-700'"
                                    class="text-white text-[11px] font-bold px-2.5 py-1.5 rounded-lg mb-1 shadow-xl whitespace-nowrap border flex flex-col items-center gap-0.5 transition-colors">
                                    <template x-if="!isOutOfRange">
                                        <div class="flex flex-col items-center gap-0.5">
                                            <span x-show="pickupDistance > 0" class="text-[9px] font-bold opacity-80 flex items-center gap-1">
                                                <i class="fa-solid fa-route text-blue-300"></i>
                                                <span x-text="pickupDistance"></span> km road distance
                                            </span>
                                        </div>
                                    </template>
                                    <template x-if="isOutOfRange">
                                        <span class="animate-pulse"><i class="fa-solid fa-triangle-exclamation text-yellow-300"></i> Out of delivery range</span>
                                    </template>
                                </div>
                                <!-- Pin icon -->
                                <div class="map-pulse">
                                    <i :class="isOutOfRange ? 'text-red-500' : 'text-blue-600'" 
                                        class="fa-solid fa-location-dot text-4xl drop-shadow-xl transition-colors"></i>
                                </div>
                            </div>

                            <!-- Map -->
                            <div id="pickup_map" class="map-container"></div>

                            <!-- Bottom Info Bar inside map -->
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-slate-900/90 to-transparent p-3 z-[300] pointer-events-none">
                                <div class="flex items-center justify-between">
                                    <div x-show="nearestWorkshop" class="flex items-center gap-2">
                                        <div class="w-7 h-7 bg-blue-600 rounded-full flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-store text-white text-[10px]"></i>
                                        </div>
                                        <div>
                                            <div class="text-white text-[10px] font-bold opacity-70">Nearest Branch</div>
                                            <div class="text-white text-xs font-bold" x-text="nearestWorkshop ? nearestWorkshop.name : ''"></div>
                                        </div>
                                    </div>
                                    <div x-show="pickupDistance > 0" class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-lg px-2.5 py-1.5">
                                        <i class="fa-solid fa-route text-amber-300 text-xs"></i>
                                        <span class="text-white text-xs font-bold"><span x-text="pickupDistance"></span> km</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="latitude" id="pickup_lat">
                        <input type="hidden" name="longitude" id="pickup_lng">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Full Pickup Address</label>
                        <textarea name="pickup_address" id="pickup_address" rows="2" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 font-semibold" placeholder="House no, Street name, City"></textarea>
                    </div>
                </div>

                <div class="flex justify-between items-center mt-6">
                    <div class="flex items-center justify-between w-full">
                        <button type="button" @click="goToStep(step - 1)" class="text-slate-500 hover:text-slate-800 font-bold flex items-center gap-2 px-4 py-2 text-sm sm:text-base">
                            <i class="fa-solid fa-arrow-left"></i> Back
                        </button>
                        <button type="button" @click="goToStep(step + 1)" :disabled="isOutOfRange || servicesLoading" class="bg-slate-900 hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed text-white px-5 py-2.5 sm:px-8 sm:py-3.5 rounded-lg sm:rounded-xl font-bold transition-all shadow-md flex items-center gap-2 text-sm sm:text-base">
                            <span x-show="!servicesLoading">Next <i class="fa-solid fa-arrow-right"></i></span>
                            <span x-show="servicesLoading"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Loading Services...</span>
                        </button>
                    </div>
                </div>
            </div>