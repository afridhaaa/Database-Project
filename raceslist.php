<?php
include 'db/db.php';  // Include your database connection

// Set the number of results per page
$results_per_page = 10;

// Determine the total number of records for pagination
$total_result = $db->results->aggregate([
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
    ['$lookup' => [
        'from' => 'drivers',
        'localField' => 'driverId',
        'foreignField' => 'driverId',
        'as' => 'driver_details'
    ]],
    ['$match' => [
        'race_details.year' => 2015,
        'position' => 1
    ]],
    ['$count' => 'total']
]);

$total_row = iterator_to_array($total_result)[0];
$total_pages = ceil($total_row['total'] / $results_per_page);

// Get the current page number from URL, if not set, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($current_page - 1) * $results_per_page;

// MongoDB query to fetch race name, circuit name, and winning driver for 2015 races with limit
$pipeline = [
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
    ['$lookup' => [
        'from' => 'drivers',
        'localField' => 'driverId',
        'foreignField' => 'driverId',
        'as' => 'driver_details'
    ]],
    ['$unwind' => '$race_details'],
    ['$unwind' => '$circuit_details'],
    ['$unwind' => '$driver_details'],
    ['$match' => [
        'race_details.year' => 2015,
        'position' => 1
    ]],
    ['$sort' => ['race_details.date' => 1]],
    ['$skip' => $start_from],
    ['$limit' => $results_per_page],
    ['$project' => [
        'race_name' => '$race_details.name',
        'circuit_name' => '$circuit_details.circuit_name',
        'winner' => '$driver_details.forename'
    ]]
];

$result = $db->results->aggregate($pipeline);

?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>races list</title>
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
                           <!-- Back Button -->
    <div class="back-button" style="margin: 20px;">
        <a href="index.php" class="button-8">← Back to Home</a>
    </div>
                            <div class="head">
     <h2>Race Winners with specific year</h2>
                            </div>
                        </div>
                        
                       

                        <div class="row mt-5">
                              <table class="table table-dark table-striped">
                                  <thead>
                                      <tr>
                                          <th>Race Name</th>
                                          <th>Circuit Name</th>
                                          <th>Winner</th>
                                      </tr>
                                  </thead>
                                  <tbody>
                                      <?php
                                      foreach ($result as $row) {
                                          echo "<tr>";
                                          echo "<td>" . $row['race_name'] . "</td>";
                                          echo "<td>" . $row['circuit_name'] . "</td>";
                                          echo "<td>" . $row['winner'] . "</td>";
                                          echo "</tr>";
                                      }
                                      ?>
                                  </tbody>
                              </table>
                          </div>

    <!-- Pagination Controls with Page Numbers -->
    <<div class="pagination">
    <?php
    // Ensure $current_page is an integer
    $current_page = isset($_GET['page']) && is_numeric($_GET['page']) 
        ? intval($_GET['page']) 
        : 1;

    // Ensure $total_pages is at least 1 to avoid errors
    $total_pages = isset($total_pages) && $total_pages > 0 
        ? intval($total_pages) 
        : 1;

    // Set default values for search and sort order
    $search_keyword = isset($_GET['search']) ? $_GET['search'] : '';
    $sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'asc';

    // Previous button
    if ($current_page > 1) {
        echo '<a href="raceslist.php?page=' . ($current_page - 1) . '&search=' . urlencode($search_keyword) . '&sort_order=' . $sort_order . '" class="button-7">Previous</a>';
    } else {
        echo '<span class="disabled">Previous</span>';
    }

    // Page number buttons
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            echo '<span class="current-page">' . $i . '</span>'; // Current page
        } else {
            echo '<a href="raceslist.php?page=' . $i . '&search=' . urlencode($search_keyword) . '&sort_order=' . $sort_order . '">' . $i . '</a>';
        }
    }

    // Next button
    if ($current_page < $total_pages) {
        echo '<a href="raceslist.php?page=' . ($current_page + 1) . '&search=' . urlencode($search_keyword) . '&sort_order=' . $sort_order . '" class="button-7">Next</a>';
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
