<?php
include 'db/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $forename = $_POST['forename'];
    $points = $_POST['points'];
    $position = $_POST['position'];
    $wins = $_POST['wins'];
    $nationality = $_POST['nationality'];
    $url = $_POST['url'];

    // Assuming you have a `drivers` table where you will insert the driver first and a driver_standings table
    $insert_driver = "INSERT INTO drivers (forename, nationality, url) VALUES ('$forename', '$nationality', '$url')";
    
    if ($conn->query($insert_driver) === TRUE) {
        $driverId = $conn->insert_id;  // Get the last inserted driver ID
        $insert_standing = "INSERT INTO driver_standings (driverId, points, position, wins) VALUES ('$driverId', '$points', '$position', '$wins')";
        
        if ($conn->query($insert_standing) === TRUE) {
            header("Location: admin_dashboard.php");
        } else {
            echo "Error inserting driver standing: " . $conn->error;
        }
    } else {
        echo "Error inserting driver: " . $conn->error;
    }

    $conn->close();
}
?>
