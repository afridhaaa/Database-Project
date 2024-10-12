<?php

include 'db/db.php';

// SQL query to join the driver_standings and drivers tables
$sql = "SELECT drivers.forename, drivers.nationality, drivers.url, driver_standings.points, driver_standings.position, driver_standings.wins 
        FROM drivers 
        INNER JOIN driver_standings ON drivers.driverId = driver_standings.driverId";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            justify-content: space-between;
            align-items: center;
            padding: 80px;
            gap: 25px;
        }

        @media (max-width: 600px){
            .container{
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
      }
        }
    </style>
</head>
<body>
    

    <div class="hero">
        <div class="container">
            <div class="box">
                <div class="box-title"><h4>Driver Wins</h4></div>
                <div class="box-content">
                <div class="table-container">
                <table>
            <thead>
                <tr>
                    <th>Forename</th>
                    <th>Wins</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Check if the query returned results
                if ($result->num_rows > 0) {
                    // Output each row of data
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['forename'] . "</td>";
                        echo "<td>" . $row['wins'] . "</td>";
                        echo "<td><a href='" . $row['url'] . "' target='_blank'>View More</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No data available</td></tr>";
                }
                ?>
            </tbody>
        </table>
                </div>
                </div>
            </div>

            <div class="box">
                <div class="box-title"><h4>Driver Points</h4></div>
                <div class="box-content">
                <div class="table-container">
                <table>
            <thead>
                <tr>
                    <th>Forename</th>
                    <th>Points</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Use the same $result to show a different table content if needed
                $result->data_seek(0); // Rewind result pointer if using same data
                if ($result->num_rows > 0) {
                    // Output each row of data
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['forename'] . "</td>";
                        echo "<td>" . $row['points'] . "</td>";
                        echo "<td><a href='" . $row['url'] . "' target='_blank'>View More</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No data available</td></tr>";
                }
                ?>
            </tbody>
        </table>
                </div>
                </div>
            </div>
            
        </div>
    </div>

    <?php
    
    $conn->close();
    ?>

    <script>
        document.getElementById("menu-icon").addEventListener("click", function() {
            var navLinks = document.getElementById("nav-links");
            navLinks.classList.toggle("active"); // Toggle the "active" class
        });
    </script>
</body>
</html>
