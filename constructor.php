<?php

include 'db/db.php';

// SQL query to fetch constructor data
$sql = "SELECT constructor_name, no_of_pole_positions, no_of_titles, constructor_points, nationality, url FROM constructors";
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
    <nav>
        <div class="menu-icon" id="menu-icon">&#9776; <h1>Formula 1</h1></div> <!-- Hamburger Icon -->
            <ul id="nav-links">
            <li><a href="index.php" id="logo">FORMULA 1</a></li>
              <li><a href="races.php">Races</a></li>
              <li><a href="ranking.php">Ranking</a></li>
              <li><a href="driver.php">Drivers</a></li>
              <li><a href="standings.php">Standings</a></li>
              <li><a href="constructor.php">Constructors</a></li>
              <li><a href="admin_login.php">Admin</a></li>
            </ul>
    </nav>

    <div class="hero">
        <div class="container">
            <div class="box">
                <div class="box-title"><h4>Constructor Details</h4></div>
                <div class="box-content">
                <div class="table-container">
                <table>
            <thead>
                <tr>
                    <th>Constructor Name</th>
                    <th>Number of Pole Positions</th>
                    <th>Number of Titles</th>
                    <th>Total Points</th>
                    <th>Nationality</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    // Output each row of data
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['constructor_name'] . "</td>";
                        echo "<td>" . $row['no_of_pole_positions'] . "</td>";
                        echo "<td>" . $row['no_of_titles'] . "</td>";
                        echo "<td>" . $row['constructor_points'] . "</td>";
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