<?php
// Connect to the database
include 'db/db.php';

// Query for Number of Races by Circuit
$sql = "SELECT circuit_id, COUNT(raceId) AS num_of_races FROM races GROUP BY circuit_id";
$result = $conn->query($sql);

// Prepare data for Chart.js
$circuit_ids = [];
$race_counts = [];
while ($row = $result->fetch_assoc()) {
    $circuit_ids[] = 'Circuit ' . $row['circuit_id'];
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
            indexAxis: 'y',  // This makes the bars horizontal
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
