<?php
include 'db/db.php';  // Include your database connection

// Set the number of results per page
$results_per_page = 10;

// Get the current page number from URL, if not set, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the skip for pagination
$skip = ($current_page - 1) * $results_per_page;

// MongoDB aggregation pipeline
$pipeline = [
    [
        '$lookup' => [
            'from' => 'drivers',
            'localField' => 'driverId',
            'foreignField' => 'driverId',
            'as' => 'driver_info'
        ]
    ],
    ['$unwind' => '$driver_info'],
    [
        '$lookup' => [
            'from' => 'constructors',
            'localField' => 'constructorId',
            'foreignField' => 'constructor_id',
            'as' => 'constructor_info'
        ]
    ],
    ['$unwind' => '$constructor_info'],
    [
        '$lookup' => [
            'from' => 'races',
            'localField' => 'raceId',
            'foreignField' => 'raceId',
            'as' => 'race_info'
        ]
    ],
    ['$unwind' => '$race_info'],
    [
        '$lookup' => [
            'from' => 'circuits',
            'localField' => 'race_info.circuit_id',
            'foreignField' => 'circuit_id',
            'as' => 'circuit_info'
        ]
    ],
    ['$unwind' => '$circuit_info'],
    [
        '$group' => [
            '_id' => [
                'forename' => '$driver_info.forename',
                'constructor_name' => '$constructor_info.constructor_name',
                'circuit_name' => '$circuit_info.circuit_name'
            ],
            'total_points' => ['$sum' => '$points']
        ]
    ],
    [
        '$match' => [
            'total_points' => ['$gt' => 50]
        ]
    ],
    ['$sort' => ['total_points' => -1]],
    ['$skip' => $skip],
    ['$limit' => $results_per_page]
];

// Execute the query
$results = $db->results->aggregate($pipeline)->toArray();

// Calculate total records for pagination
$total_pipeline = [
    [
        '$lookup' => [
            'from' => 'drivers',
            'localField' => 'driverId',
            'foreignField' => 'driverId',
            'as' => 'driver_info'
        ]
    ],
    ['$unwind' => '$driver_info'],
    [
        '$lookup' => [
            'from' => 'constructors',
            'localField' => 'constructorId',
            'foreignField' => 'constructor_id',
            'as' => 'constructor_info'
        ]
    ],
    ['$unwind' => '$constructor_info'],
    [
        '$lookup' => [
            'from' => 'races',
            'localField' => 'raceId',
            'foreignField' => 'raceId',
            'as' => 'race_info'
        ]
    ],
    ['$unwind' => '$race_info'],
    [
        '$lookup' => [
            'from' => 'circuits',
            'localField' => 'race_info.circuit_id',
            'foreignField' => 'circuit_id',
            'as' => 'circuit_info'
        ]
    ],
    ['$unwind' => '$circuit_info'],
    [
        '$group' => [
            '_id' => [
                'forename' => '$driver_info.forename',
                'constructor_name' => '$constructor_info.constructor_name',
                'circuit_name' => '$circuit_info.circuit_name'
            ],
            'total_points' => ['$sum' => '$points']
        ]
    ],
    [
        '$match' => [
            'total_points' => ['$gt' => 50]
        ]
    ],
    ['$count' => 'total']
];

$total_count_result = $db->results->aggregate($total_pipeline)->toArray();
$total_records = $total_count_result[0]['total'] ?? 0;
$total_pages = ceil($total_records / $results_per_page);
?>


<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Formula Vault - Drivers</title>
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
     <h2>Drivers with Total Points Above 50</h2>
                            </div>
                        </div>
                        
                        

                        <div class="row mt-5">
                               <table class="table table-dark table-striped">
                                   <thead>
                                       <tr>
                                           <th>Driver Name</th>
                                           <th>Constructor Name</th>
                                           <th>Circuit Name</th>
                                           <th>Total Points</th>
                                       </tr>
                                   </thead>
                                   <tbody>
                                       <?php if (empty($results)) : ?>
                                           <tr><td colspan="4" style="text-align:center;">No data available</td></tr>
                                       <?php else : ?>
                                           <?php foreach ($results as $row): ?>
                                               <tr>
                                                   <td><?php echo htmlspecialchars($row['_id']['forename']); ?></td>
                                                   <td><?php echo htmlspecialchars($row['_id']['constructor_name']); ?></td>
                                                   <td><?php echo htmlspecialchars($row['_id']['circuit_name']); ?></td>
                                                   <td><?php echo htmlspecialchars($row['total_points']); ?></td>
                                               </tr>
                                           <?php endforeach; ?>
                                       <?php endif; ?>
                                   </tbody>
                               </table>
                           </div>

    <!-- Pagination Controls -->
    <div class="pagination">
                               <?php
                               $current_page = max(1, min($current_page, $total_pages));
                               $links_to_show = 5;
                               $start_page = max(1, $current_page - floor($links_to_show / 2));
                               $end_page = min($total_pages, $start_page + $links_to_show - 1);

                               if ($current_page > 1) {
                                   echo '<a href="retrievedrivers.php?page=' . ($current_page - 1) . '" class="button-7">Previous</a>';
                               } else {
                                   echo '<span class="disabled">Previous</span>';
                               }

                               for ($i = $start_page; $i <= $end_page; $i++) {
                                   if ($i == $current_page) {
                                       echo '<a href="#" class="current-page">' . $i . '</a>';
                                   } else {
                                       echo '<a href="retrievedrivers.php?page=' . $i . '">' . $i . '</a>';
                                   }
                               }

                               if ($current_page < $total_pages) {
                                   echo '<a href="retrievedrivers.php?page=' . ($current_page + 1) . '" class="button-7">Next</a>';
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
