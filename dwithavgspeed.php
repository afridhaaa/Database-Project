<?php
include 'db/db.php';  // Include your database connection

// Set the number of results per page
$results_per_page = 10;

// Initialize variables for sorting and searching
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';
$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';

// Prepare the SQL query to get the total number of records
$sql_total = "SELECT COUNT(*) AS total 
              FROM (SELECT ci.circuit_name, d.forename, AVG(lap.milliseconds) AS avg_speed 
                    FROM lap_times lap 
                    INNER JOIN drivers d ON lap.driverId = d.driverId 
                    INNER JOIN races r ON lap.raceId = r.race_id 
                    INNER JOIN circuits ci ON r.circuit_id = ci.circuit_id 
                    WHERE ci.circuit_name LIKE ? OR d.forename LIKE ?
                    GROUP BY ci.circuit_name, d.forename) AS subquery";

$search_param = '%' . $search_keyword . '%';
$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param('ss', $search_param, $search_param);
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_row = $total_result->fetch_assoc();
$total_pages = ceil($total_row['total'] / $results_per_page);

// Get the current page number from URL, if not set, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the starting limit number for the query
$start_from = ($current_page - 1) * $results_per_page;

// SQL query to fetch circuit name, driver name, and average speed with limit
$sql = "SELECT ci.circuit_name, d.forename, AVG(lap.milliseconds) AS avg_speed 
        FROM lap_times lap 
        INNER JOIN drivers d ON lap.driverId = d.driverId 
        INNER JOIN races r ON lap.raceId = r.race_id 
        INNER JOIN circuits ci ON r.circuit_id = ci.circuit_id 
        WHERE ci.circuit_name LIKE ? OR d.forename LIKE ?
        GROUP BY ci.circuit_name, d.forename 
        ORDER BY avg_speed $sort_order 
        LIMIT ?, ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ssii', $search_param, $search_param, $start_from, $results_per_page);
$stmt->execute();
$result = $stmt->get_result();
?>



<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Drivers with Average Lap Speed</title>
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
     <h2>Drivers with Average Lap Speed</h2>
                            </div>
                        </div>
                        
 <!-- Sort and Search Form -->
 <div class="custom-filter-container">
        <form method="GET" action="avgspeed.php" class="custom-filter-form">
            <!-- Sort Dropdown -->
            <div class="custom-form-group">
                <label for="sort_order" class="custom-label">Sort by Average Speed:</label>
                <select name="sort_order" id="sort_order" class="custom-form-control" onchange="this.form.submit()">
                    <option value="ASC" <?php if ($sort_order == 'ASC') echo 'selected'; ?>>Least to Most</option>
                    <option value="DESC" <?php if ($sort_order == 'DESC') echo 'selected'; ?>>Most to Least</option>
                </select>
            </div>

            <!-- Search Bar -->
            <div class="custom-form-group">
                <label for="search" class="custom-label">Search by Driver or Circuit Name:</label>
                <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search by driver or circuit" value="<?php echo htmlspecialchars($search_keyword); ?>">
            </div>

            <!-- Search Button -->
            <div class="custom-form-group">
                <button type="submit" class="custom-btn custom-btn-primary">Search</button>
            </div>
        </form>
    </div>

    <!-- Clear Search Button: Show only when search is applied -->
    <?php if (!empty($search_keyword)) : ?>
        <div style="margin-bottom: 20px; margin-left: 30px;">
            <a href="avgspeed.php" class="custom-btn custom-btn-clear">Clear Search</a>
        </div>
    <?php endif; ?>                       

<div class="row mt-5">
    <table  class="table table-dark table-striped">
        <thead>
          <tr>
          <th>Circuit Name</th>
                                    <th>Driver Name</th>
                                    <th>Average Speed (ms)</th>
          </tr>
        </thead>
        <tbody>
        <?php
                                if ($result->num_rows > 0) {
                                    // Output each row of data
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['circuit_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['forename']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['avg_speed']) . "</td>";
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

     <!-- Pagination Controls with Page Numbers -->
     <div class="pagination">
                <?php
                // Previous button
                if ($current_page > 1) {
                    echo '<a href="avgspeed.php?page=' . ($current_page - 1) . '&sort_order=' . $sort_order . '&search=' . urlencode($search_keyword) . '" class="button-7">Previous</a>';
                } else {
                    echo '<span class="button-7 disabled">Previous</span>';
                }

                // Display page numbers
                for ($i = 1; $i <= $total_pages; $i++) {
                    if ($i == $current_page) {
                        // Current page number with special styling
                        echo '<span class="button-7 active">' . $i . '</span>';
                    } else {
                        echo '<a href="avgspeed.php?page=' . $i . '&sort_order=' . $sort_order . '&search=' . urlencode($search_keyword) . '" class="button-7">' . $i . '</a>';
                    }
                }

                // Next button
                if ($current_page < $total_pages) {
                    echo '<a href="avgspeed.php?page=' . ($current_page + 1) . '&sort_order=' . $sort_order . '&search=' . urlencode($search_keyword) . '" class="button-7">Next</a>';
                } else {
                    echo '<span class="button-7 disabled">Next</span>';
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
