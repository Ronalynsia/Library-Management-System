<?php 
session_start();
include_once 'Config/database.php';
include_once 'admin/admin-class.php';

$db = new Database();
$admin = new Admin($db);

if (isset($_POST['login'])) {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = htmlspecialchars(trim($_POST['password']));

    $login_result = $admin->login($username, $password);
    if ($login_result === 'success') {
        header('Location: admin/dashboard.php');
        exit();
    } else {
        $_SESSION['error'] = $login_result;
    }
}

if (isset($_POST['register'])) {
    $new_username = htmlspecialchars(trim($_POST['new_username']));
    $new_password = htmlspecialchars(trim($_POST['new_password']));
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $register_result = $admin->register($new_username, $hashed_password, $first_name, $last_name);
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
    <link rel="stylesheet" href=""> 
    <title>Library System - Admin Sign In / Register</title>
    <style>
       body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: Arial , sans-serif;
        }
        
        .frame {
            height: 100%;
            background: url('src/css/image/index.png') no-repeat center center fixed;
            background-size: cover;
            image-rendering: crisp-edges; 
            -webkit-optimize-contrast;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .header {
            background-color: rgba(0, 0, 0, 0.6);
            color: #fff;
            text-align: center;
            padding: 30px;
            margin-bottom: 50px;
            width: 100%;
        }

        .header h1 {
            margin: 0;
        }
        .form-section h4 {
            margin: 5px;
            font-size: 20px;
        }

        .content {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 90%;
            height: 60%;
            max-width: 400px;
        }

        .form-container input, .form-container button {
            width: 100%;
            padding: 5px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 15px;
        }

        .form-container button {
            background-color: #62442A; 
            color: #fff;
            border: none;
            cursor: pointer;
        }

        .form-container button:hover {
            background-color: #50381F; 
        }

        p {
            font-size: 30px;
            margin: 5px 0;
        }

        p[style*="color:red"] {
            color: #ff4d4d;
        }

        p[style*="color:green"] {
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="frame">
        <div class="header">
            <h1>Welcome to the Library</h1>
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
        
        <!-- Error and success messages -->
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
