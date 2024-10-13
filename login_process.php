<?php
session_start();
include 'db/db.php'; // Include your database connection

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

    // Prepare and execute the query to fetch the admin details
    $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if a user was found
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            // Verify the entered password against the hashed password in the database
            if (password_verify($password, $admin['password'])) {
                // Correct password, regenerate session ID for security
                session_regenerate_id(true);

                // Store user data in session variables
                $_SESSION['username'] = $admin['username'];
                $_SESSION['admin_id'] = $admin['id'];

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

        $stmt->close(); // Close statement
    } else {
        // SQL error
        header("Location: admin_login.php?error=sql_error");
        exit();
    }

    $conn->close(); // Close the database connection
} else {
    // Redirect to login page if accessed without form submission
    header("Location: admin_login.php");
    exit();
}
?>
