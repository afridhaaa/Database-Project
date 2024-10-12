<?php
include 'db/db.php';  // Include your database connection

// Set the number of results per page
$results_per_page = 10;

// Get search input values
$driver_name = isset($_GET['driver_name']) ? $_GET['driver_name'] : '';
$circuit_name = isset($_GET['circuit_name']) ? $_GET['circuit_name'] : '';
$race_name = isset($_GET['race_name']) ? $_GET['race_name'] : '';

// Get the sort order from the URL or set a default
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';

// Determine the total number of pages available
$sql_total = "SELECT COUNT(*) AS total FROM results rs
              INNER JOIN races r ON rs.raceId = r.race_id
              INNER JOIN circuits c ON r.circuit_id = c.circuit_id
              INNER JOIN drivers d ON rs.driverId = d.driverId
              WHERE rs.position = 1
              AND d.forename LIKE '%$driver_name%'
              AND c.circuit_name LIKE '%$circuit_name%'
              AND r.name LIKE '%$race_name%'";

$total_result = $conn->query($sql_total);
$total_row = $total_result->fetch_assoc();
$total_pages = ceil($total_row['total'] / $results_per_page);

// Get the current page number from URL, if not set, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the starting limit number for the query
$start_from = ($current_page - 1) * $results_per_page;

// SQL query to get races won by drivers with circuit names, with pagination and sorting
$sql = "SELECT d.forename, r.name AS race_name, c.circuit_name
        FROM results rs
        INNER JOIN races r ON rs.raceId = r.race_id
        INNER JOIN circuits c ON r.circuit_id = c.circuit_id
        INNER JOIN drivers d ON rs.driverId = d.driverId
        WHERE rs.position = 1
        AND d.forename LIKE '%$driver_name%'
        AND c.circuit_name LIKE '%$circuit_name%'
        AND r.name LIKE '%$race_name%'
        ORDER BY d.forename $sort_order, r.date
        LIMIT $start_from, $results_per_page";

$result = $conn->query($sql);
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
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
                           <!-- Back Button -->
    <div class="back-button" style="margin: 20px;">
        <a href="index.php" class="button-8">← Back to Home</a>
    </div>
                            <div class="head">
                                <h2>List of races won by each driver along with the circuit name</h2>
                            </div>
                        </div>
                       <!-- Sort and Search Form -->
<div class="custom-filter-container">
                    <form method="GET" action="raceswon.php" class="custom-filter-form">
                        <!-- Sort Dropdown -->
                        <div class="custom-form-group">
                            <label for="sort_order" class="custom-label">Sort by Driver Name:</label>
                            <select name="sort_order" id="sort_order" class="custom-form-control" onchange="this.form.submit()">
                                <option value="ASC" <?php if ($sort_order == 'ASC') echo 'selected'; ?>>A to Z</option>
                                <option value="DESC" <?php if ($sort_order == 'DESC') echo 'selected'; ?>>Z to A</option>
                            </select>
                        </div>

                        <!-- Search Bars -->
                        <div class="custom-form-group">
                            <label for="driver_name" class="custom-label">Search by Driver Name:</label>
                            <input type="text" name="driver_name" class="custom-form-control custom-search-bar2" placeholder="Driver Name" value="<?php echo htmlspecialchars($driver_name); ?>">
                        </div>
                        <div class="custom-form-group">
                            <label for="circuit_name" class="custom-label">Search by Circuit Name:</label>
                            <input type="text" name="circuit_name" class="custom-form-control custom-search-bar2" placeholder="Circuit Name" value="<?php echo htmlspecialchars($circuit_name); ?>">
                        </div>
                        <div class="custom-form-group">
                            <label for="race_name" class="custom-label">Search by Race Name:</label>
                            <input type="text" name="race_name" class="custom-form-control custom-search-bar2" placeholder="Race Name" value="<?php echo htmlspecialchars($race_name); ?>">
                        </div>

                        <!-- Search Button -->
                        <div class="custom-form-group">
                            <button type="submit" class="custom-btn custom-btn-primary">Search</button>
                        </div>
                    </form>
                </div>

                <!-- Clear Search Button: Show only when search is applied -->
                <?php if (!empty($driver_name) || !empty($circuit_name) || !empty($race_name)) : ?>
                    <div style="margin-bottom: 20px; margin-left: 30px;">
                        <a href="raceswon.php" class="custom-btn custom-btn-clear">Clear Search</a>
                    </div>
                <?php endif; ?>



<div class="row mt-5">
    <table  class="table table-dark table-striped">
        <thead>
          <tr>
          <th>Driver Name</th>
                                    <th>Race Name</th>
                                    <th>Circuit Name</th>
          </tr>
        </thead>
        <tbody>
        <?php
                                if ($result->num_rows > 0) {
                                    // Output each row of data
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['forename']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['race_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['circuit_name']) . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3'>No data available</td></tr>";
                                }
                                $conn->close();
                                ?>
                            </tbody>
        </tbody>
      </table>
      
</div>

<!-- Pagination Controls -->
<div class="pagination">
                <?php
                // Previous button
                if ($current_page > 1) {
                    echo '<a href="raceswon.php?page=' . ($current_page - 1) . '" class="button-7">Previous</a>';
                } else {
                    echo '<span class="disabled">Previous</span>';
                }

                // Page numbers
                for ($i = 1; $i <= $total_pages; $i++) {
                    if ($i == $current_page) {
                        echo '<a href="#" class="active">' . $i . '</a>'; // Active page
                    } else {
                        echo '<a href="raceswon.php?page=' . $i . '">' . $i . '</a>';
                    }
                }

                // Next button
                if ($current_page < $total_pages) {
                    echo '<a href="raceswon.php?page=' . ($current_page + 1) . '" class="button-7">Next</a>';
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
