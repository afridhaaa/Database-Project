<?php
include 'db/db.php';  // Include your database connection

// Set the number of results per page
$results_per_page = 10;

// Initialize variables for sorting and searching
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';
$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';

// Determine the total number of records for pagination
$sql_total = "SELECT COUNT(*) AS total 
              FROM results rs 
              INNER JOIN drivers d ON rs.driverId = d.driverId 
              INNER JOIN constructors c ON rs.constructorId = c.constructor_id 
              INNER JOIN races r ON rs.raceId = r.raceId 
              WHERE c.constructor_id IN (SELECT constructorId FROM results WHERE position <= 3) 
              AND (d.forename LIKE ? OR c.constructor_name LIKE ?)";
$stmt = $conn->prepare($sql_total);
$like_keyword = "%" . $search_keyword . "%";
$stmt->bind_param("ss", $like_keyword, $like_keyword);
$stmt->execute();
$total_result = $stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_pages = ceil($total_row['total'] / $results_per_page);

// Get the current page number from URL, if not set, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the starting limit number for the query
$start_from = ($current_page - 1) * $results_per_page;

// SQL query to fetch driver, constructor, race, and fastest lap speed data with limit
$sql = "SELECT d.forename, c.constructor_name, r.name AS race_name, rs.fastestLapSpeed 
        FROM results rs 
        INNER JOIN drivers d ON rs.driverId = d.driverId 
        INNER JOIN constructors c ON rs.constructorId = c.constructor_id 
        INNER JOIN races r ON rs.raceId = r.raceId 
        WHERE c.constructor_id IN (SELECT constructorId FROM results WHERE position <= 3) 
        AND (d.forename LIKE ? OR c.constructor_name LIKE ?)
        ORDER BY rs.fastestLapSpeed $sort_order 
        LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $like_keyword, $like_keyword, $start_from, $results_per_page);
$stmt->execute();
$result = $stmt->get_result();
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
     <h2>Drivers with Fastest Lap Speed</h2>
                            </div>
                        </div>
                        
                        <div class="custom-filter-container">
        <form method="GET" action="dwithfast.php" class="custom-filter-form">
            <div class="custom-form-group">
                <label for="sort_order" class="custom-label">Sort by Fastest Lap Speed:</label>
                <select name="sort_order" id="sort_order" class="custom-form-control" onchange="this.form.submit()">
                    <option value="DESC" <?php if ($sort_order == 'DESC') echo 'selected'; ?>>Most to Least</option>
                    <option value="ASC" <?php if ($sort_order == 'ASC') echo 'selected'; ?>>Least to Most</option>
                </select>
            </div>

            <div class="custom-form-group">
                <label for="search" class="custom-label">Search by Driver or Constructor Name:</label>
                <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search by driver or constructor" value="<?php echo htmlspecialchars($search_keyword); ?>">
            </div>

            <div class="custom-form-group">
                <button type="submit" class="custom-btn custom-btn-primary">Search</button>
            </div>
        </form>
    </div>

    <?php if (!empty($search_keyword)) : ?>
        <div style="margin-bottom: 20px; margin-left:  30px;">
            <a href="dwithfast.php" class="custom-btn custom-btn-clear">Clear Search</a>
        </div>
    <?php endif; ?>

<div class="row mt-5">
    <table  class="table table-dark table-striped">
        <thead>
          <tr>
          <th>Driver Name</th>
                                    <th>Constructor Name</th>
                                    <th>Race Name</th>
                                    <th>Fastest Lap Speed (km/h)</th>
          </tr>
        </thead>
        <tbody>
        <?php
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['forename']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['constructor_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['race_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['fastestLapSpeed']) . " km/h</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>No data available</td></tr>";
                                }
                                ?>
                            </tbody>
        </tbody>
      </table>
      
      <div class="pagination">
    <?php
    // Ensure $current_page is always an integer
    $current_page = isset($_GET['page']) && is_numeric($_GET['page']) 
        ? intval($_GET['page']) 
        : 1;

    // Ensure $total_pages is always an integer and greater than zero
    $total_pages = isset($total_pages) && is_numeric($total_pages) && $total_pages > 0 
        ? intval($total_pages) 
        : 1;

    // Ensure search and sort parameters have default values
    $search_keyword = isset($_GET['search']) ? $_GET['search'] : '';
    $sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'asc';

    // Define how many page links to display at once
    $total_display_pages = 10;

    // Display "Previous" button
    if ($current_page > 1) {
        echo '<a href="dwithfast.php?page=' . ($current_page - 1) . '&sort_order=' . $sort_order . '&search=' . urlencode($search_keyword) . '" class="button-7">Previous</a>';
    } else {
        echo '<span class="disabled">Previous</span>';
    }

    // Show the first page link, always
    if ($start_page > 2) {
        echo '<a href="dwithfast.php?page=1&sort_order=' . $sort_order . '&search=' . urlencode($search_keyword) . '" class="button-7">1</a>';
        echo '<span class="disabled">...</span>'; // Ellipsis for skipped pages
    }

    // Calculate start and end pages for pagination
    $start_page = max(2, $current_page - floor($total_display_pages / 2));
    $end_page = min($total_pages - 1, $current_page + floor($total_display_pages / 2));

    // Adjust if the range is close to the beginning or end
    if ($start_page < 2) {
        $start_page = 2;
        $end_page = min($total_pages - 1, $total_display_pages);
    } elseif ($end_page >= $total_pages - 1) {
        $end_page = $total_pages - 1;
        $start_page = max(2, $end_page - $total_display_pages + 1);
    }

    // Display the middle range of pages
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {
            echo '<span class="active-page">' . $i . '</span>'; // Highlight current page
        } else {
            echo '<a href="dwithfast.php?page=' . $i . '&sort_order=' . $sort_order . '&search=' . urlencode($search_keyword) . '" class="button-7">' . $i . '</a>';
        }
    }

    // Show the last page link, always
    if ($end_page < $total_pages - 1) {
        echo '<span class="disabled">...</span>'; // Ellipsis for skipped pages
        echo '<a href="dwithfast.php?page=' . $total_pages . '&sort_order=' . $sort_order . '&search=' . urlencode($search_keyword) . '" class="button-7">' . $total_pages . '</a>';
    }

    // Display "Next" button
    if ($current_page < $total_pages) {
        echo '<a href="dwithfast.php?page=' . ($current_page + 1) . '&sort_order=' . $sort_order . '&search=' . urlencode($search_keyword) . '" class="button-7">Next</a>';
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
