<?php
include 'db/db.php';  // MongoDB connection

// Set the number of results per page
$results_per_page = 11;

// Get the current page number from URL, if not set, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the skip limit for pagination
$skip = ($current_page - 1) * $results_per_page;

// Initialize sort order and search keyword
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';
$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';

// Build MongoDB Aggregation Pipeline
$pipeline = [];

// Join drivers collection
$pipeline[] = [
    '$lookup' => [
        'from' => 'drivers',
        'localField' => 'driverId',
        'foreignField' => 'driverId',
        'as' => 'driver_info'
    ]
];
$pipeline[] = ['$unwind' => '$driver_info'];

// Join races collection
$pipeline[] = [
    '$lookup' => [
        'from' => 'races',
        'localField' => 'raceId',
        'foreignField' => 'raceId',
        'as' => 'race_info'
    ]
];
$pipeline[] = ['$unwind' => '$race_info'];

// If there is a search keyword, add a match stage to the pipeline after lookups
if (!empty($search_keyword)) {
    $pipeline[] = [
        '$match' => [
            '$or' => [
                ['driver_info.forename' => new MongoDB\BSON\Regex($search_keyword, 'i')],  // Search by driver name
                ['race_info.year' => (int)$search_keyword]  // Search by year (convert to integer)
            ]
        ]
    ];
}

// Group by driver and year, and calculate total races and average points
$pipeline[] = [
    '$group' => [
        '_id' => [
            'driver' => '$driver_info.forename',
            'year' => '$race_info.year'
        ],
        'total_races' => ['$sum' => 1],
        'avg_points' => ['$avg' => '$points']
    ]
];

// Apply sort order
$pipeline[] = ['$sort' => ['avg_points' => ($sort_order == 'DESC' ? -1 : 1)]];

// Skip and limit for pagination
$pipeline[] = ['$skip' => $skip];
$pipeline[] = ['$limit' => $results_per_page];

// Execute aggregation query
$query = $db->results->aggregate($pipeline);
$results = iterator_to_array($query);

// Total count pipeline for pagination
$total_pipeline = [
    // Join drivers and races collections as in the main pipeline
    [
        '$lookup' => [
            'from' => 'drivers',
            'localField' => 'driverId',
            'foreignField' => 'driverId',
            'as' => 'driver_info'
        ]
    ],
    ['$unwind' => '$driver_info'],
    [
        '$lookup' => [
            'from' => 'races',
            'localField' => 'raceId',
            'foreignField' => 'raceId',
            'as' => 'race_info'
        ]
    ],
    ['$unwind' => '$race_info']
];

// Add search condition if a keyword is provided
if (!empty($search_keyword)) {
    $total_pipeline[] = [
        '$match' => [
            '$or' => [
                ['driver_info.forename' => new MongoDB\BSON\Regex($search_keyword, 'i')],  // Search by driver name
                ['race_info.year' => (int)$search_keyword]  // Search by year (convert to integer)
            ]
        ]
    ];
}

// Group by driver and year to get unique count
$total_pipeline[] = [
    '$group' => [
        '_id' => [
            'driver' => '$driver_info.driverId',
            'year' => '$race_info.year'
        ]
    ]
];
$total_pipeline[] = ['$count' => 'total'];

// Execute total count pipeline
$total_query = $db->results->aggregate($total_pipeline);
$total_result = iterator_to_array($total_query);
$total_pages = ceil(($total_result[0]['total'] ?? 0) / $results_per_page);

?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Number of Races and Average Points in 2020</title>
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
     <h2>Number of Races and Average Points By Year</h2>
                            </div>
                        </div>
    <!-- Sort and Search Form -->
<div class="custom-filter-container">
    <form method="GET" action="nofraces.php" class="custom-filter-form">
        <!-- Sort Dropdown -->
        <div class="custom-form-group">
            <label for="sort_order" class="custom-label">Sort by Points:</label>
            <select name="sort_order" id="sort_order" class="custom-form-control" onchange="this.form.submit()">
                <option value="DESC" <?php if ($sort_order == 'DESC') echo 'selected'; ?>>Most to Least</option>
                <option value="ASC" <?php if ($sort_order == 'ASC') echo 'selected'; ?>>Least to Most</option>
            </select>
        </div>

        <!-- Search Bar -->
        <div class="custom-form-group">
            <label for="search" class="custom-label">Search by Year or Driver Name:</label>
            <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search by year or driver" value="<?php echo htmlspecialchars($search_keyword); ?>">
        </div>

        <!-- Search Button -->
        <div class="custom-form-group">
            <button type="submit" class="custom-btn custom-btn-primary">Search</button>
        </div>
    </form>
</div>


    <!-- Clear Search Button: Show only when search is applied -->
    <?php if (!empty($search_keyword)) : ?>
        <div style="margin-bottom: 20px; margin-left: 30px;">
            <a href="nofraces.php" class="custom-btn custom-btn-clear">Clear Filter</a>
        </div>
    <?php endif; ?>
    

                        <div class="row mt-5">
                            <table class="table table-dark table-striped">
                                <thead>
                                    <tr>
                                        <th>Driver</th>
                                        <th>Year</th>
                                        <th>Total Races</th>
                                        <th>Average Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        if (count($results) > 0) {
                                            foreach ($results as $row) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($row['_id']['driver']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['_id']['year']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['total_races']) . "</td>";
                                                echo "<td>" . htmlspecialchars(number_format($row['avg_points'], 2)) . "</td>"; // Format to 2 decimal places
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='4'>No data available</td></tr>";
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>

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

    // Define the maximum number of links to display
    $max_links = 7;

    // Calculate the start and end pages
    $start_page = max(1, $current_page - floor($max_links / 2));
    $end_page = min($total_pages, $start_page + $max_links - 1);

    // Adjust start_page if the range is less than $max_links
    if ($end_page - $start_page + 1 < $max_links) {
        $start_page = max(1, $end_page - $max_links + 1);
    }

    // Display "Previous" button
    if ($current_page > 1) {
        echo '<a href="nofraces.php?page=' . ($current_page - 1) . '&sort_order=' . $sort_order . '&search=' . urlencode($search_keyword) . '" class="button-7">Previous</a>';
    } else {
        echo '<span class="disabled">Previous</span>';
    }

    // Display limited page numbers
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {
            echo '<span class="current-page">' . $i . '</span>'; // Current page
        } else {
            echo '<a href="nofraces.php?page=' . $i . '&sort_order=' . $sort_order . '&search=' . urlencode($search_keyword) . '">' . $i . '</a>';
        }
    }

    // Display "Next" button
    if ($current_page < $total_pages) {
        echo '<a href="nofraces.php?page=' . ($current_page + 1) . '&sort_order=' . $sort_order . '&search=' . urlencode($search_keyword) . '" class="button-7">Next</a>';
    } else {
        echo '<span class="disabled">Next</span>';
    }
    ?>
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
