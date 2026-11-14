<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('workshopOperations', () => ({
            currentTab: '{{ $currentTab }}',
            selectedBranch: '{{ $selectedBranch ?? '' }}',
            selectedDispatches: [],
            showBulkModal: false,
            bulkStatus: '',
            allCategories: {{ Js::from($categories) }},
            minimumDeliveryCost: {{ (float)($minimumDeliveryCost ?? 0) }},
            pageDispatches: {{ Js::from($dispatches->pluck('id')) }},
            filteredDispatches: {{ Js::from($dispatches->items()) }},
            autoPrintLabel: {{ \App\Models\Setting::get('auto_print_label', '0') == '1' ? 'true' : 'false' }},
            autoPrintWorkshopChit: {{ \App\Models\Setting::get('auto_print_workshop_chit', '1') == '1' ? 'true' : 'false' }},
            
            openFinalizePage(dispatch) {
                window.location.href = '/workshop/orders/' + dispatch.order.order_number + '/finalize-bill';
            },

            toggleSelectAll(e) {
                this.selectedDispatches = e.target.checked ? this.pageDispatches : [];
            },

            async workshopAction(dispatchId, action) {
                try {
                    const res = await fetch(`/workshop/dispatches/${dispatchId}/${action}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        if (action === 'start-processing' && data.order_number && this.autoPrintWorkshopChit) {
                            window.open(`/orders/${data.order_number}/chit`, '_blank');
                        }
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to update status.');
                    }
                } catch (err) {
                    console.error('AJAX Error:', err);
                    alert('Connection error');
                }
            },

            async submitBulkUpdate() {
                if (!this.bulkStatus) return alert('Please select an action');
                if (this.selectedDispatches.length === 0) return alert('No orders selected');
                
                try {
                    const res = await fetch('{{ route('workshop.bulkStatus') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        },
                        body: JSON.stringify({
                            dispatch_ids: this.selectedDispatches,
                            status: this.bulkStatus
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Error updating statuses');
                    }
                } catch (err) {
                    console.error('AJAX Error:', err);
                    alert('Connection error');
                }
            },

            @include('workshop.partials.operations_payments_js')
            @include('workshop.partials.operations_delivery_handover_js')
        }));
    });
</script>
