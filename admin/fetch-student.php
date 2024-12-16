<?php
require_once '../Config/database.php';
require_once 'admin-class.php';

$db = new Database();
$admin = new Admin($db);

if (isset($_GET['id'])) {
    $studentId = $_GET['id'];
    $student = $admin->getStudentById($studentId);

    if ($student) {
        header('Content-Type: application/json');
        echo json_encode([
            'id' => $student['student_id'],
            'first_name' => $student['first_name'],
            'last_name' => $student['last_name'],
            'course_id' => $student['course_id']
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Student not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
}
?>
