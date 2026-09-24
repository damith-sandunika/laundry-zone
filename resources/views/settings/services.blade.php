<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <h2 class="ph-title">Services & Categories</h2>
                <p class="text-sm text-gray-500 mt-1">Manage all laundry services and categories here.</p>
            </div>
            <div class="flex gap-2">
                <button onclick="document.getElementById('add-category-modal').style.display='flex'"
                    class="px-4 py-2 bg-slate-800 text-white text-xs font-semibold rounded hover:bg-slate-900 transition-colors">
                    + Add Category
                </button>
                <button onclick="document.getElementById('add-service-modal').style.display='flex'"
                    class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition-colors">
                    + Add New Service
                </button>
            </div>
        </div>
    </x-slot>

    <div class="p-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden p-6">
            
            @include('settings.partials.services.categories-list')
        </div>
    </div>    @include('settings.partials.services.add-service-modal')

    @include('settings.partials.services.edit-service-modal')

    @include('settings.partials.services.add-category-modal')

    @include('settings.partials.services.edit-category-modal')
    @include('settings.partials.services.scripts')
</x-app-layout>