<?php
include 'db/db.php';

// Query to fetch top 5 drivers based on points
$pipeline = [
    ['$lookup' => [
        'from' => 'driver_standings',
        'localField' => 'driverId',
        'foreignField' => 'driverId',
        'as' => 'standings'
    ]],
    ['$unwind' => '$standings'],
    ['$sort' => ['standings.points' => -1]],
    ['$limit' => 5]
];

$result = $db->drivers->aggregate($pipeline);

// Prepare the data for Chart.js
$driver_names = [];
$driver_points = [];
foreach ($result as $driver) {
    $driver_names[] = $driver['forename'];
    $driver_points[] = $driver['standings']['points'];
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
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {},
                tooltip: {}
            }
        }
    });
</script>
