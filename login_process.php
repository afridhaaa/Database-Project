<?php
session_start();
include 'db/db.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch admin data based on the username
    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Fetch admin details
        $admin = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $admin['password'])) {
            // Password is correct, start the session
            $_SESSION['username'] = $admin['username'];
            $_SESSION['admin_id'] = $admin['id'];
            
            // Redirect to the admin dashboard
            header("Location: admin_dashboard.php");
            exit();
        } else {
            // Invalid password
            header("Location: admin_login.php?error=1");
            exit();
        }
    } else {
        // Username not found
        header("Location: admin_login.php?error=1");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
