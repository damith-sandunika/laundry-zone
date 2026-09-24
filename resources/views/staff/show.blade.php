<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <div>
                <p class="text-xs text-gray-400 mb-1"><a href="{{ route('staff.index') }}" class="hover:text-blue-600">← Staff Directory</a></p>
                <h2 class="text-xl font-bold text-gray-800">{{ $staff->name }}'s Profile</h2>
            </div>
            <div class="flex items-center gap-2">
                <button @click="$dispatch('open-allowance')" class="px-3.5 py-2 bg-emerald-600 text-white font-bold text-xs rounded-lg hover:bg-emerald-700 transition-colors shadow-sm cursor-pointer">
                    + Allowance
                </button>
                <button @click="$dispatch('open-deduction')" class="px-3.5 py-2 bg-rose-600 text-white font-bold text-xs rounded-lg hover:bg-rose-700 transition-colors shadow-sm cursor-pointer">
                    - Deduction
                </button>
                <a href="{{ route('staff.edit', $staff) }}" class="px-4 py-2 bg-gray-100 text-gray-700 font-bold text-xs rounded-lg hover:bg-gray-200 transition-colors">
                    Edit Profile
                </a>
            </div>
        </div>
    </x-slot>

    <div x-data="{ 
        attendanceModalOpen: false, 
        leaveModalOpen: false, 
        advanceModalOpen: false,
        allowanceModalOpen: false,
        deductionModalOpen: false
    }" 
    @open-allowance.window="allowanceModalOpen = true"
    @open-deduction.window="deductionModalOpen = true"
    class="max-w-6xl mx-auto space-y-6">
        
        @include('staff.partials.profile_card')

        <!-- Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column: Attendance & Leaves -->
            <div class="lg:col-span-2 space-y-6">
                @include('staff.partials.attendance_grid')

                @include('staff.partials.leave_history')
            </div>

            <!-- Right Column: Salary Advances & Payroll -->
            <div class="space-y-6">
                @include('staff.partials.payroll_summary')

            </div>
        </div>

        <!-- MODALS -->
        @include('staff.partials.modals')

    </div>

    @push('scripts')
    @include('staff.partials.scripts')
    @endpush
</x-app-layout>


