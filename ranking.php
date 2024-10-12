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
</head>
<body>
   

    <div class="hero">
        <div class="container">
            <div class="box">
                <div class="box-title"><h4>Ranking</h4></div>
                <div class="box-content">
                <div class="table-container">
                <table>
            <thead>
                <tr>
                    <th>Forename</th>
                    <th>Position</th>
                    <th>Nationality</th>
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
                       
                        echo "<td>" . $row['position'] . "</td>";
                       
                        echo "<td>" . $row['nationality'] . "</td>";
                        echo "<td><a href='" . $row['url'] . "' target='_blank'>View More</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No data available</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
                </div>

                </div>
            </div>
            
            
        </div>
    </div>


    <script>
        document.getElementById("menu-icon").addEventListener("click", function() {
            var navLinks = document.getElementById("nav-links");
            navLinks.classList.toggle("active"); // Toggle the "active" class
          });
          
    </script>
</body>
</html>