<?php
include 'db/db.php';  // Include your database connection
// global $collection;   // Ensure $collection is accessible
$collection = $db->selectCollection('results');

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Pagination and sorting
$results_per_page = 13;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$sort_order = isset($_GET['sort']) && $_GET['sort'] === 'ASC' ? 1 : -1;

// Start from for pagination
$skip = ($current_page - 1) * $results_per_page;

// Define the MongoDB aggregation pipeline
$pipeline = [
    // Join results with drivers and constructors collections
    ['$lookup' => [
        'from' => 'drivers',
        'localField' => 'driverId',
        'foreignField' => 'driverId',
        'as' => 'driver'
    ]],
    ['$unwind' => '$driver'],
    ['$lookup' => [
        'from' => 'constructors',
        'localField' => 'constructorId',
        'foreignField' => 'constructor_id',
        'as' => 'constructor'
    ]],
    ['$unwind' => '$constructor'],
    
    // Filter for positions in the top 3
    ['$match' => ['position' => ['$lte' => 3]]]
];

// Add search term match if applicable
if (!empty($search_term)) {
    $pipeline[] = ['$match' => ['driver.forename' => new MongoDB\BSON\Regex($search_term, 'i')]];
}

// Continue pipeline with grouping, sorting, and pagination
$pipeline = array_merge($pipeline, [
    ['$group' => [
        '_id' => ['driverId' => '$driverId', 'constructorId' => '$constructorId'],
        'forename' => ['$first' => '$driver.forename'],
        'constructor_name' => ['$first' => '$constructor.constructor_name'],
        'podium_finishes' => ['$sum' => 1]
    ]],
    ['$sort' => ['podium_finishes' => $sort_order]],
    ['$skip' => $skip],
    ['$limit' => $results_per_page]
]);

// Execute the aggregation pipeline and convert results to an array directly
$data = iterator_to_array($collection->aggregate($pipeline), false);

// Count total results for pagination
$total_pipeline = [
    ['$match' => ['position' => ['$lte' => 3]]]
];

// Add search term match if applicable for total count
if (!empty($search_term)) {
    $total_pipeline[] = ['$match' => ['driver.forename' => new MongoDB\BSON\Regex($search_term, 'i')]];
}

$total_pipeline = array_merge($total_pipeline, [
    ['$group' => ['_id' => '$driverId']],
    ['$count' => 'total']
]);

$total_data = iterator_to_array($collection->aggregate($total_pipeline), false);
$total_results = $total_data[0]['total'] ?? 0;
$total_pages = ceil($total_results / $results_per_page);
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Top Drivers with Podium Finishes</title>
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
                  <a href="index.php">  <!-- <h4>Formula1</h4></a> -->
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
                                <h2>Top Drivers with Podium Finishes</h2>
                            </div>
                        </div>
                        
                        <!-- Sort and Search Form -->
                        <div class="custom-filter-container">
                            <form method="GET" action="top5.php" class="custom-filter-form">
                                <!-- Sort Dropdown -->
                                <div class="custom-form-group">
                                    <label for="sort_order" class="custom-label">Sort by Podium Finishes:</label>
                                    <select name="sort" id="sort_order" class="custom-form-control" onchange="this.form.submit()">
                                        <option value="DESC" <?php if ($sort_order == -1) echo 'selected'; ?>>Most to Least</option>
                                        <option value="ASC" <?php if ($sort_order == 1) echo 'selected'; ?>>Least to Most</option>
                                    </select>
                                </div>

                                <!-- Search Bar -->
                                <div class="custom-form-group">
                                    <label for="search" class="custom-label">Search by Driver Name:</label>
                                    <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search by driver name" value="<?php echo htmlspecialchars($search_term); ?>">
                                </div>

                                <!-- Search Button -->
                                <div class="custom-form-group">
                                    <button type="submit" class="custom-btn custom-btn-primary">Search</button>
                                </div>
                            </form>
                        </div>

                        <!-- Clear Search Button -->
                        <?php if (!empty($search_term)) : ?>
                            <div style="margin-bottom: 20px; margin-left: 30px;">
                                <a href="top5.php" class="custom-btn custom-btn-clear">Clear Search</a>
                            </div>
                        <?php endif; ?>

                        <div class="row mt-5">
                            <table class="table table-dark table-striped">
                                <thead>
                                    <tr>
                                        <th>Driver</th>
                                        <th>Constructor</th>
                                        <th>Podium Finishes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['forename']); ?></td>
                                            <td><?php echo htmlspecialchars($row['constructor_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['podium_finishes']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
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
        echo '<a href="top5.php?page=' . ($current_page - 1) . '&search=' . urlencode($search_keyword) . '&sort_order=' . $sort_order . '" class="button-7">Previous</a>';
    } else {
        echo '<span class="disabled">Previous</span>';
    }

    // Page number buttons
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            echo '<span class="current-page">' . $i . '</span>'; // Current page
        } else {
            echo '<a href="top5.php?page=' . $i . '&search=' . urlencode($search_keyword) . '&sort_order=' . $sort_order . '">' . $i . '</a>';
        }
    }

    // Next button
    if ($current_page < $total_pages) {
        echo '<a href="top5.php?page=' . ($current_page + 1) . '&search=' . urlencode($search_keyword) . '&sort_order=' . $sort_order . '" class="button-7">Next</a>';
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
