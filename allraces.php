<?php

include 'db/db.php';  // Include your database connection

// Initialize variables
$sort_order_lap = 'ASC';
$sort_order_position = 'ASC';
$search_keyword = '';

// Handle GET requests for sorting and searching
if (isset($_GET['sort_order_lap'])) {
    $sort_order_lap = $_GET['sort_order_lap'] === 'DESC' ? -1 : 1;
}

if (isset($_GET['sort_order_position'])) {
    $sort_order_position = $_GET['sort_order_position'] === 'DESC' ? -1 : 1;
}

if (isset($_GET['search'])) {
    $search_keyword = $_GET['search'];
}

// Set the number of results per page
$results_per_page = 10;

// Determine the current page
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// MongoDB Aggregation Pipeline
$pipeline = [
    [
        '$match' => [
            'fastestLapTime' => ['$ne' => null],
            'position' => ['$ne' => 1],
            'forename' => new MongoDB\BSON\Regex($search_keyword, 'i')  // Case-insensitive search
        ]
    ],
    [
        '$lookup' => [
            'from' => 'drivers',
            'localField' => 'driverId',
            'foreignField' => 'driverId',
            'as' => 'driver_details'
        ]
    ],
    [
        '$lookup' => [
            'from' => 'races',
            'localField' => 'raceId',
            'foreignField' => 'raceId',
            'as' => 'race_details'
        ]
    ],
    [
        '$unwind' => '$driver_details'
    ],
    [
        '$unwind' => '$race_details'
    ],
    [
        '$project' => [
            'race_name' => '$race_details.name',
            'forename' => '$driver_details.forename',
            'fastestLapTime' => 1,
            'position' => 1
        ]
    ],
    [
        '$sort' => [
            'fastestLapTime' => $sort_order_lap,
            'position' => $sort_order_position
        ]
    ],
    [
        '$skip' => ($current_page - 1) * $results_per_page
    ],
    [
        '$limit' => $results_per_page
    ]
];

// Execute the aggregation pipeline
$collection = $conn->selectCollection('results');  // Ensure 'results' is the correct collection name
$result = $collection->aggregate($pipeline);

// Count total documents for pagination
$total_count = $collection->countDocuments([
    'fastestLapTime' => ['$ne' => null],
    'position' => ['$ne' => 1],
    'forename' => new MongoDB\BSON\Regex($search_keyword, 'i')
]);

$total_pages = ceil($total_count / $results_per_page);

?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>All Races with Fastest Lap Time</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />  
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
                           <!-- Back Button -->
    <div class="back-button" style="margin: 20px;">
        <a href="index.php" class="button-8">← Back to Home</a>
    </div>
                            <div class="head">
     <h2>All Races with Fastest Lap Time</h2>
                            </div>
                        </div>
                        
  <!-- Sort and Search Form -->
  <div class="custom-filter-container">
                    <form method="GET" action="" class="custom-filter-form">
                        <!-- Sort by Fastest Lap Time -->
                        <div class="custom-form-group">
                            <label for="sort_order_lap" class="custom-label">Sort by Fastest Lap Time:</label>
                            <select name="sort_order_lap" id="sort_order_lap" class="custom-form-control" onchange="this.form.submit()">
                                <option value="ASC" <?php if ($sort_order_lap == 'ASC') echo 'selected'; ?>>Least to Most</option>
                                <option value="DESC" <?php if ($sort_order_lap == 'DESC') echo 'selected'; ?>>Most to Least</option>
                            </select>
                        </div>

                        <!-- Sort by Position -->
                        <div class="custom-form-group">
                            <label for="sort_order_position" class="custom-label">Sort by Position:</label>
                            <select name="sort_order_position" id="sort_order_position" class="custom-form-control" onchange="this.form.submit()">
                                <option value="ASC" <?php if ($sort_order_position == 'ASC') echo 'selected'; ?>>Least to Most</option>
                                <option value="DESC" <?php if ($sort_order_position == 'DESC') echo 'selected'; ?>>Most to Least</option>
                            </select>
                        </div>

                        <!-- Search Bar -->
                        <div class="custom-form-group">
                            <label for="search" class="custom-label">Search by Driver Name:</label>
                            <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search by driver" value="<?php echo htmlspecialchars($search_keyword); ?>">
                        </div>

                        <!-- Search Button -->
                        <div class="custom-form-group">
                            <button type="submit" class="custom-btn custom-btn-primary">Search</button>
                        </div>
                        
                        <!-- Clear Filters Button: Show only when search is applied -->
                        <?php if (!empty($search_keyword) || $sort_order_lap !== 'ASC' || $sort_order_position !== 'ASC'): ?>
                            <div class="custom-form-group">
                                <a href="?page=1" class="custom-btn custom-btn-clear">Clear Filters</a>
                            </div>
                        <?php endif; ?>
                        </form>
                </div>
    <div class="row mt-5">
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>Race Name</th>
                    <th>Driver Name</th>
                    <th>Fastest Lap Time</th>
                    <th>Position</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['race_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['forename']); ?></td>
                        <td><?php echo htmlspecialchars($row['fastestLapTime']); ?></td>
                        <td><?php echo htmlspecialchars($row['position']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

     <!-- Pagination Controls -->
     <div class="pagination">
    <?php
    // Total number of page links to show at once
    $num_links = 10; // You can adjust this number to display more/less pages
    
    // Previous button
    if ($current_page > 1): ?>
        <a href="?page=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search_keyword); ?>&sort_order_lap=<?php echo $sort_order_lap; ?>&sort_order_position=<?php echo $sort_order_position; ?>" class="pagination-link">« Previous</a>
    <?php else: ?>
        <span class="disabled">« Previous</span>
    <?php endif; ?>

    <?php
    // Display "First" page button if needed
    if ($current_page > $num_links) {
        echo '<a href="?page=1&search=' . urlencode($search_keyword) . '&sort_order_lap=' . $sort_order_lap . '&sort_order_position=' . $sort_order_position . '" class="pagination-link">1</a>';
        echo '<span class="disabled">...</span>'; // Ellipsis after first page if there are more pages in between
    }

    // Determine start and end pages for pagination
    $start_page = max(1, $current_page - floor($num_links / 2));
    $end_page = min($total_pages, $start_page + $num_links - 1);

    // Adjust start and end to fit within valid range
    if ($end_page - $start_page < $num_links - 1) {
        $start_page = max(1, $end_page - $num_links + 1);
    }

    // Page number links
    for ($page = $start_page; $page <= $end_page; $page++): ?>
        <a href="?page=<?php echo $page; ?>&search=<?php echo urlencode($search_keyword); ?>&sort_order_lap=<?php echo $sort_order_lap; ?>&sort_order_position=<?php echo $sort_order_position; ?>" class="pagination-link <?php echo ($page == $current_page) ? 'active' : ''; ?>"><?php echo $page; ?></a>
    <?php endfor; ?>

    <?php
    // Display "Last" page button if needed
    if ($end_page < $total_pages) {
        echo '<span class="disabled">...</span>'; // Ellipsis before the last page if there are skipped pages
        echo '<a href="?page=' . $total_pages . '&search=' . urlencode($search_keyword) . '&sort_order_lap=' . $sort_order_lap . '&sort_order_position=' . $sort_order_position . '" class="pagination-link">' . $total_pages . '</a>';
    }

    // Next button
    if ($current_page < $total_pages): ?>
        <a href="?page=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search_keyword); ?>&sort_order_lap=<?php echo $sort_order_lap; ?>&sort_order_position=<?php echo $sort_order_position; ?>" class="pagination-link">Next »</a>
    <?php else: ?>
        <span class="disabled">Next »</span>
    <?php endif; ?>
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
