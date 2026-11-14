    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Chart Defaults for Modern Aesthetic
            Chart.defaults.font.family = "'Inter', sans-serif";
            Chart.defaults.color = "#94a3b8";
            
            // --- Sales By Branch Chart ---
            const branchSalesCtx = document.getElementById('branchSalesChart').getContext('2d');
            const branchSalesData = {!! json_encode($chartBranchSales) !!};
            
            new Chart(branchSalesCtx, {
                type: 'bar',
                data: {
                    labels: branchSalesData.labels,
                    datasets: [{
                        label: 'Sales Revenue (Rs)',
                        data: branchSalesData.data,
                        backgroundColor: '#10b981', // Emerald 500
                        borderRadius: 6,
                        barPercentage: 0.6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            padding: 12,
                            titleFont: { size: 13, weight: 'bold' },
                            bodyFont: { size: 13 },
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return 'Rs. ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            border: { display: false },
                            ticks: {
                                callback: function(value) {
                                    if(value >= 1000) return (value/1000) + 'k';
                                    return value;
                                }
                            }
                        },
                        x: {
                            border: { display: false },
                            grid: { display: false }
                        }
                    }
                }
            });

            // --- Cash Balances Chart ---
            const cashBalanceCtx = document.getElementById('cashBalanceChart').getContext('2d');
            const cashBalanceData = {!! json_encode($chartBranchBalances ?? ['labels'=>[], 'data'=>[]]) !!};
            
            new Chart(cashBalanceCtx, {
                type: 'bar',
                data: {
                    labels: cashBalanceData.labels,
                    datasets: [{
                        label: 'Cash on Hand (Rs)',
                        data: cashBalanceData.data,
                        backgroundColor: '#3b82f6', // Blue 500
                        borderRadius: 6,
                        barPercentage: 0.6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            padding: 12,
                            titleFont: { size: 13, weight: 'bold' },
                            bodyFont: { size: 13 },
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return 'Rs. ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            border: { display: false },
                            ticks: {
                                callback: function(value) {
                                    if(value >= 1000) return (value/1000) + 'k';
                                    return value;
                                }
                            }
                        },
                        x: {
                            border: { display: false },
                            grid: { display: false }
                        }
                    }
                }
            });
        });
    </script>
