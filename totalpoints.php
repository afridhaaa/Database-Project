<?php
include 'db/db.php';  // Include your database connection

// Set how many results to display per page
$results_per_page = 15;

// Ensure $current_page is always an integer
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) 
    ? intval($_GET['page']) 
    : 1;

// Calculate the skip limit for pagination
$skip = ($current_page - 1) * $results_per_page;

// Build the total count pipeline
$total_pipeline = [
    [
        '$group' => [
            '_id' => [
                'driverId' => '$driverId',
                'constructorId' => '$constructorId',
                'raceId' => '$raceId'
            ],
            'total_points' => ['$sum' => '$points']
        ]
    ],
    [
        '$count' => 'total'
    ]
];

// Execute the total count query
$total_query = $db->results->aggregate($total_pipeline);
$total_result = iterator_to_array($total_query);
$total_results = $total_result[0]['total'] ?? 0;

// Calculate the total number of pages
$total_pages = ceil($total_results / $results_per_page);

// Build the main aggregation pipeline for results
$pipeline = [
    [
        '$group' => [
            '_id' => [
                'driverId' => '$driverId',
                'constructorId' => '$constructorId',
                'raceId' => '$raceId'
            ],
            'total_points' => ['$sum' => '$points']
        ]
    ],
    [
        '$lookup' => [
            'from' => 'drivers', // Join with drivers collection
            'localField' => '_id.driverId',
            'foreignField' => 'driverId',
            'as' => 'driver_info'
        ]
    ],
    [
        '$unwind' => '$driver_info'
    ],
    [
        '$lookup' => [
            'from' => 'constructors', // Join with constructors collection
            'localField' => '_id.constructorId',
            'foreignField' => 'constructor_id',
            'as' => 'constructor_info'
        ]
    ],
    [
        '$unwind' => '$constructor_info'
    ],
    [
        '$lookup' => [
            'from' => 'races', // Join with races collection
            'localField' => '_id.raceId',
            'foreignField' => 'raceId',
            'as' => 'race_info'
        ]
    ],
    [
        '$unwind' => '$race_info'
    ],
    [
        '$lookup' => [
            'from' => 'circuits', // Join with circuits collection
            'localField' => 'race_info.circuit_id',
            'foreignField' => 'circuit_id',
            'as' => 'circuit_info'
        ]
    ],
    [
        '$unwind' => '$circuit_info'
    ],
    [
        '$sort' => ['total_points' => -1] // Sort by total points descending
    ],
    [
        '$skip' => $skip // Skip for pagination
    ],
    [
        '$limit' => $results_per_page // Limit results per page
    ]
];

// Execute the main aggregation query
$query = $db->results->aggregate($pipeline);
$results = iterator_to_array($query);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Formula 1 Points</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />  
    <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
    <div class="topbar">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2">
                <div class="heading">
                    <a href="index.php"> 
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
                                <h2>Total Number of Points Scored by Each Driver</h2>
                            </div>
                        </div>

                        <div class="row mt-5">
                            <table class="table table-dark table-striped">
                                <thead>
                                  <tr>
                                      <th>Driver Name</th>
                                      <th>Constructor Name</th>
                                      <th>Circuit Name</th>
                                      <th>Total Points</th>
                                  </tr>
                                </thead>
                                <tbody>
                                <?php
                                    if (count($results) > 0) {
                                        foreach ($results as $row) {
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($row['driver_info']['forename']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['constructor_info']['constructor_name']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['circuit_info']['circuit_name']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['total_points']) . "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4'>No data available</td></tr>";
                                    }
                                ?>
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
$total_pages = isset($total_pages) && $total_pages > 0 
    ? intval($total_pages) 
    : 1;

// Set the maximum number of page links to show at once
$links_to_show = 7;

// Calculate start and end pages for the pagination range
$start_page = max(1, $current_page - floor($links_to_show / 2));
$end_page = min($total_pages, $start_page + $links_to_show - 1);

// Adjust start page if the range is less than $links_to_show
if ($end_page - $start_page + 1 < $links_to_show) {
    $start_page = max(1, $end_page - $links_to_show + 1);
}

// Display "Previous" button
if ($current_page > 1) {
    echo '<a href="totalpoints.php?page=' . ($current_page - 1) . '" class="button-7">Previous</a>';
} else {
    echo '<span class="disabled">Previous</span>';
}

// Display limited page numbers
for ($i = $start_page; $i <= $end_page; $i++) {
    if ($i == $current_page) {
        echo '<a href="#" class="current-page">' . $i . '</a>'; // Active page
    } else {
        echo '<a href="totalpoints.php?page=' . $i . '">' . $i . '</a>';
    }
}

// Display "Next" button
if ($current_page < $total_pages) {
    echo '<a href="totalpoints.php?page=' . ($current_page + 1) . '" class="button-7">Next</a>';
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>