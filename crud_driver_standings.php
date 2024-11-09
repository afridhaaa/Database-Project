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

// Count total documents in the driver_standings collection
$total_driver_standings = $db->driver_standings->countDocuments();

// Determine the number of total pages available
$total_pages = ceil($total_driver_standings / $results_per_page);

// Determine which page number the visitor is currently on
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;

// Determine the starting limit number
$start_limit = ($page - 1) * $results_per_page;

// Fetch the selected results from the database
$result = $db->driver_standings->find([], [
    'skip' => $start_limit,
    'limit' => $results_per_page
]);

// Create Driver Standing
if (isset($_POST['create'])) {
    $newStanding = [
        'raceId' => (int)$_POST['raceId'],
        'driverId' => (int)$_POST['driverId'],
        'points' => (float)$_POST['points'],
        'position' => (int)$_POST['position'],
        'wins' => (int)$_POST['wins']
    ];

    $db->driver_standings->insertOne($newStanding);

    $_SESSION['message'] = "Driver standing added successfully!";
    header("Location: crud_driver_standings.php?page=" . $page);
    exit();
}

// Edit Driver Standing
if (isset($_POST['edit'])) {
    $id = $_POST['driverStandingsId'];

    $updatedStanding = [
        'raceId' => (int)$_POST['raceId'],
        'driverId' => (int)$_POST['driverId'],
        'points' => (float)$_POST['points'],
        'position' => (int)$_POST['position'],
        'wins' => (int)$_POST['wins']
    ];

    $db->driver_standings->updateOne(
        ['_id' => new ObjectId($id)],
        ['$set' => $updatedStanding]
    );

    $_SESSION['message'] = "Driver standing updated successfully!";
    header("Location: crud_driver_standings.php?page=" . $page);
    exit();
}

// Delete Driver Standing
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $db->driver_standings->deleteOne(['_id' => new ObjectId($id)]);

    $_SESSION['message'] = "Driver standing deleted successfully!";
    header("Location: crud_driver_standings.php?page=" . $page);
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
                        <th>Points</th>
                        <th>Position</th>
                        <th>Wins</th>
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
                        echo "<td>{$row['points']}</td>";
                        echo "<td>{$row['position']}</td>";
                        echo "<td>{$row['wins']}</td>";
                        echo "<td>
                            <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editDriverStandingModal{$id}'><i class='fas fa-edit'></i></button>
                            <a href='?delete={$id}&page={$page}' class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></a>
                          </td>";
                        echo "</tr>";

                        // Edit Driver Standing Modal
                        echo "
                        <div class='modal fade' id='editDriverStandingModal{$id}' tabindex='-1' aria-labelledby='editDriverStandingLabel' aria-hidden='true'>
                            <div class='modal-dialog'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <h5 class='modal-title' id='editDriverStandingLabel'>Edit Driver Standing</h5>
                                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                    </div>
                                    <form action='' method='POST'>
                                        <div class='modal-body'>
                                            <input type='hidden' name='driverStandingsId' value='{$id}'>
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
                    ?>
                </tbody>
            </table>

            <nav>
                <ul class="pagination justify-content-center">
                    <?php
                    $adjacents = 7;
                    $start = max(1, $page - $adjacents);
                    $end = min($total_pages, $page + $adjacents);

                    if ($page > 1) {
                        echo "<li class='page-item'><a class='page-link' href='crud_driver_standings.php?page=1'>First</a></li>";
                        echo "<li class='page-item'><a class='page-link' href='crud_driver_standings.php?page=" . ($page - 1) . "'>&laquo; Prev</a></li>";
                    }

                    for ($i = $start; $i <= $end; $i++) {
                        $active = ($i == $page) ? 'active' : '';
                        echo "<li class='page-item $active'><a class='page-link' href='crud_driver_standings.php?page=$i'>$i</a></li>";
                    }

                    if ($page < $total_pages) {
                        echo "<li class='page-item'><a class='page-link' href='crud_driver_standings.php?page=" . ($page + 1) . "'>Next &raquo;</a></li>";
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
