<?php
include 'db/db.php';  // Include your database connection

// Set the number of results per page
$results_per_page = 10;

// Initialize search and sorting variables
$driver_name = isset($_GET['driver_name']) ? $_GET['driver_name'] : '';
$constructor_name = isset($_GET['constructor_name']) ? $_GET['constructor_name'] : '';
$circuit_name = isset($_GET['circuit_name']) ? $_GET['circuit_name'] : '';
$race_name = isset($_GET['race_name']) ? $_GET['race_name'] : '';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'starting_position'; // Default sort by starting position

// Build the SQL query for total results considering search criteria
$sql_total = "SELECT COUNT(*) AS total 
              FROM results rs 
              INNER JOIN races r ON rs.raceId = r.raceId 
              INNER JOIN constructors c ON rs.constructorId = c.constructor_id 
              INNER JOIN drivers d ON rs.driverId = d.driverId 
              WHERE rs.position < rs.grid";

if (!empty($driver_name)) {
    $sql_total .= " AND d.forename LIKE '%" . $conn->real_escape_string($driver_name) . "%'";
}
if (!empty($constructor_name)) {
    $sql_total .= " AND c.constructor_name LIKE '%" . $conn->real_escape_string($constructor_name) . "%'";
}
if (!empty($circuit_name)) {
    $sql_total .= " AND r.circuit_name LIKE '%" . $conn->real_escape_string($circuit_name) . "%'";
}
if (!empty($race_name)) {
    $sql_total .= " AND r.name LIKE '%" . $conn->real_escape_string($race_name) . "%'";
}

$total_result = $conn->query($sql_total);
$total_row = $total_result->fetch_assoc();
$total_pages = ceil($total_row['total'] / $results_per_page);

// Get the current page number from URL, if not set, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the starting limit number for the query
$start_from = ($current_page - 1) * $results_per_page;

// SQL query to retrieve races considering search and sort criteria
$sql = "SELECT d.forename, r.name AS race_name, rs.grid AS starting_position, 
               rs.position AS final_position, c.constructor_name 
        FROM results rs 
        INNER JOIN races r ON rs.raceId = r.raceId 
        INNER JOIN constructors c ON rs.constructorId = c.constructor_id 
        INNER JOIN drivers d ON rs.driverId = d.driverId 
        WHERE rs.position < rs.grid";

if (!empty($driver_name)) {
    $sql .= " AND d.forename LIKE '%" . $conn->real_escape_string($driver_name) . "%'";
}
if (!empty($constructor_name)) {
    $sql .= " AND c.constructor_name LIKE '%" . $conn->real_escape_string($constructor_name) . "%'";
}
if (!empty($circuit_name)) {
    $sql .= " AND r.circuit_name LIKE '%" . $conn->real_escape_string($circuit_name) . "%'";
}
if (!empty($race_name)) {
    $sql .= " AND r.name LIKE '%" . $conn->real_escape_string($race_name) . "%'";
}

// Determine sorting column and order
if ($sort_by == 'starting_position') {
    $sql .= " ORDER BY rs.grid " . ($sort_order === 'DESC' ? 'DESC' : 'ASC');
} else if ($sort_by == 'final_position') {
    $sql .= " ORDER BY rs.position " . ($sort_order === 'DESC' ? 'DESC' : 'ASC');
} 

$sql .= " LIMIT $start_from, $results_per_page";

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
     <h2>Races where a driver finished in a higher position than their starting grid</h2>
                            </div>
                        </div>
                        
<div class="custom-filter-container">
    <form method="GET" action="retrieveraces.php" class="custom-filter-form">
        <!-- Sort Dropdown -->
        <div class="custom-form-group">
            <label for="sort_by" class="custom-label">Sort by:</label>
            <select name="sort_by" id="sort_by" class="custom-form-control" onchange="this.form.submit()">
                <option value="starting_position" <?php if ($sort_by == 'starting_position') echo 'selected'; ?>>Starting Position</option>
                <option value="final_position" <?php if ($sort_by == 'final_position') echo 'selected'; ?>>Final Position</option>
            </select>
        </div>

        <!-- Sort Order Dropdown -->
        <div class="custom-form-group">
            <label for="sort_order" class="custom-label">Sort Order:</label>
            <select name="sort_order" id="sort_order" class="custom-form-control" onchange="this.form.submit()">
                <option value="ASC" <?php if ($sort_order == 'ASC') echo 'selected'; ?>>Ascending</option>
                <option value="DESC" <?php if ($sort_order == 'DESC') echo 'selected'; ?>>Descending</option>
            </select>
        </div>

        <!-- Search Fields -->
        <div class="custom-form-group">
            <label for="driver_name" class="custom-label">Search by Driver Name:</label>
            <input type="text" name="driver_name" class="custom-form-control custom-search-bar2" placeholder="Driver Name" value="<?php echo htmlspecialchars($driver_name); ?>">
        </div>
        <div class="custom-form-group">
            <label for="constructor_name" class="custom-label">Search by Constructor Name:</label>
            <input type="text" name="constructor_name" class="custom-form-control custom-search-bar2" placeholder="Constructor Name" value="<?php echo htmlspecialchars($constructor_name); ?>">
        </div>
       
       

        <!-- Search Button -->
        <div class="custom-form-group">
            <button type="submit" class="custom-btn custom-btn-primary">Search</button>
        </div>
    </form>
</div>

<!-- Clear Search Button: Show only when search is applied -->
<?php if (!empty($driver_name) || !empty($constructor_name) || !empty($circuit_name) || !empty($race_name)) : ?>
    <div style="margin-bottom: 20px; margin-left: 30px;">
        <a href="retrieveraces.php" class="custom-btn custom-btn-clear">Clear Search</a>
    </div>
<?php endif; ?>




<div class="row mt-5">
    <table  class="table table-dark table-striped">
        <thead>
          <tr>
          <th>Driver Name</th>
                                <th>Race Name</th>
                                <th>Starting Position</th>
                                <th>Final Position</th>
                                <th>Constructor Name</th>
          </tr>
        </thead>
        <tbody>
        <?php
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['forename']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['race_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['starting_position']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['final_position']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['constructor_name']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No data available</td></tr>";
                            }
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
                        echo '<a href="retrieveraces.php?page=' . ($current_page - 1) . '&driver_name=' . urlencode($driver_name) . '&constructor_name=' . urlencode($constructor_name) . '&circuit_name=' . urlencode($circuit_name) . '&race_name=' . urlencode($race_name) . '&sort_by=' . $sort_by . '&sort_order=' . $sort_order . '" class="button-7">Previous</a>';
                    } else {
                        echo '<span class="disabled">Prev</span>';
                    }
                    
                    // Page numbers
                    for ($i = 1; $i <= $total_pages; $i++) {
                        if ($i == $current_page) {
                            echo '<a href="#" class="active">' . $i . '</a>';
                        } else {
                            echo '<a href="retrieveraces.php?page=' . $i . '&driver_name=' . urlencode($driver_name) . '&constructor_name=' . urlencode($constructor_name) . '&circuit_name=' . urlencode($circuit_name) . '&race_name=' . urlencode($race_name) . '&sort_by=' . $sort_by . '&sort_order=' . $sort_order . '">' . $i . '</a>';
                        }
                    }

                    // Next button
                    if ($current_page < $total_pages) {
                        echo '<a href="retrieveraces.php?page=' . ($current_page + 1) . '&driver_name=' . urlencode($driver_name) . '&constructor_name=' . urlencode($constructor_name) . '&circuit_name=' . urlencode($circuit_name) . '&race_name=' . urlencode($race_name) . '&sort_by=' . $sort_by . '&sort_order=' . $sort_order . '" class="button-7">Next</a>';
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
