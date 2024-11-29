<?php
// Start output buffering to prevent "headers already sent" issues
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

// Count total documents in the drivers collection
$total_drivers = $db->drivers->countDocuments();

// Determine the number of total pages available
$total_pages = ceil($total_drivers / $results_per_page);

// Determine which page number the visitor is currently on
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;

// Determine the starting limit number
$start_limit = ($page - 1) * $results_per_page;

// Fetch the selected results from the database
$result = $db->drivers->find([], [
    'skip' => $start_limit,
    'limit' => $results_per_page
]);

// Create Driver
if (isset($_POST['create'])) {
    $newDriver = [
        
        'forename' => $_POST['forename'],
        'constructor_id' => (int)$_POST['constructor_id'],
        'no_of_fastest_laps' => (int)$_POST['no_of_fastest_laps'],
        'no_of_pole_positions' => (int)$_POST['no_of_pole_positions'],
        'no_of_race_wins' => (int)$_POST['no_of_race_wins'],
        'no_of_points' => (float)$_POST['no_of_points'],
        'no_of_podium_finishes' => (int)$_POST['no_of_podium_finishes'],
        'nationality' => $_POST['nationality'],
        'URL' => $_POST['URL']
    ];

    $db->drivers->insertOne($newDriver);

    $_SESSION['message'] = "Driver added successfully!";
    header("Location: crud_drivers.php?page=" . $page);
    exit();
}

// Edit Driver
if (isset($_POST['edit'])) {
    $id = $_POST['driverId'];

    $updatedDriver = [
        'forename' => $_POST['forename'],
        'constructor_id' => (int)$_POST['constructor_id'],
        'no_of_fastest_laps' => (int)$_POST['no_of_fastest_laps'],
        'no_of_pole_positions' => (int)$_POST['no_of_pole_positions'],
        'no_of_race_wins' => (int)$_POST['no_of_race_wins'],
        'no_of_points' => (float)$_POST['no_of_points'],
        'no_of_podium_finishes' => (int)$_POST['no_of_podium_finishes'],
        'nationality' => $_POST['nationality'],
        'URL' => $_POST['URL']
    ];

    $db->drivers->updateOne(
        ['_id' => new ObjectId($id)],
        ['$set' => $updatedDriver]
    );

    $_SESSION['message'] = "Driver updated successfully!";
    header("Location: crud_drivers.php?page=" . $page);
    exit();
}

// Delete Driver
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $db->drivers->deleteOne(['_id' => new ObjectId($id)]);

    $_SESSION['message'] = "Driver deleted successfully!";
    header("Location: crud_drivers.php?page=" . $page);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Drivers</title>
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
            <h1>Manage Drivers</h1>
            <p>Create, view, edit, or delete driver records here.</p>

            <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#createDriverModal">
                <i class="fas fa-plus"></i> Create New Driver
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
                        <!-- <th>ID</th> -->
                        <th>Name</th>
                        <th>Constructor ID</th>
                        <th>No. of Fastest Laps</th>
                        <th>No. of Pole Positions</th>
                        <th>No. of Race Wins</th>
                        <th>Points</th>
                        <th>Podium Finishes</th>
                        <th>Nationality</th>
                        <th>URL</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($result as $row) {
                        $id = (string)$row['_id'];
                        echo "<tr>";
                        // echo "<td>{$id}</td>";
                        echo "<td>{$row['forename']}</td>";
                        echo "<td>{$row['constructor_id']}</td>";
                        echo "<td>{$row['no_of_fastest_laps']}</td>";
                        echo "<td>{$row['no_of_pole_positions']}</td>";
                        echo "<td>{$row['no_of_race_wins']}</td>";
                        echo "<td>{$row['no_of_points']}</td>";
                        echo "<td>{$row['no_of_podium_finishes']}</td>";
                        echo "<td>{$row['nationality']}</td>";
                        echo "<td><a href='{$row['URL']}' target='_blank'>Visit</a></td>";
                        echo "<td>
                            <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editDriverModal{$id}'><i class='fas fa-edit'></i></button>
                            <a href='?delete={$id}&page={$page}' class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></a>
                          </td>";
                        echo "</tr>";

                        // Edit Driver Modal
                        echo "
                        <div class='modal fade' id='editDriverModal{$id}' tabindex='-1' aria-labelledby='editDriverLabel{$id}' aria-hidden='true'>
                            <div class='modal-dialog'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <h5 class='modal-title' id='editDriverLabel{$id}'>Edit Driver</h5>
                                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                    </div>
                                    <form action='' method='POST'>
                                        <div class='modal-body'>
                                            <input type='hidden' name='driverId' value='{$id}'>
                                            <div class='mb-3'>
                                                <label for='forename' class='form-label'>Forename</label>
                                                <input type='text' class='form-control' name='forename' value='{$row['forename']}' required>
                                            </div>
                                            <div class='mb-3'>
                                                <label for='constructor_id' class='form-label'>Constructor ID</label>
                                                <input type='number' class='form-control' name='constructor_id' value='{$row['constructor_id']}' required>
                                            </div>
                                            <div class='mb-3'>
                                                <label for='no_of_fastest_laps' class='form-label'>Fastest Laps</label>
                                                <input type='number' class='form-control' name='no_of_fastest_laps' value='{$row['no_of_fastest_laps']}' required>
                                            </div>
                                            <div class='mb-3'>
                                                <label for='no_of_pole_positions' class='form-label'>Pole Positions</label>
                                                <input type='number' class='form-control' name='no_of_pole_positions' value='{$row['no_of_pole_positions']}' required>
                                            </div>
                                            <div class='mb-3'>
                                                <label for='no_of_race_wins' class='form-label'>Race Wins</label>
                                                <input type='number' class='form-control' name='no_of_race_wins' value='{$row['no_of_race_wins']}' required>
                                            </div>
                                            <div class='mb-3'>
                                                <label for='no_of_points' class='form-label'>Points</label>
                                                <input type='number' class='form-control' name='no_of_points' value='{$row['no_of_points']}' required>
                                            </div>
                                            <div class='mb-3'>
                                                <label for='no_of_podium_finishes' class='form-label'>Podium Finishes</label>
                                                <input type='number' class='form-control' name='no_of_podium_finishes' value='{$row['no_of_podium_finishes']}' required>
                                            </div>
                                            <div class='mb-3'>
                                                <label for='nationality' class='form-label'>Nationality</label>
                                                <input type='text' class='form-control' name='nationality' value='{$row['nationality']}' required>
                                            </div>
                                            <div class='mb-3'>
                                                <label for='URL' class='form-label'>URL</label>
                                                <input type='text' class='form-control' name='URL' value='{$row['URL']}' required>
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

                    if ($total_pages > $adjacents && $page > 1) {
                        echo "<li class='page-item'><a class='page-link' href='crud_drivers.php?page=1'>First</a></li>";
                        echo "<li class='page-item'><a class='page-link' href='crud_drivers.php?page=" . ($page - 1) . "'>&laquo; Prev</a></li>";
                    }

                    // Show page number links
                    for ($i = $start; $i <= $end; $i++) {
                        $active = ($i == $page) ? 'active' : '';
                        echo "<li class='page-item $active'><a class='page-link' href='crud_drivers.php?page=$i'>$i</a></li>";
                    }

                    if ($total_pages > $adjacents && $page < $total_pages) {
                        echo "<li class='page-item'><a class='page-link' href='crud_drivers.php?page=" . ($page + 1) . "'>Next &raquo;</a></li>";
                        echo "<li class='page-item'><a class='page-link' href='crud_drivers.php?page=$total_pages'>Last</a></li>";
                    }
                    ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Create Driver Modal -->
<div class="modal fade" id="createDriverModal" tabindex="-1" aria-labelledby="createDriverLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createDriverLabel">Create New Driver</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <!-- Add Driver ID Field Here -->
                    <!-- <div class="mb-3">
                        <label for="driverId" class="form-label">Driver ID</label>
                        <input type="text" class="form-control" name="driverId" required>
                    </div> -->
                    <div class="mb-3">
                        <label for="forename" class="form-label">Forename</label>
                        <input type="text" class="form-control" name="forename" required>
                    </div>
                    <div class="mb-3">
                        <label for="constructor_id" class="form-label">Constructor ID</label>
                        <input type="number" class="form-control" name="constructor_id" required>
                    </div>
                    <div class="mb-3">
                        <label for="no_of_fastest_laps" class="form-label">Fastest Laps</label>
                        <input type="number" class="form-control" name="no_of_fastest_laps" required>
                    </div>
                    <div class="mb-3">
                        <label for="no_of_pole_positions" class="form-label">Pole Positions</label>
                        <input type="number" class="form-control" name="no_of_pole_positions" required>
                    </div>
                    <div class="mb-3">
                        <label for="no_of_race_wins" class="form-label">Race Wins</label>
                        <input type="number" class="form-control" name="no_of_race_wins" required>
                    </div>
                    <div class="mb-3">
                        <label for="no_of_points" class="form-label">Points</label>
                        <input type="number" class="form-control" name="no_of_points" required>
                    </div>
                    <div class="mb-3">
                        <label for="no_of_podium_finishes" class="form-label">Podium Finishes</label>
                        <input type="number" class="form-control" name="no_of_podium_finishes" required>
                    </div>
                    <div class="mb-3">
                        <label for="nationality" class="form-label">Nationality</label>
                        <input type="text" class="form-control" name="nationality" required>
                    </div>
                    <div class="mb-3">
                        <label for="URL" class="form-label">URL</label>
                        <input type="text" class="form-control" name="URL" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="create">Create Driver</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php ob_end_flush(); ?>
