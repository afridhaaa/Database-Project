<?php
// Start output buffering to prevent "headers already sent" issues
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
$sql = "SELECT COUNT(driverId) AS total FROM drivers";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_drivers = $row['total'];

// Determine the number of total pages available
$total_pages = ceil($total_drivers / $results_per_page);

// Determine which page number the visitor is currently on
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;

// Determine the starting limit number
$start_limit = ($page - 1) * $results_per_page;

// Fetch the selected results from the database
$sql = "SELECT * FROM drivers LIMIT " . $start_limit . ", " . $results_per_page;
$result = $conn->query($sql);

// Create Driver
if (isset($_POST['create'])) {
    $forename = $_POST['forename'];
    $constructor_id = $_POST['constructor_id'];
    $fastest_laps = $_POST['no_of_fastest_laps'];
    $pole_positions = $_POST['no_of_pole_positions'];
    $race_wins = $_POST['no_of_race_wins'];
    $points = $_POST['no_of_points'];
    $podium_finishes = $_POST['no_of_podium_finishes'];
    $nationality = $_POST['nationality'];
    $url = $_POST['URL'];

    $sql = "INSERT INTO drivers (forename, constructor_id, no_of_fastest_laps, no_of_pole_positions, no_of_race_wins, 
            no_of_points, no_of_podium_finishes, nationality, URL) 
            VALUES ('$forename', '$constructor_id', '$fastest_laps', '$pole_positions', '$race_wins', 
            '$points', '$podium_finishes', '$nationality', '$url')";
    $conn->query($sql);

    header("Location: crud_drivers.php");
    exit();
}

// Edit Driver
if (isset($_POST['edit'])) {
    $id = $_POST['driverId'];
    $forename = $_POST['forename'];
    $constructor_id = $_POST['constructor_id'];
    $fastest_laps = $_POST['no_of_fastest_laps'];
    $pole_positions = $_POST['no_of_pole_positions'];
    $race_wins = $_POST['no_of_race_wins'];
    $points = $_POST['no_of_points'];
    $podium_finishes = $_POST['no_of_podium_finishes'];
    $nationality = $_POST['nationality'];
    $url = $_POST['URL'];

    $sql = "UPDATE drivers SET forename='$forename', constructor_id='$constructor_id', no_of_fastest_laps='$fastest_laps', 
            no_of_pole_positions='$pole_positions', no_of_race_wins='$race_wins', no_of_points='$points', 
            no_of_podium_finishes='$podium_finishes', nationality='$nationality', URL='$url' WHERE driverId='$id'";
    $conn->query($sql);

    header("Location: crud_drivers.php");
    exit();
}

// Delete Driver
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM drivers WHERE driverId='$id'";
    $conn->query($sql);

    header("Location: crud_drivers.php");
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

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
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
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>{$row['driverId']}</td>";
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
                                <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editDriverModal{$row['driverId']}'><i class='fas fa-edit'></i></button>
                                <a href='?delete={$row['driverId']}' class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></a>
                              </td>";
                            echo "</tr>";

                            // Edit Driver Modal
                            echo "
                            <div class='modal fade' id='editDriverModal" . $row['driverId'] . "' tabindex='-1' aria-labelledby='editDriverLabel' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='editDriverLabel'>Edit Driver</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <form action='' method='POST'>
                                            <div class='modal-body'>
                                                <input type='hidden' name='driverId' value='" . $row['driverId'] . "'>
                                                <div class='mb-3'>
                                                    <label for='forename' class='form-label'>Forename</label>
                                                    <input type='text' class='form-control' name='forename' value='" . $row['forename'] . "' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='constructor_id' class='form-label'>Constructor ID</label>
                                                    <input type='number' class='form-control' name='constructor_id' value='" . $row['constructor_id'] . "' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='no_of_fastest_laps' class='form-label'>Fastest Laps</label>
                                                    <input type='number' class='form-control' name='no_of_fastest_laps' value='" . $row['no_of_fastest_laps'] . "' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='no_of_pole_positions' class='form-label'>Pole Positions</label>
                                                    <input type='number' class='form-control' name='no_of_pole_positions' value='" . $row['no_of_pole_positions'] . "' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='no_of_race_wins' class='form-label'>Race Wins</label>
                                                    <input type='number' class='form-control' name='no_of_race_wins' value='" . $row['no_of_race_wins'] . "' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='no_of_points' class='form-label'>Points</label>
                                                    <input type='number' class='form-control' name='no_of_points' value='" . $row['no_of_points'] . "' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='no_of_podium_finishes' class='form-label'>Podium Finishes</label>
                                                    <input type='number' class='form-control' name='no_of_podium_finishes' value='" . $row['no_of_podium_finishes'] . "' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='nationality' class='form-label'>Nationality</label>
                                                    <input type='text' class='form-control' name='nationality' value='" . $row['nationality'] . "' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='URL' class='form-label'>URL</label>
                                                    <input type='text' class='form-control' name='URL' value='" . $row['URL'] . "' required>
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
                        echo "<tr><td colspan='11'>No drivers found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                            <a class="page-link" href="crud_drivers.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Create Driver Modal -->
<div class="modal fade" id="createDriverModal" tabindex="-1" aria-labelledby="createDriverLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title' id='createDriverLabel'>Create New Driver</h5>
                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
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
                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                    <button type='submit' class='btn btn-primary' name='create'>Create Driver</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php ob_end_flush(); ?>
