<?php
include 'db/db.php';  // Include your MongoDB connection

// Set the number of results per page
$results_per_page = 10;

// Get the current page number from URL, if not set, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the skip limit number for pagination
$skip = ($current_page - 1) * $results_per_page;

// Get search input
$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';

// Build MongoDB aggregation pipeline
$pipeline = [
    // Join results with drivers, constructors, races, and circuits
    [
        '$lookup' => [
            'from' => 'drivers',
            'localField' => 'driverId',
            'foreignField' => 'driverId',
            'as' => 'driver_info'
        ]
    ],
    ['$unwind' => '$driver_info'], // Unwind driver info

    [
        '$lookup' => [
            'from' => 'constructors',
            'localField' => 'constructorId',
            'foreignField' => 'constructor_id',
            'as' => 'constructor_info'
        ]
    ],
    ['$unwind' => '$constructor_info'], // Unwind constructor info

    [
        '$lookup' => [
            'from' => 'races',
            'localField' => 'raceId',
            'foreignField' => 'raceId',
            'as' => 'race_info'
        ]
    ],
    ['$unwind' => '$race_info'], // Unwind race info

    [
        '$lookup' => [
            'from' => 'circuits',
            'localField' => 'race_info.circuit_id',
            'foreignField' => 'circuit_id',
            'as' => 'circuit_info'
        ]
    ],
    ['$unwind' => '$circuit_info'], // Unwind circuit info

    // Filter for position 1 and constructors with at least one title
    [
        '$match' => [
            'position' => 1,
            'constructor_info.no_of_titles' => ['$gt' => 0]
        ]
    ],

    // Apply the search filter if a keyword is provided
    [
        '$match' => [
            '$or' => [
                ['driver_info.forename' => new MongoDB\BSON\Regex($search_keyword, 'i')],
                ['constructor_info.constructor_name' => new MongoDB\BSON\Regex($search_keyword, 'i')],
                ['circuit_info.circuit_name' => new MongoDB\BSON\Regex($search_keyword, 'i')],
                ['race_info.name' => new MongoDB\BSON\Regex($search_keyword, 'i')]
            ]
        ]
    ],

    // Sort and limit results for pagination
    ['$sort' => ['constructor_info.constructor_name' => 1, 'race_info.date' => 1]],
    ['$skip' => $skip],
    ['$limit' => $results_per_page]
];

// Execute aggregation query
$query = $db->results->aggregate($pipeline);
$results = iterator_to_array($query);

// Total count pipeline for pagination calculation
$total_pipeline = [
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
        '$match' => [
            'position' => 1,
            'constructor_info.no_of_titles' => ['$gt' => 0]
        ]
    ]
];

// Add search filter for count if a keyword is present
if (!empty($search_keyword)) {
    $total_pipeline[] = [
        '$match' => [
            '$or' => [
                ['driver_info.forename' => new MongoDB\BSON\Regex($search_keyword, 'i')],
                ['constructor_info.constructor_name' => new MongoDB\BSON\Regex($search_keyword, 'i')],
                ['circuit_info.circuit_name' => new MongoDB\BSON\Regex($search_keyword, 'i')],
                ['race_info.name' => new MongoDB\BSON\Regex($search_keyword, 'i')]
            ]
        ]
    ];
}

// Execute the total count query
$total_query = $db->results->aggregate($total_pipeline);
$total_rows = iterator_to_array($total_query);
$total_pages = ceil(count($total_rows) / $results_per_page);
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />  
    <link rel="stylesheet" href="./assets/css/style.css">
    <style>
        .pagination a, .pagination span {
            margin: 0 5px;
            padding: 10px;
            text-decoration: none;
            border: 1px solid #ddd;
            color: #333;
        }

       
    </style>
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
     <h2>Drivers who won a race where the constructor won the constructor's championship</h2>
                            </div>
                        </div>
                        
<!-- Search and Filter Form -->
<div class="custom-filter-container">
        <form method="GET" action="alldrivers.php" class="custom-filter-form">
            <!-- Search Bar -->
            <div class="custom-form-group">
                <label for="search" class="custom-label">Search by Driver, Constructor, Circuit, or Race Name:</label>
                <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search..." value="<?php echo htmlspecialchars($search_keyword); ?>">
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
            <a href="alldrivers.php" class="custom-btn custom-btn-clear">Clear Search</a>
        </div>
    <?php endif; ?>

<div class="container">

    <table class="table table-dark table-striped">
        <thead>
        <tr>
            <th>Driver Name</th>
            <th>Constructor Name</th>
            <th>Race Name</th>
            <th>Circuit Name</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (!empty($results)) {
            foreach ($results as $row) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['driver_info']['forename']) . "</td>";
                echo "<td>" . htmlspecialchars($row['constructor_info']['constructor_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['race_info']['name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['circuit_info']['circuit_name']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No data available</td></tr>";
        }
        ?>
        </tbody>
    </table>

    <!-- Pagination Controls -->
    <div class="pagination">
    <?php
    // Ensure $current_page is always an integer
    $current_page = isset($_GET['page']) && is_numeric($_GET['page']) 
        ? intval($_GET['page']) 
        : 1;

    // Ensure $total_pages is a valid integer and at least 1
    $total_pages = isset($total_pages) && $total_pages > 0 
        ? intval($total_pages) 
        : 1;

    // Set how many page links to display at once
    $links_to_show = 5;

    // Calculate the start and end page numbers
    $start_page = max(1, $current_page - floor($links_to_show / 2));
    $end_page = min($total_pages, $start_page + $links_to_show - 1);

    // Adjust start page if we are at the end of the pagination range
    if ($end_page - $start_page + 1 < $links_to_show) {
        $start_page = max(1, $end_page - $links_to_show + 1);
    }

    // Display "Previous" button
    if ($current_page > 1) {
        echo '<a href="alldrivers.php?page=' . ($current_page - 1) . '" class="button-7">Previous</a>';
    } else {
        echo '<span class="disabled">Previous</span>';
    }

    // Display limited page numbers
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {
            echo '<a href="#" class="current-page">' . $i . '</a>'; // Active page
        } else {
            echo '<a href="alldrivers.php?page=' . $i . '">' . $i . '</a>';
        }
    }

    // Display "Next" button
    if ($current_page < $total_pages) {
        echo '<a href="alldrivers.php?page=' . ($current_page + 1) . '" class="button-7">Next</a>';
    } else {
        echo '<span class="disabled">Next</span>';
    }
    ?>
</div>

</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
