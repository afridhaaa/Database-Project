<?php
include 'db/db.php';  // Include your database connection

// Set the number of results per page
$results_per_page = 13;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($current_page - 1) * $results_per_page;

// Initialize search keyword and sort order
$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';
$sort_direction = ($sort_order === 'DESC') ? -1 : 1;

// MongoDB aggregation to fetch circuit names, driver names, and average laps
$pipeline = [
    ['$lookup' => [
        'from' => 'drivers',
        'localField' => 'driverId',
        'foreignField' => 'driverId',
        'as' => 'driver'
    ]],
    ['$unwind' => '$driver'],
    ['$lookup' => [
        'from' => 'races',
        'localField' => 'raceId',
        'foreignField' => 'raceId',
        'as' => 'race'
    ]],
    ['$unwind' => '$race'],
    ['$lookup' => [
        'from' => 'circuits',
        'localField' => 'race.circuit_id',
        'foreignField' => 'circuit_id',
        'as' => 'circuit'
    ]],
    ['$unwind' => '$circuit'],
    ['$match' => ['driver.forename' => new MongoDB\BSON\Regex($search_keyword, 'i')]],
    ['$group' => [
        '_id' => ['circuit_name' => '$circuit.circuit_name', 'forename' => '$driver.forename'],
        'avg_laps' => ['$avg' => '$laps']
    ]],
    ['$sort' => ['avg_laps' => $sort_direction]],
    ['$skip' => $start_from],
    ['$limit' => $results_per_page]
];

$collection = $db->results;
$result = $collection->aggregate($pipeline);

// Count total results for pagination
$total_count_pipeline = [
    ['$lookup' => [
        'from' => 'drivers',
        'localField' => 'driverId',
        'foreignField' => 'driverId',
        'as' => 'driver'
    ]],
    ['$unwind' => '$driver'],
    ['$lookup' => [
        'from' => 'races',
        'localField' => 'raceId',
        'foreignField' => 'raceId',
        'as' => 'race'
    ]],
    ['$unwind' => '$race'],
    ['$lookup' => [
        'from' => 'circuits',
        'localField' => 'race.circuit_id',
        'foreignField' => 'circuit_id',
        'as' => 'circuit'
    ]],
    ['$unwind' => '$circuit'],
    ['$match' => ['driver.forename' => new MongoDB\BSON\Regex($search_keyword, 'i')]],
    ['$group' => [
        '_id' => ['circuit_name' => '$circuit.circuit_name', 'forename' => '$driver.forename']
    ]],
    ['$count' => 'total']
];

$total_count_result = $collection->aggregate($total_count_pipeline);
$total_row = iterator_to_array($total_count_result);
$total_pages = isset($total_row[0]) ? ceil($total_row[0]['total'] / $results_per_page) : 1;
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Average Laps by Circuit and Driver</title>
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
                           <!-- Back Button -->
                            <div class="back-button" style="margin: 20px;">
                                <a href="index.php" class="button-8">← Back to Home</a>
                            </div>
                            <div class="head">
                                <h2>Average Laps by Circuit and Driver</h2>
                            </div>
                        </div>
                        
                       <!-- Sort and Search Form -->
                        <div class="custom-filter-container">
                            <form method="GET" action="avgn.php" class="custom-filter-form">
                                <!-- Sort Dropdown -->
                                <div class="custom-form-group">
                                    <label for="sort_order" class="custom-label">Sort by Average Laps:</label>
                                    <select name="sort_order" id="sort_order" class="custom-form-control" onchange="this.form.submit()">
                                        <option value="DESC" <?php if ($sort_order == 'DESC') echo 'selected'; ?>>Most to Least</option>
                                        <option value="ASC" <?php if ($sort_order == 'ASC') echo 'selected'; ?>>Least to Most</option>
                                    </select>
                                </div>

                                <!-- Search Bar -->
                                <div class="custom-form-group">
                                    <label for="search" class="custom-label">Search by Driver Name:</label>
                                    <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search by driver name" value="<?php echo htmlspecialchars($search_keyword); ?>">
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
            <a href="avgn.php" class="custom-btn custom-btn-clear">Clear Filter</a>
        </div>
    <?php endif; ?>

                        <div class="row mt-5">
                            <table class="table table-dark table-striped">
                                <thead>
                                    <tr>
                                        <th>Circuit Name</th>
                                        <th>Driver Name</th>
                                        <th>Average Laps</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Loop through MongoDB result set
                                    foreach ($result as $row) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['_id']['circuit_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['_id']['forename']) . "</td>";
                                        echo "<td>" . htmlspecialchars(number_format($row['avg_laps'], 2)) . "</td>";
                                        echo "</tr>";
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

    // Ensure search_term and sort_order parameters have default values
    $search_keyword = isset($_GET['search']) ? $_GET['search'] : '';
    $sort_order = isset($_GET['sort']) ? $_GET['sort'] : 'asc';

    // Previous button
if ($current_page > 1) {
    echo '<a href="avgn.php?page=' . ($current_page - 1) . '&search=' . urlencode($search_keyword) . '&sort_order=' . $sort_order . '" class="button-7">Previous</a>';
} else {
    echo '<span class="disabled">Previous</span>';
}

// Page numbers
for ($i = 1; $i <= $total_pages; $i++) {
    if ($i == $current_page) {
        echo '<span class="current-page">' . $i . '</span>';
    } else {
        echo '<a href="avgn.php?page=' . $i . '&search=' . urlencode($search_keyword) . '&sort_order=' . $sort_order . '">' . $i . '</a>';
    }
}

// Next button
if ($current_page < $total_pages) {
    echo '<a href="avgn.php?page=' . ($current_page + 1) . '&search=' . urlencode($search_keyword) . '&sort_order=' . $sort_order . '" class="button-7">Next</a>';
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
    document.getElementById("view-more-btn")?.addEventListener("click", function() {
        var moreItems = document.getElementById("more-items");
        var viewMoreBtn = document.getElementById("view-more-li");
        moreItems.style.display = "block";
        viewMoreBtn.style.display = "none";
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>