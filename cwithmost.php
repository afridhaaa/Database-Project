<?php
include 'db/db.php';  // Include your database connection

// Set the number of results per page
$results_per_page = 13; // Adjust this to match the number of results you want to show per page

// Get the current page number from URL, if not set, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the starting limit number for the query
$start_from = ($current_page - 1) * $results_per_page;

// Initialize sort order and search keyword
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';
$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';

// SQL query to fetch constructors and circuits with the most race wins
$sql = "SELECT c.constructor_name, ci.circuit_name, COUNT(rs.position) AS total_wins 
        FROM results rs 
        INNER JOIN constructors c ON rs.constructorId = c.constructor_id 
        INNER JOIN races r ON rs.raceId = r.raceId 
        INNER JOIN circuits ci ON r.circuit_id = ci.circuit_id 
        WHERE rs.position = 1";

// Add search condition
if (!empty($search_keyword)) {
    $sql .= " AND (c.constructor_name LIKE '%$search_keyword%' OR 
                     EXISTS (SELECT 1 FROM drivers d WHERE d.driverId = rs.driverId AND d.forename LIKE '%$search_keyword%'))";
}

$sql .= " GROUP BY c.constructor_name, ci.circuit_name 
          ORDER BY total_wins $sort_order 
          LIMIT $start_from, $results_per_page";

$result = $conn->query($sql);

// Count total wins for pagination
$sql_total = "SELECT COUNT(DISTINCT c.constructor_id, ci.circuit_id) AS total 
              FROM results rs 
              INNER JOIN constructors c ON rs.constructorId = c.constructor_id 
              INNER JOIN races r ON rs.raceId = r.raceId 
              INNER JOIN circuits ci ON r.circuit_id = ci.circuit_id 
              WHERE rs.position = 1";

// Add search condition for total count
if (!empty($search_keyword)) {
    $sql_total .= " AND (c.constructor_name LIKE '%$search_keyword%' OR 
                          EXISTS (SELECT 1 FROM drivers d WHERE d.driverId = rs.driverId AND d.forename LIKE '%$search_keyword%'))";
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
    <title>Constructors with Most Wins</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />  
    <link rel="stylesheet" href="./assets/css/style.css">
   <style>
        /* Make the body and html take the full height */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        /* Flexbox for the entire page layout */
        .wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #212529;
        }

        /* Main content should grow to take available space */
        #main {
            flex: 1;
        }

        /* Footer styling */
        footer {
            background-color: #15151E;
            color: white;
            text-align: center;
            padding: 10px 0;
        }
    </style>
</head>
  <body>
    <div class="wrapper">
    <div class="topbar">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2">
                <!-- <div class="heading">
                  <a href="index.php">  <h4>Formula1</h4></a>
                </div> -->
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
     <h2>Circuits and Constructors with Most Wins</h2>
                            </div>
                        </div>
                        
    <!-- Sort and Search Form -->
    <div class="custom-filter-container">
        <form method="GET" action="cwithmost.php" class="custom-filter-form">
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
                <label for="search" class="custom-label">Search by Constructor or Driver Name:</label>
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
            <a href="cwithmost.php" class="custom-btn custom-btn-clear">Clear Filter</a>
        </div>
    <?php endif; ?>

<div class="row mt-5">
    <table  class="table table-dark table-striped">
        <thead>
          <tr>
          <th>Constructor</th>
                                    <th>Circuit</th>
                                    <th>Total Wins</th>
          </tr>
        </thead>
        <tbody>
        <?php
                                if ($result->num_rows > 0) {
                                    // Output each row of data
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['constructor_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['circuit_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['total_wins']) . "</td>";
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
      <<div class="pagination">
    <?php
    // Ensure $current_page is an integer
    $current_page = isset($_GET['page']) && is_numeric($_GET['page']) 
        ? intval($_GET['page']) 
        : 1;

    // Ensure $total_pages is at least 1 to avoid errors
    $total_pages = isset($total_pages) && $total_pages > 0 
        ? intval($total_pages) 
        : 1;

    // Set default values for search and sort order
    $search_keyword = isset($_GET['search']) ? $_GET['search'] : '';
    $sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'asc';

    // Previous button
    if ($current_page > 1) {
        echo '<a href="cwithmost.php?page=' . ($current_page - 1) . '&search=' . urlencode($search_keyword) . '&sort_order=' . $sort_order . '" class="button-7">Previous</a>';
    } else {
        echo '<span class="disabled">Previous</span>';
    }

    // Page number buttons
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            echo '<span class="current-page">' . $i . '</span>'; // Current page
        } else {
            echo '<a href="cwithmost.php?page=' . $i . '&search=' . urlencode($search_keyword) . '&sort_order=' . $sort_order . '">' . $i . '</a>';
        }
    }

    // Next button
    if ($current_page < $total_pages) {
        echo '<a href="cwithmost.php?page=' . ($current_page + 1) . '&search=' . urlencode($search_keyword) . '&sort_order=' . $sort_order . '" class="button-7">Next</a>';
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
   <footer>
        <p style="background-color: #15151E; color: white;">&copy; 2024 Formula Vault. All rights reserved.</p>
    </footer>
            </div>
            </div>


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
