import Chart from 'chart.js/auto';

export function initCharts(weeklySalesData, topItems) {
    // Weekly Sales Chart
    const weeklySalesCtx = document.getElementById('weeklySalesChart')?.getContext('2d');
    if (weeklySalesCtx) {
        new Chart(weeklySalesCtx, {
            type: 'bar',
            data: {
                labels: weeklySalesData.weeks,
                datasets: [
                    {
                        label: 'Total Sales',
                        data: weeklySalesData.sales,
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1,
                        yAxisID: 'y',
                        order: 2,
                    },
                    {
                        label: 'Revenue (Rs.)',
                        data: weeklySalesData.revenue,
                        borderColor: 'rgba(34, 197, 94, 1)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        borderWidth: 2,
                        type: 'line',
                        yAxisID: 'y1',
                        order: 1,
                        fill: true,
                        tension: 0.4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { size: 12 },
                            padding: 15,
                            usePointStyle: true,
                        },
                    },
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Number of Sales',
                        },
                        ticks: {
                            beginAtZero: true,
                        },
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Revenue (Rs.)',
                        },
                        ticks: {
                            beginAtZero: true,
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                },
            },
        });
    }

    // Top Selling Items Donut Chart
    const topItemsCtx = document.getElementById('topItemsChart')?.getContext('2d');
    if (topItemsCtx) {
        const colors = [
            'rgba(255, 99, 132, 0.8)',
            'rgba(54, 162, 235, 0.8)',
            'rgba(255, 206, 86, 0.8)',
            'rgba(75, 192, 192, 0.8)',
            'rgba(153, 102, 255, 0.8)',
            'rgba(255, 159, 64, 0.8)',
            'rgba(199, 199, 199, 0.8)',
            'rgba(83, 102, 255, 0.8)',
        ];

        new Chart(topItemsCtx, {
            type: 'doughnut',
            data: {
                labels: topItems.map(item => item.name),
                datasets: [
                    {
                        label: 'Quantity Sold',
                        data: topItems.map(item => item.quantity),
                        backgroundColor: colors.slice(0, topItems.length),
                        borderColor: 'rgba(255, 255, 255, 1)',
                        borderWidth: 2,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 11 },
                            padding: 10,
                            usePointStyle: true,
                        },
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const item = topItems[context.dataIndex];
                                return context.label + ': ' + context.parsed.y + ' qty | Rs. ' + item.revenue;
                            },
                        },
                    },
                },
            },
        });
    }
}
