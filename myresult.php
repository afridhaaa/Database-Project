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

// Construct SQL query for fetching results data with sorting and search
$sql = "SELECT resultId, positionOrder, `position`, points, time, fastestLap, fastestLapTime, fastestLapSpeed, rank 
    FROM results 
    WHERE `position` LIKE '%$search_keyword%' 
    OR points LIKE '%$search_keyword%' 
    OR rank LIKE '%$search_keyword%' 
    ORDER BY `position` $sort_order 
    LIMIT $start_from, $results_per_page
    ";

$result = $conn->query($sql);

// Find out the total number of pages
$total_sql = "SELECT COUNT(*) AS total 
                FROM results 
                WHERE `position` LIKE '%$search_keyword%' 
                OR points LIKE '%$search_keyword%' 
                OR rank LIKE '%$search_keyword%'
                ";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_pages = ceil($total_row["total"] / $results_per_page);
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Formula1 - Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/style.css">
    <style>
        .pagination {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .page-numbers {
            display: flex;
            justify-content: center;
            margin: 5px 0;
        }

        .page-numbers a {
            margin: 0 5px;
            text-decoration: none;
            padding: 5px 10px;
            border: 1px solid #007bff;
            color: #007bff;
        }

        .page-numbers a.active {
            background-color: #007bff;
            color: white;
        }

        .page-numbers .disabled {
            color: grey;
        }
    </style>
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
                                <h2>Race Results</h2>
                            </div>
                        </div>
                        
                        <!-- Sort and Search Form -->
                        <div class="custom-filter-container my-5">
                            <form method="GET" action="myresult.php" class="custom-filter-form">
                                <!-- Sort Dropdown -->
                                <div class="custom-form-group">
                                    <label for="sort_order" class="custom-label">Sort by Position:</label>
                                    <select name="sort_order" id="sort_order" class="custom-form-control" onchange="this.form.submit()">
                                        <option value="ASC" <?php if ($sort_order == 'ASC') echo 'selected'; ?>>Ascending</option>
                                        <option value="DESC" <?php if ($sort_order == 'DESC') echo 'selected'; ?>>Descending</option>
                                    </select>
                                </div>

                                <!-- Search Bar -->
                                <div class="custom-form-group">
                                    <label for="search" class="custom-label">Search by Position, Points, or Rank:</label>
                                    <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search Results" value="<?php echo htmlspecialchars($search_keyword); ?>">
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
                                <a href="myresult.php" class="custom-btn custom-btn-clear">Clear Search</a>
                            </div>
                        <?php endif; ?>

                        <div class="row mt-5">
                            <table class="table table-dark table-striped">
                                <thead>
                                    <tr>
                                        
                                     
                                        <th scope="col">Points</th>
                                        <th scope="col">Time</th>
                                        <th scope="col">Fastest Lap</th>
                                        <th scope="col">Fastest Lap Time</th>
                                        <th scope="col">Fastest Lap Speed</th>
                                        <th scope="col">Rank</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                    if ($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                         
                                       
                                            echo "<td>" . $row['points'] . "</td>";
                                            echo "<td>" . $row['time'] . "</td>";
                                            echo "<td>" . $row['fastestLap'] . "</td>";
                                            echo "<td>" . $row['fastestLapTime'] . "</td>";
                                            echo "<td>" . $row['fastestLapSpeed'] . "</td>";
                                            echo "<td>" . $row['rank'] . "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='9'>No data available</td></tr>";
                                    }
                                ?>
                                </tbody>
                            </table>
                        </div>

                       <!-- Pagination Controls -->
<div class="pagination">
    <?php
    // Previous button
    if ($current_page > 1) {
        echo '<a href="myresult.php?page=' . ($current_page - 1) . '" class="button-7">Previous</a>';
    } else {
        echo '<span class="disabled">Previous</span>';
    }

    // Page numbers
    echo '<div class="page-numbers">';

    // Calculate the start and end range for pagination
    $start_page = max(1, $current_page - 2); // Start 2 pages back from current
    $end_page = min($total_pages, $current_page + 2); // End 2 pages ahead of current

    // Ensure that the pagination does not show less than 1 or more than the total pages
    if ($start_page > 1) {
        echo '<a href="myresult.php?page=1">1</a>'; // Show first page if start_page > 1
        if ($start_page > 2) {
            echo '<span>...</span>'; // Ellipsis if there's a gap
        }
    }

    // Loop through the range of pages to display
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {
            echo '<a href="#" class="active">' . $i . '</a>'; // Active page
        } else {
            echo '<a href="myresult.php?page=' . $i . '">' . $i . '</a>'; // Other pages
        }
    }

    if ($end_page < $total_pages) {
        if ($end_page < $total_pages - 1) {
            echo '<span>...</span>'; // Ellipsis if there's a gap
        }
        echo '<a href="myresult.php?page=' . $total_pages . '">' . $total_pages . '</a>'; // Show last page if end_page < total_pages
    }

    echo '</div>';

    // Next button
    if ($current_page < $total_pages) {
        echo '<a href="myresult.php?page=' . ($current_page + 1) . '" class="button-7">Next</a>';
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
