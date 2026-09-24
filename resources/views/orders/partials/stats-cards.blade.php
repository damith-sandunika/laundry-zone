        <!-- STAT CARDS -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-4 mb-6">

            <!-- Pending Dispatch -->
            <div onclick="window.location.href='?tab=received{{ request('q') ? '&q=' . request('q') : '' }}{{ request('branch_id') ? '&branch_id=' . request('branch_id') : '' }}'"
                class="flex flex-col items-center justify-center p-2 rounded-xl border-2 cursor-pointer transition-all duration-300 {{ $tab === 'received' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md' }}">
                <span
                    class="text-xs md:text-sm font-black mt-1 {{ $tab === 'received' ? 'text-orange-700' : 'text-gray-900' }}">{{ $counts['received'] ?? 0 }}</span>
                <span
                    class="text-[10px] md:text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">New</span>
            </div>

            <!-- At Workshop -->
            <div onclick="window.location.href='?tab=processing{{ request('q') ? '&q=' . request('q') : '' }}{{ request('branch_id') ? '&branch_id=' . request('branch_id') : '' }}'"
                class="flex flex-col items-center justify-center p-2 rounded-xl border-2 cursor-pointer transition-all duration-300 {{ $tab === 'processing' ? 'border-yellow-500 bg-yellow-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md' }}">
                <span
                    class="text-xs md:text-sm font-black mt-1 {{ $tab === 'processing' ? 'text-yellow-700' : 'text-gray-900' }}">{{ $counts['processing'] ?? 0 }}</span>
                <span class="text-[10px] md:text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">At
                    Workshop</span>
            </div>

            <!-- Arriving -->
            <div onclick="window.location.href='?tab=arriving{{ request('q') ? '&q=' . request('q') : '' }}{{ request('branch_id') ? '&branch_id=' . request('branch_id') : '' }}'"
                class="flex flex-col items-center justify-center p-2 rounded-xl border-2 cursor-pointer transition-all duration-300 {{ $tab === 'arriving' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md' }}">
                <span
                    class="text-xs md:text-sm font-black mt-1 {{ $tab === 'arriving' ? 'text-blue-700' : 'text-gray-900' }}">{{ $counts['arriving'] ?? 0 }}</span>
                <span
                    class="text-[10px] md:text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Arriving</span>
            </div>

            <!-- Ready for Pickup -->
            <div onclick="window.location.href='?tab=done{{ request('q') ? '&q=' . request('q') : '' }}{{ request('branch_id') ? '&branch_id=' . request('branch_id') : '' }}'"
                class="flex flex-col items-center justify-center p-2 rounded-xl border-2 cursor-pointer transition-all duration-300 {{ $tab === 'done' ? 'border-green-500 bg-green-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md' }}">
                <span
                    class="text-xs md:text-sm font-black mt-1 {{ $tab === 'done' ? 'text-green-700' : 'text-gray-900' }}">{{ $counts['done'] ?? 0 }}</span>
                <span
                    class="text-[10px] md:text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Ready</span>
            </div>
        </div>