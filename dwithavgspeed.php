<?php
include 'db/db.php';  // Include your database connection

// Set the number of results per page
$results_per_page = 10;

// Initialize variables for sorting and searching
$sort_order = isset($_GET['sort_order']) && $_GET['sort_order'] === 'DESC' ? -1 : 1;
$sort_order_text = $sort_order === 1 ? 'ASC' : 'DESC';

$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';

// Get the current page number from URL, if not set, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($current_page - 1) * $results_per_page;

// Aggregation pipeline for counting total records
$pipeline_count = [
    ['$lookup' => [
        'from' => 'drivers',
        'localField' => 'driverId',
        'foreignField' => 'driverId',
        'as' => 'driver_details'
    ]],
    ['$lookup' => [
        'from' => 'races',
        'localField' => 'raceId',
        'foreignField' => 'raceId',
        'as' => 'race_details'
    ]],
    ['$lookup' => [
        'from' => 'circuits',
        'localField' => 'race_details.circuit_id',
        'foreignField' => 'circuit_id',
        'as' => 'circuit_details'
    ]],
    ['$unwind' => '$driver_details'],
    ['$unwind' => '$race_details'],
    ['$unwind' => '$circuit_details'],
    ['$match' => [
        '$or' => [
            ['circuit_details.circuit_name' => new MongoDB\BSON\Regex($search_keyword, 'i')],
            ['driver_details.forename' => new MongoDB\BSON\Regex($search_keyword, 'i')]
        ]
    ]],
    ['$group' => [
        '_id' => [
            'circuit_name' => '$circuit_details.circuit_name',
            'driver_name' => '$driver_details.forename'
        ],
        'avg_speed' => ['$avg' => '$milliseconds']
    ]],
    ['$count' => 'total']
];

$total_result = $db->lap_times->aggregate($pipeline_count)->toArray();
$total_count = $total_result[0]['total'] ?? 0;
$total_pages = ceil($total_count / $results_per_page);

// Aggregation pipeline to fetch paginated data
$pipeline = [
    ['$lookup' => [
        'from' => 'drivers',
        'localField' => 'driverId',
        'foreignField' => 'driverId',
        'as' => 'driver_details'
    ]],
    ['$lookup' => [
        'from' => 'races',
        'localField' => 'raceId',
        'foreignField' => 'raceId',
        'as' => 'race_details'
    ]],
    ['$lookup' => [
        'from' => 'circuits',
        'localField' => 'race_details.circuit_id',
        'foreignField' => 'circuit_id',
        'as' => 'circuit_details'
    ]],
    ['$unwind' => '$driver_details'],
    ['$unwind' => '$race_details'],
    ['$unwind' => '$circuit_details'],
    ['$match' => [
        '$or' => [
            ['circuit_details.circuit_name' => new MongoDB\BSON\Regex($search_keyword, 'i')],
            ['driver_details.forename' => new MongoDB\BSON\Regex($search_keyword, 'i')]
        ]
    ]],
    ['$group' => [
        '_id' => [
            'circuit_name' => '$circuit_details.circuit_name',
            'driver_name' => '$driver_details.forename'
        ],
        'avg_speed' => ['$avg' => '$milliseconds']
    ]],
    ['$sort' => ['avg_speed' => $sort_order]],
    ['$skip' => $start_from],
    ['$limit' => $results_per_page]
];

$result = $db->lap_times->aggregate($pipeline);

?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Drivers with Average Lap Speed</title>
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
                  <a href="index.php">  <!-- <h4>Formula Vault</h4></a> -->
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
                           <!-- Back Button -->
    <div class="back-button" style="margin: 20px;">
        <a href="index.php" class="button-8">← Back to Home</a>
    </div>
                            <div class="head">
     <h2>Drivers with Average Lap Speed</h2>
                            </div>
                        </div>
                        
 <!-- Sort and Search Form -->
 <div class="custom-filter-container">
        <form method="GET" action="dwithavgspeed.php" class="custom-filter-form">
            <!-- Sort Dropdown -->
            <div class="custom-form-group">
                <label for="sort_order" class="custom-label">Sort by Average Speed:</label>
                <select name="sort_order" id="sort_order" class="custom-form-control" onchange="this.form.submit()">
                    <option value="ASC" <?php if ($sort_order_text === 'ASC') echo 'selected'; ?>>Least to Most</option>
                    <option value="DESC" <?php if ($sort_order_text === 'DESC') echo 'selected'; ?>>Most to Least</option>
                </select>
            </div>

            <!-- Search Bar -->
            <div class="custom-form-group">
                <label for="search" class="custom-label">Search by Driver or Circuit Name:</label>
                <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search by driver or circuit" value="<?php echo htmlspecialchars($search_keyword); ?>">
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
            <a href="dwithavgspeed.php" class="custom-btn custom-btn-clear">Clear Search</a>
        </div>
    <?php endif; ?>                       

    <div class="row mt-5">
                              <table class="table table-dark table-striped">
                                  <thead>
                                      <tr>
                                          <th>Circuit Name</th>
                                          <th>Driver Name</th>
                                          <th>Average Speed (ms)</th>
                                      </tr>
                                  </thead>
                                  <tbody>
                                      <?php
                                      foreach ($result as $row) {
                                          echo "<tr>";
                                          echo "<td>" . htmlspecialchars($row['_id']['circuit_name']) . "</td>";
                                          echo "<td>" . htmlspecialchars($row['_id']['driver_name']) . "</td>";
                                          echo "<td>" . htmlspecialchars($row['avg_speed']) . "</td>";
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

    // Ensure $total_pages is a valid integer and at least 1
    $total_pages = isset($total_pages) && $total_pages > 0
        ? intval($total_pages)
        : 1;

    // Define the maximum number of links to display
    $max_links = 7;

    // Calculate start and end pages
    $start_page = max(1, $current_page - floor($max_links / 2));
    $end_page = min($total_pages, $start_page + $max_links - 1);

    // Adjust start_page if the range is less than $max_links
    if ($end_page - $start_page < $max_links - 1) {
        $start_page = max(1, $end_page - $max_links + 1);
    }

    // Display "Previous" button
    if ($current_page > 1) {
        echo '<a href="dwithavgspeed.php?page=' . ($current_page - 1) . '&search=' . urlencode($search_keyword) . '&sort_order=' . $sort_order . '" class="button-7">Previous</a>';
    } else {
        echo '<span class="disabled">Previous</span>';
    }

    // Display limited page numbers
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {
            echo '<span class="current-page">' . $i . '</span>'; // Active page
        } else {
            echo '<a href="dwithavgspeed.php?page=' . $i . '&search=' . urlencode($search_keyword) . '&sort_order=' . $sort_order . '">' . $i . '</a>';
        }
    }

    // Display "Next" button
    if ($current_page < $total_pages) {
        echo '<a href="dwithavgspeed.php?page=' . ($current_page + 1) . '&search=' . urlencode($search_keyword) . '&sort_order=' . $sort_order . '" class="button-7">Next</a>';
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>
