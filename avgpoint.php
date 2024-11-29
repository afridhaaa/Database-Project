<?php

include 'db/db.php';  // Include your database connection

// Set the number of results per page
$results_per_page = 10;

// Default sort order and search keyword
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC'; // Default to DESC
$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';

// Get the current page number from URL, if not set, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the skip limit number for pagination
$skip = ($current_page - 1) * $results_per_page;

// Build MongoDB aggregation pipeline for average points
$pipeline = [
    // Match to filter by driver or constructor names
    [
        '$lookup' => [
            'from' => 'drivers',
            'localField' => 'driverId',
            'foreignField' => 'driverId',
            'as' => 'driver'
        ]
    ],
    ['$unwind' => '$driver'],
    [
        '$lookup' => [
            'from' => 'constructors',
            'localField' => 'constructorId',
            'foreignField' => 'constructor_id',
            'as' => 'constructor'
        ]
    ],
    ['$unwind' => '$constructor'],
    [
        '$match' => [
            '$or' => [
                ['driver.forename' => new MongoDB\BSON\Regex($search_keyword, 'i')],
                ['constructor.constructor_name' => new MongoDB\BSON\Regex($search_keyword, 'i')]
            ]
        ]
    ],
    [
        '$group' => [
            '_id' => [
                'driver' => '$driver.forename',
                'constructor' => '$constructor.constructor_name'
            ],
            'avg_points' => ['$avg' => '$points']
        ]
    ],
    // Sort by average points
    ['$sort' => ['avg_points' => ($sort_order == 'DESC' ? -1 : 1)]],
    // Skip and limit for pagination
    ['$skip' => $skip],
    ['$limit' => $results_per_page]
];

// Execute aggregation query
$query = $db->results->aggregate($pipeline);
$results = iterator_to_array($query);

// Count total distinct driver-constructor pairs for pagination
$total_pipeline = [
    [
        '$lookup' => [
            'from' => 'drivers',
            'localField' => 'driverId',
            'foreignField' => 'driverId',
            'as' => 'driver'
        ]
    ],
    ['$unwind' => '$driver'],
    [
        '$lookup' => [
            'from' => 'constructors',
            'localField' => 'constructorId',
            'foreignField' => 'constructor_id',
            'as' => 'constructor'
        ]
    ],
    ['$unwind' => '$constructor'],
    [
        '$match' => [
            '$or' => [
                ['driver.forename' => new MongoDB\BSON\Regex($search_keyword, 'i')],
                ['constructor.constructor_name' => new MongoDB\BSON\Regex($search_keyword, 'i')]
            ]
        ]
    ],
    [
        '$group' => [
            '_id' => [
                'driver' => '$driver.forename',
                'constructor' => '$constructor.constructor_name'
            ]
        ]
    ],
    ['$count' => 'total']
];

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
    <title>Average Points by Driver and Constructor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVcey ```php
QqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />  
    <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
   <div class="topbar">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2">
                <!-- <div class="heading">
                  <a href="index.php">  <h4>Formula1</h4></a>
                </div> -->
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
                                <h2>The Average Points Scored by Each Driver for a Particular Constructor</h2>
                            </div>
                        </div>
                       <!-- Sort and Search Form -->
<div class="custom-filter-container my-5">
    <form method="GET" action="avgpoint.php" class="custom-filter-form">
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
        <label for="search" class="custom-label">Search by Constructor or Driver Name:</label>
            <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search by constructor or driver" value="<?php echo htmlspecialchars($search_keyword); ?>">
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
        <a href="avgpoint.php" class="custom-btn custom-btn-clear">Clear Search</a>
    </div>
<?php endif; ?>


<div class="row mt-5">
    <table class="table table-dark table-striped">
        <thead>
          <tr>
          <th scope="col">Driver Name</th>
          <th scope="col">Constructor Name</th>
          <th scope="col">Average Points</th>
          </tr>
        </thead>
        <tbody>
        <?php
                                if (count($results) > 0) {
                                    // Output each row of data
                                    foreach ($results as $row) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['_id']['driver']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['_id']['constructor']) . "</td>";
                                        echo "<td>" . round($row['avg_points'], 2) . "</td>"; // Rounded to 2 decimal places
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
    // Ensure $current_page is an integer
    $current_page = isset($_GET['page']) && is_numeric($_GET['page']) 
        ? intval($_GET['page']) 
        : 1;

    // Ensure $total_pages is a valid integer
    $total_pages = isset($total_pages) && $total_pages > 0 
        ? intval($total_pages) 
        : 1;

    // Ensure search and sort parameters have default values
    $search_keyword = isset($_GET['search']) ? $_GET['search'] : '';
    $sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'asc';

    // Display "Previous" button
    if ($current_page > 1) {
        echo '<a href="avgpoint.php?page=' . ($current_page - 1) . '&sort_order=' . $sort_order . '&search=' . urlencode($search_keyword) . '" class="button-7">Previous</a>';
    } else {
        echo '<span class="disabled">Previous</span>';
    }

    // Display page numbers
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            echo '<a href="#" class="current-page">' . $i . '</a>'; // Active page
        } else {
            echo '<a href="avgpoint.php?page=' . $i . '&sort_order=' . $sort_order . '&search=' . urlencode($search_keyword) . '">' . $i . '</a>';
        }
    }

    // Display "Next" button
    if ($current_page < $total_pages) {
        echo '<a href="avgpoint.php?page=' . ($current_page + 1) . '&sort_order=' . $sort_order . '&search=' . urlencode($search_keyword) . '" class="button-7">Next</a>';
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

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>