<?php
require_once '../Config/database.php';
require_once 'admin-class.php';

$db = new Database();
$admin = new Admin($db);

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $student_id = $_GET['id'];
    $student = $admin->getStudentById($student_id);

    if ($student) {
        echo json_encode($student);
    } else {
        echo json_encode(['error' => 'Student not found']);
    }
} else {
    echo json_encode(['error' => 'No student ID provided']);
}
?>
