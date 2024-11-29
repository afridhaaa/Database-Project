<?php
include 'db/db.php';

$collection = $db->selectCollection('results');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define how many results you want per page
$results_per_page = 10;

// Determine which page number visitor is currently on
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Get search terms from GET parameters
$search_points = isset($_GET['search_points']) ? trim($_GET['search_points']) : '';
$search_rank = isset($_GET['search_rank']) ? trim($_GET['search_rank']) : '';
$sort_order = isset($_GET['sort_order']) && $_GET['sort_order'] === 'DESC' ? -1 : 1;

// Start from for pagination
$skip = ($current_page - 1) * $results_per_page;

// MongoDB aggregation pipeline
$pipeline = [];

// Add search conditions if the search fields are filled
$match_conditions = [];
if (!empty($search_rank)) {
    $match_conditions['rank'] = intval($search_rank); // Exact numeric match for year
}
if (!empty($search_points)) {
    $match_conditions['points'] = intval($search_points); // Exact numeric match for round
}

// Add `$match` stage if there are any conditions
if (!empty($match_conditions)) {
    $pipeline[] = ['$match' => $match_conditions];
}

// Include necessary fields and sort the results
$pipeline[] = [
    '$project' => [
        'points' => 1,
        'time' => 1,
        'fastestLap' => 1,
        'fastestLapTime' => 1,
        'fastestLapSpeed' => 1,
        'rank' => 1,
    ]
];
$pipeline[] = ['$sort' => ['rank' => $sort_order]];
$pipeline[] = ['$skip' => $skip];
$pipeline[] = ['$limit' => $results_per_page];

// Fetch data using aggregation pipeline
$data = iterator_to_array($collection->aggregate($pipeline), false);

// Count total documents for pagination
$total_pipeline = [];
if (!empty($match_conditions)) {
    $total_pipeline[] = ['$match' => $match_conditions];
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
    <title>Formula1 - Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                                <h2>Race Results</h2>
                            </div>
                        </div>
                        
                        <!-- Sort and Search Form -->
                        <div class="custom-filter-container my-5">
                        <form method="GET" action="myresult.php" class="custom-filter-form">
                            <!-- Search Bar for Points -->
                            <div class="custom-form-group">
                                <label for="search_points" class="custom-label">Search by Points:</label>
                                <input type="text" name="search_points" class="custom-form-control" placeholder="Search by Points" value="<?php echo htmlspecialchars($_GET['search_points'] ?? ''); ?>">
                            </div>

                            <!-- Search Bar for Rank -->
                            <div class="custom-form-group">
                                <label for="search_rank" class="custom-label">Search by Rank:</label>
                                <input type="text" name="search_rank" class="custom-form-control" placeholder="Search by Rank" value="<?php echo htmlspecialchars($_GET['search_rank'] ?? ''); ?>">
                            </div>

                            <!-- Sort Dropdown -->
                            <div class="custom-form-group">
                                <label for="sort_order" class="custom-label">Sort by Rank:</label>
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

                        <!-- Clear Search Button -->
                        <?php if (!empty($search_position) || !empty($search_points) || !empty($search_rank)) : ?>
                            <div style="margin-bottom: 20px; margin-left: 30px;">
                                <a href="myresult.php" class="custom-btn custom-btn-clear">Clear Search</a>
                            </div>
                        <?php endif; ?>

                        <!-- Results Table -->
                        <div class="row mt-5">
                            <table class="table table-dark table-striped">
                                <thead>
                                    <tr>
                                        <th>Points</th>
                                        <th>Time</th>
                                        <th>Fastest Lap</th>
                                        <th>Fastest Lap Time</th>
                                        <th>Fastest Lap Speed</th>
                                        <th>Rank</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['points']); ?></td>
                                            <td><?php echo htmlspecialchars($row['time']); ?></td>
                                            <td><?php echo htmlspecialchars($row['fastestLap']); ?></td>
                                            <td><?php echo htmlspecialchars($row['fastestLapTime']); ?></td>
                                            <td><?php echo htmlspecialchars($row['fastestLapSpeed']); ?></td>
                                            <td><?php echo htmlspecialchars($row['rank']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
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
                    <a class="page-link" href="myresult.php?page=' . ($page - 1) . '">Previous</a>
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
                    <a class="page-link" href="myresult.php?page=' . $i . '">' . $i . '</a>
                  </li>';
        }

        // Next button
        if ($page < $total_pages) {
            echo '<li class="page-item">
                    <a class="page-link" href="myresult.php?page=' . ($page + 1) . '">Next</a>
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