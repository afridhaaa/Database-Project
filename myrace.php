<?php
include 'db/db.php';

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

// Construct SQL query for fetching race data with sorting and search
$sql = "SELECT raceId, year, round, circuit_id, name, date, url 
        FROM races 
        WHERE name LIKE '%$search_keyword%' 
        OR year LIKE '%$search_keyword%' 
        OR round LIKE '%$search_keyword%' 
        ORDER BY name $sort_order 
        LIMIT $start_from, $results_per_page";
$result = $conn->query($sql);

// Find out the total number of pages
$total_sql = "SELECT COUNT(*) AS total 
              FROM races 
              WHERE name LIKE '%$search_keyword%' 
              OR year LIKE '%$search_keyword%' 
              OR round LIKE '%$search_keyword%'";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_pages = ceil($total_row["total"] / $results_per_page);
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Formula1 - Races</title>
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
                                <h2>Race Details</h2>
                            </div>
                        </div>
                        
                        <!-- Sort and Search Form -->
                        <div class="custom-filter-container my-5">
                            <form method="GET" action="myrace.php" class="custom-filter-form">
                                <!-- Sort Dropdown -->
                                <div class="custom-form-group">
                                    <label for="sort_order" class="custom-label">Sort by Name:</label>
                                    <select name="sort_order" id="sort_order" class="custom-form-control" onchange="this.form.submit()">
                                        <option value="ASC" <?php if ($sort_order == 'ASC') echo 'selected'; ?>>Ascending</option>
                                        <option value="DESC" <?php if ($sort_order == 'DESC') echo 'selected'; ?>>Descending</option>
                                    </select>
                                </div>

                                <!-- Search Bar -->
                                <div class="custom-form-group">
                                    <label for="search" class="custom-label">Search by Name, Year, or Round:</label>
                                    <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search Races" value="<?php echo htmlspecialchars($search_keyword); ?>">
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
                                <a href="myrace.php" class="custom-btn custom-btn-clear">Clear Search</a>
                            </div>
                        <?php endif; ?>

                        <div class="row mt-5">
                            <table class="table table-dark table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">Race ID</th>
                                        <th scope="col">Year</th>
                                        <th scope="col">Round</th>
                                        <th scope="col">Circuit ID</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">URL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                    if ($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . $row['raceId'] . "</td>";
                                            echo "<td>" . $row['year'] . "</td>";
                                            echo "<td>" . $row['round'] . "</td>";
                                            echo "<td>" . $row['circuit_id'] . "</td>";
                                            echo "<td>" . $row['name'] . "</td>";
                                            echo "<td>" . $row['date'] . "</td>";
                                            echo "<td><a href='" . $row['url'] . "' target='_blank'>Link</a></td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='7'>No data available</td></tr>";
                                    }
                                ?>
                                </tbody>
                            </table>
                        </div>

            
                        <nav>
    <ul class="pagination justify-content-center">
        <?php
        // Ensure $page and $total_pages are integers
        $page = isset($_GET['page']) && is_numeric($_GET['page']) 
            ? intval($_GET['page']) 
            : 1;

        $total_pages = isset($total_pages) && is_numeric($total_pages) && $total_pages > 0 
            ? intval($total_pages) 
            : 1;

        // Define the number of pagination links to display
        $max_links = 8;

        // Calculate the start and end pages for the pagination
        $start_page = max(1, $page - floor($max_links / 2));
        $end_page = min($total_pages, $start_page + $max_links - 1);

        // Adjust start page if we're close to the end
        if ($end_page - $start_page < $max_links - 1) {
            $start_page = max(1, $end_page - $max_links + 1);
        }

        // Previous button
        if ($page > 1) {
            echo '<li class="page-item">
                    <a class="page-link" href="myrace.php?page=' . ($page - 1) . '">Previous</a>
                  </li>';
        } else {
            echo '<li class="page-item disabled">
                    <span class="page-link">Previous</span>
                  </li>';
        }

        // Display page numbers within the calculated range
        for ($i = $start_page; $i <= $end_page; $i++) {
            $active = ($i == $page) ? 'active' : '';
            echo '<li class="page-item ' . $active . '">
                    <a class="page-link" href="myrace.php?page=' . $i . '">' . $i . '</a>
                  </li>';
        }

        // Next button
        if ($page < $total_pages) {
            echo '<li class="page-item">
                    <a class="page-link" href="myrace.php?page=' . ($page + 1) . '">Next</a>
                  </li>';
        } else {
            echo '<li class="page-item disabled">
                    <span class="page-link">Next</span>
                  </li>';
        }
        ?>
    </ul>
</nav>





                        
                    </div>
                </div>
            </div>
        </div>
    </div>
   </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
