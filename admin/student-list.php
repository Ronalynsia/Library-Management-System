<?php
session_start();
require_once '../Config/database.php';
require_once 'admin-class.php';

$db = new Database();
$admin = new Admin($db);

if (isset($_GET['id'])) {
    $student = $admin->getStudentById($_GET['id']);
    echo json_encode($student);
    exit();
}

$courses = $admin->getAllCourses()->fetch_all(MYSQLI_ASSOC);

$limit = 5; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$students = $admin->getPaginatedStudents($limit, $offset);
$totalStudents = $admin->getStudentCount();
$totalPages = ceil($totalStudents / $limit);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
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
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_student'])) {
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
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_student'])) {
    $id = $_POST['delete_student'];

    if ($admin->deleteStudent($id)) {
        $_SESSION['success'] = "Student deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete student.";
    }
    header("Location: student-list.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        <?php while ($student = $students->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($student['course_name']); ?></td>
                <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                <td><?php echo htmlspecialchars($student['first_name']); ?></td>
                <td><?php echo htmlspecialchars($student['last_name']); ?></td>
                <td>
                    <button onclick="showEditForm(<?php echo $student['student_id']; ?>)">Edit</button>
                    <button onclick="confirmDelete(<?php echo $student['student_id']; ?>)">Delete</button>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<div style="text-align: center; margin-top: 20px;">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?php echo $i; ?>" style="margin: 0 5px; text-decoration: none; 
           <?php echo ($i === $page) ? 'font-weight: bold; color: #805c41;' : 'color: #333;' ?>">
            <?php echo $i; ?>
        </a>
    <?php endfor; ?>
</div>

<div id="add-student-modal" class="modal">
    <form method="POST">
        <h3>Add New Student</h3>
        <label for="first-name">First Name:</label>
        <input type="text" id="first-name" name="first_name" required><br>
        <label for="last-name">Last Name:</label>
        <input type="text" id="last-name" name="last_name" required><br>
        <label for="student-id">Student ID:</label>
        <input type="text" id="student-id" name="student_id" required><br>
        <label for="course">Course:</label>
        <select id="course" name="course_id">
            <option value="">Select a course</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
            <?php endforeach; ?>
        </select><br>
        <button type="submit" name="add_student">Add Student</button>
        <button type="button" onclick="hideAddForm()">Cancel</button>
    </form>
</div>

<div id="edit-student-modal" class="modal">
    <form method="POST">
        <h3>Edit Student</h3>
        <input type="hidden" id="edit-id" name="edit_id">
        <label for="edit-first-name">First Name:</label>
        <input type="text" id="edit-first-name" name="edit_first_name" required><br>
        <label for="edit-last-name">Last Name:</label>
        <input type="text" id="edit-last-name" name="edit_last_name" required><br>
        <label for="edit-course">Course:</label>
        <select id="edit-course" name="edit_course_id" required>
            <option value="">Select a course</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
            <?php endforeach; ?>
        </select><br>
        <button type="submit" name="edit_student">Save Changes</button>
        <button type="button" onclick="hideEditForm()">Cancel</button>
    </form>
</div>

<div class="overlay" onclick="hideAddForm()"></div>

<script>
    function showAddForm() {
        document.getElementById('add-student-modal').style.display = 'block';
        document.querySelector('.overlay').style.display = 'block';
    }

    function hideAddForm() {
        document.getElementById('add-student-modal').style.display = 'none';
        document.querySelector('.overlay').style.display = 'none';
    }

    function showEditForm(id) {
        document.getElementById('edit-id').value = id;
        document.getElementById('edit-student-modal').style.display = 'block';
        document.querySelector('.overlay').style.display = 'block';

        fetch('student-list.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                document.getElementById('edit-first-name').value = data.first_name;
                document.getElementById('edit-last-name').value = data.last_name;
                document.getElementById('edit-course').value = data.course_id;
            });
    }

    function hideEditForm() {
        document.getElementById('edit-student-modal').style.display = 'none';
        document.querySelector('.overlay').style.display = 'none';
    }

    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this student?')) {
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = 'student-list.php';
            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'delete_student';
            input.value = id;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
<a href="dashboard.php" class="button">Dashboard</a>
</body>
</html>
