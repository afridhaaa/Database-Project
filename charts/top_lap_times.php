<?php
// Connect to the database
include 'db/db.php';

// Query to get top lap times (in milliseconds) along with driver names
$sql = "
    SELECT lap_times.raceId, drivers.forename, lap_times.milliseconds
    FROM lap_times
    INNER JOIN drivers ON lap_times.driverId = drivers.driverId
    ORDER BY lap_times.milliseconds ASC
    LIMIT 10";

$result = $conn->query($sql);

// Prepare data for Chart.js
$lap_times = [];
$drivers = [];

while ($row = $result->fetch_assoc()) {
    $lap_times[] = $row['milliseconds'] / 1000; // Convert milliseconds to seconds
    $drivers[] = 'Race ' . $row['raceId'] . ', ' . $row['forename']; // Display race ID and driver for labels
}
?>

<canvas id="topLapTimesChart" width="400" height="200"></canvas>

<script>
    var ctx = document.getElementById('topLapTimesChart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'line',  
        data: {
            labels: <?php echo json_encode($drivers); ?>, // Display driver names in the labels
            datasets: [{
                label: 'Top Lap Times (in seconds)',
                data: <?php echo json_encode($lap_times); ?>,
                fill: false,
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: false, 
                    title: {
                        display: true,
                        text: 'Time (in seconds)' // Add a label to y-axis
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Race and Driver'
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        }
    });
</script>
