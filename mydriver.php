<?php
include 'db/db.php';
include 'process.php';

// Define how many results you want per page
$results_per_page = 10;

// Determine which page number visitor is currently on
if (isset($_GET['page'])) {
    $current_page = $_GET['page'];
} else {
    $current_page = 1;
}

// Determine the SQL LIMIT starting number for the results on the displaying page
$start_from = ($current_page - 1) * $results_per_page;

// Handle sorting
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';

// Handle search
$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';

// SQL query to fetch drivers data with search, sort, and pagination
$sql = "SELECT forename, nationality, url 
        FROM drivers 
        WHERE forename LIKE '%$search_keyword%' OR nationality LIKE '%$search_keyword%' 
        ORDER BY forename $sort_order 
        LIMIT $start_from, $results_per_page";
$result = $conn->query($sql);

// Find out the total number of pages
$total_sql = "SELECT COUNT(*) AS total 
              FROM drivers 
              WHERE forename LIKE '%$search_keyword%' OR nationality LIKE '%$search_keyword%'";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_pages = ceil($total_row["total"] / $results_per_page);

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
                <div class="heading">
                  <a href="index.php">  <h4>Formula1</h4></a>
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
                                        <option value="ASC" <?php if ($sort_order == 'ASC') echo 'selected'; ?>>A to Z</option>
                                        <option value="DESC" <?php if ($sort_order == 'DESC') echo 'selected'; ?>>Z to A</option>
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
                                    <th scope="col">Url</th>
                                  </tr>
                                </thead>
                                <tbody>
                                <?php
                                    if ($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . $row['forename'] . "</td>";
                                            echo "<td>" . $row['nationality'] . "</td>";
                                            echo "<td><a href='" . $row['url'] . "' target='_blank'>View More</a></td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='3'>No data available</td></tr>";
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
                                echo '<a href="mydriver.php?page=' . ($current_page - 1) . '" class="button-7">Previous</a>';
                            } else {
                                echo '<span class="disabled">Previous</span>';
                            }

                            // Page numbers
                            for ($i = 1; $i <= $total_pages; $i++) {
                                if ($i == $current_page) {
                                    echo '<a href="#" class="active">' . $i . '</a>'; // Active page
                                } else {
                                    echo '<a href="mydriver.php?page=' . $i . '">' . $i . '</a>';
                                }
                            }

                            // Next button
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
