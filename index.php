<?php
include 'db/db.php';
include 'process.php';

// Define how many results you want per page
$results_per_page = 10;

// Determine which page number visitor is currently on
$current_page = isset($_GET['page']) ? $_GET['page'] : 1;

// Determine the SQL LIMIT starting number for the results on the displaying page
$start_from = ($current_page - 1) * $results_per_page;

// SQL query to fetch constructor data with LIMIT for pagination
$sql = "SELECT constructor_name, no_of_pole_positions, no_of_titles, constructor_points, nationality, url 
        FROM constructors 
        ORDER BY constructor_points DESC 
        LIMIT $start_from, $results_per_page";
$result = $conn->query($sql);

// Find out the total number of pages
$total_sql = "SELECT COUNT(*) AS total FROM constructors";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_pages = ceil($total_row["total"] / $results_per_page);

// SQL query to fetch top 5 drivers based on points
$top_drivers_sql = "SELECT d.forename, ds.points 
                    FROM drivers d 
                    JOIN driver_standings ds ON d.driverId = ds.driverId 
                    ORDER BY ds.points DESC 
                    LIMIT 5";
$top_drivers_result = $conn->query($top_drivers_sql);

// SQL query to fetch top 5 constructors based on points
$top_constructors_sql = "SELECT constructor_name, constructor_points 
                         FROM constructors 
                         ORDER BY constructor_points DESC 
                         LIMIT 5";
$top_constructors_result = $conn->query($top_constructors_sql);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Formula1 - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />  
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>
<div class="wrapper">
<section id="main">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 p-0 full">
                <?php include("side.php"); ?>
            </div>
            <div class="col-md-10 p-0 " >
                <div class="submain">
                    <div class="container py-4">
                    <div class="col-md-12 p-0">

                    <div class="swiper-container-wrapper">
                    <div class="swiper-container" style="overflow: hidden;">
                    <div class="swiper-wrapper">
  <div class="swiper-slide">
    <img src="assets/images/F1Pics/F1Pic6.jpg" alt="Image 1" style="width: 100%; height: auto;" class="slide-image" />
    <div class="overlay-text" style="font-family: 'Formula1Bold';color: white;">
      Welcome to the World of Formula 1
    </div>
  </div>

  <div class="swiper-slide">
    <img src="assets/images/F1Pics/F1Pic4.jpg" alt="Image 3" style="width: 100%; height: auto;" class="slide-image" />
    <div class="overlay-text" style="font-family: 'Formula1Bold'; color: white;">
      Where Legends Are Made
    </div>
  </div>

  <div class="swiper-slide">
    <img src="assets/images/F1Pics/F1Pic5.jpg" alt="Image 4" style="width: 100%; height: auto;" class="slide-image" />
    <div class="overlay-text" style="font-family: 'Formula1Regular'; color: white;">
      If you no longer go for a gap that exists, you're no longer a racing driver.
    </div>
  </div>

  <div class="swiper-slide">
    <img src="assets/images/F1Pics/F1Pic1.jpg" alt="Image 5" style="width: 100%; height: auto;" class="slide-image" />
    <div class="overlay-text" style="font-family: 'Formula1Regular';  color: white;">
      Speed and Thrill Awaits
    </div>
  </div>

  <div class="swiper-slide">
    <img src="assets/images/F1Pics/F1Pic2.jpg" alt="Image 2" style="width: 100%; height: auto;" class="slide-image" />
    <div class="overlay-text" style="font-family: 'Formula1Bold'; color: white;">
      Meet the Drivers
    </div>
  </div>
</div>

    <!-- <div class="swiper-pagination"></div> -->
</div>
</div>
                <!-- Top 5 Drivers Section -->
                <div class="row mt-5">
                    <div class="head">
                        <h2 class="mb-4" style="font-size:24px">Top 5 Drivers</h2>
                    </div>
                    
                    <!-- Driver Table -->
                    <div class="col-md-6">
                        <table class="table table-dark table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">Driver Name</th>
                                    <th scope="col">Total Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($top_drivers_result->num_rows > 0) {
                                    while ($driver = $top_drivers_result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $driver['forename'] . "</td>";
                                        echo "<td>" . $driver['points'] . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='2'>No data available</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Driver Graph -->
                    <div class="col-md-6">
                        <?php include 'charts/top_drivers_chart.php'; ?>
                    </div>
                </div>


                <!-- Top 5 Constructors Section -->
                <div class="row mt-5">
                    <div class="head">
                        <h2 class="mb-4" style="font-size:24px">Top 5 Constructors</h2>
                    </div>
                    
                    <!-- Constructors Table -->
                    <div class="col-md-6">
                        <table class="table table-dark table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">Constructor Name</th>
                                    <th scope="col">Total Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($top_constructors_result->num_rows > 0) {
                                    while ($constructor = $top_constructors_result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $constructor['constructor_name'] . "</td>";
                                        echo "<td>" . $constructor['constructor_points'] . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='2'>No data available</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Constructors Graph -->
                    <div class="col-md-6">
                        <?php include 'charts/top_constructors_chart.php'; ?>
                    </div>
                </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script>
    const swiper = new Swiper('.swiper-container', {
        loop: true, // Enable looping of slides
        autoplay: {
            delay: 2000, // Delay between transitions (2 seconds)
            disableOnInteraction: false, // Autoplay continues after interaction
        },
        pagination: {
            el: '.swiper-pagination', // Element for pagination
            clickable: true, // Make pagination clickable
        },
        // Remove navigation since we don't want arrows
        navigation: false,
    });
</script>

</body>
</html>
