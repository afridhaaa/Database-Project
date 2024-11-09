<?php

include 'db/db.php';  // Include your database connection

// Set the number of results per page
$results_per_page = 10;

// Get sort order from URL
$sort_order = isset($_GET['sort_order']) && in_array($_GET['sort_order'], ['ASC', 'DESC']) ? $_GET['sort_order'] : 'DESC';

// Get search keyword from URL
$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';

// Modify the SQL query for total wins, search by driver name, and sorting
$sql_total = "SELECT COUNT(*) AS total 
              FROM (SELECT d.forename, ci.circuit_name, COUNT(rs.position) AS total_wins 
                    FROM results rs 
                    INNER JOIN drivers d ON rs.driverId = d.driverId 
                    INNER JOIN races r ON rs.raceId = r.raceId 
                    INNER JOIN circuits ci ON r.circuit_id = ci.circuit_id 
                    WHERE rs.position = 1 ";

if (!empty($search_keyword)) {
    $sql_total .= "AND d.forename LIKE '%$search_keyword%' ";
}

$sql_total .= "GROUP BY d.forename, ci.circuit_name) AS subquery";

$total_result = $conn->query($sql_total);
$total_row = $total_result->fetch_assoc();
$total_pages = ceil($total_row['total'] / $results_per_page);

// Get the current page number from URL, if not set, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the starting limit number for the query
$start_from = ($current_page - 1) * $results_per_page;

// Main SQL query to fetch driver names, circuit names, and total wins with limit, search, and sort
$sql = "SELECT d.forename, ci.circuit_name, COUNT(rs.position) AS total_wins 
        FROM results rs 
        INNER JOIN drivers d ON rs.driverId = d.driverId 
        INNER JOIN races r ON rs.raceId = r.raceId 
        INNER JOIN circuits ci ON r.circuit_id = ci.circuit_id 
        WHERE rs.position = 1 ";

if (!empty($search_keyword)) {
    $sql .= "AND d.forename LIKE '%$search_keyword%' ";
}

$sql .= "GROUP BY d.forename, ci.circuit_name 
        ORDER BY total_wins $sort_order 
        LIMIT $start_from, $results_per_page";

$result = $conn->query($sql);

?>


<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Most Race Wins</title>
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
     <h2>Most Race Wins</h2>
                            </div>
                        </div>
                        
  <!-- Sort and Search Form -->
  <div class="custom-filter-container">
        <form method="GET" action="mostracewins.php" class="custom-filter-form">
            <!-- Sort Dropdown -->
            <div class="custom-form-group">
                <label for="sort_order" class="custom-label">Sort by Total Wins:</label>
                <select name="sort_order" id="sort_order" class="custom-form-control" onchange="this.form.submit()">
                    <option value="DESC" <?php if (isset($_GET['sort_order']) && $_GET['sort_order'] == 'DESC') echo 'selected'; ?>>Most to Least</option>
                    <option value="ASC" <?php if (isset($_GET['sort_order']) && $_GET['sort_order'] == 'ASC') echo 'selected'; ?>>Least to Most</option>
                </select>
            </div>

            <!-- Search Bar -->
            <div class="custom-form-group">
                <label for="search" class="custom-label">Search by Driver Name:</label>
                <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search by driver" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
            </div>

            <!-- Search Button -->
            <div class="custom-form-group">
                <button type="submit" class="custom-btn custom-btn-primary">Search</button>
            </div>
        </form>
    </div>

    <!-- Clear Search Button: Show only when search is applied -->
    <?php if (!empty($_GET['search'])) : ?>
        <div style="margin-bottom: 20px; margin-left: 30px;">
            <a href="mostracewins.php" class="custom-btn custom-btn-clear">Clear Search</a>
        </div>
    <?php endif; ?>     

<div class="row mt-5">
    <table  class="table table-dark table-striped">
        <thead>
          <tr>
          <th>Driver</th>
                                    <th>Circuit Name</th>
                                    <th>Total Wins</th>
          </tr>
        </thead>
        <tbody>
        <?php
                                if ($result->num_rows > 0) {
                                    // Output each row of data
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $row['forename'] . "</td>";
                                        echo "<td>" . $row['circuit_name'] . "</td>";
                                        echo "<td>" . $row['total_wins'] . "</td>";
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

     <!-- Pagination Controls -->
     <div class="pagination">
    <?php
    // Ensure $current_page and $total_pages are integers
    $current_page = isset($_GET['page']) && is_numeric($_GET['page']) 
        ? intval($_GET['page']) 
        : 1;

    $total_pages = isset($total_pages) && is_numeric($total_pages) && $total_pages > 0 
        ? intval($total_pages) 
        : 1;

    // Define the maximum number of links to display
    $max_links = 8;

    // Calculate the start and end page numbers
    $start_page = max(1, $current_page - floor($max_links / 2));
    $end_page = min($total_pages, $start_page + $max_links - 1);

    // Adjust the start page if we're near the end
    if ($end_page - $start_page < $max_links - 1) {
        $start_page = max(1, $end_page - $max_links + 1);
    }

    // Previous button
    if ($current_page > 1) {
        echo '<a href="mostracewins.php?page=' . ($current_page - 1) . '" class="button-7">Previous</a>';
    } else {
        echo '<span class="disabled">Previous</span>';
    }

    // First page link and ellipsis, if necessary
    if ($start_page > 1) {
        echo '<a href="mostracewins.php?page=1" class="button-7">1</a>';
        if ($start_page > 2) {
            echo '<span class="ellipsis">...</span>';
        }
    }

    // Page number links within the range
    for ($page = $start_page; $page <= $end_page; $page++) {
        if ($page == $current_page) {
            echo '<span class="current-page">' . $page . '</span>';
        } else {
            echo '<a href="mostracewins.php?page=' . $page . '" class="button-7">' . $page . '</a>';
        }
    }

    // Last page link and ellipsis, if necessary
    if ($end_page < $total_pages) {
        if ($end_page < $total_pages - 1) {
            echo '<span class="ellipsis">...</span>';
        }
        echo '<a href="mostracewins.php?page=' . $total_pages . '" class="button-7">' . $total_pages . '</a>';
    }

    // Next button
    if ($current_page < $total_pages) {
        echo '<a href="mostracewins.php?page=' . ($current_page + 1) . '" class="button-7">Next</a>';
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
