<?php
session_start();
include 'db/db.php'; // Include the database connection

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_SESSION['admin_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if new password matches confirm password
    if ($new_password !== $confirm_password) {
        header("Location: update_password.php?error=Passwords do not match");
        exit();
    }

    // Fetch the current admin password from the database
    $stmt = $conn->prepare("SELECT password FROM admin WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    // Verify the current password
    if (!password_verify($current_password, $admin['password'])) {
        header("Location: update_password.php?error=Incorrect current password");
        exit();
    }

    // Hash the new password
    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update the password in the database
    $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_new_password, $admin_id);

    if ($stmt->execute()) {
        header("Location: update_password.php?success=1");
    } else {
        header("Location: update_password.php?error=Could not update password");
    }

    $stmt->close();
    $conn->close();
}
?>
