<?php
include 'db/db.php';  // Include your database connection

// Set the number of results per page
$results_per_page = 13;

// Get search keyword and sort order from GET parameters
$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';
$sort_order = isset($_GET['sort_order']) ? strtoupper($_GET['sort_order']) : 'ASC';

// Determine the current page number
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the skip limit for pagination
$skip = ($current_page - 1) * $results_per_page;

// Build MongoDB aggregation pipeline
$pipeline = [
    // Match for fastest lap time not null
    ['$match' => ['fastestLapTime' => ['$ne' => null]]],

    // Join with drivers collection
    [
        '$lookup' => [
            'from' => 'drivers',
            'localField' => 'driverId',
            'foreignField' => 'driverId',
            'as' => 'driver_info'
        ]
    ],
    ['$unwind' => '$driver_info'],

    // Join with races collection
    [
        '$lookup' => [
            'from' => 'races',
            'localField' => 'raceId',
            'foreignField' => 'raceId',
            'as' => 'race_info'
        ]
    ],
    ['$unwind' => '$race_info'],

    // Join with circuits collection
    [
        '$lookup' => [
            'from' => 'circuits',
            'localField' => 'race_info.circuit_id',
            'foreignField' => 'circuit_id',
            'as' => 'circuit_info'
        ]
    ],
    ['$unwind' => '$circuit_info']
];

// Add search filter if a keyword is present
if (!empty($search_keyword)) {
    $pipeline[] = [
        '$match' => [
            '$or' => [
                ['driver_info.forename' => new MongoDB\BSON\Regex($search_keyword, 'i')],
                ['race_info.name' => new MongoDB\BSON\Regex($search_keyword, 'i')]
            ]
        ]
    ];
}

// Sort based on fastest lap time
$sort_order_value = ($sort_order === 'DESC') ? -1 : 1;
$pipeline[] = ['$sort' => ['fastestLapTime' => $sort_order_value]];

// Skip and limit for pagination
$pipeline[] = ['$skip' => $skip];
$pipeline[] = ['$limit' => $results_per_page];

// Execute aggregation query
$query = $db->results->aggregate($pipeline);
$results = iterator_to_array($query);

// Total count for pagination
$total_pipeline = [
    ['$match' => ['fastestLapTime' => ['$ne' => null]]],
    ['$lookup' => [
        'from' => 'drivers',
        'localField' => 'driverId',
        'foreignField' => 'driverId',
        'as' => 'driver_info'
    ]],
    ['$unwind' => '$driver_info'],
    ['$lookup' => [
        'from' => 'races',
        'localField' => 'raceId',
        'foreignField' => 'raceId',
        'as' => 'race_info'
    ]],
    ['$unwind' => '$race_info'],
    ['$lookup' => [
        'from' => 'circuits',
        'localField' => 'race_info.circuit_id',
        'foreignField' => 'circuit_id',
        'as' => 'circuit_info'
    ]],
    ['$unwind' => '$circuit_info']
];

// Add search filter for total count if a keyword is present
if (!empty($search_keyword)) {
    $total_pipeline[] = [
        '$match' => [
            '$or' => [
                ['driver_info.forename' => new MongoDB\BSON\Regex($search_keyword, 'i')],
                ['race_info.name' => new MongoDB\BSON\Regex($search_keyword, 'i')]
            ]
        ]
    ];
}

// Count total documents for pagination
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
    <meta name="viewport" content ="width=device-width, initial-scale=1">
    <title>Fastest Lap Times</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />  
    <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
    <div class="topbar">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-2">
                    <!-- <div class="heading">
                        <a href="index.php"><h4>Formula1</h4></a>
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
                                <div class="back-button" style="margin: 20px;">
                                    <a href="index.php" class="button-8">← Back to Home</a>
                                </div>
                                <div class="head">
                                    <h2>Fastest Lap Times</h2>
                                </div>
                            </div>
                            
                            <!-- Sort and Search Form -->
                            <div class="custom-filter-container">
                                <form method="GET" action="laptime.php" class="custom-filter-form">
                                    <div class="custom-form-group">
                                        <label for="sort_order" class="custom-label">Sort by Fastest Lap Time:</label>
                                        <select name="sort_order" id="sort_order" class="custom-form-control" onchange="this.form.submit()">
                                            <option value="ASC" <?php if ($sort_order == 'ASC') echo 'selected'; ?>>Fastest to Slowest</option>
                                            <option value="DESC" <?php if ($sort_order == 'DESC') echo 'selected'; ?>>Slowest to Fastest</option>
                                        </select>
                                    </div>

                                    <div class="custom-form-group">
                                        <label for="search" class="custom-label">Search by Driver or Race Name:</label>
                                        <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search by driver or race" value="<?php echo htmlspecialchars($search_keyword); ?>">
                                    </div>

                                    <div class="custom-form-group">
                                        <button type="submit" class="custom-btn custom-btn-primary">Search</button>
                                    </div>
                                </form>
                            </div>

                            <?php if (!empty($search_keyword)) : ?>
                                <div style="margin-bottom: 20px; margin-left: 30px;">
                                    <a href="laptime.php" class="custom-btn custom-btn-clear">Clear Search</a>
                                </div>
                            <?php endif; ?>

                            <div class="row mt-5">
                                <table class="table table-dark table-striped">
                                    <thead>
                                        <tr>
                                            <th>Driver Name</th>
                                            <th>Race Name</th>
                                            <th>Circuit Name</th>
                                            <th>Fastest Lap Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (count($results) > 0) {
                                            foreach ($results as $row) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($row['driver_info']['forename']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['race_info']['name']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['circuit_info']['circuit_name']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['fastestLapTime']) . "</td>";
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

                                // Display "Previous" button
                                if ($current_page > 1) {
                                    echo '<a href="laptime.php?page=' . ($current_page - 1) . '&sort_order=' . $sort_order . '&search=' . urlencode($search_keyword) . '" class="button-7">Previous</a>';
                                } else {
                                    echo '<span class="disabled">Previous</span>';
                                }

                                // Display page numbers
                                for ($i = 1; $i <= $total_pages; $i++) {
                                    if ($i == $current_page) {
                                        echo '<span class="current-page">' . $i . '</span>'; // Current page
                                    } else {
                                        echo '<a href="laptime.php?page=' . $i . '&sort_order=' . $sort_order . '&search=' . urlencode($search_keyword) . '">' . $i . '</a>';
                                    }
                                }

                                // Display "Next" button
                                if ($current_page < $total_pages) {
                                    echo '<a href="laptime.php?page=' . ($current_page + 1) . '&sort_order=' . $sort_order . '&search=' . urlencode($search_keyword) . '" class="button-7">Next</a>';
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