        <!-- STAT CARDS / TABS -->
        <div class="grid grid-cols-2 gap-4 mb-6" style="max-width:400px;">
            <!-- Incoming Pickups -->
            <a href="?tab=pickups"
                class="{{ $currentTab === 'pickups' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md' }} flex flex-col items-center justify-center p-4 md:px-6 md:py-5 rounded-xl border-2 cursor-pointer transition-all duration-300" style="text-decoration:none;">
                <div class="{{ $currentTab === 'pickups' ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-600' }} w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </div>
                <span class="text-sm font-black mt-2 {{ $currentTab === 'pickups' ? 'text-blue-700' : 'text-gray-900' }}">{{ $pickupsCount }} Pickups</span>
            </a>

            <!-- Return Deliveries -->
            <a href="?tab=returns"
                class="{{ $currentTab === 'returns' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md' }} flex flex-col items-center justify-center p-4 md:px-6 md:py-5 rounded-xl border-2 cursor-pointer transition-all duration-300" style="text-decoration:none;">
                <div class="{{ $currentTab === 'returns' ? 'bg-purple-600 text-white' : 'bg-purple-100 text-purple-600' }} w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                    </svg>
                </div>
                <span class="text-sm font-black mt-2 {{ $currentTab === 'returns' ? 'text-purple-700' : 'text-gray-900' }}">{{ $returnsCount }} Returns</span>
            </a>
        </div>