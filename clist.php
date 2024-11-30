<?php
include 'db/db.php';  // MongoDB connection

// Set the number of results per page
$results_per_page = 5;

// Get the current page number from URL, if not set, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the skip limit for pagination
$skip = ($current_page - 1) * $results_per_page;

// Initialize search term and sort order
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_order = isset($_GET['sort_order']) && $_GET['sort_order'] === 'ASC' ? 1 : -1;  // -1 for DESC, 1 for ASC

// Build MongoDB Aggregation Pipeline
$pipeline = [];

// Match only documents where `fastestLap` is not null
$pipeline[] = [
    '$match' => ['fastestLap' => ['$ne' => null]]
];

// Lookup to join the constructors collection
$pipeline[] = [
    '$lookup' => [
        'from' => 'constructors',
        'localField' => 'constructorId',
        'foreignField' => 'constructor_id',
        'as' => 'constructor_info'
    ]
];
$pipeline[] = ['$unwind' => '$constructor_info'];

// Lookup to join the races collection
$pipeline[] = [
    '$lookup' => [
        'from' => 'races',
        'localField' => 'raceId',
        'foreignField' => 'raceId',
        'as' => 'race_info'
    ]
];
$pipeline[] = ['$unwind' => '$race_info'];

// Lookup to join the circuits collection
$pipeline[] = [
    '$lookup' => [
        'from' => 'circuits',
        'localField' => 'race_info.circuit_id',
        'foreignField' => 'circuit_id',
        'as' => 'circuit_info'
    ]
];
$pipeline[] = ['$unwind' => '$circuit_info'];

// If a search term is provided, add a match stage to filter constructors by name
if (!empty($search_term)) {
    $pipeline[] = [
        '$match' => [
            'constructor_info.constructor_name' => new MongoDB\BSON\Regex($search_term, 'i')
        ]
    ];
}

// Group by constructor and circuit names, and calculate the total fastest laps
$pipeline[] = [
    '$group' => [
        '_id' => [
            'constructor_name' => '$constructor_info.constructor_name',
            'circuit_name' => '$circuit_info.circuit_name'
        ],
        'total_fastest_laps' => ['$sum' => 1]
    ]
];

// Filter to include only records with 5 or more fastest laps
$pipeline[] = [
    '$match' => [
        'total_fastest_laps' => ['$gte' => 5]
    ]
];

// Sort by `total_fastest_laps` based on sort order
$pipeline[] = ['$sort' => ['total_fastest_laps' => $sort_order]];

// Add skip and limit stages for pagination
$pipeline[] = ['$skip' => $skip];
$pipeline[] = ['$limit' => $results_per_page];

// Execute aggregation query
$query = $db->results->aggregate($pipeline);
$results = iterator_to_array($query);

// Calculate total records for pagination
$total_pipeline = [
    [
        '$match' => [
            'fastestLap' => ['$ne' => null],
        ]
    ],
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
];

// Add search term to the total pipeline
if (!empty($search_term)) {
    $total_pipeline[] = [
        '$match' => [
            'constructor_info.constructor_name' => new MongoDB\BSON\Regex($search_term, 'i')
        ]
    ];
}

$total_pipeline[] = [
    '$group' => [
        '_id' => [
            'constructor_name' => '$constructor_info.constructor_name',
            'circuit_name' => '$circuit_info.circuit_name'
        ],
        'total_fastest_laps' => ['$sum' => 1]
    ]
];

$total_pipeline[] = [
    '$match' => [
        'total_fastest_laps' => ['$gte' => 5]
    ]
];
$total_pipeline[] = ['$count' => 'total'];

// Execute the total count query for pagination
$total_query = $db->results->aggregate($total_pipeline);
$total_result = iterator_to_array($total_query);
$total_pages = ceil(($total_result[0]['total'] ?? 0) / $results_per_page);
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Constructors with Fastest Laps</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />  
    <link rel="stylesheet" href="./assets/css/style.css">
    <style>
        /* Make the body and html take the full height */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        /* Flexbox for the entire page layout */
        .wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #212529;
        }

        /* Main content should grow to take available space */
        #main {
            flex: 1;
        }

        /* Footer styling */
        footer {
            background-color: #15151E;
            color: white;
            text-align: center;
            padding: 10px 0;
        }
    </style>
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
                           <!-- Back Button -->
    <div class="back-button" style="margin: 20px;">
        <a href="index.php" class="button-8">← Back to Home</a>
    </div>
                            <div class="head">
     <h2>Constructors with Fastest Laps</h2>
                            </div>
                        </div>
                        
                       
        <!-- Sort and Search Form -->
    <div class="custom-filter-container">
        <form method="GET" action="clist.php" class="custom-filter-form">
            <!-- Sort Dropdown -->
            <div class="custom-form-group">
                <label for="sort_order" class="custom-label">Sort by Fastest Laps:</label>
                <select name="sort_order" id="sort_order" class="custom-form-control" onchange="this.form.submit()">
    <option value="1" <?php if ($sort_order == 1) echo 'selected'; ?>>Least to Most</option>
    <option value="-1" <?php if ($sort_order == -1) echo 'selected'; ?>>Most to Least</option>
</select>

            </div>

            <!-- Search Bar -->
            <div class="custom-form-group">
                <label for="search" class="custom-label">Search by Constructor:</label>
                <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search by Constructor" value="<?php echo htmlspecialchars($search_term); ?>">
            </div>

            <!-- Search Button -->
            <div class="custom-form-group">
                <button type="submit" class="custom-btn custom-btn-primary">Search</button>
            </div>
        </form>
    </div>
    <!-- Clear Search Button: Show only when search is applied -->
    <?php if (!empty($search_term)) : ?>
        <div style="margin-bottom: 20px; margin-left: 30px;">
            <a href="clist.php" class="custom-btn custom-btn-clear">Clear Filter</a>
        </div>
    <?php endif; ?>


<div class="row mt-5">
                                <table class="table table-dark table-striped">
                                    <thead>
                                        <tr>
                                            <th>Constructor</th>
                                            <th>Circuit</th>
                                            <th>Total Fastest Laps</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($results)): ?>
                                            <tr><td colspan="3" style="text-align:center;"><?php echo htmlspecialchars($no_data_message); ?></td></tr>
                                        <?php else: ?>
                                            <?php foreach ($results as $row): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['_id']['constructor_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['_id']['circuit_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['total_fastest_laps']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
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

    // Ensure search_term and sort_order parameters have default values
    $search_term = isset($_GET['search']) ? $_GET['search'] : '';
    $sort_order = isset($_GET['sort']) ? $_GET['sort'] : 'asc';

    // Previous button
if ($current_page > 1) {
    echo '<a href="clist.php?page=' . ($current_page - 1) . '&search=' . urlencode($search_term) . '&sort_order=' . $sort_order . '" class="button-7">Previous</a>';
} else {
    echo '<span class="disabled">Previous</span>';
}

// Page numbers
for ($i = 1; $i <= $total_pages; $i++) {
    if ($i == $current_page) {
        echo '<span class="current-page">' . $i . '</span>';
    } else {
        echo '<a href="clist.php?page=' . $i . '&search=' . urlencode($search_term) . '&sort_order=' . $sort_order . '">' . $i . '</a>';
    }
}

// Next button
if ($current_page < $total_pages) {
    echo '<a href="clist.php?page=' . ($current_page + 1) . '&search=' . urlencode($search_term) . '&sort_order=' . $sort_order . '" class="button-7">Next</a>';
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

