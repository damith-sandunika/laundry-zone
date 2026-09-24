    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ApexCharts logic
            const dailyData = @json($dailyRevenue);
            const walkinRev = {{ $walkinRevenue }};
            const deliveryRev = {{ $deliveryRevenue }};
            
            // Format dates and series for revenue trend
            const categories = dailyData.map(d => {
                let date = new Date(d.date);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            });
            const seriesData = dailyData.map(d => parseFloat(d.revenue));

            // Revenue Chart
            var revOptions = {
                series: [{
                    name: 'Revenue',
                    data: seriesData
                }],
                chart: {
                    height: 300,
                    type: 'area',
                    toolbar: { show: false },
                    fontFamily: 'inherit'
                },
                colors: ['#2563eb'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                xaxis: {
                    categories: categories,
                    labels: { style: { colors: '#64748b', fontWeight: 600 } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        style: { colors: '#64748b', fontWeight: 600 },
                        formatter: function(val) { return "Rs." + val.toLocaleString() }
                    }
                },
                grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                tooltip: {
                    theme: 'light',
                    y: { formatter: function(val) { return "Rs." + val.toLocaleString() } }
                }
            };

            var revChart = new ApexCharts(document.querySelector("#revenueChart"), revOptions);
            revChart.render();

            // Payment Chart (Donut)
            let collectedCash = {{ $collectedCash ?? 0 }};
            let collectedCard = {{ $collectedCard ?? 0 }};
            let collectedBank = {{ $collectedBank ?? 0 }};
            let totalPending = {{ $totalPending ?? 0 }};

            if (collectedCash > 0 || collectedCard > 0 || collectedBank > 0 || totalPending > 0) {
                var payOptions = {
                    series: [collectedCash, collectedCard, collectedBank, totalPending],
                    labels: ['Cash', 'Card', 'Bank Transfer', 'Pending Receivables'],
                    chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
                    colors: ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b'],
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '70%',
                                labels: {
                                    show: true,
                                    name: { fontSize: '14px', fontWeight: 600, color: '#64748b' },
                                    value: { fontSize: '24px', fontWeight: 900, color: '#0f172a', formatter: function(val) { return "Rs." + val.toLocaleString() } },
                                    total: {
                                        show: true,
                                        label: 'Total Expected',
                                        formatter: function (w) {
                                            return "Rs." + (w.globals.seriesTotals.reduce((a, b) => a + b, 0)).toLocaleString()
                                        }
                                    }
                                }
                            }
                        }
                    },
                    dataLabels: { enabled: false },
                    stroke: { width: 0 },
                    legend: { position: 'bottom', horizontalAlign: 'center', fontWeight: 700, markers: { radius: 12 } },
                    tooltip: { theme: 'light', y: { formatter: function(val) { return "Rs." + val.toLocaleString() } } }
                };
                var payChart = new ApexCharts(document.querySelector("#paymentChart"), payOptions);
                payChart.render();
            } else {
                document.querySelector("#paymentChart").innerHTML = '<div class="text-center text-slate-400 font-semibold p-10">No payment data to display</div>';
            }
        });

        function reportData() {
            return {
                activeReport: '{{ request()->has('credit_page') ? 'credit_orders' : 'expenses' }}',
                expenseCategoryFilter: '',
                orders: @json($orders->getCollection()),
                pendingModalOpen: false,
                selectedServiceName: '',
                selectedServiceOrders: [],
                
                collectModalOpen: false,
                collectOrderId: null,
                collectOrderNumber: '',
                collectAmount: 0,

                openCollectModal(id, number, amount) {
                    this.collectOrderId = id;
                    this.collectOrderNumber = number;
                    this.collectAmount = amount;
                    this.collectModalOpen = true;
                },

                openPendingOrders(serviceName) {
                    this.selectedServiceName = serviceName;
                    
                    let filtered = [];
                    this.orders.forEach(order => {
                        const total = parseFloat(order.total) || 0;
                        const paid = parseFloat(order.paid_amount) || 0;
                        if (total <= paid) return;

                        if (serviceName === '__all__') {
                            order.service_due = total - paid;
                            filtered.push(order);
                            return;
                        }

                        let matchFound = false;
                        let serviceValue = 0;
                        
                        if (order.items && order.items.length > 0) {
                            order.items.forEach(item => {
                                const svc = item.service_type || item.serviceType || item.service_type_id;
                                if (!svc) return;
                                const name = typeof svc === 'object' ? (svc.name || '') : '';
                                if (name === serviceName) {
                                    matchFound = true;
                                    serviceValue += parseFloat(item.total_price) || 0;
                                }
                            });
                        }

                        if (matchFound) {
                            // Calculate specific service due
                            let subtotal = parseFloat(order.subtotal) || 0;
                            let discount = parseFloat(order.discount) || 0;
                            let proportion = subtotal > 0 ? (1 - (discount / subtotal)) : 1;
                            let finalServiceValue = serviceValue * proportion;
                            
                            // Calculate pending proportion
                            let orderPending = total - paid;
                            let pendingProportion = total > 0 ? (orderPending / total) : 0;
                            
                            order.service_due = finalServiceValue * pendingProportion;
                            filtered.push(order);
                        }
                    });
                    
                    this.selectedServiceOrders = filtered;
                    this.pendingModalOpen = true;
                }
            }
        }
    </script>
