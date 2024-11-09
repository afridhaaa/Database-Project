<?php
// Start output buffering to prevent headers already sent errors
ob_start();

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
include 'db/db.php'; // Include the database connection
use MongoDB\BSON\ObjectId; // Import MongoDB ObjectId

// Pagination settings
$results_per_page = 10; // Number of results per page

// Count total documents in the lap_times collection
$total_lap_times = $db->lap_times->countDocuments();

// Determine the number of total pages available
$total_pages = ceil($total_lap_times / $results_per_page);

// Determine which page number the visitor is currently on
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;

// Determine the starting limit number
$start_limit = ($page - 1) * $results_per_page;

// Fetch the selected results from the database
$result = $db->lap_times->find([], [
    'skip' => $start_limit,
    'limit' => $results_per_page
]);

// Create Lap Time Record
if (isset($_POST['create'])) {
    $newLapTime = [
        'raceId' => (int)$_POST['raceId'],
        'driverId' => (int)$_POST['driverId'],
        'lap' => (int)$_POST['lap'],
        'position' => (int)$_POST['position'],
        'time' => $_POST['time'],
        'milliseconds' => (int)$_POST['milliseconds']
    ];

    $db->lap_times->insertOne($newLapTime);

    $_SESSION['message'] = "Lap Time record added successfully!";
    header("Location: crud_lap_times.php?page=" . $page);
    exit();
}

// Edit Lap Time Record
if (isset($_POST['edit'])) {
    $id = $_POST['id'];

    $updatedLapTime = [
        'raceId' => (int)$_POST['raceId'],
        'driverId' => (int)$_POST['driverId'],
        'lap' => (int)$_POST['lap'],
        'position' => (int)$_POST['position'],
        'time' => $_POST['time'],
        'milliseconds' => (int)$_POST['milliseconds']
    ];

    $db->lap_times->updateOne(
        ['_id' => new ObjectId($id)],
        ['$set' => $updatedLapTime]
    );

    $_SESSION['message'] = "Lap Time record updated successfully!";
    header("Location: crud_lap_times.php?page=" . $page);
    exit();
}

// Delete Lap Time Record
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $db->lap_times->deleteOne(['_id' => new ObjectId($id)]);

    $_SESSION['message'] = "Lap Time record deleted successfully!";
    header("Location: crud_lap_times.php?page=" . $page);
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

            <!-- Display Popup Notification -->
            <?php if (isset($_SESSION['message'])): ?>
                <div id="popupMessage" class="alert alert-success" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 1050;">
                    <?php echo $_SESSION['message']; ?>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <script>
                // Automatically hide the popup after 3 seconds
                window.onload = function() {
                    var popup = document.getElementById("popupMessage");
                    if (popup) {
                        setTimeout(function() {
                            popup.style.transition = "opacity 0.5s ease";
                            popup.style.opacity = "0";
                            setTimeout(function() { popup.remove(); }, 500); // Remove the element after it fades out
                        }, 3000);
                    }
                };
            </script>

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
                    foreach ($result as $row) {
                        $id = (string)$row['_id'];
                        echo "<tr>";
                        echo "<td>{$id}</td>";
                        echo "<td>{$row['raceId']}</td>";
                        echo "<td>{$row['driverId']}</td>";
                        echo "<td>{$row['lap']}</td>";
                        echo "<td>{$row['position']}</td>";
                        echo "<td>{$row['time']}</td>";
                        echo "<td>{$row['milliseconds']}</td>";
                        echo "<td>
                            <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editLapTimeModal{$id}'><i class='fas fa-edit'></i></button>
                            <a href='?delete={$id}&page={$page}' class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></a>
                          </td>";
                        echo "</tr>";

                        // Edit Lap Time Modal
                        echo "
                        <div class='modal fade' id='editLapTimeModal{$id}' tabindex='-1' aria-labelledby='editLapTimeLabel{$id}' aria-hidden='true'>
                            <div class='modal-dialog'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <h5 class='modal-title' id='editLapTimeLabel{$id}'>Edit Lap Time</h5>
                                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                    </div>
                                    <form action='' method='POST'>
                                        <div class='modal-body'>
                                            <input type='hidden' name='id' value='{$id}'>
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
