<?php
include 'db/db.php';
include 'process.php';

// Define how many results you want per page
$results_per_page = 10;

// Determine which page number visitor is currently on
$current_page = isset($_GET['page']) ? $_GET['page'] : 1;

// Determine the SQL LIMIT starting number for the results on the displaying page
$start_from = ($current_page - 1) * $results_per_page;

// SQL query to fetch constructor data with LIMIT for pagination
$sql = "SELECT constructor_name, no_of_pole_positions, no_of_titles, constructor_points, nationality, url 
        FROM constructors 
        ORDER BY constructor_points DESC 
        LIMIT $start_from, $results_per_page";
$result = $conn->query($sql);

// Find out the total number of pages
$total_sql = "SELECT COUNT(*) AS total FROM constructors";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_pages = ceil($total_row["total"] / $results_per_page);

// SQL query to fetch top 5 drivers based on points
$top_drivers_sql = "SELECT d.forename, ds.points 
                    FROM drivers d 
                    JOIN driver_standings ds ON d.driverId = ds.driverId 
                    ORDER BY ds.points DESC 
                    LIMIT 5";
$top_drivers_result = $conn->query($top_drivers_sql);

// SQL query to fetch top 5 constructors based on points
$top_constructors_sql = "SELECT constructor_name, constructor_points 
                         FROM constructors 
                         ORDER BY constructor_points DESC 
                         LIMIT 5";
$top_constructors_result = $conn->query($top_constructors_sql);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Formula1 - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />  
    <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
<div class="topbar">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2">
                <div class="heading">
                    <a href="index.php"><h4>Formula1</h4></a>
                </div>
            </div>
        </div>
    </div>
</div>
<section id="main">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 p-0 full">
                <?php include("side.php"); ?>
            </div>
            <div class="col-md-10 p-0">
                <div class="submain">
                    <div class="container py-3">
                       
                        <!-- Top 5 Drivers -->
                        <div class="row mt-5">
                            <div class="head">
                                <h2 class="mb-4">Top 5 Drivers</h2>
                            </div>
                            <table class="table table-dark table-striped">
                                <thead>
                                <tr>
                                    <th scope="col">Driver Name</th>
                                    <th scope="col">Total Points</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                if ($top_drivers_result->num_rows > 0) {
                                    while ($driver = $top_drivers_result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $driver['forename'] . "</td>";
                                        echo "<td>" . $driver['points'] . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='2'>No data available</td></tr>";
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Top 5 Constructors -->
                        <div class="row mt-5">
                            <div class="head">
                                <h2 class="mb-4">Top 5 Constructors by Points</h2>
                            </div>
                            <table class="table table-dark table-striped">
                                <thead>
                                <tr>
                                    <th scope="col">Constructor Name</th>
                                    <th scope="col">Total Points</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                if ($top_constructors_result->num_rows > 0) {
                                    while ($constructor = $top_constructors_result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $constructor['constructor_name'] . "</td>";
                                        echo "<td>" . $constructor['constructor_points'] . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='2'>No data available</td></tr>";
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Controls -->
                        <div class="pagination">
                            <?php
                            // Previous button
                            if ($current_page > 1) {
                                echo '<a href="index.php?page=' . ($current_page - 1) . '" class="button-7">Previous</a>';
                            } else {
                                echo '<span class="disabled">Previous</span>';
                            }

                            // Page numbers
                            for ($i = 1; $i <= $total_pages; $i++) {
                                if ($i == $current_page) {
                                    echo '<a href="#" class="active">' . $i . '</a>'; // Active page
                                } else {
                                    echo '<a href="index.php?page=' . $i . '">' . $i . '</a>';
                                }
                            }

                            // Next button
                            if ($current_page < $total_pages) {
                                echo '<a href="index.php?page=' . ($current_page + 1) . '" class="button-7">Next</a>';
                            } else {
                                echo '<span class="disabled">Next</span>';
                            }
                            ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
