<?php
include 'db/db.php';

$constructor_id = $_GET['id'];

$sql = "DELETE FROM constructors WHERE constructor_id = $constructor_id";

if ($conn->query($sql) === TRUE) {
    header("Location: admin_dashboard.php");
} else {
    echo "Error deleting constructor: " . $conn->error;
}

$conn->close();
?>
