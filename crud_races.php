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

// Count total documents in the races collection
$total_races = $db->races->countDocuments();

// Determine the number of total pages available
$total_pages = ceil($total_races / $results_per_page);

// Determine which page number the visitor is currently on
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;

// Determine the starting limit number
$start_limit = ($page - 1) * $results_per_page;

// Fetch the selected results from the database
$result = $db->races->find([], [
    'skip' => $start_limit,
    'limit' => $results_per_page
]);

// Create Race Record
if (isset($_POST['create'])) {
    $newRace = [
        'year' => (int)$_POST['year'],
        'round' => (int)$_POST['round'],
        'circuit_id' => (int)$_POST['circuit_id'],
        'name' => $_POST['name'],
        'date' => $_POST['date'],
        'url' => $_POST['url']
    ];

    $db->races->insertOne($newRace);

    $_SESSION['message'] = "Race added successfully!";
    header("Location: crud_races.php?page=" . $page);
    exit();
}

// Edit Race Record
if (isset($_POST['edit'])) {
    $raceId = $_POST['raceId'];

    $updatedRace = [
        'year' => (int)$_POST['year'],
        'round' => (int)$_POST['round'],
        'circuit_id' => (int)$_POST['circuit_id'],
        'name' => $_POST['name'],
        'date' => $_POST['date'],
        'url' => $_POST['url']
    ];

    $db->races->updateOne(
        ['_id' => new ObjectId($raceId)],
        ['$set' => $updatedRace]
    );

    $_SESSION['message'] = "Race updated successfully!";
    header("Location: crud_races.php?page=" . $page);
    exit();
}

// Delete Race Record
if (isset($_GET['delete'])) {
    $raceId = $_GET['delete'];
    $db->races->deleteOne(['_id' => new ObjectId($raceId)]);

    $_SESSION['message'] = "Race deleted successfully!";
    header("Location: crud_races.php?page=" . $page);
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
                    foreach ($result as $row) {
                        $id = (string)$row['_id'];
                        echo "<tr>";
                        echo "<td>{$id}</td>";
                        echo "<td>{$row['year']}</td>";
                        echo "<td>{$row['round']}</td>";
                        echo "<td>{$row['circuit_id']}</td>";
                        echo "<td>{$row['name']}</td>";
                        echo "<td>{$row['date']}</td>";
                        echo "<td><a href='{$row['url']}' target='_blank'>Visit</a></td>";
                        echo "<td>
                            <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editRaceModal{$id}'><i class='fas fa-edit'></i></button>
                            <a href='?delete={$id}&page={$page}' class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></a>
                          </td>";
                        echo "</tr>";

                        // Edit Race Modal
                        echo "
                        <div class='modal fade' id='editRaceModal{$id}' tabindex='-1' aria-labelledby='editRaceLabel{$id}' aria-hidden='true'>
                            <div class='modal-dialog'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <h5 class='modal-title' id='editRaceLabel{$id}'>Edit Race</h5>
                                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                    </div>
                                    <form action='' method='POST'>
                                        <div class='modal-body'>
                                            <input type='hidden' name='raceId' value='{$id}'>
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
