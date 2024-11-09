<?php
// Connect to the database
include 'db/db.php';

// Query for Fastest Lap Speeds, joining results and drivers collections
$pipeline = [
    ['$lookup' => [
        'from' => 'drivers',
        'localField' => 'driverId',
        'foreignField' => 'driverId',
        'as' => 'driver_details'
    ]],
    ['$unwind' => '$driver_details'],
    ['$sort' => ['fastestLapSpeed' => -1]],
    ['$limit' => 10]
];

$result = $db->results->aggregate($pipeline);

// Prepare data for Chart.js
$lap_speeds = [];
$races = [];
foreach ($result as $row) {
    $lap_speeds[] = $row['fastestLapSpeed'];
    $races[] = 'Race ' . $row['raceId'] . ', ' . $row['driver_details']['forename'];
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
