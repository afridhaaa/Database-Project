<?php
// Include your DB connection
include 'db/db.php';

// Fetch top 5 constructors based on points
$result = $db->constructors->find(
    [],
    ['sort' => ['constructor_points' => -1], 'limit' => 5]
);

// Prepare the data for Chart.js
$constructor_names = [];
$constructor_points = [];
foreach ($result as $constructor) {
    $constructor_names[] = $constructor['constructor_name'];
    $constructor_points[] = $constructor['constructor_points'];
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
                    beginAtZero: true
                },
                x: {}
            },
            plugins: {
                legend: {
                    labels: {}
                }
            }
        }
    });
</script>
