<?php
// Connect to the database
include 'db/db.php';

// Query for Wins by Driver
$sql = "SELECT forename, no_of_race_wins FROM drivers ORDER BY no_of_race_wins DESC";
$result = $conn->query($sql);

// Prepare data for Chart.js
$drivers = [];
$wins = [];
while ($row = $result->fetch_assoc()) {
    $drivers[] = $row['forename'];
    $wins[] = $row['no_of_race_wins'];
}
?>

<canvas id="winsByDriverChart" width="400" height="200"></canvas>

<script>
    var ctx = document.getElementById('winsByDriverChart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($drivers); ?>,
            datasets: [{
                label: 'Wins by Driver',
                data: <?php echo json_encode($wins); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
