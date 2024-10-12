<?php

include 'db/db.php';  // Include your database connection

// Initialize variables
$sort_order_lap = 'ASC';  // Default sorting for fastest lap time
$sort_order_position = 'ASC';  // Default sorting for position
$search_keyword = '';  // Default search keyword

// Handle GET requests for sorting and searching
if (isset($_GET['sort_order_lap'])) {
    $sort_order_lap = $_GET['sort_order_lap'];
}

if (isset($_GET['sort_order_position'])) {
    $sort_order_position = $_GET['sort_order_position'];
}

if (isset($_GET['search'])) {
    $search_keyword = $_GET['search'];
}

// Set the number of results per page
$results_per_page = 10;

// Determine the total number of records for pagination
$sql_total = "SELECT COUNT(*) AS total 
              FROM (SELECT r.name AS race_name, d.forename, rs.fastestLapTime, rs.position 
                    FROM results rs 
                    INNER JOIN drivers d ON rs.driverId = d.driverId 
                    INNER JOIN races r ON rs.raceId = r.race_id 
                    WHERE rs.fastestLapTime IS NOT NULL AND rs.position <> 1
                    AND (d.forename LIKE ?)) AS subquery";  // Use placeholders for prepared statements

$stmt = $conn->prepare($sql_total);
$search_param = "%$search_keyword%";
$stmt->bind_param("s", $search_param);
$stmt->execute();
$total_result = $stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_pages = ceil($total_row['total'] / $results_per_page);

// Get the current page number from URL, if not set, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the starting limit number for the query
$start_from = ($current_page - 1) * $results_per_page;

// SQL query to fetch race names, driver names, fastest lap times, and positions with limit
$sql = "SELECT r.name AS race_name, d.forename, rs.fastestLapTime, rs.position 
        FROM results rs 
        INNER JOIN drivers d ON rs.driverId = d.driverId 
        INNER JOIN races r ON rs.raceId = r.race_id 
        WHERE rs.fastestLapTime IS NOT NULL AND rs.position <> 1 
        AND (d.forename LIKE ?) 
        ORDER BY rs.fastestLapTime $sort_order_lap, rs.position $sort_order_position 
        LIMIT ?, ?";  // Use placeholders for prepared statements

$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $search_param, $start_from, $results_per_page);
$stmt->execute();
$result = $stmt->get_result();
?>


<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>All Races with Fastest Lap Time</title>
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
     <h2>All Races with Fastest Lap Time</h2>
                            </div>
                        </div>
                        
  <!-- Sort and Search Form -->
  <div class="custom-filter-container">
                    <form method="GET" action="" class="custom-filter-form">
                        <!-- Sort by Fastest Lap Time -->
                        <div class="custom-form-group">
                            <label for="sort_order_lap" class="custom-label">Sort by Fastest Lap Time:</label>
                            <select name="sort_order_lap" id="sort_order_lap" class="custom-form-control" onchange="this.form.submit()">
                                <option value="ASC" <?php if ($sort_order_lap == 'ASC') echo 'selected'; ?>>Least to Most</option>
                                <option value="DESC" <?php if ($sort_order_lap == 'DESC') echo 'selected'; ?>>Most to Least</option>
                            </select>
                        </div>

                        <!-- Sort by Position -->
                        <div class="custom-form-group">
                            <label for="sort_order_position" class="custom-label">Sort by Position:</label>
                            <select name="sort_order_position" id="sort_order_position" class="custom-form-control" onchange="this.form.submit()">
                                <option value="ASC" <?php if ($sort_order_position == 'ASC') echo 'selected'; ?>>Least to Most</option>
                                <option value="DESC" <?php if ($sort_order_position == 'DESC') echo 'selected'; ?>>Most to Least</option>
                            </select>
                        </div>

                        <!-- Search Bar -->
                        <div class="custom-form-group">
                            <label for="search" class="custom-label">Search by Driver Name:</label>
                            <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search by driver" value="<?php echo htmlspecialchars($search_keyword); ?>">
                        </div>

                        <!-- Search Button -->
                        <div class="custom-form-group">
                            <button type="submit" class="custom-btn custom-btn-primary">Search</button>
                        </div>
                        
                        <!-- Clear Filters Button: Show only when search is applied -->
                        <?php if (!empty($search_keyword) || $sort_order_lap !== 'ASC' || $sort_order_position !== 'ASC'): ?>
                            <div class="custom-form-group">
                                <a href="?page=1" class="custom-btn custom-btn-clear">Clear Filters</a>
                            </div>
                        <?php endif; ?>
                        </form>
                </div>
<div class="row mt-5">
    <table  class="table table-dark table-striped">
        <thead>
          <tr>
          <th>Race Name</th>
                            <th>Driver Name</th>
                            <th>Fastest Lap Time</th>
                            <th>Position</th>
          </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['race_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['forename']); ?></td>
                                <td><?php echo htmlspecialchars($row['fastestLapTime']); ?></td>
                                <td><?php echo htmlspecialchars($row['position']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                            </tbody>
        </tbody>
      </table>
      
</div>

     <!-- Pagination Controls -->
     <div class="pagination">
                    <?php if ($current_page > 1): ?>
                        <a href="?page=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search_keyword); ?>&sort_order_lap=<?php echo $sort_order_lap; ?>&sort_order_position=<?php echo $sort_order_position; ?>" class="pagination-link">« Previous</a>
                    <?php endif; ?>

                    <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                        <a href="?page=<?php echo $page; ?>&search=<?php echo urlencode($search_keyword); ?>&sort_order_lap=<?php echo $sort_order_lap; ?>&sort_order_position=<?php echo $sort_order_position; ?>" class="pagination-link <?php echo ($page == $current_page) ? 'active' : ''; ?>"><?php echo $page; ?></a>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="?page=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search_keyword); ?>&sort_order_lap=<?php echo $sort_order_lap; ?>&sort_order_position=<?php echo $sort_order_position; ?>" class="pagination-link">Next »</a>
                    <?php endif; ?>
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
