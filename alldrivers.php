<?php

include 'db/db.php';  // Include your database connection

// Set the number of results per page
$results_per_page = 10;

// Get search input
$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';

// Modify the SQL query to include the search functionality
$sql_total = "SELECT COUNT(*) AS total 
              FROM results rs 
              INNER JOIN drivers d ON rs.driverId = d.driverId 
              INNER JOIN constructors c ON rs.constructorId = c.constructor_id 
              INNER JOIN races r ON rs.raceId = r.raceId 
              INNER JOIN circuits ci ON r.circuit_id = ci.circuit_id 
              WHERE rs.position = 1 
              AND c.constructor_id IN (SELECT constructor_id FROM constructors WHERE no_of_titles > 0)";

// Check if a search keyword is provided and adjust the SQL query accordingly
if (!empty($search_keyword)) {
    $sql_total .= " AND (d.forename LIKE '%$search_keyword%' 
                    OR c.constructor_name LIKE '%$search_keyword%' 
                    OR ci.circuit_name LIKE '%$search_keyword%' 
                    OR r.name LIKE '%$search_keyword%')";
}

$total_result = $conn->query($sql_total);
$total_row = $total_result->fetch_assoc();
$total_pages = ceil($total_row['total'] / $results_per_page);

// Get the current page number from URL, if not set, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the starting limit number for the query
$start_from = ($current_page - 1) * $results_per_page;

// SQL query to fetch driver, constructor, race, and circuit data with limit
$sql = "SELECT d.forename, c.constructor_name, r.name AS race_name, ci.circuit_name 
        FROM results rs 
        INNER JOIN drivers d ON rs.driverId = d.driverId 
        INNER JOIN constructors c ON rs.constructorId = c.constructor_id 
        INNER JOIN races r ON rs.raceId = r.raceId 
        INNER JOIN circuits ci ON r.circuit_id = ci.circuit_id 
        WHERE rs.position = 1 
        AND c.constructor_id IN (SELECT constructor_id FROM constructors WHERE no_of_titles > 0)";

// Apply the search filter if a keyword is provided
if (!empty($search_keyword)) {
    $sql .= " AND (d.forename LIKE '%$search_keyword%' 
             OR c.constructor_name LIKE '%$search_keyword%' 
             OR ci.circuit_name LIKE '%$search_keyword%' 
             OR r.name LIKE '%$search_keyword%')";
}

$sql .= " ORDER BY c.constructor_name, r.date 
          LIMIT $start_from, $results_per_page";

$result = $conn->query($sql);

?>


<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />  
    <link rel="stylesheet" href="./assets/css/style.css">
    <style>
        .pagination a, .pagination span {
            margin: 0 5px;
            padding: 10px;
            text-decoration: none;
            border: 1px solid #ddd;
            color: #333;
        }

       
    </style>
</head>
  <body>
   <div class="topbar">
    <div class="container-fluid">
        <div class="row">
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
     <h2>Drivers who won a race where the constructor won the constructor's championship</h2>
                            </div>
                        </div>
                        
<!-- Search and Filter Form -->
<div class="custom-filter-container">
        <form method="GET" action="alldrivers.php" class="custom-filter-form">
            <!-- Search Bar -->
            <div class="custom-form-group">
                <label for="search" class="custom-label">Search by Driver, Constructor, Circuit, or Race Name:</label>
                <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search..." value="<?php echo htmlspecialchars($search_keyword); ?>">
            </div>
            
            <!-- Search Button -->
            <div class="custom-form-group">
                <button type="submit" class="custom-btn custom-btn-primary">Search</button>
            </div>
        </form>
    </div>

    <!-- Clear Search Button: Show only when search is applied -->
    <?php if (!empty($search_keyword)) : ?>
        <div style="margin-bottom: 20px; margin-left:  30px;">
            <a href="alldrivers.php" class="custom-btn custom-btn-clear">Clear Search</a>
        </div>
    <?php endif; ?>


<div class="row mt-5">
    <table  class="table table-dark table-striped">
        <thead>
          <tr>
          <th>Driver Name</th>
                                    <th>Constructor Name</th>
                                    <th>Race Name</th>
                                    <th>Circuit Name</th>
          </tr>
        </thead>
        <tbody>
        <?php
                                if ($result->num_rows > 0) {
                                    // Output each row of data
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['forename']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['constructor_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['race_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['circuit_name']) . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>No data available</td></tr>";
                                }
                                ?>
                            </tbody>
        </tbody>
      </table>
      
</div>

    <!-- Pagination Controls (Previous, Next, and Page Numbers) -->
<!-- Pagination Controls (Previous, Next, and Limited Page Numbers) -->
<div class="pagination">
    <?php
    // Ensure $current_page is always an integer
    $current_page = isset($_GET['page']) && is_numeric($_GET['page']) 
        ? intval($_GET['page']) 
        : 1;

    // Ensure $total_pages is a valid integer and at least 1
    $total_pages = isset($total_pages) && $total_pages > 0 
        ? intval($total_pages) 
        : 1;

    // Set how many page links to display at once
    $links_to_show = 5;

    // Calculate the start and end page numbers
    $start_page = max(1, $current_page - floor($links_to_show / 2));
    $end_page = min($total_pages, $start_page + $links_to_show - 1);

    // Adjust start page if we are at the end of the pagination range
    if ($end_page - $start_page + 1 < $links_to_show) {
        $start_page = max(1, $end_page - $links_to_show + 1);
    }

    // Display "Previous" button
    if ($current_page > 1) {
        echo '<a href="alldrivers.php?page=' . ($current_page - 1) . '" class="button-7">Previous</a>';
    } else {
        echo '<span class="disabled">Previous</span>';
    }

    // Display limited page numbers
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {
            echo '<a href="#" class="current-page">' . $i . '</a>'; // Active page
        } else {
            echo '<a href="alldrivers.php?page=' . $i . '">' . $i . '</a>';
        }
    }

    // Display "Next" button
    if ($current_page < $total_pages) {
        echo '<a href="alldrivers.php?page=' . ($current_page + 1) . '" class="button-7">Next</a>';
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
