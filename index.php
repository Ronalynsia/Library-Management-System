<?php
    include_once 'config/settings-configuration.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In / Register</title>
    <link rel="stylesheet" href="src/css/styles.css">
</head>
<body>
<div class="container">
    <div class="form-wrapper">
        <!-- SIGN IN FORM -->
        <div class="form-container">
            <h1>SIGN IN</h1>
            <form action="dashboard/admin/authentication/admin-class.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="email" name="email" placeholder="Enter Email" required>
                <input type="password" name="password" placeholder="Enter Password" required>
                <button type="submit" name="btn-signin">SIGN IN</button>
            </form>
            <div class="footer-links">
                <a href="forgot-password.php">Forgot Password?</a>
                <h5>ALREADY HAVE AN ACCOUNT?</h5>
            </div>
        </div>

        <!-- REGISTER FORM -->
        <div class="form-container">
            <h1>REGISTER</h1>
            <form action="dashboard/admin/authentication/admin-class.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="text" name="username" placeholder="Enter Username" required>
                <input type="email" name="email" placeholder="Enter Email" required>
                <input type="password" name="password" placeholder="Enter Password" required>
                <button type="submit" name="btn-signup">SIGN UP</button>
            </form>
            <div>
                <h5>DON'T HAVE AN ACCOUNT?</h5>
            </div>
        </div>
    </div>
</div>
</body>
</html>

