<?php
session_start();
include 'db/db.php'; // Include the database connection

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validate input fields
    if (empty($_POST['username']) || empty($_POST['password'])) {
        header("Location: admin_login.php?error=empty_fields");
        exit();
    }
    
    // Sanitize input to prevent XSS attacks
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);

    // Fetch the admin details from MongoDB
    $admin = $db->admin->findOne(['username' => $username]);

    // Check if a user was found
    if ($admin) {
        // Verify the entered password against the hashed password in MongoDB
        if (password_verify($password, $admin['password'])) {
            // Correct password, regenerate session ID for security
            session_regenerate_id(true);

            // Store user data in session variables
            $_SESSION['username'] = $admin['username'];
            $_SESSION['admin_id'] = $admin['_id'];

            // Redirect to the admin dashboard
            header("Location: admin_dashboard.php");
            exit();
        } else {
            // Incorrect password
            header("Location: admin_login.php?error=incorrect_password");
            exit();
        }
    } else {
        // No user found with that username
        header("Location: admin_login.php?error=user_not_found");
        exit();
    }
} else {
    // Redirect to login page if accessed without form submission
    header("Location: admin_login.php");
    exit();
}
?>
