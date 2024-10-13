<?php
include 'db/db.php';

// Define how many results you want per page
$results_per_page = 10;

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
                <div class="heading">
                  <a href="index.php"><h4>Formula1</h4></a>
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

                        // Pagination Controls
<div class="pagination">
    <?php
    // Previous button
    if ($current_page > 1) {
        echo '<a href="myrace.php?page=' . ($current_page - 1) . '" class="button-7">Previous</a>';
    } else {
        echo '<span class="disabled">Previous</span>';
    }

    // Display page numbers
    $page_count = 0; // To count how many pages have been displayed
    echo '<div class="page-numbers">'; // Container for page numbers

    // Calculate the range of pages to display
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);

    // Show first page if necessary
    if ($start_page > 1) {
        echo '<a href="myrace.php?page=1">1</a>';
        if ($start_page > 2) {
            echo '<span>...</span>'; // Show ellipsis if there are pages skipped
        }
    }

    // Display pages in the range
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {
            echo '<a href="#" class="active">' . $i . '</a>'; // Active page
        } else {
            echo '<a href="myrace.php?page=' . $i . '">' . $i . '</a>';
        }
    }

    // Show last page if necessary
    if ($end_page < $total_pages) {
        if ($end_page < $total_pages - 1) {
            echo '<span>...</span>'; // Show ellipsis if there are pages skipped
        }
        echo '<a href="myrace.php?page=' . $total_pages . '">' . $total_pages . '</a>';
    }

    echo '</div>'; // Close the container

    // Next button
    if ($current_page < $total_pages) {
        echo '<a href="myrace.php?page=' . ($current_page + 1) . '" class="button-7">Next</a>';
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
