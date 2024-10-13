<?php
include 'db/db.php';
include 'process.php';

// Define how many results you want per page
$results_per_page = 13;

// Determine which page number visitor is currently on
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $current_page = (int)$_GET['page'];
} else {
    $current_page = 1; // Set default page number to 1
}

// Determine the SQL LIMIT starting number for the results on the displaying page
$start_from = ($current_page - 1) * $results_per_page;

// Handle sorting and search
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';
$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';

// Construct SQL query for fetching lap times with sorting and search
$sql = "SELECT lt.id, lt.raceId, lt.driverId, lt.lap, lt.position, lt.time, lt.milliseconds 
        FROM lap_times lt 
        WHERE lt.driverId LIKE '%$search_keyword%' 
        OR lt.position LIKE '%$search_keyword%' 
        OR lt.lap LIKE '%$search_keyword%' 
        ORDER BY lt.lap $sort_order 
        LIMIT $start_from, $results_per_page";
$result = $conn->query($sql);

// Find out the total number of pages
$total_sql = "SELECT COUNT(*) AS total 
              FROM lap_times lt 
              WHERE lt.driverId LIKE '%$search_keyword%' 
              OR lt.position LIKE '%$search_keyword%' 
              OR lt.lap LIKE '%$search_keyword%'";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_pages = ceil($total_row["total"] / $results_per_page);
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Formula1 - Lap Times</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/style.css">
</head>
  <body>
   <div class="topbar">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2">

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
                            <div class="head">
                                <h2>Lap Times Details</h2>
                            </div>
                        </div>
                        
                        <!-- Sort and Search Form -->
                        <div class="custom-filter-container my-5">
                            <form method="GET" action="mylap.php" class="custom-filter-form">
                                <!-- Sort Dropdown -->
                                <div class="custom-form-group">
                                    <label for="sort_order" class="custom-label">Sort by Lap:</label>
                                    <select name="sort_order" id="sort_order" class="custom-form-control" onchange="this.form.submit()">
                                        <option value="ASC" <?php if ($sort_order == 'ASC') echo 'selected'; ?>>Ascending</option>
                                        <option value="DESC" <?php if ($sort_order == 'DESC') echo 'selected'; ?>>Descending</option>
                                    </select>
                                </div>

                                <!-- Search Bar -->
                                <div class="custom-form-group">
                                    <label for="search" class="custom-label">Search by Driver ID, Position, or Lap:</label>
                                    <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search Lap Times" value="<?php echo htmlspecialchars($search_keyword); ?>">
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
                                <a href="mylap.php" class="custom-btn custom-btn-clear">Clear Search</a>
                            </div>
                        <?php endif; ?>

                        <div class="row mt-5">
                            <table class="table table-dark table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Race ID</th>
                                        <th scope="col">Driver ID</th>
                                        <th scope="col">Lap</th>
                                        <th scope="col">Position</th>
                                        <th scope="col">Time</th>
                                        <th scope="col">Milliseconds</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                    if ($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . $row['id'] . "</td>";
                                            echo "<td>" . $row['raceId'] . "</td>";
                                            echo "<td>" . $row['driverId'] . "</td>";
                                            echo "<td>" . $row['lap'] . "</td>";
                                            echo "<td>" . $row['position'] . "</td>";
                                            echo "<td>" . $row['time'] . "</td>";
                                            echo "<td>" . $row['milliseconds'] . "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='7'>No data available</td></tr>";
                                    }
                                ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Controls -->
                        <div class="pagination">
    <?php
    // Ensure $current_page is always an integer
    $current_page = isset($_GET['page']) && is_numeric($_GET['page']) 
        ? intval($_GET['page']) 
        : 1;

    // Ensure $total_pages is a valid integer
    $total_pages = isset($total_pages) && is_numeric($total_pages) && $total_pages > 0 
        ? intval($total_pages) 
        : 1;

    // Display "Previous" button
    if ($current_page > 1) {
        echo '<a href="mylap.php?page=' . ($current_page - 1) . '" class="button-7">Previous</a>';
    } else {
        echo '<span class="disabled">Previous</span>';
    }

    // Calculate the start and end page numbers
    $start_page = max(1, $current_page - 2); // Start from two pages before the current page
    $end_page = min($total_pages, $current_page + 2); // End at two pages after the current page

    // Adjust if current page is near the beginning
    if ($current_page <= 2) {
        $end_page = min(4, $total_pages); // Show up to 4 pages if near the start
    }

    // Adjust if current page is near the end
    if ($current_page >= $total_pages - 1) {
        $start_page = max(1, $total_pages - 3); // Show the last 4 pages if near the end
    }

    // Display page numbers
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {
            echo '<span class="current-page">' . $i . '</span>'; // Highlight current page
        } else {
            echo '<a href="mylap.php?page=' . $i . '" class="button-7">' . $i . '</a>';
        }
    }

    // Display "Next" button
    if ($current_page < $total_pages) {
        echo '<a href="mylap.php?page=' . ($current_page + 1) . '" class="button-7">Next</a>';
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
