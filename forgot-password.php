<?php
    include_once 'config/settings-configuration.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="src/css/styles.css">
</head>
<body>

    <h2>Forgot Password</h2>
    <form action="dashboard/admin/authentication/admin-class.php" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token?>">
    <label for="email">Enter your email:</label>
    <input type="email" name="email" required>
    <button type="submit" name="btn-forgot-password">Submit</button>
</form>

</body>
</html>


