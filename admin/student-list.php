<?php
session_start();
require_once '../Config/database.php';
require_once 'admin-class.php';

$db = new Database();
$admin = new Admin($db);

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // Ensure the page is at least 1
$offset = ($page - 1) * $limit;

// Fetch students and total count
$students = $admin->getPaginatedStudents($limit, $offset);
$total_students = $admin->getTotalStudentsCount();
$total_pages = ceil($total_students / $limit);

// Fetch courses
$courses = $admin->getAllCourses();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_student'])) {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $student_id = $_POST['student_id'];
        $course_id = $_POST['course_id'];

        if ($admin->addStudent($first_name, $last_name, $course_id, $student_id)) {
            $_SESSION['success'] = "Student added successfully!";
        } else {
            $_SESSION['error'] = "Failed to add student.";
        }

        header("Location: student-list.php");
        exit();
    } elseif (isset($_POST['edit_student'])) {
        $id = $_POST['edit_id'];
        $first_name = $_POST['edit_first_name'];
        $last_name = $_POST['edit_last_name'];
        $course_id = $_POST['edit_course_id'];

        if ($admin->updateStudent($id, $first_name, $last_name, $course_id)) {
            $_SESSION['success'] = "Student updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update student.";
        }

        header("Location: student-list.php");
        exit();
    } elseif (isset($_POST['delete_student'])) {
        $id = $_POST['delete_student'];

        if ($admin->deleteStudent($id)) {
            $_SESSION['success'] = "Student deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete student.";
        }

        header("Location: student-list.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student List - Library System</title>
    <link rel="stylesheet" href="css/student-list.css">
</head>
<body>

<h3>Student List</h3>

<?php if (isset($_SESSION['success'])): ?>
    <p style="color: green;">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    </p>
<?php elseif (isset($_SESSION['error'])): ?>
    <p style="color: red;">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </p>
<?php endif; ?>

<button onclick="showAddForm()">+ New Student</button>

<table>
    <thead>
        <tr>
            <th>Course</th>
            <th>Student ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($students as $student): ?>
            <tr>
                <td><?php echo htmlspecialchars($student['course_name']); ?></td>
                <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                <td><?php echo htmlspecialchars($student['first_name']); ?></td>
                <td><?php echo htmlspecialchars($student['last_name']); ?></td>
                <td>
                    <button onclick="showEditForm(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['first_name']); ?>', '<?php echo htmlspecialchars($student['last_name']); ?>', <?php echo $student['course_id']; ?>)">Edit</button>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="delete_student" value="<?php echo $student['id']; ?>">
                        <button type="submit" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<nav>
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?php echo $i; ?>" <?php if ($i === $page) echo 'style="font-weight:bold;"'; ?>><?php echo $i; ?></a>
    <?php endfor; ?>
</nav>

<div class="overlay" id="overlay" onclick="hideForms()"></div>

<!-- Add Student Modal -->
<div class="modal" id="add-student-modal">
    <form method="POST">
        <h3>Add New Student</h3>
        <label>First Name:</label><input type="text" name="first_name" required><br>
        <label>Last Name:</label><input type="text" name="last_name" required><br>
        <label>Student ID:</label><input type="text" name="student_id" required><br>
        <label>Course:</label>
        <select name="course_id" required>
            <option value="">Select a course</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
            <?php endforeach; ?>
        </select><br>
        <button type="submit" name="add_student">Add Student</button>
        <button type="button" onclick="hideForms()">Cancel</button>
    </form>
</div>

<!-- Edit Student Modal -->
<div class="modal" id="edit-student-modal">
    <form method="POST">
        <h3>Edit Student</h3>
        <input type="hidden" name="edit_id" id="edit-id">
        <label>First Name:</label><input type="text" name="edit_first_name" id="edit-first-name" required><br>
        <label>Last Name:</label><input type="text" name="edit_last_name" id="edit-last-name" required><br>
        <label>Course:</label>
        <select name="edit_course_id" id="edit-course" required>
            <option value="">Select a course</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
            <?php endforeach; ?>
        </select><br>
        <button type="submit" name="edit_student">Update Student</button>
        <button type="button" onclick="hideForms()">Cancel</button>
    </form>
</div>

<script>
function showAddForm() {
    document.getElementById('add-student-modal').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
}

function showEditForm(id, firstName, lastName, courseId) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-first-name').value = firstName;
    document.getElementById('edit-last-name').value = lastName;
    document.getElementById('edit-course').value = courseId;
    document.getElementById('edit-student-modal').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
}

function hideForms() {
    document.querySelectorAll('.modal').forEach(modal => modal.style.display = 'none');
    document.getElementById('overlay').style.display = 'none';
}
</script>

<a href="dashboard.php" class="button">Dashboard</a>

</body>
</html>
