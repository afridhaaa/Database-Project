<?php
include 'db/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $driverId = $_POST['driverId'];
    $forename = $_POST['forename'];
    $points = $_POST['points'];
    $position = $_POST['position'];
    $wins = $_POST['wins'];
    $nationality = $_POST['nationality'];
    $url = $_POST['url'];

    // Update the drivers table
    $update_driver = "UPDATE drivers 
                      SET forename = '$forename', nationality = '$nationality', url = '$url' 
                      WHERE driverId = $driverId";
                      
    if ($conn->query($update_driver) === TRUE) {
        // Update the driver_standings table
        $update_standing = "UPDATE driver_standings 
                            SET points = '$points', position = '$position', wins = '$wins' 
                            WHERE driverId = $driverId";

        if ($conn->query($update_standing) === TRUE) {
            header("Location: admin_dashboard.php");
        } else {
            echo "Error updating driver standing: " . $conn->error;
        }
    } else {
        echo "Error updating driver: " . $conn->error;
    }

    $conn->close();
}
?>
