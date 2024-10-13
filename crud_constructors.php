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
$sql = "SELECT COUNT(constructor_id) AS total FROM constructors";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_constructors = $row['total'];

// Determine the number of total pages available
$total_pages = ceil($total_constructors / $results_per_page);

// Determine which page number the visitor is currently on
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;

// Determine the starting limit number
$start_limit = ($page - 1) * $results_per_page;

// Fetch the selected results from the database
$sql = "SELECT * FROM constructors LIMIT " . $start_limit . ", " . $results_per_page;
$result = $conn->query($sql);

// Create Constructor
if (isset($_POST['create'])) {
    $name = $_POST['constructor_name'];
    $pole_positions = $_POST['no_of_pole_positions'];
    $titles = $_POST['no_of_titles'];
    $points = $_POST['constructor_points'];
    $nationality = $_POST['nationality'];
    $url = $_POST['url'];

    $sql = "INSERT INTO constructors (constructor_name, no_of_pole_positions, no_of_titles, constructor_points, nationality, url) 
            VALUES ('$name', '$pole_positions', '$titles', '$points', '$nationality', '$url')";
    $conn->query($sql);

    header("Location: crud_constructors.php");
    exit();
}

// Edit Constructor
if (isset($_POST['edit'])) {
    $id = $_POST['constructor_id'];
    $name = $_POST['constructor_name'];
    $pole_positions = $_POST['no_of_pole_positions'];
    $titles = $_POST['no_of_titles'];
    $points = $_POST['constructor_points'];
    $nationality = $_POST['nationality'];
    $url = $_POST['url'];

    $sql = "UPDATE constructors SET constructor_name='$name', no_of_pole_positions='$pole_positions', no_of_titles='$titles', 
            constructor_points='$points', nationality='$nationality', url='$url' WHERE constructor_id='$id'";
    $conn->query($sql);

    header("Location: crud_constructors.php");
    exit();
}

// Delete Constructor
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM constructors WHERE constructor_id='$id'";
    $conn->query($sql);

    header("Location: crud_constructors.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Constructors</title>
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
            <h1>Manage Constructors</h1>
            <p>Manage constructor records here. Create, view, edit, or delete constructors.</p>

            <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#createConstructorModal">
                <i class="fas fa-plus"></i> Create New Constructor
            </button>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>No. of Pole Positions</th>
                        <th>No. of Titles</th>
                        <th>Points</th>
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
                            echo "<td>{$row['constructor_id']}</td>";
                            echo "<td>{$row['constructor_name']}</td>";
                            echo "<td>{$row['no_of_pole_positions']}</td>";
                            echo "<td>{$row['no_of_titles']}</td>";
                            echo "<td>{$row['constructor_points']}</td>";
                            echo "<td>{$row['nationality']}</td>";
                            echo "<td><a href='{$row['url']}' target='_blank'>Visit</a></td>";
                            echo "<td>
                                <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editConstructorModal{$row['constructor_id']}'><i class='fas fa-edit'></i></button>
                                <a href='?delete={$row['constructor_id']}' class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></a>
                              </td>";
                            echo "</tr>";

                            // Edit Constructor Modal
                            echo "
                            <div class='modal fade' id='editConstructorModal" . $row['constructor_id'] . "' tabindex='-1' aria-labelledby='editConstructorLabel' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='editConstructorLabel'>Edit Constructor</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <form action='' method='POST'>
                                            <div class='modal-body'>
                                                <input type='hidden' name='constructor_id' value='" . $row['constructor_id'] . "'>
                                                <div class='mb-3'>
                                                    <label for='constructor_name' class='form-label'>Constructor Name</label>
                                                    <input type='text' class='form-control' name='constructor_name' value='" . $row['constructor_name'] . "' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='no_of_pole_positions' class='form-label'>Pole Positions</label>
                                                    <input type='number' class='form-control' name='no_of_pole_positions' value='" . $row['no_of_pole_positions'] . "' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='no_of_titles' class='form-label'>Titles</label>
                                                    <input type='number' class='form-control' name='no_of_titles' value='" . $row['no_of_titles'] . "' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='constructor_points' class='form-label'>Points</label>
                                                    <input type='number' class='form-control' name='constructor_points' value='" . $row['constructor_points'] . "' required>
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='nationality' class='form-label'>Nationality</label>
                                                    <input type='text' class='form-control' name='nationality' value='" . $row['nationality'] . "' required>
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
                        echo "<tr><td colspan='8'>No constructors found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                            <a class="page-link" href="crud_constructors.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Create Constructor Modal -->
<div class="modal fade" id="createConstructorModal" tabindex="-1" aria-labelledby="createConstructorLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createConstructorLabel">Create New Constructor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="constructor_name" class="form-label">Constructor Name</label>
                        <input type="text" class="form-control" name="constructor_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="no_of_pole_positions" class="form-label">Pole Positions</label>
                        <input type="number" class="form-control" name="no_of_pole_positions" required>
                    </div>
                    <div class="mb-3">
                        <label for="no_of_titles" class="form-label">Titles</label>
                        <input type="number" class="form-control" name="no_of_titles" required>
                    </div>
                    <div class="mb-3">
                        <label for="constructor_points" class="form-label">Points</label>
                        <input type="number" class="form-control" name="constructor_points" required>
                    </div>
                    <div class="mb-3">
                        <label for="nationality" class="form-label">Nationality</label>
                        <input type="text" class="form-control" name="nationality" required>
                    </div>
                    <div class="mb-3">
                        <label for="url" class="form-label">URL</label>
                        <input type="text" class="form-control" name="url" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="create">Create Constructor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php ob_end_flush(); ?>
