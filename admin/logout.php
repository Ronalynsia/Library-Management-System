<?php
session_start();
include '../Config/database.php';
include 'admin-class.php';

$db = new Database();
$admin = new Admin($db);

if (isset($_SESSION['admin'])) {
    $admin_id = $_SESSION['admin'];


    $admin->logAction($admin_id, 'logout');


    session_destroy();
    header('Location: index.php');
    exit();
} else {
 
    header('Location: index.php');
    exit();
}
?>
