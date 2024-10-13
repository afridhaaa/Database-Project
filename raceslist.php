<?php

include 'db/db.php';  // Include your database connection

// Set the number of results per page
$results_per_page = 10;

// Determine the total number of records for pagination
$sql_total = "SELECT COUNT(*) AS total 
              FROM results rs 
              INNER JOIN races r ON rs.raceId = r.raceId 
              INNER JOIN circuits c ON r.circuit_id = c.circuit_id 
              INNER JOIN drivers d ON rs.driverId = d.driverId 
              WHERE r.year = 2015 AND rs.position = 1";
$total_result = $conn->query($sql_total);
$total_row = $total_result->fetch_assoc();
$total_pages = ceil($total_row['total'] / $results_per_page);

// Get the current page number from URL, if not set, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the starting limit number for the query
$start_from = ($current_page - 1) * $results_per_page;

// SQL query to fetch race name, circuit name, and winning driver for 2015 races with limit
$sql = "SELECT r.name AS race_name, c.circuit_name, d.forename AS winner 
        FROM results rs 
        INNER JOIN races r ON rs.raceId = r.raceId 
        INNER JOIN circuits c ON r.circuit_id = c.circuit_id 
        INNER JOIN drivers d ON rs.driverId = d.driverId 
        WHERE r.year = 2015 AND rs.position = 1 
        ORDER BY r.date 
        LIMIT $start_from, $results_per_page";

$result = $conn->query($sql);

?>



<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>races list</title>
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
                  <a href="index.php">  <h4>Formula1</h4></a>
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
                           <!-- Back Button -->
    <div class="back-button" style="margin: 20px;">
        <a href="index.php" class="button-8">← Back to Home</a>
    </div>
                            <div class="head">
     <h2>Race Winners with specific year</h2>
                            </div>
                        </div>
                        
                       

<div class="row mt-5">
    <table  class="table table-dark table-striped">
        <thead>
          <tr>
          <th>Race Name</th>
                                    <th>Circuit Name</th>
                                    <th>Winner</th>
          </tr>
        </thead>
        <tbody>
        <?php
                                if ($result->num_rows > 0) {
                                    // Output each row of data
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $row['race_name'] . "</td>";
                                        echo "<td>" . $row['circuit_name'] . "</td>";
                                        echo "<td>" . $row['winner'] . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3'>No data available</td></tr>";
                                }
                                ?>
                            </tbody>
        </tbody>
      </table>
      
</div>

    <!-- Pagination Controls (Previous and Next only) -->
<div class="pagination">
    <?php
    // Previous button
    if ($current_page > 1) {
        echo '<a href="raceslist.php?page=' . ($current_page - 1) . '" class="button-7">Previous</a>';
    } else {
        echo '<span class="disabled">Previous</span>';
    }

    // Next button
    if ($current_page < $total_pages) {
        echo '<a href="raceslist.php?page=' . ($current_page + 1) . '" class="button-7">Next</a>';
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



   <script>
    // Show more items when "View More" is clicked
    document.getElementById("view-more-btn").addEventListener("click", function() {
        var moreItems = document.getElementById("more-items");
        var viewMoreBtn = document.getElementById("view-more-li");
        moreItems.style.display = "block";
        viewMoreBtn.style.display = "none";
    });
</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>
