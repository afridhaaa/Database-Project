<?php
include 'db/db.php';  // Include your database connection

// Set the number of results per page
$results_per_page = 10;

// Initialize variables for sorting and searching
$sort_order = isset($_GET['sort_order']) && $_GET['sort_order'] === 'ASC' ? 1 : -1;
$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';

// Get the current page number from URL, if not set, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($current_page - 1) * $results_per_page;

// Aggregation pipeline for counting total records
$pipeline_count = [
    ['$lookup' => [
        'from' => 'races',
        'localField' => 'raceId',
        'foreignField' => 'raceId',
        'as' => 'race_details'
    ]],
    ['$lookup' => [
        'from' => 'constructors',
        'localField' => 'constructorId',
        'foreignField' => 'constructor_id',
        'as' => 'constructor_details'
    ]],
    ['$unwind' => '$race_details'],
    ['$unwind' => '$constructor_details'],
    ['$match' => [
        'position' => ['$lte' => 5],
        'constructor_details.constructor_name' => new MongoDB\BSON\Regex($search_keyword, 'i')
    ]],
    ['$group' => [
        '_id' => [
            'race_name' => '$race_details.name',
            'constructor_name' => '$constructor_details.constructor_name'
        ],
        'drivers_in_top_5' => ['$sum' => 1]
    ]],
    ['$match' => [
        'drivers_in_top_5' => ['$gt' => 1]
    ]],
    ['$count' => 'total']
];

$total_result = $db->results->aggregate($pipeline_count)->toArray();
$total_count = $total_result[0]['total'] ?? 0;
$total_pages = ceil($total_count / $results_per_page);

// Aggregation pipeline to fetch paginated data
$pipeline = [
    ['$lookup' => [
        'from' => 'races',
        'localField' => 'raceId',
        'foreignField' => 'raceId',
        'as' => 'race_details'
    ]],
    ['$lookup' => [
        'from' => 'constructors',
        'localField' => 'constructorId',
        'foreignField' => 'constructor_id',
        'as' => 'constructor_details'
    ]],
    ['$unwind' => '$race_details'],
    ['$unwind' => '$constructor_details'],
    ['$match' => [
        'position' => ['$lte' => 5],
        'constructor_details.constructor_name' => new MongoDB\BSON\Regex($search_keyword, 'i')
    ]],
    ['$group' => [
        '_id' => [
            'race_name' => '$race_details.name',
            'constructor_name' => '$constructor_details.constructor_name'
        ],
        'drivers_in_top_5' => ['$sum' => 1]
    ]],
    ['$match' => [
        'drivers_in_top_5' => ['$gt' => 1]
    ]],
    ['$sort' => ['drivers_in_top_5' => $sort_order]],
    ['$skip' => $start_from],
    ['$limit' => $results_per_page]
];

$result = $db->results->aggregate($pipeline);

?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Races with Multiple Drivers in Top 5</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />  
    <link rel="stylesheet" href="./assets/css/style.css">
   
</head>
  <body>
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
     <h2>Races with Multiple Drivers in Top 5</h2>
                            </div>
                        </div>
                        
  <!-- Sort and Search Form -->
  <div class="custom-filter-container">
        <form method="GET" action="raceswithmdrivers.php" class="custom-filter-form">
            <!-- Sort by Drivers in Top 5 -->
            <div class="custom-form-group">
                <label for="sort_order" class="custom-label">Sort by Drivers in Top 5:</label>
                <select name="sort_order" id="sort_order" class="custom-form-control" onchange="this.form.submit()">
                    <option value="DESC" <?php if ($sort_order == 'DESC') echo 'selected'; ?>>Most to Least</option>
                    <option value="ASC" <?php if ($sort_order == 'ASC') echo 'selected'; ?>>Least to Most</option>
                </select>
            </div>

            <!-- Search by Constructor -->
            <div class="custom-form-group">
                <label for="search" class="custom-label">Search by Constructor Name:</label>
                <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search by constructor" value="<?php echo htmlspecialchars($search_keyword); ?>">
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
            <a href="raceswithmdrivers.php" class="custom-btn custom-btn-clear">Clear Search</a>
        </div>
    <?php endif; ?>                      

    <div class="row mt-5">
                              <table class="table table-dark table-striped">
                                  <thead>
                                      <tr>
                                          <th>Race Name</th>
                                          <th>Constructor Name</th>
                                          <th>Drivers in Top 5</th>
                                      </tr>
                                  </thead>
                                  <tbody>
                                      <?php
                                      foreach ($result as $row) {
                                          echo "<tr>";
                                          echo "<td>" . htmlspecialchars($row['_id']['race_name']) . "</td>";
                                          echo "<td>" . htmlspecialchars($row['_id']['constructor_name']) . "</td>";
                                          echo "<td>" . htmlspecialchars($row['drivers_in_top_5']) . "</td>";
                                          echo "</tr>";
                                      }
                                      ?>
                                  </tbody>
                              </table>
                          </div>

     <!-- Pagination Controls -->
     <div class="pagination">
                <?php
                // Previous button
                if ($current_page > 1) {
                    echo '<a href="raceswithmdrivers.php?page=' . ($current_page - 1) . '" class="button-7">Previous</a>';
                } else {
                    echo '<span class="disabled">Previous</span>';
                }

                // Page numbers
                for ($page = 1; $page <= $total_pages; $page++) {
                    if ($page == $current_page) {
                        echo '<span class="current-page">' . $page . '</span>'; // Current page
                    } else {
                        echo '<a href="raceswithmdrivers.php?page=' . $page . '">' . $page . '</a>'; // Other pages
                    }
                }

                // Next button
                if ($current_page < $total_pages) {
                    echo '<a href="raceswithmdrivers.php?page=' . ($current_page + 1) . '" class="button-7">Next</a>';
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
