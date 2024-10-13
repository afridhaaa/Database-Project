<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Formula Vault - Admin Login</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./assets/css/login.css">
</head>
<body>
<section id="main">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 p-0 full">
                <?php include("side.php"); ?> 
            </div>
            <div class="col-md-10 p-0 d-flex justify-content-center align-items-center" style="height: 100vh;">
                <div class="login-container d-flex">
                    <!-- Illustration -->
                    <div class="illustration">
                        <img src="https://img.freepik.com/free-vector/access-control-system-abstract-concept-illustration_335657-3180.jpg" alt="Login Illustration" class="img-fluid">
                    </div>

                    <!-- Login Form -->
                    <div class="login-form-container">
                        <h2 class="mb-4">Login as Admin User</h2>
                        <form action="login_process.php" method="POST">
                            <div class="mb-3">
                                <input type="text" id="username" name="username" class="form-control" placeholder="Username" required>
                            </div>
                            <div class="mb-3">
                                <input type="password" id="password" name="password" class="form-control" placeholder="********" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">LOGIN</button>
                        </form>

                        <!-- Error/Success Messages -->
                        <?php
                            if (isset($_GET['error'])) {
                                if ($_GET['error'] == 'incorrect_password') {
                                    echo '<p class="text-danger mt-3">Incorrect password. Please try again.</p>';
                                } elseif ($_GET['error'] == 'user_not_found') {
                                    echo '<p class="text-danger mt-3">No account found with that username.</p>';
                                }
                            }
                        ?>
                    </div>
                </div>
            </div> 
        </div> 
    </div> 
</section>
</body>
</html>
