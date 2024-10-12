<?php
include 'db/db.php';  // Include your database connection

// Set the number of results per page
$results_per_page = 5; // Adjust this to match the number of results you want to show per page

// Get the current page number from URL, if not set, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Get the search term and sort order from URL
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_order = isset($_GET['sort']) ? $_GET['sort'] : 'DESC';

// Calculate the starting limit number for the query
$start_from = ($current_page - 1) * $results_per_page;

// SQL query to fetch constructor names, circuit names, and total fastest laps
$sql = "SELECT 
            c.constructor_name, 
            ci.circuit_name, 
            COUNT(rs.fastestLap) AS total_fastest_laps 
        FROM 
            results rs 
        INNER JOIN 
            constructors c ON rs.constructorId = c.constructor_id 
        INNER JOIN 
            races r ON rs.raceId = r.race_id 
        INNER JOIN 
            circuits ci ON r.circuit_id = ci.circuit_id 
        WHERE 
            rs.fastestLap IS NOT NULL";

// Append search filter if a search term is provided
if (!empty($search_term)) {
    $sql .= " AND c.constructor_name LIKE '%" . $conn->real_escape_string($search_term) . "%'";
}

$sql .= " GROUP BY 
            c.constructor_name, ci.circuit_name 
          HAVING 
            COUNT(rs.fastestLap) >= 5 
          ORDER BY 
            total_fastest_laps " . ($sort_order === 'ASC' ? 'ASC' : 'DESC') . " 
          LIMIT $start_from, $results_per_page";

$result = $conn->query($sql);

// Check if the result set is empty
$no_data_message = '';
if ($result->num_rows === 0) {
    $no_data_message = "No constructors found matching your search criteria.";
}

// Count total constructors for pagination
$sql_total = "SELECT COUNT(DISTINCT c.constructor_id) AS total 
              FROM results rs 
              INNER JOIN constructors c ON rs.constructorId = c.constructor_id 
              INNER JOIN races r ON rs.raceId = r.race_id 
              INNER JOIN circuits ci ON r.circuit_id = ci.circuit_id 
              WHERE rs.fastestLap IS NOT NULL";

// Append search filter for total count if a search term is provided
if (!empty($search_term)) {
    $sql_total .= " AND c.constructor_name LIKE '%" . $conn->real_escape_string($search_term) . "%'";
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
    <title>Constructors with Fastest Laps</title>
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
     <h2>Constructors with Fastest Laps</h2>
                            </div>
                        </div>
                        
                        <div class="custom-filter-container">
        <!-- Search and Sort Form -->
        <form method="GET" action="clist.php" class="custom-filter-form">
            <div class="custom-form-group">
                <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search by constructor name" value="<?php echo htmlspecialchars($search_term); ?>">
            </div>
            <div class="custom-form-group">
                <select name="sort" class="custom-form-control">
                    <option value="DESC" <?php echo ($sort_order === 'DESC') ? 'selected' : ''; ?>>Sort by Total Fastest Laps (Descending)</option>
                    <option value="ASC" <?php echo ($sort_order === 'ASC') ? 'selected' : ''; ?>>Sort by Total Fastest Laps (Ascending)</option>
                </select>
            </div>
            <div class="custom-form-group">
                <button type="submit" class="custom-btn custom-btn-primary">Apply</button>
                <?php if (!empty($search_term)): ?>
                    <a href="clist.php" class="button-7">Clear Filter</a>
                <?php endif; ?>
                </div>
        </form>
    </div>

<div class="row mt-5">
    <table  class="table table-dark table-striped">
        <thead>
          <tr>
          <th>Constructor</th>
                                    <th>Circuit</th>
                                    <th>Total Fastest Laps</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($no_data_message): ?>
                                    <tr>
                                        <td colspan="3" style="text-align:center;"><?php echo htmlspecialchars($no_data_message); ?></td>
                                    </tr>
                                <?php else: ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['constructor_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['circuit_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['total_fastest_laps']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
        </tbody>
      </table>
      
</div>

        <!-- Pagination Controls -->
        <div class="pagination">
                <?php
                // Previous button
                if ($current_page > 1) {
                    echo '<a href="clist.php?page=' . ($current_page - 1) . '&search=' . urlencode($search_term) . '&sort=' . $sort_order . '" class="button-7">Previous</a>';
                } else {
                    echo '<span class="disabled">Previous</span>';
                }

                // Page numbers
                for ($i = 1; $i <= $total_pages; $i++) {
                    if ($i == $current_page) {
                        echo '<span class="current-page">' . $i . '</span>';
                    } else {
                        echo '<a href="clist.php?page=' . $i . '&search=' . urlencode($search_term) . '&sort=' . $sort_order . '">' . $i . '</a>';
                    }
                }

                // Next button
                if ($current_page < $total_pages) {
                    echo '<a href="clist.php?page=' . ($current_page + 1) . '&search=' . urlencode($search_term) . '&sort=' . $sort_order . '" class="button-7">Next</a>';
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
