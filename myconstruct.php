<?php
include 'db/db.php';
include 'process.php';

// Define the MongoDB collection
$collection = $client->FormulaVault->constructors;

// Define how many results you want per page
$results_per_page = 10;

// Determine the sorting order
$sort_order = isset($_GET['sort_order']) && strtolower($_GET['sort_order']) === 'asc' ? 1 : -1;

// Handle search keyword
$search_keyword = '';
if (isset($_GET['search'])) {
    $search_keyword = $_GET['search'];
}

// Determine which page number visitor is currently on
if (isset($_GET['page'])) {
    $current_page = (int) $_GET['page'];
} else {
    $current_page = 1;
}

// Calculate the skip limit for pagination
$start_from = ($current_page - 1) * $results_per_page;

// MongoDB aggregation pipeline
$pipeline = [];

// Match stage for filtering by search keyword
if (!empty($search_keyword)) {
    $pipeline[] = [
        '$match' => [
            'constructor_name' => new MongoDB\BSON\Regex($search_keyword, 'i') // Case-insensitive match
        ]
    ];
}

// Sort stage
$pipeline[] = [
    '$sort' => ['constructor_points' => $sort_order]
];

// Skip and limit for pagination
$pipeline[] = ['$skip' => $start_from];
$pipeline[] = ['$limit' => $results_per_page];

// Execute the query
$result = $collection->aggregate($pipeline)->toArray();

// Count total documents for pagination
$total_count = $collection->countDocuments([
    'constructor_name' => new MongoDB\BSON\Regex($search_keyword, 'i')
]);

$total_pages = ceil($total_count / $results_per_page);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Formula1 - Constructors</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" crossorigin="anonymous" />  
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
                                <h2>Constructors Details</h2>
                            </div>
                        </div>

                        <!-- Sort and Search Form -->
                  <div class="custom-filter-container my-5">
                    <form method="GET" action="myconstruct.php" class="custom-filter-form">
                      <!-- Sort Dropdown -->
                      <div class="custom-form-group">
                        <label for="sort_order" class="custom-label">Sort by Points:</label>
                        <select name="sort_order" id="sort_order" class="custom-form-control" onchange="this.form.submit()">
                          <option value="DESC" <?php if ($sort_order == -1) echo 'selected'; ?>>Most to Least</option>
                          <option value="ASC" <?php if ($sort_order == 1) echo 'selected'; ?>>Least to Most</option>
                        </select>
                      </div>
                                <!-- Search Bar -->
                                <div class="custom-form-group">
                                    <label for="search" class="custom-label">Search by constructor name:</label>
                                    <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search by constructor name" value="<?php echo $search_keyword; ?>">
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
                                <a href="myconstruct.php" class="custom-btn custom-btn-clear">Clear Search</a>
                            </div>
                        <?php endif; ?>

                        <!-- Table Data -->
                        <div class="row mt-5">
                    <table class="table table-dark table-striped">
                      <thead>
                        <tr>
                          <th scope="col">Constructor Name</th>
                          <th scope="col">No. of Pole Positions</th>
                          <th scope="col">No. of Titles</th>
                          <th scope="col">Constructor Points</th>
                          <th scope="col">Nationality</th>
                          <th scope="col">URL</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if (!empty($result)) : ?>
                          <?php foreach ($result as $row) : ?>
                            <tr>
                              <td><?php echo htmlspecialchars($row['constructor_name'] ?? ''); ?></td>
                              <td><?php echo htmlspecialchars($row['no_of_pole_positions'] ?? 0); ?></td>
                              <td><?php echo htmlspecialchars($row['no_of_titles'] ?? 0); ?></td>
                              <td><?php echo htmlspecialchars($row['constructor_points'] ?? 0); ?></td>
                              <td><?php echo htmlspecialchars($row['nationality'] ?? ''); ?></td>
                              <td><a href="<?php echo htmlspecialchars($row['url'] ?? '#'); ?>" target="_blank">View More</a></td>
                            </tr>
                          <?php endforeach; ?>
                        <?php else : ?>
                          <tr><td colspan="6">No data available</td></tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>

                        <!-- Pagination Controls -->
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

    // Ensure search_keyword and sort_order parameters have default values
    $search_keyword = isset($_GET['search']) ? $_GET['search'] : '';
    $sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'asc';

    // Number of links to display
    $max_links = 7;

    // Calculate start and end page for pagination
    $start_page = max(1, $current_page - floor($max_links / 2));
    $end_page = min($total_pages, $start_page + $max_links - 1);

    // Adjust start_page if the total number of pages is less than $max_links
    if ($end_page - $start_page < $max_links - 1) {
        $start_page = max(1, $end_page - $max_links + 1);
    }

    // Previous button
    if ($current_page > 1) {
        echo '<a href="myconstruct.php?page=' . ($current_page - 1) . '&search=' . urlencode($search_keyword) . '&sort_order=' . $sort_order . '" class="button-7">Previous</a>';
    } else {
        echo '<span class="disabled">Previous</span>';
    }

    // Page number links
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {
            echo '<span class="current-page">' . $i . '</span>';
        } else {
            echo '<a href="myconstruct.php?page=' . $i . '&search=' . urlencode($search_keyword) . '&sort_order=' . $sort_order . '">' . $i . '</a>';
        }
    }

    // Next button
    if ($current_page < $total_pages) {
        echo '<a href="myconstruct.php?page=' . ($current_page + 1) . '&search=' . urlencode($search_keyword) . '&sort_order=' . $sort_order . '" class="button-7">Next</a>';
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
