<?php
// Start output buffering to avoid headers already sent errors
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
$sql = "SELECT COUNT(circuit_id) AS total FROM circuits";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_circuits = $row['total'];

// Determine the number of total pages available
$total_pages = ceil($total_circuits / $results_per_page);

// Determine which page number the visitor is currently on
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;

// Determine the starting limit number
$start_limit = ($page - 1) * $results_per_page;

// Fetch the selected results from the database
$sql = "SELECT * FROM circuits LIMIT " . $start_limit . ", " . $results_per_page;
$result = $conn->query($sql);

// Create Circuit
if (isset($_POST['create'])) {
    $name = $_POST['circuit_name'];
    $location = $_POST['circuit_location'];
    $country = $_POST['circuit_country'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $altitude = $_POST['altitude'];
    $url = $_POST['url'];

    $sql = "INSERT INTO circuits (circuit_name, circuit_location, circuit_country, latitude, longitude, altitude, url) 
            VALUES ('$name', '$location', '$country', '$latitude', '$longitude', '$altitude', '$url')";
    $conn->query($sql);

    header("Location: crud_circuits.php");
    exit();
}

// Edit Circuit
if (isset($_POST['edit'])) {
    $id = $_POST['circuit_id'];
    $name = $_POST['circuit_name'];
    $location = $_POST['circuit_location'];
    $country = $_POST['circuit_country'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $altitude = $_POST['altitude'];
    $url = $_POST['url'];

    $sql = "UPDATE circuits SET circuit_name='$name', circuit_location='$location', circuit_country='$country', 
            latitude='$latitude', longitude='$longitude', altitude='$altitude', url='$url' WHERE circuit_id='$id'";
    $conn->query($sql);

    header("Location: crud_circuits.php");
    exit();
}

// Delete Circuit
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM circuits WHERE circuit_id='$id'";
    $conn->query($sql);

    header("Location: crud_circuits.php");
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
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>{$row['circuit_id']}</td>";
                            echo "<td>{$row['circuit_name']}</td>";
                            echo "<td>{$row['circuit_location']}</td>";
                            echo "<td>{$row['circuit_country']}</td>";
                            echo "<td>{$row['latitude']}</td>";
                            echo "<td>{$row['longitude']}</td>";
                            echo "<td>{$row['altitude']}</td>";
                            echo "<td><a href='{$row['url']}' target='_blank'>Visit</a></td>";
                            echo "<td>
                                <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editCircuitModal{$row['circuit_id']}'><i class='fas fa-edit'></i></button>
                                <a href='?delete={$row['circuit_id']}' class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></a>
                              </td>";
                            echo "</tr>";

                            // Edit Circuit Modal
                            echo "
                            <div class='modal fade' id='editCircuitModal" . $row['circuit_id'] . "' tabindex='-1' aria-labelledby='editCircuitLabel' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='editCircuitLabel'>Edit Circuit</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <form action='' method='POST'>
                                            <div class='modal-body'>
                                                <input type='hidden' name='circuit_id' value='" . $row['circuit_id'] . "'>
                                                <div class='mb-3'>
                                                    <label for='circuit_name' class='form-label'>Circuit Name</label>
                                                    <input type='text' class='form-control' name='circuit_name' value='" . $row['circuit_name'] . "' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='circuit_location' class='form-label'>Location</label>
                                                    <input type='text' class='form-control' name='circuit_location' value='" . $row['circuit_location'] . "' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='circuit_country' class='form-label'>Country</label>
                                                    <input type='text' class='form-control' name='circuit_country' value='" . $row['circuit_country'] . "' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='latitude' class='form-label'>Latitude</label>
                                                    <input type='text' class='form-control' name='latitude' value='" . $row['latitude'] . "' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='longitude' class='form-label'>Longitude</label>
                                                    <input type='text' class='form-control' name='longitude' value='" . $row['longitude'] . "' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='altitude' class='form-label'>Altitude</label>
                                                    <input type='text' class='form-control' name='altitude' value='" . $row['altitude'] . "' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='url' class='form-label'>URL</label>
                                                    <input type='text' class='form-control' name='url' value='" . $row['url'] . "' required>
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
                        echo "<tr><td colspan='9'>No circuits found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                            <a class="page-link" href="crud_circuits.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
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
