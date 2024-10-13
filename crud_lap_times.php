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
$sql = "SELECT COUNT(id) AS total FROM lap_times";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_lap_times = $row['total'];

// Determine the number of total pages available
$total_pages = ceil($total_lap_times / $results_per_page);

// Determine which page number the visitor is currently on
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;

// Determine the starting limit number
$start_limit = ($page - 1) * $results_per_page;

// Fetch the selected results from the database
$sql = "SELECT * FROM lap_times LIMIT " . $start_limit . ", " . $results_per_page;
$result = $conn->query($sql);

// Create Lap Time Record
if (isset($_POST['create'])) {
    $raceId = $_POST['raceId'];
    $driverId = $_POST['driverId'];
    $lap = $_POST['lap'];
    $position = $_POST['position'];
    $time = $_POST['time'];
    $milliseconds = $_POST['milliseconds'];

    $sql = "INSERT INTO lap_times (raceId, driverId, lap, position, time, milliseconds) 
            VALUES ('$raceId', '$driverId', '$lap', '$position', '$time', '$milliseconds')";
    $conn->query($sql);

    header("Location: crud_lap_times.php");
    exit();
}

// Edit Lap Time Record
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $raceId = $_POST['raceId'];
    $driverId = $_POST['driverId'];
    $lap = $_POST['lap'];
    $position = $_POST['position'];
    $time = $_POST['time'];
    $milliseconds = $_POST['milliseconds'];

    $sql = "UPDATE lap_times SET raceId='$raceId', driverId='$driverId', lap='$lap', position='$position', 
            time='$time', milliseconds='$milliseconds' WHERE id='$id'";
    $conn->query($sql);

    header("Location: crud_lap_times.php");
    exit();
}

// Delete Lap Time Record
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM lap_times WHERE id='$id'";
    $conn->query($sql);

    header("Location: crud_lap_times.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Lap Times</title>
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
            <h1>Manage Lap Times</h1>
            <p>Create, view, edit, or delete lap time records here.</p>

            <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#createLapTimeModal">
                <i class="fas fa-plus"></i> Create New Lap Time
            </button>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Race ID</th>
                        <th>Driver ID</th>
                        <th>Lap</th>
                        <th>Position</th>
                        <th>Time</th>
                        <th>Milliseconds</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>{$row['id']}</td>";
                            echo "<td>{$row['raceId']}</td>";
                            echo "<td>{$row['driverId']}</td>";
                            echo "<td>{$row['lap']}</td>";
                            echo "<td>{$row['position']}</td>";
                            echo "<td>{$row['time']}</td>";
                            echo "<td>{$row['milliseconds']}</td>";
                            echo "<td>
                                <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editLapTimeModal{$row['id']}'><i class='fas fa-edit'></i></button>
                                <a href='?delete={$row['id']}' class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></a>
                              </td>";
                            echo "</tr>";

                            // Edit Modal
                            echo "
                            <div class='modal fade' id='editLapTimeModal{$row['id']}' tabindex='-1' aria-labelledby='editLapTimeLabel' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='editLapTimeLabel'>Edit Lap Time</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <form action='' method='POST'>
                                            <div class='modal-body'>
                                                <input type='hidden' name='id' value='{$row['id']}'>
                                                <div class='mb-3'>
                                                    <label for='raceId' class='form-label'>Race ID</label>
                                                    <input type='number' class='form-control' name='raceId' value='{$row['raceId']}' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='driverId' class='form-label'>Driver ID</label>
                                                    <input type='number' class='form-control' name='driverId' value='{$row['driverId']}' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='lap' class='form-label'>Lap</label>
                                                    <input type='number' class='form-control' name='lap' value='{$row['lap']}' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='position' class='form-label'>Position</label>
                                                    <input type='number' class='form-control' name='position' value='{$row['position']}' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='time' class='form-label'>Time</label>
                                                    <input type='text' class='form-control' name='time' value='{$row['time']}' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='milliseconds' class='form-label'>Milliseconds</label>
                                                    <input type='number' class='form-control' name='milliseconds' value='{$row['milliseconds']}' required>
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
                        echo "<tr><td colspan='8'>No lap times found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <nav>
                <ul class="pagination justify-content-center">
                    <?php
                    $adjacents = 7;
                    $start = max(1, $page - $adjacents);
                    $end = min($total_pages, $page + $adjacents);

                    if ($page > 1) {
                        echo "<li class='page-item'><a class='page-link' href='crud_lap_times.php?page=1'>First</a></li>";
                        echo "<li class='page-item'><a class='page-link' href='crud_lap_times.php?page=" . ($page - 1) . "'>&laquo; Prev</a></li>";
                    }

                    for ($i = $start; $i <= $end; $i++) {
                        $active = ($i == $page) ? 'active' : '';
                        echo "<li class='page-item $active'><a class='page-link' href='crud_lap_times.php?page=$i'>$i</a></li>";
                    }

                    if ($page < $total_pages) {
                        echo "<li class='page-item'><a class='page-link' href='crud_lap_times.php?page=" . ($page + 1) . "'>Next &raquo;</a></li>";
                        echo "<li class='page-item'><a class='page-link' href='crud_lap_times.php?page=$total_pages'>Last</a></li>";
                    }
                    ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Create Lap Time Modal -->
<div class="modal fade" id="createLapTimeModal" tabindex="-1" aria-labelledby="createLapTimeLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createLapTimeLabel">Create New Lap Time</h5>
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
                        <label for="lap" class="form-label">Lap</label>
                        <input type="number" class="form-control" name="lap" required>
                    </div>
                    <div class="mb-3">
                        <label for="position" class="form-label">Position</label>
                        <input type="number" class="form-control" name="position" required>
                    </div>
                    <div class="mb-3">
                        <label for="time" class="form-label">Time</label>
                        <input type="text" class="form-control" name="time" required>
                    </div>
                    <div class="mb-3">
                        <label for="milliseconds" class="form-label">Milliseconds</label>
                        <input type="number" class="form-control" name="milliseconds" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="create">Create Lap Time</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php ob_end_flush(); ?>
