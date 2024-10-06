<?php
include 'db/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $constructor_name = $_POST['constructor_name'];
    $no_of_pole_positions = $_POST['no_of_pole_positions'];
    $no_of_titles = $_POST['no_of_titles'];
    $constructor_points = $_POST['constructor_points'];
    $nationality = $_POST['nationality'];
    $url = $_POST['url'];

    $sql = "INSERT INTO constructors (constructor_name, no_of_pole_positions, no_of_titles, constructor_points, nationality, url) 
            VALUES ('$constructor_name', '$no_of_pole_positions', '$no_of_titles', '$constructor_points', '$nationality', '$url')";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: admin_dashboard.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
