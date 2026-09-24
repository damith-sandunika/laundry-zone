    <x-slot name="scripts">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const chartData = {
                today: { labels: {!! json_encode($chartData['today']['labels']) !!}, pending: {!! json_encode($chartData['today']['pending']) !!}, delivered: {!! json_encode($chartData['today']['delivered']) !!} },
                weekly: { labels: {!! json_encode($chartData['weekly']['labels']) !!}, pending: {!! json_encode($chartData['weekly']['pending']) !!}, delivered: {!! json_encode($chartData['weekly']['delivered']) !!} },
                monthly: { labels: {!! json_encode($chartData['monthly']['labels']) !!}, pending: {!! json_encode($chartData['monthly']['pending']) !!}, delivered: {!! json_encode($chartData['monthly']['delivered']) !!} },
            };

            const ctx = document.getElementById('orderChart');
            let chart = null;

            function renderChart(tab) {
                const d = chartData[tab];
                if (chart) chart.destroy();
                chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: d.labels,
                        datasets: [
                            { label: 'Processing', data: d.pending, backgroundColor: '#f59e0b', borderRadius: 6, barPercentage: 0.55 },
                            { label: 'Completed', data: d.delivered, backgroundColor: '#10b981', borderRadius: 6, barPercentage: 0.55 }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { backgroundColor: '#0f172a', padding: 12, cornerRadius: 8 } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 11, weight: '600' } } },
                            x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 12, weight: '700' } } }
                        }
                    }
                });
            }

            if (ctx) renderChart('weekly');

            const tabBtns = document.querySelectorAll('.chart-tab-btn');
            tabBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    tabBtns.forEach(b => {
                        b.className = 'chart-tab-btn px-4 py-1.5 text-xs font-bold rounded-lg transition-all text-slate-500 hover:text-slate-700';
                    });
                    btn.className = 'chart-tab-btn active-tab px-4 py-1.5 text-xs font-bold rounded-lg transition-all bg-white text-slate-800 shadow-sm ring-1 ring-slate-200/50';
                    renderChart(btn.dataset.chartTab);
                });
            });
        </script>
    </x-slot>
