<?php
// Connect to the database
include 'db/db.php';

// Query for top lap times (ascending order) with driver names
$pipeline = [
    ['$lookup' => [
        'from' => 'drivers',
        'localField' => 'driverId',
        'foreignField' => 'driverId',
        'as' => 'driver_details'
    ]],
    ['$unwind' => '$driver_details'],
    ['$sort' => ['milliseconds' => 1]],
    ['$limit' => 10]
];

$result = $db->lap_times->aggregate($pipeline);

// Prepare data for Chart.js
$lap_times = [];
$drivers = [];
foreach ($result as $row) {
    $lap_times[] = $row['milliseconds'] / 1000;
    $drivers[] = 'Race ' . $row['raceId'] . ', ' . $row['driver_details']['forename'];
}
?>

<canvas id="topLapTimesChart" width="400" height="200"></canvas>

<script>
    var ctx = document.getElementById('topLapTimesChart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($drivers); ?>,
            datasets: [{
                label: 'Top Lap Times (in seconds)',
                data: <?php echo json_encode($lap_times); ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: false
                },
                x: {}
            },
            plugins: {
                legend: {}
            }
        }
    });
</script>
