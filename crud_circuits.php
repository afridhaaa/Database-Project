<?php
// Start output buffering to avoid headers already sent errors
ob_start();

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
include 'db/db.php'; // Include the database connection
use MongoDB\BSON\ObjectId;

// Pagination settings
$results_per_page = 10; // Number of results per page

// Find out the number of results stored in the database
$total_circuits = $db->circuits->countDocuments(); // Make sure this returns an integer

// Determine the number of total pages available
$total_pages = max(1, ceil($total_circuits / $results_per_page));

// Determine which page number the visitor is currently on
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Ensure current page is within the bounds
$page = max(1, min($page, $total_pages));

// Determine the starting limit number
$start_limit = ($page - 1) * $results_per_page;

// Fetch the selected results from the database
$result = $db->circuits->find([], [
    'skip' => $start_limit,
    'limit' => $results_per_page
])->toArray(); // Convert to an array for easier handling

// Create Circuit
if (isset($_POST['create'])) {
    $newCircuit = [
        'circuit_name' => $_POST['circuit_name'],
        'circuit_location' => $_POST['circuit_location'],
        'circuit_country' => $_POST['circuit_country'],
        'latitude' => $_POST['latitude'],
        'longitude' => $_POST['longitude'],
        'altitude' => $_POST['altitude'],
        'url' => $_POST['url']
    ];

    $db->circuits->insertOne($newCircuit);

    $_SESSION['message'] = "Circuit added successfully!";
    header("Location: crud_circuits.php?page=" . $page);
    exit();
}

// Edit Circuit
if (isset($_POST['edit'])) {
    $id = $_POST['circuit_id']; // Ensure this is the MongoDB ObjectId

    $updatedCircuit = [
        'circuit_name' => $_POST['circuit_name'],
        'circuit_location' => $_POST['circuit_location'],
        'circuit_country' => $_POST['circuit_country'],
        'latitude' => $_POST['latitude'],
        'longitude' => $_POST['longitude'],
        'altitude' => $_POST['altitude'],
        'url' => $_POST['url']
    ];

    $db->circuits->updateOne(
        ['_id' => new ObjectId($id)], // Use the ObjectId here
        ['$set' => $updatedCircuit]
    );

    $_SESSION['message'] = "Circuit updated successfully!";
    header("Location: crud_circuits.php?page=" . $page);
    exit();
}

// Delete Circuit
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $db->circuits->deleteOne(['_id' => new ObjectId($id)]);

    $_SESSION['message'] = "Circuit deleted successfully!";
    header("Location: crud_circuits.php?page=" . $page);
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Circuits</title>
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
            <h1>Manage Circuits</h1>
            <p>Race course built for racing. Create, view, edit, or delete circuit records here.</p>

            <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#createCircuitModal">
                <i class="fas fa-plus"></i> Create New Circuit
            </button>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Country</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Altitude</th>
                        <th>URL</th>
                        <th>Actions</th>
                    </tr>
                </thead>

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

                <tbody>
                    <?php
                    foreach ($result as $row) {
                        echo "<tr>";
                        echo "<td>{$row['_id']}</td>";
                        echo "<td>{$row['circuit_name']}</td>";
                        echo "<td>{$row['circuit_location']}</td>";
                        echo "<td>{$row['circuit_country']}</td>";
                        echo "<td>{$row['latitude']}</td>";
                        echo "<td>{$row['longitude']}</td>";
                        echo "<td>{$row['altitude']}</td>";
                        echo "<td><a href='{$row['url']}' target='_blank'>Visit</a></td>";
                        echo "<td>
                            <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editCircuitModal{$row['_id']}'><i class='fas fa-edit'></i></button>
                            <a href='?delete={$row['_id']}' class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></a>
                          </td>";
                        echo "</tr>";

                        // Edit Circuit Modal
                        echo "
                        <div class='modal fade' id='editCircuitModal{$row['_id']}' tabindex='-1' aria-labelledby='editCircuitLabel' aria-hidden='true'>
                            <div class='modal-dialog'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <h5 class='modal-title' id='editCircuitLabel'>Edit Circuit</h5>
                                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                    </div>
                                    <form action='' method='POST'>
                                        <div class='modal-body'>
                                            <input type='hidden' name='circuit_id' value='{$row['_id']}'>
                                            <div class='mb-3'>
                                                <label for='circuit_name' class='form-label'>Circuit Name</label>
                                                <input type='text' class='form-control' name='circuit_name' value='{$row['circuit_name']}' required>
                                            </div>
                                            <div class='mb-3'>
                                                <label for='circuit_location' class='form-label'>Location</label>
                                                <input type='text' class='form-control' name='circuit_location' value='{$row['circuit_location']}' required>
                                            </div>
                                            <div class='mb-3'>
                                                <label for='circuit_country' class='form-label'>Country</label>
                                                <input type='text' class='form-control' name='circuit_country' value='{$row['circuit_country']}' required>
                                            </div>
                                            <div class='mb-3'>
                                                <label for='latitude' class='form-label'>Latitude</label>
                                                <input type='text' class='form-control' name='latitude' value='{$row['latitude']}' required>
                                            </div>
                                            <div class='mb-3'>
                                                <label for='longitude' class='form-label'>Longitude</label>
                                                <input type='text' class='form-control' name='longitude' value='{$row['longitude']}' required>
                                            </div>
                                            <div class='mb-3'>
                                                <label for='altitude' class='form-label'>Altitude</label>
                                                <input type='text' class='form-control' name='altitude' value='{$row['altitude']}' required>
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

                        <nav>
                <ul class="pagination justify-content-center">
                    <?php
                    $adjacents = 7;
                    $start = max(1, $page - $adjacents);
                    $end = min($total_pages, $page + $adjacents);

                    if ($total_pages > $adjacents && $page > 1) {
                        echo "<li class='page-item'><a class='page-link' href='crud_circuits.php?page=1'>First</a></li>";
                        echo "<li class='page-item'><a class='page-link' href='crud_circuits.php?page=" . ($page - 1) . "'>&laquo; Prev</a></li>";
                    }

                    // Show page number links
                    for ($i = $start; $i <= $end; $i++) {
                        $active = ($i == $page) ? 'active' : '';
                        echo "<li class='page-item $active'><a class='page-link' href='crud_circuits.php?page=$i'>$i</a></li>";
                    }

                    if ($total_pages > $adjacents && $page < $total_pages) {
                        echo "<li class='page-item'><a class='page-link' href='crud_circuits.php?page=" . ($page + 1) . "'>Next &raquo;</a></li>";
                        echo "<li class='page-item'><a class='page-link' href='crud_circuits.php?page=$total_pages'>Last</a></li>";
                    }
                    ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<div class="modal fade" id="createCircuitModal" tabindex="-1" aria-labelledby="createCircuitLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createCircuitLabel">Create New Circuit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="circuit_name" class="form-label">Circuit Name</label>
                        <input type="text" class="form-control" name="circuit_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="circuit_location" class="form-label">Location</label>
                        <input type="text" class="form-control" name="circuit_location" required>
                    </div>
                    <div class="mb-3">
                        <label for="circuit_country" class="form-label">Country</label>
                        <input type="text" class="form-control" name="circuit_country" required>
                    </div>
                    <div class="mb-3">
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="text" class="form-control" name="latitude" required>
                    </div>
                    <div class="mb-3">
                        <label for="longitude" class="form-label">Longitude</label>
                        <input type="text" class="form-control" name="longitude" required>
                    </div>
                    <div class="mb-3">
                        <label for="altitude" class="form-label">Altitude</label>
                        <input type="text" class="form-control" name="altitude" required>
                    </div>
                    <div class="mb-3">
                        <label for="url" class="form-label">URL</label>
                        <input type="text" class="form-control" name="url" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="create">Create Circuit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php ob_end_flush(); ?>
