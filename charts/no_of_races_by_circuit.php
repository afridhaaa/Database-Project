<?php
// Connect to the database
include 'db/db.php';

// Query for Number of Races by Circuit
$pipeline = [
    ['$group' => ['_id' => '$circuit_id', 'num_of_races' => ['$sum' => 1]]]
];

$result = $db->races->aggregate($pipeline);

// Prepare data for Chart.js
$circuit_ids = [];
$race_counts = [];
foreach ($result as $row) {
    $circuit_ids[] = 'Circuit ' . $row['_id'];
    $race_counts[] = $row['num_of_races'];
}
?>

<canvas id="racesByCircuitChart" width="400" height="200"></canvas>

<script>
    var ctx = document.getElementById('racesByCircuitChart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'bar',  
        data: {
            labels: <?php echo json_encode($circuit_ids); ?>,
            datasets: [{
                label: 'Number of Races by Circuit',
                data: <?php echo json_encode($race_counts); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            scales: {
                x: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Races'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Circuit'
                    }
                }
            }
        }
    });
</script>
