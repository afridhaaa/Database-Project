<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
include 'db/db.php'; // Include the database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Formula Vault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/sidebar.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="container-fluid h-100">
    <div class="row h-100">
        <!-- Sidebar -->
        <div class="col-md-2 p-0 sidebar">
            <?php include("sidebar.php"); ?>
        </div>

        <!-- Main Content Area -->
        <div class="col-md-10 main-content">
            <h1>Admin Dashboard</h1></br>

            <div class="row">
                <!-- Stats Cards -->
                <div class="col-md-3">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-header">Total Drivers</div>
                        <div class="card-body">
                            <?php
                            $result = $conn->query("SELECT COUNT(*) AS total_drivers FROM drivers");
                            $data = $result->fetch_assoc();
                            echo "<h3>{$data['total_drivers']}</h3>";
                            ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-header">Total Races</div>
                        <div class="card-body">
                            <?php
                            $result = $conn->query("SELECT COUNT(*) AS total_races FROM races");
                            $data = $result->fetch_assoc();
                            echo "<h3>{$data['total_races']}</h3>";
                            ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card text-white bg-info mb-3">
                        <div class="card-header">Total Constructors</div>
                        <div class="card-body">
                            <?php
                            $result = $conn->query("SELECT COUNT(*) AS total_constructors FROM constructors");
                            $data = $result->fetch_assoc();
                            echo "<h3>{$data['total_constructors']}</h3>";
                            ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-header">Total Circuits</div>
                        <div class="card-body">
                            <?php
                            $result = $conn->query("SELECT COUNT(*) AS total_circuits FROM circuits");
                            $data = $result->fetch_assoc();
                            echo "<h3>{$data['total_circuits']}</h3>";
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Wins by Driver Chart -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">Wins by Driver</div>
                        <div class="card-body">
                            <?php include("charts/wins_by_driver.php"); ?>
                        </div>
                    </div>
                </div>

                <!-- Top Lap Times Chart -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">Top Lap Times</div>
                        <div class="card-body">
                            <?php include("charts/top_lap_times.php"); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Number of Races by Circuits Chart -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">Number of Races by Circuit</div>
                        <div class="card-body">
                            <?php include("charts/no_of_races_by_circuit.php"); ?>
                        </div>
                    </div>
                </div>

                <!-- Fastest Lap Speeds Chart -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">Fastest Lap Speeds</div>
                        <div class="card-body">
                            <?php include("charts/fastest_lap_speed.php"); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
