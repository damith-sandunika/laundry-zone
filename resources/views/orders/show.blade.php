<x-app-layout>
        @include('orders.partials.header')

@if(session('success'))
        <div
            style="margin-bottom:12px;padding:10px 14px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;font-size:12px;font-weight:700;color:#16a34a;">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div
            style="margin-bottom:12px;padding:10px 14px;background:#fef2f2;border:1.5px solid #fecaca;border-radius:10px;font-size:12px;font-weight:700;color:#dc2626;">
            <div style="display:flex;align-items:center;gap:6px;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div
            style="margin-bottom:12px;padding:10px 14px;background:#fef2f2;border:1.5px solid #fecaca;border-radius:10px;font-size:12px;font-weight:700;color:#dc2626;">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>Please fix the following errors:</span>
            </div>
            <ul style="margin:0;padding-left:20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('print_bill_id'))
        <script>
            window.onload = function () {
                window.open("{{ route('orders.print', session('print_bill_id')) }}", "_blank");
            };
        </script>
    @endif

    @include('orders.partials.workflow_actions')

        @include('orders.partials.correction_requests')

    <div class="flex flex-col gap-4 items-start">
        <div class="w-full flex flex-col gap-4">
            @include('orders.partials.main_column')

            <div class="max-w-sm flex flex-col gap-4 mt-4">
                @include('orders.partials.payment_slips')

            </div>
        </div>

        @include('orders.partials.maps')



</x-app-layout>
