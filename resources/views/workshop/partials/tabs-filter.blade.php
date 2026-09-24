        <!-- OPERATIONS (TABS) -->
        <div class="mb-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Operations
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-4 mb-4">
                <!-- Incoming -->
                <a href="?tab=incoming&branch={{ $selectedBranch ?? '' }}"
                    :class="currentTab === 'incoming' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md'"
                    class="flex flex-col items-center justify-center p-2 md:px-4 md:py-4 rounded-xl border-2 cursor-pointer transition-all duration-300" style="text-decoration:none;">
                    <span class="text-xs md:text-sm font-black mt-1" :class="currentTab === 'incoming' ? 'text-orange-700' : 'text-gray-900'">{{ $counts['incoming'] }}</span>
                    <span class="text-[10px] md:text-[11px] font-semibold text-gray-400 uppercase tracking-wider text-center">Incoming</span>
                </a>

                <!-- To Wash -->
                <a href="?tab=to_wash&branch={{ $selectedBranch ?? '' }}"
                    :class="currentTab === 'to_wash' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md'"
                    class="flex flex-col items-center justify-center p-2 md:px-4 md:py-4 rounded-xl border-2 cursor-pointer transition-all duration-300" style="text-decoration:none;">
                    <span class="text-xs md:text-sm font-black mt-1" :class="currentTab === 'to_wash' ? 'text-blue-700' : 'text-gray-900'">{{ $counts['to_wash'] }}</span>
                    <span class="text-[10px] md:text-[11px] font-semibold text-gray-400 uppercase tracking-wider text-center">To Wash</span>
                </a>

                <!-- Washing -->
                <a href="?tab=washing&branch={{ $selectedBranch ?? '' }}"
                    :class="currentTab === 'washing' ? 'border-yellow-500 bg-yellow-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md'"
                    class="flex flex-col items-center justify-center p-2 md:px-4 md:py-4 rounded-xl border-2 cursor-pointer transition-all duration-300" style="text-decoration:none;">
                    <span class="text-xs md:text-sm font-black mt-1" :class="currentTab === 'washing' ? 'text-yellow-700' : 'text-gray-900'">{{ $counts['washing'] }}</span>
                    <span class="text-[10px] md:text-[11px] font-semibold text-gray-400 uppercase tracking-wider text-center">Washing</span>
                </a>

                <!-- Completed -->
                <a href="?tab=completed&branch={{ $selectedBranch ?? '' }}"
                    :class="currentTab === 'completed' ? 'border-green-500 bg-green-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md'"
                    class="flex flex-col items-center justify-center p-2 md:px-4 md:py-4 rounded-xl border-2 cursor-pointer transition-all duration-300" style="text-decoration:none;">
                    <span class="text-xs md:text-sm font-black mt-1" :class="currentTab === 'completed' ? 'text-green-700' : 'text-gray-900'">{{ $counts['completed'] }}</span>
                    <span class="text-[10px] md:text-[11px] font-semibold text-gray-400 uppercase tracking-wider text-center">Completed</span>
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="mb-6 flex justify-end items-center">
            <select x-model="selectedBranch" @change="window.location.href = '?tab={{ $currentTab }}&branch=' + $event.target.value" class="px-4 py-2 pr-8 border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 outline-none focus:border-blue-500">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>