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
$sql = "SELECT COUNT(raceId) AS total FROM races";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_races = $row['total'];

// Determine the number of total pages available
$total_pages = ceil($total_races / $results_per_page);

// Determine which page number the visitor is currently on
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;

// Determine the starting limit number
$start_limit = ($page - 1) * $results_per_page;

// Fetch the selected results from the database
$sql = "SELECT * FROM races LIMIT " . $start_limit . ", " . $results_per_page;
$result = $conn->query($sql);

// Create Race Record
if (isset($_POST['create'])) {
    $year = $_POST['year'];
    $round = $_POST['round'];
    $circuit_id = $_POST['circuit_id'];
    $name = $_POST['name'];
    $date = $_POST['date'];
    $url = $_POST['url'];

    $sql = "INSERT INTO races (year, round, circuit_id, name, date, url) 
            VALUES ('$year', '$round', '$circuit_id', '$name', '$date', '$url')";
    $conn->query($sql);

    header("Location: crud_races.php");
    exit();
}

// Edit Race Record
if (isset($_POST['edit'])) {
    $raceId = $_POST['raceId'];
    $year = $_POST['year'];
    $round = $_POST['round'];
    $circuit_id = $_POST['circuit_id'];
    $name = $_POST['name'];
    $date = $_POST['date'];
    $url = $_POST['url'];

    $sql = "UPDATE races SET year='$year', round='$round', circuit_id='$circuit_id', 
            name='$name', date='$date', url='$url' WHERE raceId='$raceId'";
    $conn->query($sql);

    header("Location: crud_races.php");
    exit();
}

// Delete Race Record
if (isset($_GET['delete'])) {
    $raceId = $_GET['delete'];
    $sql = "DELETE FROM races WHERE raceId='$raceId'";
    $conn->query($sql);

    header("Location: crud_races.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Races</title>
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
            <h1>Manage Races</h1>
            <p>Create, view, edit, or delete race records here.</p>

            <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#createRaceModal">
                <i class="fas fa-plus"></i> Create New Race
            </button>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Year</th>
                        <th>Round</th>
                        <th>Circuit ID</th>
                        <th>Name</th>
                        <th>Date</th>
                        <th>URL</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>{$row['raceId']}</td>";
                            echo "<td>{$row['year']}</td>";
                            echo "<td>{$row['round']}</td>";
                            echo "<td>{$row['circuit_id']}</td>";
                            echo "<td>{$row['name']}</td>";
                            echo "<td>{$row['date']}</td>";
                            echo "<td><a href='{$row['url']}' target='_blank'>Visit</a></td>";
                            echo "<td>
                                <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editRaceModal{$row['raceId']}'><i class='fas fa-edit'></i></button>
                                <a href='?delete={$row['raceId']}' class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></a>
                              </td>";
                            echo "</tr>";

                            // Edit Race Modal
    echo "
    <div class='modal fade' id='editRaceModal{$row['raceId']}' tabindex='-1' aria-labelledby='editRaceLabel' aria-hidden='true'>
        <div class='modal-dialog'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <h5 class='modal-title' id='editRaceLabel'>Edit Race</h5>
                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                </div>
                <form action='' method='POST'>
                    <div class='modal-body'>
                        <input type='hidden' name='raceId' value='{$row['raceId']}'>
                        <div class='mb-3'>
                            <label for='year' class='form-label'>Year</label>
                            <input type='number' class='form-control' name='year' value='{$row['year']}' required>
                        </div>
                        <div class='mb-3'>
                            <label for='round' class='form-label'>Round</label>
                            <input type='number' class='form-control' name='round' value='{$row['round']}' required>
                        </div>
                        <div class='mb-3'>
                            <label for='circuit_id' class='form-label'>Circuit ID</label>
                            <input type='number' class='form-control' name='circuit_id' value='{$row['circuit_id']}' required>
                        </div>
                        <div class='mb-3'>
                            <label for='name' class='form-label'>Name</label>
                            <input type='text' class='form-control' name='name' value='{$row['name']}' required>
                        </div>
                        <div class='mb-3'>
                            <label for='date' class='form-label'>Date</label>
                            <input type='date' class='form-control' name='date' value='{$row['date']}' required>
                        </div>
                        <div class='mb-3'>
                            <label for='url' class='form-label'>URL</label>
                            <input type='text' class='form-control' name='url' value='{$row['url']}' required>
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
                        echo "<tr><td colspan='8'>No races found</td></tr>";
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
                        echo "<li class='page-item'><a class='page-link' href='crud_races.php?page=1'>First</a></li>";
                        echo "<li class='page-item'><a class='page-link' href='crud_races.php?page=" . ($page - 1) . "'>&laquo; Prev</a></li>";
                    }

                    for ($i = $start; $i <= $end; $i++) {
                        $active = ($i == $page) ? 'active' : '';
                        echo "<li class='page-item $active'><a class='page-link' href='crud_races.php?page=$i'>$i</a></li>";
                    }

                    if ($page < $total_pages) {
                        echo "<li class='page-item'><a class='page-link' href='crud_races.php?page=" . ($page + 1) . "'>Next &raquo;</a></li>";
                        echo "<li class='page-item'><a class='page-link' href='crud_races.php?page=$total_pages'>Last</a></li>";
                    }
                    ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Create Race Modal -->
<div class="modal fade" id="createRaceModal" tabindex="-1" aria-labelledby="createRaceLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createRaceLabel">Create New Race</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="year" class="form-label">Year</label>
                        <input type="number" class="form-control" name="year" required>
                    </div>
                    <div class="mb-3">
                        <label for="round" class="form-label">Round</label>
                        <input type="number" class="form-control" name="round" required>
                    </div>
                    <div class="mb-3">
                        <label for="circuit_id" class="form-label">Circuit ID</label>
                        <input type="number" class="form-control" name="circuit_id" required>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label for="url" class="form-label">URL</label>
                        <input type="text" class="form-control" name="url" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="create">Create Race</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php ob_end_flush(); ?>
