<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Form</title>
    <link rel="stylesheet" href="form.css">
</head>
<body>
    <h2>Admin Login</h2>
    <form action="login_process.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <input type="submit" value="Login">
    </form>

      <!-- Error/Success Messages -->
      <?php
    if (isset($_GET['error'])) {
        echo '<p class="error">Invalid username or password</p>';
    }
    if (isset($_GET['success'])) {
        echo '<p class="success">Signup successful, please login!</p>';
    }
    ?>
    
</body>
</html>
