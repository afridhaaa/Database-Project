<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Password</title>
    <link rel="stylesheet" href="form.css">
</head>
<body>
    <h2>Update Password</h2>
    <form action="update_password_process.php" method="POST">
        <label for="current_password">Current Password:</label>
        <input type="password" id="current_password" name="current_password" required><br><br>
        
        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password" required><br><br>

        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required><br><br>

        <input type="submit" value="Update Password">
    </form>

    <!-- Error/Success Messages -->
    <?php
    if (isset($_GET['error'])) {
        echo '<p class="error">Error: ' . $_GET['error'] . '</p>';
    }
    if (isset($_GET['success'])) {
        echo '<p class="success">Password updated successfully!</p>';
    }
    ?>
</body>
</html>
