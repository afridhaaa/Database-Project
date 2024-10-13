<?php
// Connect to the database
include 'db/db.php';

// Query for Fastest Lap Speeds, joining drivers to get the driver name
$sql = "
    SELECT results.raceId, drivers.forename, results.fastestLapSpeed 
    FROM results
    INNER JOIN drivers ON results.driverId = drivers.driverId
    ORDER BY results.fastestLapSpeed DESC
    LIMIT 10";
    
$result = $conn->query($sql);

// Prepare data for Chart.js
$lap_speeds = [];
$races = [];
while ($row = $result->fetch_assoc()) {
    $lap_speeds[] = $row['fastestLapSpeed'];
    $races[] = 'Race ' . $row['raceId'] . ', ' . $row['forename'];
}
?>

<canvas id="fastestLapSpeedsChart" width="400" height="200"></canvas>

<script>
    var ctx = document.getElementById('fastestLapSpeedsChart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'bar',  
        data: {
            labels: <?php echo json_encode($races); ?>,
            datasets: [{
                label: 'Fastest Lap Speeds (in km/h)',
                data: <?php echo json_encode($lap_speeds); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: false,
                    min: 240, 
                    max: 260, 
                    title: {
                        display: true,
                        text: 'Speed (km/h)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Race and Driver'
                    }
                }
            }
        }
    });
</script>
