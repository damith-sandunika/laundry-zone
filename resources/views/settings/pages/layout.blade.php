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
                <h2 class="ph-title">{{ $title ?? 'Settings' }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $description ?? 'Manage system configuration' }}</p>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="bg-green-50 text-green-700 px-6 py-4 mb-6 text-sm font-semibold border border-green-200 rounded-xl shadow-sm flex items-center">
            <svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 text-red-700 px-6 py-4 mb-6 text-sm font-semibold border border-red-200 rounded-xl shadow-sm flex flex-col justify-center">
            <div class="flex items-center mb-2">
                <svg class="w-5 h-5 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
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

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden p-6" x-data="{ activeTab: '{{ str_replace('-', '_', request()->segment(2) ?? 'general') }}', serviceModalOpen: false, cubbyModalOpen: false }">
        {{ $slot }}
        @include('settings.tabs.modals')
    </div>
</x-app-layout>