<x-app-layout>

    @include('workshop.partials.finalize_bill.styles')

    <!-- Wrapping to override default app layout padding for a full height POS -->
    <div class="pos-wrapper -mt-6 -mx-4 lg:-mx-8 h-[calc(100vh-64px)] bg-slate-100 p-3 lg:p-4" x-data="finalizeBill()">

        <div class="flex flex-col lg:flex-row gap-3 lg:gap-4 h-full overflow-hidden">

            @include('workshop.partials.finalize_bill.services-left')

            @include('workshop.partials.finalize_bill.order-panel-right')
        </div>
    </div>

    @include('workshop.partials.finalize_bill.scripts')
</x-app-layout>