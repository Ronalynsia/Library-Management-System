<?php
    include_once 'config/settings-configuration.php';
    // Assume the reset token is passed via GET in the URL
    $token = $_GET['token'] ?? ''; // Get the token from the URL
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="src/css/styles.css">
</head>
<body>

    <form action="dashboard/admin/authentication/admin-class.php" method="POST">
        <h2>Reset Password</h2>
     
        <input type="hidden" name="token" value="<?php echo $token; ?>"> <!-- Ensure token is included -->
        
        <label for="new_password">New Password:</label>
        <input type="password" name="new_password" required>
        
        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" name="confirm_password" required>
        
        <button type="submit" name="btn-reset-password">Reset Password</button>
    </form>
</body>
</html>
