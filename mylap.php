<?php
include 'db/db.php';
$collection = $db->selectCollection('lap_times');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define how many results you want per page
$results_per_page = 10;

$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search_lap = isset($_GET['search_lap']) ? trim($_GET['search_lap']) : '';
$search_position = isset($_GET['search_position']) ? trim($_GET['search_position']) : '';
$sort_order = isset($_GET['sort_order']) && $_GET['sort_order'] === 'DESC' ? -1 : 1;

// Start from for pagination
$skip = ($current_page - 1) * $results_per_page;

$pipeline = [];

// Add search conditions if the search bars are filled
$match_conditions = [];
if (!empty($search_lap)) {
    $match_conditions[] = ['lap' => intval($search_lap)];  // Exact match for lap
}
if (!empty($search_position)) {
    $match_conditions[] = ['position' => intval($search_position)];  // Exact match for position
}

// Add $match stage if there are any conditions
if (!empty($match_conditions)) {
    $pipeline[] = ['$match' => ['$or' => $match_conditions]];
}

// Include necessary fields and sort results
$pipeline[] = [
    '$project' => [
        '_id' => 1,
        'raceId' => 1,
        'driverId' => 1,
        'lap' => 1,
        'position' => 1,
        'time' => 1,
        'milliseconds' => 1
    ]
];
$pipeline[] = ['$sort' => ['lap' => $sort_order]];

// Add pagination stages
$pipeline[] = ['$skip' => $skip];
$pipeline[] = ['$limit' => $results_per_page];

// Fetch data using aggregation pipeline
$data = iterator_to_array($collection->aggregate($pipeline), false);

// Total count for pagination
$total_pipeline = [];
if (!empty($match_conditions)) {
    $total_pipeline[] = ['$match' => ['$or' => $match_conditions]];
}
$total_pipeline[] = ['$count' => 'total'];

$total_data = iterator_to_array($collection->aggregate($total_pipeline), false);
$total_count = $total_data[0]['total'] ?? 0;
$total_pages = ceil($total_count / $results_per_page);
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
                <div class="heading">
                  <a href="index.php"> <!-- <h4>Formula1</h4></a> -->
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
                            <div class="back-button" style="margin: 20px;">
                                <a href="index.php" class="button-8">← Back to Home</a>
                            </div>
                            <div class="head">
                                <h2>Lap Times Details</h2>
                            </div>
                        </div>
                        
                        <!-- Sort and Search Form -->
                        <div class="custom-filter-container my-5">
                            <form method="GET" action="mylap.php" class="custom-filter-form">
                                <!-- Search Bar for Lap -->
                                <div class="custom-form-group">
                                    <label for="search_lap" class="custom-label">Search by Lap:</label>
                                    <input type="text" name="search_lap" class="custom-form-control" placeholder="Search by Lap" value="<?php echo htmlspecialchars($_GET['search_lap'] ?? ''); ?>">
                                </div>

                                <!-- Search Bar for Position -->
                                <div class="custom-form-group">
                                    <label for="search_position" class="custom-label">Search by Position:</label>
                                    <input type="text" name="search_position" class="custom-form-control" placeholder="Search by Position" value="<?php echo htmlspecialchars($_GET['search_position'] ?? ''); ?>">
                                </div>

                                <!-- Sort Dropdown -->
                                <div class="custom-form-group">
                                    <label for="sort_order" class="custom-label">Sort by Lap:</label>
                                    <select name="sort_order" id="sort_order" class="custom-form-control" onchange="this.form.submit()">
                                        <option value="ASC" <?php if ($sort_order == 1) echo 'selected'; ?>>Ascending</option>
                                        <option value="DESC" <?php if ($sort_order == -1) echo 'selected'; ?>>Descending</option>
                                    </select>
                                </div>

                                <!-- Search Button -->
                                <div class="custom-form-group">
                                    <button type="submit" class="custom-btn custom-btn-primary">Search</button>
                                </div>
                            </form>
                        </div>

                        <!-- Clear Search Button: Show only when search is applied -->
                        <?php if (!empty($search_lap) || !empty($search_position)) : ?>
                            <div style="margin-bottom: 20px; margin-left: 30px;">
                                <a href="mylap.php" class="custom-btn custom-btn-clear">Clear Search</a>
                            </div>
                        <?php endif; ?>


                        <div class="row mt-5">
                            <table class="table table-dark table-striped">
                                <thead>
                                    <tr>
                                        <th>Race ID</th>
                                        <th>Driver ID</th>
                                        <th>Lap</th>
                                        <th>Position</th>
                                        <th>Time</th>
                                        <th>Milliseconds</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['raceId']); ?></td>
                                            <td><?php echo htmlspecialchars($row['driverId']); ?></td>
                                            <td><?php echo htmlspecialchars($row['lap']); ?></td>
                                            <td><?php echo htmlspecialchars($row['position']); ?></td>
                                            <td><?php echo htmlspecialchars($row['time']); ?></td>
                                            <td><?php echo htmlspecialchars($row['milliseconds']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>