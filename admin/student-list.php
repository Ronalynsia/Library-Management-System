<?php
session_start();
require_once '../Config/database.php';
require_once 'admin-class.php';

$db = new Database();
$admin = new Admin($db);

$students = $admin->getAllStudents();
$categories = $admin->getAllCategories();

// Pagination setup
$limit = 5; // Students per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch paginated students and total student count
$students = $admin->getPaginatedStudents($limit, $offset);
$totalStudents = $admin->getStudentCount();
$totalPages = ceil($totalStudents / $limit);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_student'])) {
        // Handle adding a new student
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $course_id = $_POST['course_id'];

        if ($admin->addStudent($first_name, $last_name, $course_id)) {
            $_SESSION['success'] = "Student added successfully!";
        } else {
            $_SESSION['error'] = "Failed to add student.";
        }

        header("Location: total-students.php");
        exit();
    } elseif (isset($_POST['edit_student'])) {
        // Handle editing an existing student
        $id = $_POST['edit_id'];
        $first_name = $_POST['edit_first_name'];
        $last_name = $_POST['edit_last_name'];
        $course_id = $_POST['edit_course_id'];

        if ($admin->updateStudent($id, $first_name, $last_name, $course_id)) {
            $_SESSION['success'] = "Student updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update student.";
        }

        header("Location: total-students.php");
        exit();
    } elseif (isset($_POST['delete_student'])) {
        // Handle deleting a student
        $id = $_POST['delete_student'];

        if ($admin->deleteStudent($id)) {
            $_SESSION['success'] = "Student deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete student.";
        }

        header("Location: total-students.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Students List - Library System</title>
    <link rel="stylesheet" href="css/total-students.css">
</head>
<body>
<!-- Overlay -->
<div class="overlay" id="overlay" onclick="hideModal()"></div>

<!-- Add Student Modal -->
<div class="modal" id="add-form">
    <form method="POST">
        <h3>Add New Student</h3>
        <label>First Name:</label><input type="text" name="first_name" required><br>
        <label>Last Name:</label><input type="text" name="last_name" required><br>
        <label>Course:</label>
        <select name="course_id" required>
            <option value="">Select a course</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id']; ?>"><?= htmlspecialchars($category['category_name']); ?></option>
            <?php endforeach; ?>
        </select><br>
        <button type="submit" name="add_student">Add Student</button>
        <button type="button" onclick="hideModal()">Cancel</button>
    </form>
</div>

<!-- Edit Student Modal -->
<div class="modal" id="edit-form">
    <form method="POST">
        <h3>Edit Student</h3>
        <input type="hidden" name="edit_id" id="edit_id">
        <label>First Name:</label><input type="text" name="edit_first_name" id="edit_first_name" required><br>
        <label>Last Name:</label><input type="text" name="edit_last_name" id="edit_last_name" required><br>
        <label>Course:</label>
        <select name="edit_course_id" id="edit_course_id" required>
            <option value="">Select a course</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id']; ?>"><?= htmlspecialchars($category['category_name']); ?></option>
            <?php endforeach; ?>
        </select><br>
        <button type="submit" name="edit_student">Update Student</button>
        <button type="button" onclick="hideModal()">Cancel</button>
    </form>
</div>

<h3>Student List</h3>
<!-- Student List -->
<button onclick="showModal('add-form')">Add New Student</button>
<table>
    <thead>
        <tr>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Course</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($students as $student): ?>
            <tr>
                <td><?= htmlspecialchars($student['first_name']); ?></td>
                <td><?= htmlspecialchars($student['last_name']); ?></td>
                <td><?= htmlspecialchars($student['course_name']); ?></td>
                <td>
                    <button onclick="showEditForm(
                        <?= $student['id']; ?>,
                        '<?= htmlspecialchars($student['first_name']); ?>',
                        '<?= htmlspecialchars($student['last_name']); ?>',
                        <?= $student['course_id']; ?>
                    )">Edit</button>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="delete_student" value="<?= $student['id']; ?>">
                        <button type="submit" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div style="text-align: center; margin-top: 20px;">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i; ?>" style="margin: 0 5px; text-decoration: none; 
           <?= ($i === $page) ? 'font-weight: bold; color: #805c41;' : 'color: #333;' ?>">
            <?= $i; ?>
        </a>
    <?php endfor; ?>
</div>

<script>
function showModal(modalId) {
    document.getElementById('overlay').classList.add('active');
    document.getElementById(modalId).classList.add('active');
}

function hideModal() {
    document.getElementById('overlay').classList.remove('active');
    document.querySelectorAll('.modal').forEach(modal => modal.classList.remove('active'));
}

function showEditForm(id, first_name, last_name, course_id) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_first_name').value = first_name;
    document.getElementById('edit_last_name').value = last_name;
    document.getElementById('edit_course_id').value = course_id;
    showModal('edit-form');
}
</script>

<a href="dashboard.php" class="button">Dashboard</a>

</body>
</html>
