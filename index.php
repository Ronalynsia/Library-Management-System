<?php 
session_start();
include_once 'Config/database.php';
include_once 'admin/admin-class.php';

$db = new Database();
$admin = new Admin($db);

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $login_result = $admin->login($username, $password);
    if ($login_result === 'success') {
        header('Location: admin/dashboard.php');
        exit();
    } else {
        $_SESSION['error'] = $login_result;
    }
}

if (isset($_POST['register'])) {
    $new_username = $_POST['new_username'];
    $new_password = $_POST['new_password'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];

    $register_result = $admin->register($new_username, $new_password, $first_name, $last_name);
    if ($register_result === 'Registration successful. You can now log in.') {
        $_SESSION['success'] = $register_result;
        header('Location: admin/index.php');
        exit();
    } else {
        $_SESSION['error'] = $register_result;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library System - Sign In / Register</title>
    <link rel="stylesheet" href="admin/css/index2.css"> <!-- Link to external CSS -->
</head>
<body>
    <div class="frame">
        <div class="header">
            <div class="header-container">
                <h1>Welcome to Our Library</h1>
                <h2>Sign In or Register</h2>
            </div>
        </div>
        <div class="content">
            <div class="form-container">
                <div class="form-section">
                    <h4>Sign In</h4>
                    <form action="" method="POST">
                        <input type="text" name="username" placeholder="Username" required>
                        <input type="password" name="password" placeholder="Password" required>
                        <button type="submit" name="login">Sign In</button>
                    </form>
                </div>
                <div class="form-section">
                    <h4>Register New Admin</h4>
                    <form action="" method="POST">
                        <input type="text" name="first_name" placeholder="First Name" required>
                        <input type="text" name="last_name" placeholder="Last Name" required>
                        <input type="text" name="new_username" placeholder="Username" required>
                        <input type="password" name="new_password" placeholder="Password" required>
                        <button type="submit" name="register">Register</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
        if (isset($_SESSION['error'])) {
            echo "<p style='color:red; text-align:center;'>{$_SESSION['error']}</p>";
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo "<p style='color:green; text-align:center;'>{$_SESSION['success']}</p>";
            unset($_SESSION['success']);
        }
        ?>
    </div>
</body>
</html>