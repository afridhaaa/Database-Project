<?php
include 'db/db.php';
include 'process.php';

// Define the MongoDB collection
$collection = $client->FormulaVault->circuits; // Adjust collection path as needed

// Define how many results you want per page
$results_per_page = 10;

// Handle pagination
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($current_page - 1) * $results_per_page;

// Handle sorting
$sort_order = isset($_GET['sort_order']) && strtolower($_GET['sort_order']) === 'desc' ? -1 : 1;

// Handle search keyword
$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';

// Build MongoDB aggregation pipeline
$pipeline = [];

if (!empty($search_keyword)) {
    $pipeline[] = [
        // Add lowercase version of circuit_name for case-insensitive matching
        '$addFields' => [
            'circuit_name_lower' => ['$toLower' => '$circuit_name'],
            'circuit_location_lower' => ['$toLower' => '$circuit_location'],
            'circuit_country_lower' => ['$toLower' => '$circuit_country']
        ]
    ];

    $pipeline[] = [
        '$match' => [
            '$or' => [
                ['circuit_name_lower' => new MongoDB\BSON\Regex(strtolower($search_keyword), 'i')],
                ['circuit_location_lower' => new MongoDB\BSON\Regex(strtolower($search_keyword), 'i')],
                ['circuit_country_lower' => new MongoDB\BSON\Regex(strtolower($search_keyword), 'i')]
            ]
        ]
    ];
}


// Sort stage
$pipeline[] = ['$sort' => ['circuit_name' => $sort_order]];

// Skip and limit stages for pagination
$pipeline[] = ['$skip' => $start_from];
$pipeline[] = ['$limit' => $results_per_page];

// Execute the aggregation query
$result = $collection->aggregate($pipeline)->toArray();

// Total document count for pagination
$total_count = $collection->countDocuments(
    !empty($search_keyword)
        ? ['$or' => [
            ['circuit_name' => new MongoDB\BSON\Regex($search_keyword, 'i')],
            ['circuit_location' => new MongoDB\BSON\Regex($search_keyword, 'i')],
            ['circuit_country' => new MongoDB\BSON\Regex($search_keyword, 'i')]
        ]]
        : []
);

$total_pages = ceil($total_count / $results_per_page);
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> Formula Vault - Circuits</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
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
                <!-- <div class="heading">
                  <a href="index.php"><h4>Formula Vault</h4></a>
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
                            <div class="head">
                                <h2>Circuits Details</h2>
                            </div>
                        </div>
                        
                        <!-- Sort and Search Form -->
                        <div class="custom-filter-container my-5">
                            <form method="GET" action="mycircuit.php" class="custom-filter-form">
                                <!-- Sort Dropdown -->
                                <div class="custom-form-group">
                                    <label for="sort_order" class="custom-label">Sort by Circuit Name:</label>
                                    <select name="sort_order" id="sort_order" class="custom-form-control" onchange="this.form.submit()">
                                        <option value="ASC" <?php if ($sort_order == 1) echo 'selected'; ?>>A-Z</option>
                                        <option value="DESC" <?php if ($sort_order == -1) echo 'selected'; ?>>Z-A</option>
                                    </select>
                                </div>

                                <!-- Search Bar -->
                                <div class="custom-form-group">
                                    <label for="search" class="custom-label">Search by Circuit Name, Location, or Country:</label>
                                    <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search Circuits" value="<?php echo htmlspecialchars($search_keyword); ?>">
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
                                <a href="mycircuit.php" class="custom-btn custom-btn-clear">Clear Search</a>
                            </div>
                        <?php endif; ?>

                        <div class="row mt-5">
                            <table class="table table-dark table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">Circuit Name</th>
                                        <th scope="col">Circuit Location</th>
                                        <th scope="col">Circuit Country</th>
                                        <th scope="col">Latitude</th>
                                        <th scope="col">Longitude</th>
                                        <th scope="col">Altitude</th>
                                        <th scope="col">Url</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($result)) : ?>
                                    <?php foreach ($result as $row) : ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['circuit_name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['circuit_location'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['circuit_country'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['latitude'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['longitude'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['altitude'] ?? ''); ?></td>
                                            <td><a href="<?php echo htmlspecialchars($row['url'] ?? '#'); ?>" target="_blank">View More</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr><td colspan="7">No data available</td></tr>
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

    // Preserve search parameters
    $query_params = [
        'search_name' => $search_name ?? '',
        'search_year' => $search_year ?? '',
        'search_round' => $search_round ?? '',
        'sort_order' => $sort_order ?? ''
    ];

    // Display "Previous" button
    if ($current_page > 1) {
        $query_params['page'] = $current_page - 1;
        echo '<a href="mycircuit.php?' . http_build_query($query_params) . '" class="button-7">Previous</a>';
    } else {
        echo '<span class="disabled">Previous</span>';
    }

    // Display limited page numbers
    for ($i = $start_page; $i <= $end_page; $i++) {
        $query_params['page'] = $i;
        if ($i == $current_page) {
            echo '<span class="current-page">' . $i . '</span>'; // Current page
        } else {
            echo '<a href="mycircuit.php?' . http_build_query($query_params) . '">' . $i . '</a>';
        }
    }

    // Display "Next" button
    if ($current_page < $total_pages) {
        $query_params['page'] = $current_page + 1;
        echo '<a href="mycircuit.php?' . http_build_query($query_params) . '" class="button-7">Next</a>';
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
