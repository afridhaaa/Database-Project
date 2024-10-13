<?php
include 'db/db.php';  // Include your database connection

// Set how many results to display per page
$results_per_page = 15;

// Ensure $current_page is always an integer
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) 
    ? intval($_GET['page']) 
    : 1;

// Calculate the offset for the query
$offset = ($current_page - 1) * $results_per_page;

// Query to get the total number of rows (for pagination)
$total_query = "SELECT COUNT(DISTINCT d.forename, c.constructor_name, ci.circuit_name) AS total 
                FROM results rs 
                INNER JOIN drivers d ON rs.driverId = d.driverId 
                INNER JOIN constructors c ON rs.constructorId = c.constructor_id 
                INNER JOIN races r ON rs.raceId = r.raceId 
                INNER JOIN circuits ci ON r.circuit_id = ci.circuit_id";

$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_results = $total_row['total'];

// Calculate the total number of pages
$total_pages = ceil($total_results / $results_per_page);

// SQL query to get the results for the current page
$sql = "SELECT d.forename, c.constructor_name, ci.circuit_name, SUM(rs.points) AS total_points 
        FROM results rs 
        INNER JOIN drivers d ON rs.driverId = d.driverId 
        INNER JOIN constructors c ON rs.constructorId = c.constructor_id 
        INNER JOIN races r ON rs.raceId = r.raceId 
        INNER JOIN circuits ci ON r.circuit_id = ci.circuit_id 
        GROUP BY d.forename, c.constructor_name, ci.circuit_name 
        ORDER BY total_points DESC 
        LIMIT $results_per_page OFFSET $offset";

$result = $conn->query($sql);
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Formula 1 Points</title>
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
                        <div class="row">
                            <div class="back-button" style="margin: 20px;">
                                <a href="index.php" class="button-8">← Back to Home</a>
                            </div>
                            <div class="head">
                                <h2>Total Number of Points Scored by Each Driver</h2>
                            </div>
                        </div>

                        <div class="row mt-5">
                            <table class="table table-dark table-striped">
                                <thead>
                                  <tr>
                                      <th>Driver Name</th>
                                      <th>Constructor Name</th>
                                      <th>Circuit Name</th>
                                      <th>Total Points</th>
                                  </tr>
                                </thead>
                                <tbody>
                                <?php
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . $row['forename'] . "</td>";
                                            echo "<td>" . $row['constructor_name'] . "</td>";
                                            echo "<td>" . $row['circuit_name'] . "</td>";
                                            echo "<td>" . $row['total_points'] . "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4'>No data available</td></tr>";
                                    }
                                    $conn->close();
                                ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Controls -->
                        <!-- Pagination Controls (Previous and Next only) -->
    <div class="pagination">
    <?php
    // Ensure $current_page is always an integer
    $current_page = isset($_GET['page']) && is_numeric($_GET['page']) 
        ? intval($_GET['page']) 
        : 1;

    // Ensure $total_pages is a valid integer
    $total_pages = isset($total_pages) && $total_pages > 0 
        ? intval($total_pages) 
        : 1;

    // Set how many page links to show at once
    $links_to_show = 5;

    // Calculate start and end pages for the pagination range
    $start_page = max(1, $current_page - floor($links_to_show / 2));
    $end_page = min($total_pages, $start_page + $links_to_show - 1);

    // Adjust start page if there are fewer pages on the right side
    if ($end_page - $start_page + 1 < $links_to_show) {
        $start_page = max(1, $end_page - $links_to_show + 1);
    }

    // Display "Previous" button
    if ($current_page > 1) {
        echo '<a href="totalpoints.php?page=' . ($current_page - 1) . '" class="button-7">Previous</a>';
    } else {
        echo '<span class="disabled">Previous</span>';
    }

    // Display page numbers
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {
            echo '<a href="#" class="current-page">' . $i . '</a>'; // Active page
        } else {
            echo '<a href="totalpoints.php?page=' . $i . '">' . $i . '</a>';
        }
    }

    // Display "Next" button
    if ($current_page < $total_pages) {
        echo '<a href="totalpoints.php?page=' . ($current_page + 1) . '" class="button-7">Next</a>';
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
