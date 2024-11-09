<?php
// Start output buffering to avoid headers already sent errors
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

// Find out the number of results stored in the database
$total_constructors = $db->constructors->countDocuments();

// Determine the number of total pages available
$total_pages = ceil($total_constructors / $results_per_page);

// Determine which page number the visitor is currently on
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;

// Determine the starting limit number
$start_limit = ($page - 1) * $results_per_page;

// Fetch the selected results from the database
$result = $db->constructors->find([], [
    'skip' => $start_limit,
    'limit' => $results_per_page
]);

// Create Constructor
if (isset($_POST['create'])) {
    $newConstructor = [
        'constructor_name' => $_POST['constructor_name'],
        'no_of_pole_positions' => (int)$_POST['no_of_pole_positions'],
        'no_of_titles' => (int)$_POST['no_of_titles'],
        'constructor_points' => (float)$_POST['constructor_points'],
        'nationality' => $_POST['nationality'],
        'url' => $_POST['url']
    ];

    $db->constructors->insertOne($newConstructor);

    $_SESSION['message'] = "Constructor added successfully!";
    header("Location: crud_constructors.php?page=" . $page);
    exit();
}

// Edit Constructor
if (isset($_POST['edit'])) {
    $id = $_POST['constructor_id'];

    $updatedConstructor = [
        'constructor_name' => $_POST['constructor_name'],
        'no_of_pole_positions' => (int)$_POST['no_of_pole_positions'],
        'no_of_titles' => (int)$_POST['no_of_titles'],
        'constructor_points' => (float)$_POST['constructor_points'],
        'nationality' => $_POST['nationality'],
        'url' => $_POST['url']
    ];

    $db->constructors->updateOne(
        ['_id' => new ObjectId($id)],
        ['$set' => $updatedConstructor]
    );

    $_SESSION['message'] = "Constructor updated successfully!";
    header("Location: crud_constructors.php?page=" . $page);
    exit();
}

// Delete Constructor
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $db->constructors->deleteOne(['_id' => new ObjectId($id)]);

    $_SESSION['message'] = "Constructor deleted successfully!";
    header("Location: crud_constructors.php?page=" . $page);
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
                        $id = (string)$row['_id'];
                        echo "<tr>";
                        echo "<td>{$id}</td>";
                        echo "<td>{$row['constructor_name']}</td>";
                        echo "<td>{$row['no_of_pole_positions']}</td>";
                        echo "<td>{$row['no_of_titles']}</td>";
                        echo "<td>{$row['constructor_points']}</td>";
                        echo "<td>{$row['nationality']}</td>";
                        echo "<td><a href='{$row['url']}' target='_blank'>Visit</a></td>";
                        echo "<td>
                            <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editConstructorModal{$id}'><i class='fas fa-edit'></i></button>
                            <a href='?delete={$id}' class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></a>
                          </td>";
                        echo "</tr>";
                        
                        // Edit Constructor Modal
                        echo "
                        <div class='modal fade' id='editConstructorModal{$id}' tabindex='-1' aria-labelledby='editConstructorLabel{$id}' aria-hidden='true'>
                            <div class='modal-dialog'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <h5 class='modal-title' id='editConstructorLabel{$id}'>Edit Constructor</h5>
                                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                    </div>
                                    <form action='' method='POST'>
                                        <div class='modal-body'>
                                            <input type='hidden' name='constructor_id' value='{$id}'>
                                            <div class='mb-3'>
                                                <label for='constructor_name' class='form-label'>Constructor Name</label>
                                                <input type='text' class='form-control' name='constructor_name' value='{$row['constructor_name']}' required>
                                            </div>
                                            <div class='mb-3'>
                                                <label for='no_of_pole_positions' class='form-label'>Pole Positions</label>
                                                <input type='number' class='form-control' name='no_of_pole_positions' value='{$row['no_of_pole_positions']}' required>
                                            </div>
                                            <div class='mb-3'>
                                                <label for='no_of_titles' class='form-label'>Titles</label>
                                                <input type='number' class='form-control' name='no_of_titles' value='{$row['no_of_titles']}' required>
                                            </div>
                                            <div class='mb-3'>
                                                <label for='constructor_points' class='form-label'>Points</label>
                                                <input type='number' class='form-control' name='constructor_points' value='{$row['constructor_points']}' required>
                                            </div>
                                            <div class='mb-3'>
                                                <label for='nationality' class='form-label'>Nationality</label>
                                                <input type='text' class='form-control' name='nationality' value='{$row['nationality']}' required>
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
                        echo "<li class='page-item'><a class='page-link' href='crud_constructors.php?page=1'>First</a></li>";
                        echo "<li class='page-item'><a class='page-link' href='crud_constructors.php?page=" . ($page - 1) . "'>&laquo; Prev</a></li>";
                    }

                    // Show page number links
                    for ($i = $start; $i <= $end; $i++) {
                        $active = ($i == $page) ? 'active' : '';
                        echo "<li class='page-item $active'><a class='page-link' href='crud_constructors.php?page=$i'>$i</a></li>";
                    }

                    if ($total_pages > $adjacents && $page < $total_pages) {
                        echo "<li class='page-item'><a class='page-link' href='crud_constructors.php?page=" . ($page + 1) . "'>Next &raquo;</a></li>";
                        echo "<li class='page-item'><a class='page-link' href='crud_constructors.php?page=$total_pages'>Last</a></li>";
                    }
                    ?>
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
                <h5 class='modal-title' id='createConstructorLabel'>Create New Constructor</h5>
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
