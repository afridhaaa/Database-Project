<?php
include 'db/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $constructor_id = $_POST['constructor_id'];
    $constructor_name = $_POST['constructor_name'];
    $no_of_pole_positions = $_POST['no_of_pole_positions'];
    $no_of_titles = $_POST['no_of_titles'];
    $constructor_points = $_POST['constructor_points'];
    $nationality = $_POST['nationality'];
    $url = $_POST['url'];

    $sql = "UPDATE constructors 
            SET constructor_name='$constructor_name', no_of_pole_positions='$no_of_pole_positions', no_of_titles='$no_of_titles', 
                constructor_points='$constructor_points', nationality='$nationality', url='$url' 
            WHERE constructor_id = $constructor_id";

    if ($conn->query($sql) === TRUE) {
        header("Location: admin_dashboard.php");
    } else {
        echo "Error updating constructor: " . $conn->error;
    }

    $conn->close();
}
?>
