<?php
include 'db/db.php';

// SQL query to fetch top 5 drivers based on points
$top_drivers_sql = "SELECT d.forename, ds.points 
                    FROM drivers d 
                    JOIN driver_standings ds ON d.driverId = ds.driverId 
                    ORDER BY ds.points DESC 
                    LIMIT 5";
$top_drivers_result = $conn->query($top_drivers_sql);

// Prepare the data for Chart.js
$driver_names = [];
$driver_points = [];
if ($top_drivers_result->num_rows > 0) {
    while ($driver = $top_drivers_result->fetch_assoc()) {
        $driver_names[] = $driver['forename'];
        $driver_points[] = $driver['points'];
    }
}
?>
<canvas id="topDriversChart" width="400" height="200"></canvas>

<script>
    var ctx = document.getElementById('topDriversChart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($driver_names); ?>,
            datasets: [{
                label: 'Points',
                data: <?php echo json_encode($driver_points); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                hoverBackgroundColor: 'rgba(75, 192, 192, 0.4)', 
                hoverBorderColor: 'rgba(255, 255, 255, 1)', 
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
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.7)', 
                    titleColor: '#ffffff', 
                    bodyColor: '#ffffff' 
                }
            }
        }
    });
</script>
