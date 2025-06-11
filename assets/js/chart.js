jQuery(document).ready(function($) {
    const ctx = document.getElementById('wcpcPriceChart');
    let chartInstance = null;

    if (ctx && typeof wcpc_chart_data !== 'undefined') {
        console.log('Chart initialized, wcpc_chart_data:', wcpc_chart_data);
        
        // Function to create/update chart
        function updateChart(chartData) {
            console.log('updateChart called with:', chartData);
            
            if (chartData.has_data && chartData.data.length >= 1) {
                // Show the chart container
                $(ctx).parent().show();
                
                // Destroy existing chart if it exists
                if (chartInstance) {
                    chartInstance.destroy();
                }
                
                // Create new chart
                chartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: chartData.label,
                            data: chartData.data,
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
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: false,
                                ticks: {
                                    callback: function(value, index, ticks) {
                                        return '$' + value;
                                    }
                                }
                            }
                        }
                    }
                });
                console.log('Chart created successfully');
            } else {
                // Hide chart if no data
                $(ctx).parent().hide();
                console.log('Chart hidden - no data');
            }
        }
        
        // Handle variable products
        if (wcpc_chart_data.is_variable) {
            console.log('Variable product detected');
            
            // Listen for variation selection using body delegation (more reliable)
            $('body').on('found_variation', 'form.variations_form', function(event, variation) {
                console.log('Variation found event triggered:', variation);
                
                if (variation && variation.variation_id) {
                    console.log('Fetching data for variation ID:', variation.variation_id);
                    
                    // Fetch chart data for this variation
                    $.ajax({
                        url: wcpc_chart_data.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'wcpc_get_variation_chart_data',
                            variation_id: variation.variation_id,
                            nonce: wcpc_chart_data.nonce
                        },
                        beforeSend: function() {
                            console.log('AJAX request started');
                        },
                        success: function(response) {
                            console.log('AJAX response received:', response);
                            if (response.success) {
                                updateChart(response.data);
                            } else {
                                console.log('AJAX success but response failed:', response);
                                $(ctx).parent().hide();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log('AJAX request failed:', status, error);
                            $(ctx).parent().hide();
                        }
                    });
                }
            });
            
            // Hide chart when variation is reset
            $('body').on('reset_data', 'form.variations_form', function() {
                console.log('Variation reset event triggered');
                $(ctx).parent().hide();
                if (chartInstance) {
                    chartInstance.destroy();
                    chartInstance = null;
                }
            });
            
        } else {
            // Handle simple products
            console.log('Simple product detected');
            updateChart(wcpc_chart_data);
        }
    } else {
        console.log('Chart context or data not found');
        if (!ctx) console.log('Canvas element not found');
        if (typeof wcpc_chart_data === 'undefined') console.log('wcpc_chart_data not defined');
    }
});