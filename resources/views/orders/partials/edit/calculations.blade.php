<script>
    window.finalizeBillCalculations = {
        getItemTotal(item, allTurnaroundTypes, allExtras) {
            let base = parseFloat(item.base_price || 0) * parseFloat(item.qty || 1);
            let turnaroundCharge = 0;
            if (item.turnaround_type_id === 'custom') {
                turnaroundCharge = base * (parseFloat(item.custom_turnaround_percentage || 0) / 100);
            } else if (item.turnaround_type_id) {
                let tt = allTurnaroundTypes.find(t => t.id == item.turnaround_type_id);
                if (tt) {
                    if (tt.markup_type === 'percentage') {
                        turnaroundCharge = base * (parseFloat(tt.markup_value) / 100);
                    } else {
                        turnaroundCharge = parseFloat(tt.markup_value) * (parseFloat(item.qty) || 1);
                    }
                }
            }
            let extrasTotal = 0;
            if (item.selected_extras && item.selected_extras.length > 0) {
                for (let ex of item.selected_extras) {
                    let eData = allExtras.find(e => e.id == ex.id);
                    if (eData) {
                        extrasTotal += parseFloat(eData.price) * parseFloat(ex.qty || 1);
                    }
                }
            }
            return base + turnaroundCharge + extrasTotal;
        },

        getCategoryString(serviceId, services, categories) {
            let serviceArray = services || [];
            let service = serviceArray.find(s => s.id === serviceId);
            if (!service) return '';
            let cId = service.service_category_id || service.category_id;
            for (let cat of categories) {
                if (cat.id == cId) return cat.name;
                if (cat.children) {
                    for (let sub of cat.children) {
                        if (sub.id == cId) return cat.name + ' > ' + sub.name;
                    }
                }
            }
            return '';
        }
    };
</script>
