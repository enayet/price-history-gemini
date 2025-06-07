document.addEventListener('DOMContentLoaded', function () {
    // Handle both old ID format (for backward compatibility) and new product-specific format
    let ctx = document.getElementById('wcpcPriceChart');
    
    // If the old ID doesn't exist, try to find the product-specific one
    if (!ctx && typeof wcpc_chart_data !== 'undefined' && wcpc_chart_data.product_id) {
        ctx = document.getElementById('wcpcPriceChart-' + wcpc_chart_data.product_id);
    }

    if (ctx && typeof wcpc_chart_data !== 'undefined') {
        // Ensure we have data to display
        if (!wcpc_chart_data.data || wcpc_chart_data.data.length === 0) {
            return;
        }
        
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
});