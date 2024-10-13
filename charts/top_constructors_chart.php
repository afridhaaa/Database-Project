<?php
// Include your DB connection
include 'db/db.php';

// SQL query to fetch top 5 constructors based on points
$top_constructors_sql = "SELECT constructor_name, constructor_points 
                         FROM constructors 
                         ORDER BY constructor_points DESC 
                         LIMIT 5";
$top_constructors_result = $conn->query($top_constructors_sql);

// Prepare the data for Chart.js
$constructor_names = [];
$constructor_points = [];
if ($top_constructors_result->num_rows > 0) {
    while ($constructor = $top_constructors_result->fetch_assoc()) {
        $constructor_names[] = $constructor['constructor_name'];
        $constructor_points[] = $constructor['constructor_points'];
    }
}
?>

<canvas id="topConstructorsChart" width="400" height="200"></canvas>

<script>
    var ctx = document.getElementById('topConstructorsChart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($constructor_names); ?>,
            datasets: [{
                label: 'Points',
                data: <?php echo json_encode($constructor_points); ?>,
                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(255, 255, 255, 1)', 
                pointBorderColor: 'rgba(255, 255, 255, 1)', 
                pointBorderWidth: 2,
                pointRadius: 4
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#ffffff' 
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.2)' 
                    }
                },
                x: {
                    ticks: {
                        color: '#ffffff' 
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.2)' 
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: '#ffffff'
                    }
                }
            }
        }
    });
</script>
