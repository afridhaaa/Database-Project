<?php
// Start output buffering to prevent headers already sent errors
ob_start();

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
include 'db/db.php'; // Include the database connection

// Pagination settings
$results_per_page = 10; // Number of results per page

// Find out the number of results stored in the database
$sql = "SELECT COUNT(resultId) AS total FROM results";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_results = $row['total'];

// Determine the number of total pages available
$total_pages = ceil($total_results / $results_per_page);

// Determine which page number the visitor is currently on
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;

// Determine the starting limit number
$start_limit = ($page - 1) * $results_per_page;

// Fetch the selected results from the database
$sql = "SELECT * FROM results LIMIT " . $start_limit . ", " . $results_per_page;
$result = $conn->query($sql);

// Create Result Record
if (isset($_POST['create'])) {
    $raceId = $_POST['raceId'] ?? '';
    $driverId = $_POST['driverId'] ?? '';
    $constructorId = $_POST['constructorId'] ?? '';
    $number = $_POST['number'] ?? '';
    $grid = $_POST['grid'] ?? '';
    $position = $_POST['position'] ?? '';
    $positionOrder = $_POST['positionOrder'] ?? '';
    $points = $_POST['points'] ?? '';
    $laps = $_POST['laps'] ?? '';
    $time = $_POST['time'] ?? '';
    $milliseconds = $_POST['milliseconds'] ?? '';
    $fastestLap = $_POST['fastestLap'] ?? '';
    $rank = $_POST['rank'] ?? '';
    $fastestLapTime = $_POST['fastestLapTime'] ?? '';
    $fastestLapSpeed = $_POST['fastestLapSpeed'] ?? '';
    $statusId = $_POST['statusId'] ?? '';

    $sql = "INSERT INTO results (raceId, driverId, constructorId, number, grid, position, positionOrder, points, laps, time, milliseconds, fastestLap, `rank`, fastestLapTime, fastestLapSpeed, statusId)
            VALUES ('$raceId', '$driverId', '$constructorId', '$number', '$grid', '$position', '$positionOrder', '$points', '$laps', '$time', '$milliseconds', '$fastestLap', '$rank', '$fastestLapTime', '$fastestLapSpeed', '$statusId')";

    $conn->query($sql);

    header("Location: crud_results.php");
    exit();
}


// Edit Result Record
if (isset($_POST['edit'])) {
    $resultId = $_POST['resultId'];
    $raceId = $_POST['raceId'] ?? '';
    $driverId = $_POST['driverId'] ?? '';
    $constructorId = $_POST['constructorId'] ?? '';
    $number = $_POST['number'] ?? '';
    $grid = $_POST['grid'] ?? '';
    $position = $_POST['position'] ?? '';
    $positionOrder = $_POST['positionOrder'] ?? '';
    $points = $_POST['points'] ?? '';
    $laps = $_POST['laps'] ?? '';
    $time = $_POST['time'] ?? '';
    $milliseconds = $_POST['milliseconds'] ?? '';
    $fastestLap = $_POST['fastestLap'] ?? '';
    $rank = $_POST['rank'] ?? '';
    $fastestLapTime = $_POST['fastestLapTime'] ?? '';
    $fastestLapSpeed = $_POST['fastestLapSpeed'] ?? '';
    $statusId = $_POST['statusId'] ?? '';

    $sql = "UPDATE results SET 
                raceId='$raceId', 
                driverId='$driverId', 
                constructorId='$constructorId', 
                number='$number', 
                grid='$grid', 
                position='$position', 
                positionOrder='$positionOrder', 
                points='$points', 
                laps='$laps', 
                time='$time', 
                milliseconds='$milliseconds', 
                fastestLap='$fastestLap', 
                `rank`='$rank', 
                fastestLapTime='$fastestLapTime', 
                fastestLapSpeed='$fastestLapSpeed', 
                statusId='$statusId' 
            WHERE resultId='$resultId'";

    $conn->query($sql);

    header("Location: crud_results.php");
    exit();
}

// Delete Result Record
if (isset($_GET['delete'])) {
    $resultId = $_GET['delete'];
    $sql = "DELETE FROM results WHERE resultId='$resultId'";
    $conn->query($sql);

    header("Location: crud_results.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/sidebar.css">
</head>
<body>

<div class="container-fluid h-100">
    <div class="row h-100 g-0">
        <div class="col-md-0 p-0">
            <?php include("sidebar.php"); ?>
        </div>

        <div class="col-md-10 p-4">
            <h1>Manage Results</h1>
            <p>Create, view, edit, or delete race results here.</p>

            <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#createResultModal">
                <i class="fas fa-plus"></i> Create New Result
            </button>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                    <th>ID</th>
            <th>Race ID</th>
            <th>Driver ID</th>
            <th>Constructor ID</th>
            <th>Number</th>
            <th>Grid</th>
            <th>Position</th>
            <th>Position Order</th>
            <th>Points</th>
            <th>Laps</th>
            <th>Time</th>
            <th>Milliseconds</th>
            <th>Fastest Lap</th>
            <th>Rank</th>
            <th>Fastest Lap Time</th>
            <th>Fastest Lap Speed</th>
            <th>Status ID</th>
            <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>{$row['resultId']}</td>";
                            echo "<td>{$row['raceId']}</td>";
                            echo "<td>{$row['driverId']}</td>";
                            echo "<td>{$row['constructorId']}</td>";
                            echo "<td>{$row['number']}</td>";
                            echo "<td>{$row['grid']}</td>";
                            echo "<td>{$row['position']}</td>";
                            echo "<td>{$row['positionOrder']}</td>";
                            echo "<td>{$row['points']}</td>";
                            echo "<td>{$row['laps']}</td>";
                echo "<td>{$row['time']}</td>";
                echo "<td>{$row['milliseconds']}</td>";
                echo "<td>{$row['fastestLap']}</td>";
                echo "<td>{$row['rank']}</td>";
                echo "<td>{$row['fastestLapTime']}</td>";
                echo "<td>{$row['fastestLapSpeed']}</td>";
                echo "<td>{$row['statusId']}</td>";
                            echo "<td>
                                    <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editResultModal{$row['resultId']}'><i class='fas fa-edit'></i></button>
                                    <a href='?delete={$row['resultId']}' class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></a>
                                  </td>";
                            echo "</tr>";

                            // Edit Result Modal
    echo "
    <div class='modal fade' id='editResultModal{$row['resultId']}' tabindex='-1' aria-labelledby='editResultLabel' aria-hidden='true'>
        <div class='modal-dialog'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <h5 class='modal-title' id='editResultLabel'>Edit Result</h5>
                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                </div>
                <form action='' method='POST'>
                    <div class='modal-body'>
                        <input type='hidden' name='resultId' value='{$row['resultId']}'>
                        <div class='mb-3'>
                            <label for='raceId' class='form-label'>Race ID</label>
                            <input type='number' class='form-control' name='raceId' value='{$row['raceId']}' required>
                        </div>
                        <div class='mb-3'>
                            <label for='driverId' class='form-label'>Driver ID</label>
                            <input type='number' class='form-control' name='driverId' value='{$row['driverId']}' required>
                        </div>
                        <div class='mb-3'>
                            <label for='constructorId' class='form-label'>Constructor ID</label>
                            <input type='number' class='form-control' name='constructorId' value='{$row['constructorId']}' required>
                        </div>
                        <div class='mb-3'>
                            <label for='number' class='form-label'>Number</label>
                            <input type='number' class='form-control' name='number' value='{$row['number']}'>
                        </div>
                        <div class='mb-3'>
                            <label for='grid' class='form-label'>Grid</label>
                            <input type='number' class='form-control' name='grid' value='{$row['grid']}'>
                        </div>
                        <div class='mb-3'>
                            <label for='position' class='form-label'>Position</label>
                            <input type='text' class='form-control' name='position' value='{$row['position']}'>
                        </div>
                        <div class='mb-3'>
                            <label for='positionOrder' class='form-label'>Position Order</label>
                            <input type='number' class='form-control' name='positionOrder' value='{$row['positionOrder']}'>
                        </div>
                        <div class='mb-3'>
                            <label for='points' class='form-label'>Points</label>
                            <input type='number' step='0.1' class='form-control' name='points' value='{$row['points']}'>
                        </div>
                        <div class='mb-3'>
                            <label for='laps' class='form-label'>Laps</label>
                            <input type='number' class='form-control' name='laps' value='{$row['laps']}'>
                        </div>
                        <div class='mb-3'>
                            <label for='time' class='form-label'>Time</label>
                            <input type='text' class='form-control' name='time' value='{$row['time']}'>
                        </div>
                        <div class='mb-3'>
                            <label for='milliseconds' class='form-label'>Milliseconds</label>
                            <input type='number' class='form-control' name='milliseconds' value='{$row['milliseconds']}'>
                        </div>
                        <div class='mb-3'>
                            <label for='fastestLap' class='form-label'>Fastest Lap</label>
                            <input type='number' class='form-control' name='fastestLap' value='{$row['fastestLap']}'>
                        </div>
                        <div class='mb-3'>
                            <label for='rank' class='form-label'>Rank</label>
                            <input type='number' class='form-control' name='rank' value='{$row['rank']}'>
                        </div>
                        <div class='mb-3'>
                            <label for='fastestLapTime' class='form-label'>Fastest Lap Time</label>
                            <input type='text' class='form-control' name='fastestLapTime' value='{$row['fastestLapTime']}'>
                        </div>
                        <div class='mb-3'>
                            <label for='fastestLapSpeed' class='form-label'>Fastest Lap Speed</label>
                            <input type='number' step='0.001' class='form-control' name='fastestLapSpeed' value='{$row['fastestLapSpeed']}'>
                        </div>
                        <div class='mb-3'>
                            <label for='statusId' class='form-label'>Status ID</label>
                            <input type='number' class='form-control' name='statusId' value='{$row['statusId']}'>
                        </div>
                    </div>
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                        <button type='submit' class='btn btn-primary' name='edit'>Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>";
                        }
                    } else {
                        echo "<tr><td colspan='8'>No results found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<nav>
                <ul class="pagination justify-content-center">
                    <?php
                    $adjacents = 7;
                    $start = max(1, $page - $adjacents);
                    $end = min($total_pages, $page + $adjacents);

                    if ($page > 1) {
                        echo "<li class='page-item'><a class='page-link' href='crud_results.php?page=1'>First</a></li>";
                        echo "<li class='page-item'><a class='page-link' href='crud_results.php?page=" . ($page - 1) . "'>&laquo; Prev</a></li>";
                    }

                    for ($i = $start; $i <= $end; $i++) {
                        $active = ($i == $page) ? 'active' : '';
                        echo "<li class='page-item $active'><a class='page-link' href='crud_results.php?page=$i'>$i</a></li>";
                    }

                    if ($page < $total_pages) {
                        echo "<li class='page-item'><a class='page-link' href='crud_results.php?page=" . ($page + 1) . "'>Next &raquo;</a></li>";
                        echo "<li class='page-item'><a class='page-link' href='crud_results.php?page=$total_pages'>Last</a></li>";
                    }
                    ?>
                </ul>
            </nav>

<!-- Create Result Modal -->
<div class="modal fade" id="createResultModal" tabindex="-1" aria-labelledby="createResultLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createResultLabel">Create New Result</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="mb-3"><label for="raceId">Race ID</label><input type="number" class="form-control" name="raceId" required></div>
                    <div class="mb-3"><label for="driverId">Driver ID</label><input type="number" class="form-control" name="driverId" required></div>
                    <div class="mb-3"><label for="constructorId">Constructor ID</label><input type="number" class="form-control" name="constructorId" required></div>
                    <div class="mb-3"><label for="number">Number</label><input type="number" class="form-control" name="number"></div>
                    <div class="mb-3"><label for="grid">Grid</label><input type="number" class="form-control" name="grid"></div>
                    <div class="mb-3"><label for="position">Position</label><input type="text" class="form-control" name="position"></div>
                    <div class="mb-3"><label for="positionOrder">Position Order</label><input type="number" class="form-control" name="positionOrder"></div>
                    <div class="mb-3"><label for="points">Points</label><input type="number" step="0.1" class="form-control" name="points"></div>
                    <div class="mb-3"><label for="laps">Laps</label><input type="number" class="form-control" name="laps"></div>
                    <div class="mb-3"><label for="time">Time</label><input type="text" class="form-control" name="time"></div>
                    <div class="mb-3"><label for="milliseconds">Milliseconds</label><input type="number" class="form-control" name="milliseconds"></div>
                    <div class="mb-3"><label for="fastestLap">Fastest Lap</label><input type="number" class="form-control" name="fastestLap"></div>
                    <div class="mb-3"><label for="rank">Rank</label><input type="number" class="form-control" name="rank"></div>
                    <div class="mb-3"><label for="fastestLapTime">Fastest Lap Time</label><input type="text" class="form-control" name="fastestLapTime"></div>
                    <div class="mb-3"><label for="fastestLapSpeed">Fastest Lap Speed</label><input type="number" step="0.001" class="form-control" name="fastestLapSpeed"></div>
                    <div class="mb-3"><label for="statusId">Status ID</label><input type="number" class="form-control" name="statusId"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="create">Create Result</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php ob_end_flush(); ?>
