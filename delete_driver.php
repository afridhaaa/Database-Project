<?php
include 'db/db.php';

$driverId = $_GET['id'];

// First, delete from driver_standings
$delete_standing = "DELETE FROM driver_standings WHERE driverId = $driverId";

if ($conn->query($delete_standing) === TRUE) {
    // Then, delete from drivers
    $delete_driver = "DELETE FROM drivers WHERE driverId = $driverId";
    
    if ($conn->query($delete_driver) === TRUE) {
        header("Location: admin_dashboard.php");
    } else {
        echo "Error deleting driver: " . $conn->error;
    }
} else {
    echo "Error deleting driver standing: " . $conn->error;
}

$conn->close();
?>
