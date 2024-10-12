<?php
include 'db/db.php';  // Include your database connection

// Set the number of results per page
$results_per_page = 10;

// Determine the total number of pages available
$sql_total = "SELECT COUNT(DISTINCT d.forename) AS total 
              FROM results rs 
              INNER JOIN drivers d ON rs.driverId = d.driverId 
              WHERE rs.position = 1";

// Add search functionality
$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search_keyword)) {
    $sql_total .= " AND (d.forename LIKE '%" . $conn->real_escape_string($search_keyword) . "%' OR d.driverId LIKE '%" . $conn->real_escape_string($search_keyword) . "%')";
}

$total_result = $conn->query($sql_total);
$total_row = $total_result->fetch_assoc();
$total_pages = ceil($total_row['total'] / $results_per_page);

// Get the current page number from URL, if not set, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the starting limit number for the query
$start_from = ($current_page - 1) * $results_per_page;

// SQL query to get top drivers with search and sort functionality
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';
$sql = "SELECT d.forename, COUNT(rs.position) AS total_wins, c.circuit_country 
        FROM results rs 
        INNER JOIN races r ON rs.raceId = r.race_id 
        INNER JOIN circuits c ON r.circuit_id = c.circuit_id 
        INNER JOIN drivers d ON rs.driverId = d.driverId 
        WHERE rs.position = 1 
        " . (!empty($search_keyword) ? " AND (d.forename LIKE '%" . $conn->real_escape_string($search_keyword) . "%' OR d.driverId LIKE '%" . $conn->real_escape_string($search_keyword) . "%')" : "") . "
        GROUP BY d.forename, c.circuit_country 
        ORDER BY total_wins $sort_order 
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
     <h2>Top Drivers by Total Number of Race Wins</h2>
                            </div>
                        </div>
                       <!-- Search and Sort Form -->
<div class="custom-filter-container">
    <form method="GET" action="topdriver.php" class="custom-filter-form">
        <!-- Sort Dropdown -->
        <div class="custom-form-group">
            <label for="sort_order" class="custom-label">Sort by Wins:</label>
            <select name="sort_order" id="sort_order" class="custom-form-control" onchange="this.form.submit()">
                <option value="DESC" <?php if ($sort_order == 'DESC') echo 'selected'; ?>>Most to Least</option>
                <option value="ASC" <?php if ($sort_order == 'ASC') echo 'selected'; ?>>Least to Most</option>
            </select>
        </div>

        <!-- Search Bar -->
        <div class="custom-form-group">
            <label for="search" class="custom-label">Search by driver name:</label>
            <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search by constructor or driver" value="<?php echo htmlspecialchars($search_keyword); ?>">
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
        <a href="topdriver.php" class="custom-btn custom-btn-clear">Clear Search</a>
    </div>
<?php endif; ?>




<div class="row mt-5">
    <table  class="table table-dark table-striped">
        <thead>
          <tr>
          <th>Driver Name</th>
                                <th>Total Wins</th>
                                <th>Country</th>
          </tr>
        </thead>
        <tbody>
        <?php
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['forename']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['total_wins']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['circuit_country']) . "</td>";
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
            // Previous button
            if ($current_page > 1) {
                echo '<a href="topdriver.php?page=' . ($current_page - 1) . '" class="button-7">Previous</a>';
            } else {
                echo '<span class="disabled">Previous</span>';
            }

            // Page numbers
            for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $current_page) {
                    echo '<a href="#" class="active">' . $i . '</a>';
                } else {
                    echo '<a href="topdriver.php?page=' . $i . '">' . $i . '</a>';
                }
            }

            // Next button
            if ($current_page < $total_pages) {
                echo '<a href="topdriver.php?page=' . ($current_page + 1) . '" class="button-7">Next</a>';
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
