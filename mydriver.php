<?php
include 'db/db.php';
include 'process.php';

// Define the MongoDB collection
$collection = $client->FormulaVault->drivers;

// Set the number of results per page
$results_per_page = 10;

// Determine which page number visitor is currently on
if (isset($_GET['page'])) {
    $current_page = $_GET['page'];
} else {
    $current_page = 1;
}

// Calculate the skip limit for pagination
$start_from = ($current_page - 1) * $results_per_page;

// Handle sorting
$sort_order = isset($_GET['sort_order']) && strtolower($_GET['sort_order']) === 'desc' ? -1 : 1;

// Handle search
$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';

$pipeline = [
    // Match documents based on search criteria
    [
        '$match' => [
            '$or' => [
                ['forename' => new MongoDB\BSON\Regex($search_keyword, 'i')],
                ['nationality' => new MongoDB\BSON\Regex($search_keyword, 'i')]
            ]
        ]
    ],
    // Add a lowercase version of forename for case-insensitive sorting
    [
        '$addFields' => [
            'forename_lower' => ['$toLower' => '$forename']
        ]
    ],
    // Sort by the lowercase forename field
    [
        '$sort' => [
            'forename_lower' => $sort_order
        ]
    ],
    // Skip documents for pagination
    [
        '$skip' => $start_from
    ],
    // Limit the number of documents per page
    [
        '$limit' => $results_per_page
    ]
];


// Execute aggregation query
$result = $collection->aggregate($pipeline);

// Count total documents for pagination
$total_count = $collection->countDocuments([
    '$or' => [
        ['forename' => new MongoDB\BSON\Regex($search_keyword, 'i')],
        ['nationality' => new MongoDB\BSON\Regex($search_keyword, 'i')]
    ]
]);

$total_pages = ceil($total_count / $results_per_page);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Drivers - Formula1</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="./assets/css/style.css">
</head>
  <body>
   <div class="topbar">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2">
                <!-- <div class="heading">
                  <a href="index.php">  <h4>Formula1</h4></a>
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
                                <h2>Drivers Details</h2>
                            </div>
                        </div>

                        <!-- Sort and Search Form -->
                        <div class="custom-filter-container my-5">
                            <form method="GET" action="mydriver.php" class="custom-filter-form">
                                <!-- Sort Dropdown -->
                                <div class="custom-form-group">
                                    <label for="sort_order" class="custom-label">Sort by Name:</label>
                                    <select name="sort_order" id="sort_order" class="custom-form-control" onchange="this.form.submit()">
                                        <option value="ASC" <?php if ($sort_order == 1) echo 'selected'; ?>>A to Z</option>
                                        <option value="DESC" <?php if ($sort_order == -1) echo 'selected'; ?>>Z to A</option>
                                    </select>
                                </div>

                                <!-- Search Bar -->
                                <div class="custom-form-group">
                                <label for="search" class="custom-label">Search by Driver Name or Nationality:</label>
                                    <input type="text" name="search" class="custom-form-control custom-search-bar" placeholder="Search by driver name or nationality" value="<?php echo $search_keyword; ?>">
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
                                <a href="mydriver.php" class="custom-btn custom-btn-clear">Clear Search</a>
                            </div>
                        <?php endif; ?>

                        <!-- Table Data -->
                        <div class="row mt-5">
                            <table class="table table-dark table-striped">
                                <thead>
                                  <tr>
                                    <th scope="col">Driver Name</th>
                                    <th scope="col">Nationality</th>
                                    <th scope="col">URL</th>
                                  </tr>
                                </thead>
                                <tbody>
                                <?php
                                    foreach ($result as $row) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['forename']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['nationality']) . "</td>";
                                        echo "<td><a href='" . htmlspecialchars($row['url']) . "' target='_blank'>View More</a></td>";
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

    // Display "Previous" button
    if ($current_page > 1) {
        echo '<a href="mydriver.php?page=' . ($current_page - 1) . '" class="button-7">Previous</a>';
    } else {
        echo '<span class="disabled">Previous</span>';
    }

    // Display page numbers
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            echo '<a href="#" class="current-page">' . $i . '</a>'; // Active page
        } else {
            echo '<a href="mydriver.php?page=' . $i . '">' . $i . '</a>';
        }
    }

    // Display "Next" button
    if ($current_page < $total_pages) {
        echo '<a href="mydriver.php?page=' . ($current_page + 1) . '" class="button-7">Next</a>';
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
