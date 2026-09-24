<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div>
                <h2 class="ph-title">Order
                    Management</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Manage and track customer laundry
                    states</p>
            </div>
            <a href="{{ route('pos.index') }}"
                style="display:flex;align-items:center;gap:7px;padding:10px 18px;background:#1d4ed8;color:white;font-size:13px;font-weight:700;border-radius:10px;text-decoration:none;box-shadow:0 2px 10px rgba(29,78,216,0.3);transition:all 0.15s;"
                onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='#1d4ed8'">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                </svg>
                New POS Order
            </a>
        </div>
    </x-slot>

    @if(session('success'))
        <div
            style="margin-bottom:16px;padding:12px 18px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;font-size:13px;font-weight:700;color:#16a34a;">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div
            style="margin-bottom:16px;padding:12px 18px;background:#fef2f2;border:1.5px solid #fecaca;border-radius:10px;font-size:13px;font-weight:700;color:#dc2626;">
            <div style="display:flex;align-items:center;gap:6px;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- Scripts must be loaded BEFORE x-data so Alpine.data('ordersPage') is registered first --}}
    @include('orders.partials.scripts')

    <div x-data="ordersPage()">
        @include('orders.partials.stats-cards')

        <!-- TABLE SECTION -->
        <div
            style="background:white;border-radius:16px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);overflow:hidden;display:flex;flex-direction:column;gap:0;">
            @include('orders.partials.filters')
            @include('orders.partials.table')
        </div>

        {{-- All modals INSIDE x-data scope --}}
        @include('orders.partials.modals')

        <x-modals.request-payment />
        <x-modals.review-slip />

    </div>
</x-app-layout>