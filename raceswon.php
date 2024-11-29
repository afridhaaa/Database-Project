<?php
include 'db/db.php';  // Include your database connection

// Set the number of results per page
$results_per_page = 10;

// Get search input values
$driver_name = isset($_GET['driver_name']) ? $_GET['driver_name'] : '';
$circuit_name = isset($_GET['circuit_name']) ? $_GET['circuit_name'] : '';
$race_name = isset($_GET['race_name']) ? $_GET['race_name'] : '';

// Get the sort order from the URL or set a default
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';

// Get the current page number from URL, if not set, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the skip limit number for pagination
$skip = ($current_page - 1) * $results_per_page;

// Build MongoDB aggregation pipeline
$pipeline = [
    // Match for position = 1
    ['$match' => ['position' => 1]],
    
    // Join races collection
    [
        '$lookup' => [
            'from' => 'races',
            'localField' => 'raceId',
            'foreignField' => 'raceId',
            'as' => 'race_info'
        ]
    ],
    ['$unwind' => '$race_info'],

    // Join circuits collection
    [
        '$lookup' => [
            'from' => 'circuits',
            'localField' => 'race_info.circuit_id',
            'foreignField' => 'circuit_id',
            'as' => 'circuit_info'
        ]
    ],
    ['$unwind' => '$circuit_info'],

    // Join drivers collection
    [
        '$lookup' => [
            'from' => 'drivers',
            'localField' => 'driverId',
            'foreignField' => 'driverId',
            'as' => 'driver_info'
        ]
    ],
    ['$unwind' => '$driver_info']
];

// Add search filters if keywords are present
$search_filters = [];
if (!empty($driver_name)) {
    $search_filters[] = ['driver_info.forename' => new MongoDB\BSON\Regex($driver_name, 'i')];
}
if (!empty($circuit_name)) {
    $search_filters[] = ['circuit_info.circuit_name' => new MongoDB\BSON\Regex($circuit_name, 'i')];
}
if (!empty($race_name)) {
    $search_filters[] = ['race_info.name' => new MongoDB\BSON\Regex($race_name, 'i')];
}

if (!empty($search_filters)) {
    $pipeline[] = ['$match' => ['$or' => $search_filters]];
}

// Sort based on driver name
$pipeline[] = ['$sort' => ['driver_info.forename' => ($sort_order == 'ASC' ? 1 : -1)]];

// Skip and limit for pagination
$pipeline[] = ['$skip' => $skip];
$pipeline[] = ['$limit' => $results_per_page];

// Execute aggregation query
$query = $db->results->aggregate($pipeline);
$results = iterator_to_array($query);

// Count total results for pagination
$total_pipeline = [
    ['$match' => ['position' => 1]],
    [
        '$lookup' => [
            'from' => 'races',
            'localField' => 'raceId',
            'foreignField' => 'raceId',
            'as' => 'race_info'
        ]
    ],
    ['$unwind' => '$race_info'],
    [
        '$lookup' => [
            'from' => 'circuits',
            'localField' => 'race_info.circuit_id',
            'foreignField' => 'circuit_id',
            'as' => 'circuit_info'
        ]
    ],
    ['$unwind' => '$circuit_info'],
    [
        '$lookup' => [
            'from' => 'drivers',
            'localField' => 'driverId',
            'foreignField' => 'driverId',
            'as' => 'driver_info'
        ]
    ],
    ['$unwind' => '$driver_info']
];

// Add search filters for total count
if (!empty($search_filters)) {
    $total_pipeline[] = ['$match' => ['$or' => $search_filters]];
}

// Count total documents
$total_pipeline[] = ['$count' => 'total'];

// Execute total count pipeline
$total_query = $db->results->aggregate($total_pipeline);
$total_result = iterator_to_array($total_query);
$total_pages = ceil(($total_result[0]['total'] ?? 0 ) / $results_per_page);

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Races Won by Drivers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
<div class="topbar">
    <div class="container-fluid">
        <div class="row">
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
                                <h2>List of races won by each driver along with the circuit name</h2>
                            </div>
                        </div>
                        <!-- Sort and Search Form -->
                        <div class="custom-filter-container">
                            <form method="GET" action="raceswon.php" class="custom-filter-form">
                                <!-- Sort Dropdown -->
                                <div class="custom-form-group">
                                    <label for="sort_order" class="custom-label">Sort by Driver Name:</label>
                                    <select name="sort_order" id="sort_order" class="custom-form-control" onchange="this.form.submit()">
                                        <option value="ASC" <?php if ($sort_order == 'ASC') echo 'selected'; ?>>A to Z</option>
                                        <option value="DESC" <?php if ($sort_order == 'DESC') echo 'selected'; ?>>Z to A</option>
                                    </select>
                                </div>

                                <!-- Search Bars -->
                                <div class="custom-form-group">
                                    <label for="driver_name" class="custom-label">Search by Driver Name:</label>
                                    <input type="text" name="driver_name" class="custom-form-control custom-search-bar2" placeholder="Driver Name" value="<?php echo htmlspecialchars($driver_name); ?>">
                                </div>
                                <div class="custom-form-group">
                                    <label for="circuit_name" class="custom-label">Search by Circuit Name:</label>
                                    <input type="text" name="circuit_name" class="custom-form-control custom-search-bar2" placeholder="Circuit Name" value="<?php echo htmlspecialchars($circuit_name); ?>">
                                </div>
                                <div class="custom-form-group">
                                    <label for="race_name" class="custom-label">Search by Race Name:</label>
                                    <input type="text" name="race_name" class="custom-form-control custom-search-bar2" placeholder="Race Name" value="<?php echo htmlspecialchars($race_name); ?>">
                                </div>

                                <!-- Search Button -->
                                <div class="custom-form-group">
                                    <button type="submit" class="custom-btn custom-btn-primary">Search</button>
                                </div>
                            </form>
                        </div>

                        <!-- Clear Search Button: Show only when search is applied -->
                        <?php if (!empty($driver_name) || !empty($circuit_name) || !empty($race_name)) : ?>
                            <div style="margin-bottom: 20px; margin-left: 30px;">
                                <a href="raceswon.php" class="custom-btn custom-btn-clear">Clear Search</a>
                            </div>
                        <?php endif; ?>

                        <div class="row mt-5">
                            <table class="table table-dark table-striped">
 <thead>
                                    <tr>
                                        <th>Driver Name</th>
                                        <th>Race Name</th>
                                        <th>Circuit Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                    if (count($results) > 0) {
                                        // Output each row of data
                                        foreach ($results as $row) {
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($row['driver_info']['forename']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['race_info']['name']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['circuit_info']['circuit_name']) . "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='3'>No data available</td></tr>";
                                    }
                                ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Controls -->
                        <div class="pagination">
                            <?php
                            // Ensure $current_page and $total_pages are integers
                            $current_page = isset($_GET['page']) && is_numeric($_GET['page']) 
                                ? intval($_GET['page']) 
                                : 1;

                            $total_pages = isset($total_pages) && is_numeric($total_pages) && $total_pages > 0 
                                ? intval($total_pages) 
                                : 1;

                            // Previous button
                            if ($current_page > 1) {
                                echo '<a href="raceswon.php?page=' . ($current_page - 1) . '&driver_name=' . urlencode($driver_name) . '&circuit_name=' . urlencode($circuit_name) . '&race_name=' . urlencode($race_name) . '&sort_order=' . $sort_order . '" class="button-7">Previous</a>';
                            } else {
                                echo '<span class="disabled">Previous</span>';
                            }

                            // Display first three pages
                            for ($i = 1; $i <= min(3, $total_pages); $i++) {
                                if ($i == $current_page) {
                                    echo '<a href="#" class="active">' . $i . '</a>'; // Active page
                                } else {
                                    echo '<a href="raceswon.php?page=' . $i . '&driver_name=' . urlencode($driver_name) . '&circuit_name=' . urlencode($circuit_name) . '&race_name=' . urlencode($race_name) . '&sort_order=' . $sort_order . '" class="button-7">' . $i . '</a>';
                                }
                            }

                            // Ellipsis after first three pages if needed
                            if ($total_pages > 6 && $current_page > 4) {
                                echo '<span class="ellipsis">...</span>';
                            }

                            // Display middle three pages
                            $start = max(4, $current_page - 1);
                            $end = min($total_pages - 3, $current_page + 1);

                            for ($i = $start; $i <= $end; $i++) {
                                if ($i > 3 && $i < $total_pages - 2) { // Only show if it's not the first three or last three
                                    if ($i == $current_page) {
                                        echo '<a href="#" class="active">' . $i . '</a>'; // Active page
                                    } else {
                                        echo '<a href="raceswon.php?page=' . $i . '&driver_name=' . urlencode($driver_name) . '&circuit_name=' . urlencode($circuit_name) . '&race_name=' . urlencode($race_name) . '&sort_order=' . $sort_order . '" class="button-7">' . $i . '</a>';
                                    }
                                }
                            }

                            // Ellipsis before last three pages if needed
                            if ($total_pages > 6 && $current_page < $total_pages - 3) {
                                echo '<span class="ellipsis">...</span>';
                            }

                            // Display last three pages
                            for ($i = max(1, $total_pages - 2); $i <= $total_pages; $i++) {
                                if ($i == $current_page) {
                                    echo '<a href="#" class="active">' . $i . '</a>'; // Active page
                                } else {
                                    echo '<a href="raceswon.php?page=' . $i . '&driver_name=' . urlencode($driver_name) . '&circuit_name=' . urlencode($circuit_name) . '&race_name=' . urlencode($race_name) . '&sort_order=' . $sort_order . '" class="button-7">' . $i . '</a>';
                                }
                            }

                            // Next button
                            if ($current_page < $total_pages) {
                                echo '<a href="raceswon.php?page=' . ($current_page + 1) . '&driver_name=' . urlencode($driver_name) . '&circuit_name=' . urlencode($circuit_name) . '&race_name=' . urlencode($race_name) . '&sort_order=' . $sort_order . '" class="button-7">Next</a>';
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