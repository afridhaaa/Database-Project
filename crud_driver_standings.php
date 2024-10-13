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
$sql = "SELECT COUNT(driverStandingsId) AS total FROM driver_standings";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_driver_standings = $row['total'];

// Determine the number of total pages available
$total_pages = ceil($total_driver_standings / $results_per_page);

// Determine which page number the visitor is currently on
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;

// Determine the starting limit number
$start_limit = ($page - 1) * $results_per_page;

// Fetch the selected results from the database
$sql = "SELECT * FROM driver_standings LIMIT " . $start_limit . ", " . $results_per_page;
$result = $conn->query($sql);

// Create Driver Standing
if (isset($_POST['create'])) {
    $raceId = $_POST['raceId'];
    $driverId = $_POST['driverId'];
    $points = $_POST['points'];
    $position = $_POST['position'];
    $wins = $_POST['wins'];

    $sql = "INSERT INTO driver_standings (raceId, driverId, points, position, wins) 
            VALUES ('$raceId', '$driverId', '$points', '$position', '$wins')";
    $conn->query($sql);

    header("Location: crud_driver_standings.php");
    exit();
}

// Edit Driver Standing
if (isset($_POST['edit'])) {
    $id = $_POST['driverStandingsId'];
    $raceId = $_POST['raceId'];
    $driverId = $_POST['driverId'];
    $points = $_POST['points'];
    $position = $_POST['position'];
    $wins = $_POST['wins'];

    $sql = "UPDATE driver_standings SET raceId='$raceId', driverId='$driverId', points='$points', 
            position='$position', wins='$wins' WHERE driverStandingsId='$id'";
    $conn->query($sql);

    header("Location: crud_driver_standings.php");
    exit();
}

// Delete Driver Standing
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM driver_standings WHERE driverStandingsId='$id'";
    $conn->query($sql);

    header("Location: crud_driver_standings.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Driver Standings</title>
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
            <h1>Manage Driver Standings</h1>
            <p>Create, view, edit, or delete driver standings records here.</p>

            <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#createDriverStandingModal">
                <i class="fas fa-plus"></i> Create New Standing
            </button>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Race ID</th>
                        <th>Driver ID</th>
                        <th>Points</th>
                        <th>Position</th>
                        <th>Wins</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>{$row['driverStandingsId']}</td>";
                            echo "<td>{$row['raceId']}</td>";
                            echo "<td>{$row['driverId']}</td>";
                            echo "<td>{$row['points']}</td>";
                            echo "<td>{$row['position']}</td>";
                            echo "<td>{$row['wins']}</td>";
                            echo "<td>
                                <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editDriverStandingModal{$row['driverStandingsId']}'><i class='fas fa-edit'></i></button>
                                <a href='?delete={$row['driverStandingsId']}' class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></a>
                              </td>";
                            echo "</tr>";

                            // Edit Modal
                            echo "
                            <div class='modal fade' id='editDriverStandingModal{$row['driverStandingsId']}' tabindex='-1' aria-labelledby='editDriverStandingLabel' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='editDriverStandingLabel'>Edit Driver Standing</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <form action='' method='POST'>
                                            <div class='modal-body'>
                                                <input type='hidden' name='driverStandingsId' value='{$row['driverStandingsId']}'>
                                                <div class='mb-3'>
                                                    <label for='raceId' class='form-label'>Race ID</label>
                                                    <input type='number' class='form-control' name='raceId' value='{$row['raceId']}' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='driverId' class='form-label'>Driver ID</label>
                                                    <input type='number' class='form-control' name='driverId' value='{$row['driverId']}' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='points' class='form-label'>Points</label>
                                                    <input type='number' step='0.01' class='form-control' name='points' value='{$row['points']}' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='position' class='form-label'>Position</label>
                                                    <input type='number' class='form-control' name='position' value='{$row['position']}' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='wins' class='form-label'>Wins</label>
                                                    <input type='number' class='form-control' name='wins' value='{$row['wins']}' required>
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
                        echo "<tr><td colspan='7'>No standings found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <nav>
    <ul class="pagination justify-content-center">
        <?php
        $adjacents = 7; // How many pages to show on each side of the current page
        $start = max(1, $page - $adjacents); // Calculate the starting page number
        $end = min($total_pages, $page + $adjacents); // Calculate the ending page number

        // "First" button - only visible if the current page is not the first page
        if ($page > 1) {
            echo "<li class='page-item'><a class='page-link' href='crud_driver_standings.php?page=1'>First</a></li>";
        }

        // "Previous" button - only visible if the current page is greater than 1
        if ($page > 1) {
            echo "<li class='page-item'><a class='page-link' href='crud_driver_standings.php?page=" . ($page - 1) . "'>&laquo; Prev</a></li>";
        }

        // Page number links - display only the calculated range of pages
        for ($i = $start; $i <= $end; $i++) {
            $active = ($i == $page) ? 'active' : '';
            echo "<li class='page-item $active'><a class='page-link' href='crud_driver_standings.php?page=$i'>$i</a></li>";
        }

        // "Next" button - only visible if the current page is not the last page
        if ($page < $total_pages) {
            echo "<li class='page-item'><a class='page-link' href='crud_driver_standings.php?page=" . ($page + 1) . "'>Next &raquo;</a></li>";
        }

        // "Last" button - only visible if the current page is not the last page
        if ($page < $total_pages) {
            echo "<li class='page-item'><a class='page-link' href='crud_driver_standings.php?page=$total_pages'>Last</a></li>";
        }
        ?>
    </ul>
</nav>

        </div>
    </div>
</div>

<!-- Create Driver Standing Modal -->
<div class="modal fade" id="createDriverStandingModal" tabindex="-1" aria-labelledby="createDriverStandingLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createDriverStandingLabel">Create New Driver Standing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="raceId" class="form-label">Race ID</label>
                        <input type="number" class="form-control" name="raceId" required>
                    </div>
                    <div class="mb-3">
                        <label for="driverId" class="form-label">Driver ID</label>
                        <input type="number" class="form-control" name="driverId" required>
                    </div>
                    <div class="mb-3">
                        <label for="points" class="form-label">Points</label>
                        <input type="number" step="0.01" class="form-control" name="points" required>
                    </div>
                    <div class="mb-3">
                        <label for="position" class="form-label">Position</label>
                        <input type="number" class="form-control" name="position" required>
                    </div>
                    <div class="mb-3">
                        <label for="wins" class="form-label">Wins</label>
                        <input type="number" class="form-control" name="wins" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="create">Create Standing</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php ob_end_flush(); ?>
