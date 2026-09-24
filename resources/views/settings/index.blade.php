<x-app-layout>
@php
    $isCentralized = \App\Models\Setting::get('centralized_management', '1') == '1';
    $isGlobalManager = auth()->user()->canManageGlobalConfig();
    $isSuperAdmin = auth()->user()->role === 'super_admin';
    $canManageConfig = ($isCentralized && $isGlobalManager) || (!$isCentralized && !$isSuperAdmin);
@endphp
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <h2 class="ph-title">System Settings</h2>
                <p class="text-sm text-gray-500 mt-1">Configure laundry details, services, and stock locations.</p>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div
            class="bg-green-50 text-green-700 px-6 py-4 mb-6 text-sm font-semibold border border-green-200 rounded-xl shadow-sm flex items-center">
            <svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div
            class="bg-red-50 text-red-700 px-6 py-4 mb-6 text-sm font-semibold border border-red-200 rounded-xl shadow-sm flex flex-col justify-center">
            <div class="flex items-center mb-2">
                <svg class="w-5 h-5 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
                Please fix the following errors:
            </div>
            <ul class="list-disc list-inside ml-8 text-xs font-normal">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div x-data="{ activeTab: (localStorage.getItem('settingsActiveTab') || 'general'), serviceModalOpen: false, cubbyModalOpen: false }"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        <!-- Premium Horizontally Scrollable Tab Header -->
        <div class="p-3 border-b border-gray-100 bg-gray-50/50 overflow-x-auto scrollbar-none">
            <div class="flex gap-2 w-max bg-gray-200/50 p-1 rounded-xl">
                <button @click="activeTab = 'general'; localStorage.setItem('settingsActiveTab', 'general')"
                    :class="activeTab === 'general' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'"
                    class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                    General Settings
                </button>

                @if($canManageConfig)
                    <button @click="activeTab = 'extras'; localStorage.setItem('settingsActiveTab', 'extras')"
                        :class="activeTab === 'extras' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'"
                        class="relative px-3 py-2 text-sm font-medium rounded-xl transition-all duration-200 whitespace-nowrap">
                        Extras
                    </button>
                    <button
                        @click="activeTab = 'turnaround_types'; localStorage.setItem('settingsActiveTab', 'turnaround_types')"
                        :class="activeTab === 'turnaround_types' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'"
                        class="relative px-3 py-2 text-sm font-medium rounded-xl transition-all duration-200 whitespace-nowrap">
                        Turnarounds
                    </button>
                    <button @click="activeTab = 'packages'; localStorage.setItem('settingsActiveTab', 'packages')"
                        :class="activeTab === 'packages' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'"
                        class="relative px-3 py-2 text-sm font-medium rounded-xl transition-all duration-200">
                        Packages
                    </button>
                    <button @click="activeTab = 'delivery'; localStorage.setItem('settingsActiveTab', 'delivery')"
                        :class="activeTab === 'delivery' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'"
                        class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                        Delivery Settings
                    </button>
                    <button @click="activeTab = 'pickup_times'; localStorage.setItem('settingsActiveTab', 'pickup_times')"
                        :class="activeTab === 'pickup_times' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'"
                        class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                        Pickup Times
                    </button>
                    <button
                        @click="activeTab = 'payment_methods'; localStorage.setItem('settingsActiveTab', 'payment_methods')"
                        :class="activeTab === 'payment_methods' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'"
                        class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                        Payment Methods
                    </button>
                @endif

                @if(auth()->user()->role !== 'super_admin')
                <button @click="activeTab = 'cubbies'; localStorage.setItem('settingsActiveTab', 'cubbies')"
                    :class="activeTab === 'cubbies' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'"
                    class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                    Stock Locations
                </button>
                @endif

                @if(auth()->user()->role === 'super_admin')
                    <button
                        @click="activeTab = 'notifications'; localStorage.setItem('settingsActiveTab', 'notifications')"
                        :class="activeTab === 'notifications' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'"
                        class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                        Notifications
                    </button>
                @endif
            </div>
        </div>

        <div class="p-6">
            <!-- GENERAL SETTINGS TAB -->
            @include('settings.tabs.general')

            @if($canManageConfig)
            <!-- EXTRAS TAB -->
            <div x-show="activeTab === 'extras'" style="display: none;">
                @include('settings.tabs.extras')
            </div>
            @endif

            @if($canManageConfig)
            <!-- TURNAROUND TYPES TAB -->
            <div x-show="activeTab === 'turnaround_types'" style="display: none;">
                @include('settings.tabs.turnaround_types')
            </div>
            @endif

            @if(auth()->user()->role !== 'super_admin')
            <!-- CUBBIES (STOCK LOCATIONS) TAB -->
            <div x-show="activeTab === 'cubbies'" style="display: none;">
                @include('settings.tabs.cubbies')
            </div>
            @endif

            @if($canManageConfig)
            <!-- DELIVERY SETTINGS TAB -->
            <div x-show="activeTab === 'delivery'" style="display: none;">
                @include('settings.tabs.delivery')
            </div>
            @endif

            @if($canManageConfig)
            <!-- PICKUP TIMES TAB -->
            <div x-show="activeTab === 'pickup_times'" style="display: none;">
                @include('settings.tabs.pickup_times')
            </div>
            @endif

            @if($canManageConfig)
            <!-- PAYMENT METHODS TAB -->
            <div x-show="activeTab === 'payment_methods'" style="display: none;">
                @include('settings.tabs.payment_methods')
            </div>
            @endif

            <!-- NOTIFICATIONS TAB -->
            @include('settings.tabs.notifications')

            @if($canManageConfig)
            <!-- PACKAGES TAB -->
            @include('settings.tabs.packages')
            @endif

        </div>

        @include('settings.tabs.modals')

</x-app-layout>