<?php
include 'db/db.php';

$collection = $db->selectCollection('races');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define how many results you want per page
$results_per_page = 13;

// Determine which page number visitor is currently on
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Get search terms from GET parameters
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$search_year = isset($_GET['search_year']) ? trim($_GET['search_year']) : '';
$search_round = isset($_GET['search_round']) ? trim($_GET['search_round']) : '';
$sort_order = isset($_GET['sort_order']) && $_GET['sort_order'] === 'DESC' ? -1 : 1;

// Start from for pagination
$skip = ($current_page - 1) * $results_per_page;

// MongoDB aggregation pipeline
$pipeline = [];

// Add search conditions if the search fields are filled
$match_conditions = [];
if (!empty($search_name)) {
    $match_conditions['name'] = ['$regex' => $search_name, '$options' => 'i']; // Case-insensitive regex for name
}
if (!empty($search_year)) {
    $match_conditions['year'] = intval($search_year); // Exact numeric match for year
}
if (!empty($search_round)) {
    $match_conditions['round'] = intval($search_round); // Exact numeric match for round
}

// Add `$match` stage if there are any conditions
if (!empty($match_conditions)) {
    $pipeline[] = ['$match' => $match_conditions];
}

// Include necessary fields and sort the results
$pipeline[] = [
    '$project' => [
        'raceId' => 1,
        'year' => 1,
        'round' => 1,
        'circuit_id' => 1,
        'name' => 1,
        'date' => 1,
        'url' => 1
    ]
];
$pipeline[] = ['$sort' => ['name' => $sort_order]];
$pipeline[] = ['$skip' => $skip];
$pipeline[] = ['$limit' => $results_per_page];

// Fetch data using aggregation pipeline
$data = iterator_to_array($collection->aggregate($pipeline), false);

// Count total documents for pagination
$total_pipeline = [];
if (!empty($match_conditions)) {
    $total_pipeline[] = ['$match' => $match_conditions];
}
$total_pipeline[] = ['$count' => 'total'];

$total_data = iterator_to_array($collection->aggregate($total_pipeline), false);
$total_count = $total_data[0]['total'] ?? 0;
$total_pages = ceil($total_count / $results_per_page);
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Formula Vault - Races</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
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
                                <h2>Race Details</h2>
                            </div>
                        </div>
                        
                        <!-- Sort and Search Form -->
                        <div class="custom-filter-container my-5">
                        <form method="GET" action="myrace.php" class="custom-filter-form">
                            <!-- Search Bar for Name -->
                            <div class="custom-form-group">
                                <label for="search_name" class="custom-label">Search by Name:</label>
                                <input type="text" name="search_name" class="custom-form-control" placeholder="Search by Name" value="<?php echo htmlspecialchars($_GET['search_name'] ?? ''); ?>">
                            </div>

                            <!-- Search Bar for Year -->
                            <div class="custom-form-group">
                                <label for="search_year" class="custom-label">Search by Year:</label>
                                <input type="text" name="search_year" class="custom-form-control" placeholder="Search by Year" value="<?php echo htmlspecialchars($_GET['search_year'] ?? ''); ?>">
                            </div>

                            <!-- Search Bar for Round -->
                            <div class="custom-form-group">
                                <label for="search_round" class="custom-label">Search by Round:</label>
                                <input type="text" name="search_round" class="custom-form-control" placeholder="Search by Round" value="<?php echo htmlspecialchars($_GET['search_round'] ?? ''); ?>">
                            </div>

                            <!-- Sort Dropdown -->
                            <div class="custom-form-group">
                                <label for="sort_order" class="custom-label">Sort by Name:</label>
                                <select name="sort_order" id="sort_order" class="custom-form-control" onchange="this.form.submit()">
                                    <option value="ASC" <?php if ($sort_order == 1) echo 'selected'; ?>>Ascending</option>
                                    <option value="DESC" <?php if ($sort_order == -1) echo 'selected'; ?>>Descending</option>
                                </select>
                            </div>

                            <!-- Search Button -->
                            <div class="custom-form-group">
                                <button type="submit" class="custom-btn custom-btn-primary">Search</button>
                            </div>
                        </form>
                        </div>

                        <!-- Clear Search Button: Show only when search is applied -->
                        <?php if (!empty($search_name) || !empty($search_year) || !empty($search_round)) : ?>
                            <div style="margin-bottom: 20px; margin-left: 30px;">
                                <a href="myrace.php" class="custom-btn custom-btn-clear">Clear Search</a>
                            </div>
                        <?php endif; ?>

                        <div class="row mt-5">
                            <table class="table table-dark table-striped">
                                <thead>
                                    <tr>
                                        <!-- <th>Race ID</th> -->
                                        <th>Year</th>
                                        <th>Round</th>
                                        <th>Circuit ID</th>
                                        <th>Name</th>
                                        <th>Date</th>
                                        <th>URL</th>
                                    </tr>
                                </thead>
                                <tbody>
    <?php foreach ($data as $row): ?>
        <tr>
            <!-- <td><?php echo isset($row['raceId']) ? htmlspecialchars($row['raceId']) : 'N/A'; ?></td> -->
            <td><?php echo isset($row['year']) ? htmlspecialchars($row['year']) : 'N/A'; ?></td>
            <td><?php echo isset($row['round']) ? htmlspecialchars($row['round']) : 'N/A'; ?></td>
            <td><?php echo isset($row['circuit_id']) ? htmlspecialchars($row['circuit_id']) : 'N/A'; ?></td>
            <td><?php echo isset($row['name']) ? htmlspecialchars($row['name']) : 'N/A'; ?></td>
            <td><?php echo isset($row['date']) ? htmlspecialchars($row['date']) : 'N/A'; ?></td>
            <td>
                <?php if (isset($row['url']) && !empty($row['url'])): ?>
                    <a href="<?php echo htmlspecialchars($row['url']); ?>" target="_blank">Link</a>
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
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

   // Preserve search parameters
$query_params = [
    'search_name' => $search_name ?? '',
    'search_year' => $search_year ?? '',
    'search_round' => $search_round ?? '',
    'sort_order' => $sort_order === -1 ? 'DESC' : 'ASC' // Ensure sort_order is included
];

// Display "Previous" button
if ($current_page > 1) {
    $query_params['page'] = $current_page - 1;
    echo '<a href="myrace.php?' . http_build_query($query_params) . '" class="button-7">Previous</a>';
} else {
    echo '<span class="disabled">Previous</span>';
}

// Display limited page numbers
for ($i = $start_page; $i <= $end_page; $i++) {
    $query_params['page'] = $i;
    if ($i == $current_page) {
        echo '<span class="current-page">' . $i . '</span>'; // Current page
    } else {
        echo '<a href="myrace.php?' . http_build_query($query_params) . '">' . $i . '</a>';
    }
}

// Display "Next" button
if ($current_page < $total_pages) {
    $query_params['page'] = $current_page + 1;
    echo '<a href="myrace.php?' . http_build_query($query_params) . '" class="button-7">Next</a>';
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>