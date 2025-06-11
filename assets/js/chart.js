document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('wcpcPriceChart');

    if (ctx && typeof wcpc_chart_data !== 'undefined') {
        // Only show chart if we have actual data
        if (wcpc_chart_data.has_data && wcpc_chart_data.data.length > 1) {
            // Show the chart container
            ctx.parentElement.style.display = 'block';
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: wcpc_chart_data.labels,
                    datasets: [{
                        label: wcpc_chart_data.label,
                        data: wcpc_chart_data.data,
                        fill: false,
                        borderColor: 'rgb(128, 128, 128)',
                        tension: 0.1,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgb(75, 192, 192)',
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false // Hide the legend as it's self-explanatory
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            ticks: {
                                 // Include a currency symbol in the ticks
                                callback: function(value, index, ticks) {
                                    // A proper implementation would pass the currency symbol from PHP
                                    return '$' + value;
                                }
                            }
                        }
                    }
                }
            });
        }
    }
});