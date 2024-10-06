<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
include 'db/db.php';

// Fetch constructor data
$constructor_sql = "SELECT constructor_id, constructor_name, no_of_pole_positions, no_of_titles, constructor_points, nationality, url FROM constructors";
$constructor_result = $conn->query($constructor_sql);

// Fetch driver standings data
$driver_sql = "SELECT drivers.driverId, drivers.forename, drivers.nationality, drivers.url, driver_standings.points, driver_standings.position, driver_standings.wins 
               FROM drivers 
               INNER JOIN driver_standings ON drivers.driverId = driver_standings.driverId";
$driver_result = $conn->query($driver_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin-dashboard.css">
    
</head>
<body>
<nav>
        <div class="menu-icon" id="menu-icon">&#9776; <h1>Formula 1</h1></div> <!-- Hamburger Icon -->
        <ul id="nav-links1">
            <li><a href="index.php" id="logo">FORMULA 1</a></li>
            <div class="logout">
                <a href="update_password.php">Update Password</a>
                <a href="logout.php">Logout</a>
            </div>
        </ul>
    </nav>

    <div class="admin-heading">
        <h2>Admin Dashboard</h2>
    </div>

    <div class="hero">
        <div class="container">
        <h2>Constructor Management</h2>
    <!-- Constructor Create Form -->
    <div class="form-container">
    <h2>Add Constructor</h2>
    <form action="create_constructor.php" method="POST">
        <input type="text" name="constructor_name" placeholder="Constructor Name" required>
        <input type="number" name="no_of_pole_positions" placeholder="Pole Positions" required>
        <input type="number" name="no_of_titles" placeholder="Titles" required>
        <input type="number" name="constructor_points" placeholder="Points" required>
        <input type="text" name="nationality" placeholder="Nationality" required>
        <input type="text" name="url" placeholder="Details URL" required>
        <button type="submit">Add Constructor</button>
    </form>
</div>

   <div class="box">
   <div class="box-title"><h4>Constructor Details</h4></div>
    <div class="box-content">
        <div class="table-container">
             <!-- Constructor Table -->
    <table>
        <thead>
            <tr>
                <th>Constructor Name</th>
                <th>Pole Positions</th>
                <th>Titles</th>
                <th>Points</th>
                <th>Nationality</th>
                <th>Details</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($constructor_result->num_rows > 0) {
                while ($row = $constructor_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['constructor_name'] . "</td>";
                    echo "<td>" . $row['no_of_pole_positions'] . "</td>";
                    echo "<td>" . $row['no_of_titles'] . "</td>";
                    echo "<td>" . $row['constructor_points'] . "</td>";
                    echo "<td>" . $row['nationality'] . "</td>";
                    echo "<td><a href='" . $row['url'] . "' target='_blank'>View</a></td>";
                    echo "<td>
                          <a href='edit_constructor.php?id=" . $row['constructor_id'] . "'>Edit</a>
                          <a href='delete_constructor.php?id=" . $row['constructor_id'] . "' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No data available</td></tr>";
            }
            ?>
        </tbody>
    </table>
        </div>
    </div>
   </div>

   <hr>

    <h2>Driver Standings Management</h2>
    <div class="form-container">
    <h2>Add Driver Standings</h2>
<form action="create_driver.php" method="POST">
    <input type="text" name="forename" placeholder="Driver Forename" required>
    <input type="number" name="points" placeholder="Points" required>
    <input type="number" name="position" placeholder="Position" required>
    <input type="number" name="wins" placeholder="Wins" required>
    <input type="text" name="nationality" placeholder="Nationality" required>
    <input type="text" name="url" placeholder="Details URL" required>
    <button type="submit">Add Driver Standing</button>
</form>
    </div>

   <!-- Driver Standings Table -->

<div class="box">
<div class="box-title"><h4>Driver Standings</h4></div>
    <div class="box-content">
        <div class="table-container">
        <table>
    <thead>
        <tr>
            <th>Forename</th>
            <th>Points</th>
            <th>Position</th>
            <th>Wins</th>
            <th>Nationality</th>
            <th>Details</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($driver_result->num_rows > 0) {
            while ($row = $driver_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['forename'] . "</td>";
                echo "<td>" . $row['points'] . "</td>";
                echo "<td>" . $row['position'] . "</td>";
                echo "<td>" . $row['wins'] . "</td>";
                echo "<td>" . $row['nationality'] . "</td>";
                echo "<td><a href='" . $row['url'] . "' target='_blank'>View</a></td>";
                echo "<td>
                      <a href='edit_driver.php?id=" . $row['driverId'] . "'>Edit</a>
                      <a href='delete_driver.php?id=" . $row['driverId'] . "' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No data available</td></tr>";
        }
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
