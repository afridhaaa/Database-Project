<?php
include 'db/db.php';  // Include your database connection

// Set the number of results per page
$results_per_page = 5; // Adjust this to match your query limit

// Get the current page number from URL, if not set, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Get search term from the URL
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Get sort order from the URL, default is 'DESC'
$sort_order = isset($_GET['sort']) ? $_GET['sort'] : 'DESC';
$sort_order = ($sort_order === 'ASC') ? 'ASC' : 'DESC'; // Ensure valid sort order

// Calculate the starting limit number for the query
$start_from = ($current_page - 1) * $results_per_page;

// SQL query to fetch top drivers with the most podium finishes
$sql = "SELECT d.forename, c.constructor_name, COUNT(rs.position) AS podium_finishes 
        FROM results rs 
        INNER JOIN drivers d ON rs.driverId = d.driverId 
        INNER JOIN constructors c ON rs.constructorId = c.constructor_id 
        WHERE rs.position <= 3";

// Add search functionality
if (!empty($search_term)) {
    $sql .= " AND d.forename LIKE '%" . $conn->real_escape_string($search_term) . "%'";
}

$sql .= " GROUP BY d.driverId, c.constructor_id 
          ORDER BY podium_finishes $sort_order 
          LIMIT $start_from, $results_per_page";

$result = $conn->query($sql);

// Count total podium finishes for pagination
$sql_total = "SELECT COUNT(DISTINCT d.driverId) AS total 
              FROM results rs 
              INNER JOIN drivers d ON rs.driverId = d.driverId 
              WHERE rs.position <= 3";

if (!empty($search_term)) {
    $sql_total .= " AND d.forename LIKE '%" . $conn->real_escape_string($search_term) . "%'";
}

$total_result = $conn->query($sql_total);
$total_row = $total_result->fetch_assoc();
$total_pages = ceil($total_row['total'] / $results_per_page);
?>


<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Top Drivers with Podium Finishes</title>
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
     <h2>Top Drivers with Podium Finishes</h2>
                            </div>
                        </div>
                        
   <!-- Sort and Search Form -->
   <div class="custom-filter-container">
        <form method="GET" action="top5.php" class="custom-filter-form">
            <!-- Sort Dropdown -->
            <div class="custom-form-group">
                <label for="sort_order" class="custom-label">Sort by Podium Finishes:</label>
                <select name="sort" id="sort_order" class="custom-form-control" onchange="this.form.submit()">
                    <option value="DESC" <?php if ($sort_order == 'DESC') echo 'selected'; ?>>Most to Least</option>
                    <option value="ASC" <?php if ($sort_order == 'ASC') echo 'selected'; ?>>Least to Most</option>
                </select>
            </div>

            <!-- Search Bar -->
            <div class="custom-form-group">
                <label for="search" class="custom-label">Search by Driver Name:</label>
                <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search by driver name" value="<?php echo htmlspecialchars($search_term); ?>">
            </div>

            <!-- Search Button -->
            <div class="custom-form-group">
                <button type="submit" class="custom-btn custom-btn-primary">Search</button>
            </div>
        </form>
    </div>

    <!-- Clear Search Button: Show only when search is applied -->
    <?php if (!empty($search_term)) : ?>
        <div style="margin-bottom: 20px; margin-left: 30px;">
            <a href="top5.php" class="custom-btn custom-btn-clear">Clear Search</a>
        </div>
    <?php endif; ?>
<div class="row mt-5">
    <table  class="table table-dark table-striped">
        <thead>
          <tr>
          <th>Driver</th>
                                    <th>Constructor</th>
                                    <th>Podium Finishes</th>
          </tr>
        </thead>
        <tbody>
        <?php
                                if ($result->num_rows > 0) {
                                    // Output each row of data
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['forename']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['constructor_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['podium_finishes']) . "</td>";
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
                <!-- Previous button -->
                <?php
                if ($current_page > 1) {
                    echo '<a href="top5.php?page=' . ($current_page - 1) . '&search=' . urlencode($search_term) . '&sort=' . $sort_order . '" class="button-7">Previous</a>';
                } else {
                    echo '<span class="disabled">Previous</span>';
                }

                // Page number links
                for ($i = 1; $i <= $total_pages; $i++) {
                    if ($i == $current_page) {
                        echo '<span class="current-page">' . $i . '</span>'; // Current page
                    } else {
                        echo '<a href="top5.php?page=' . $i . '&search=' . urlencode($search_term) . '&sort=' . $sort_order . '">' . $i . '</a>';
                    }
                }

                // Next button
                if ($current_page < $total_pages) {
                    echo '<a href="top5.php?page=' . ($current_page + 1) . '&search=' . urlencode($search_term) . '&sort=' . $sort_order . '" class="button-7">Next</a>';
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
