<?php
include 'db/db.php';  // Include your database connection

// Set the number of results per page
$results_per_page = 13;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($current_page - 1) * $results_per_page;

// Get sort order and search keyword from GET parameters
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';
$sort_direction = ($sort_order === 'DESC') ? -1 : 1;
$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';

// MongoDB aggregation pipeline to fetch drivers, constructors, circuits, and total points
$pipeline = [
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
            'from' => 'constructors',
            'localField' => 'constructorId',
            'foreignField' => 'constructor_id',
            'as' => 'constructor_info'
        ]
    ],
    ['$unwind' => '$constructor_info'],
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
    // Search filter based on driver or constructor name if a search keyword is provided
    [
        '$match' => [
            '$or' => [
                ['driver_info.forename' => new MongoDB\BSON\Regex($search_keyword, 'i')],
                ['constructor_info.constructor_name' => new MongoDB\BSON\Regex($search_keyword, 'i')]
            ]
        ]
    ],
    [
        '$group' => [
            '_id' => [
                'forename' => '$driver_info.forename',
                'constructor_name' => '$constructor_info.constructor_name',
                'circuit_name' => '$circuit_info.circuit_name'
            ],
            'total_points' => ['$sum' => '$points']
        ]
    ],
    ['$sort' => ['total_points' => $sort_direction]],
    ['$skip' => $start_from],
    ['$limit' => $results_per_page]
];

$collection = $db->results;
$result = $collection->aggregate($pipeline);

// Count total results for pagination
$total_count_pipeline = [
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
            'from' => 'constructors',
            'localField' => 'constructorId',
            'foreignField' => 'constructor_id',
            'as' => 'constructor_info'
        ]
    ],
    ['$unwind' => '$constructor_info'],
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
        '$match' => [
            '$or' => [
                ['driver_info.forename' => new MongoDB\BSON\Regex($search_keyword, 'i')],
                ['constructor_info.constructor_name' => new MongoDB\BSON\Regex($search_keyword, 'i')]
            ]
        ]
    ],
    [
        '$group' => [
            '_id' => [
                'forename' => '$driver_info.forename',
                'constructor_name' => '$constructor_info.constructor_name',
                'circuit_name' => '$circuit_info.circuit_name'
            ]
        ]
    ],
    ['$count' => 'total']
];

$total_count_result = $collection->aggregate($total_count_pipeline);
$total_row = iterator_to_array($total_count_result);
$total_records = isset($total_row[0]['total']) ? $total_row[0]['total'] : 0;
$total_pages = ($total_records > 0) ? ceil($total_records / $results_per_page) : 1;
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Drivers, Constructors, Circuits and Points</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
<div class="wrapper">
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
                                <div class="back-button" style="margin: 20px;">
                                    <a href="index.php" class="button-8">← Back to Home</a>
                                </div>
                                <div class="head">
                                    <h2>Drivers, Constructors, Circuits and Points</h2>
                                </div>
                            </div>

                            <!-- Sort and Search Form -->
                            <div class="custom-filter-container">
                                <form method="GET" action="rtl.php" class="custom-filter-form">
                                    <div class="custom-form-group">
                                        <label for="sort_order" class="custom-label">Sort by Points:</label>
                                        <select name="sort_order" id="sort_order" class="custom-form-control" onchange="this.form.submit()">
                                            <option value="DESC" <?php if ($sort_order == 'DESC') echo 'selected'; ?>>Most to Least</option>
                                            <option value="ASC" <?php if ($sort_order == 'ASC') echo 'selected'; ?>>Least to Most</option>
                                        </select>
                                    </div>

                                    <div class="custom-form-group">
                                        <label for="search" class="custom-label">Search by driver or constructor name:</label>
                                        <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search by driver or constructor" value="<?php echo htmlspecialchars($search_keyword); ?>">
                                    </div>

                                    <div class="custom-form-group">
                                        <button type="submit" class="custom-btn custom-btn-primary">Search</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Clear Search Button: Show only when search is applied -->
                            <?php if (!empty($search_keyword)) : ?>
                                <div style="margin-bottom: 20px; margin-left: 30px;">
                                    <a href="rtl.php" class="custom-btn custom-btn-clear">Clear Filter</a>
                                </div>
                            <?php endif; ?>

                            <div class="row mt-5">
                                <table class="table table-dark table-striped">
                                    <thead>
                                        <tr>
                                            <th>Driver</th>
                                            <th>Constructor</th>
                                            <th>Circuit</th>
                                            <th>Total Points</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($result as $row) {
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($row['_id']['forename']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['_id']['constructor_name']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['_id']['circuit_name']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['total_points']) . "</td>";
                                            echo "</tr>";
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

    // Ensure $total_pages is a valid integer
    $total_pages = isset($total_pages) && $total_pages > 0 
        ? intval($total_pages) 
        : 1;

    // Ensure search_keyword and sort_order parameters have default values
    $search_keyword = isset($_GET['search']) ? $_GET['search'] : '';
    $sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';

    // Previous button
    if ($current_page > 1) {
        echo '<a href="rtl.php?page=' . ($current_page - 1) . '&search=' . urlencode($search_keyword) . '&sort_order=' . urlencode($sort_order) . '" class="button-7">Previous</a>';
    } else {
        echo '<span class="disabled">Previous</span>';
    }

    // Page numbers
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            echo '<span class="current-page">' . $i . '</span>';
        } else {
            echo '<a href="rtl.php?page=' . $i . '&search=' . urlencode($search_keyword) . '&sort_order=' . urlencode($sort_order) . '">' . $i . '</a>';
        }
    }

    // Next button
    if ($current_page < $total_pages) {
        echo '<a href="rtl.php?page=' . ($current_page + 1) . '&search=' . urlencode($search_keyword) . '&sort_order=' . urlencode($sort_order) . '" class="button-7">Next</a>';
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
