<?php
session_start();
require_once '../Config/database.php';
require_once 'admin-class.php';

$db = new Database();
$admin = new Admin($db);

// Pagination Logic
$limit = 5; // Records per page
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $limit;

// Get total course count and fetch paginated courses
$totalCourses = $admin->getCourseCount();
$totalPages = ceil($totalCourses / $limit);
$courses = $admin->getCourses($limit, $offset);

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_course'])) {
        $course_name = trim($_POST['course_name']);
        $description = trim($_POST['description']);
        if (!empty($course_name) && !empty($description)) {
            if ($admin->addCourse($course_name, $description)) {
                $_SESSION['success'] = "Course added successfully!";
            } else {
                $_SESSION['error'] = "Failed to add course.";
            }
        } else {
            $_SESSION['error'] = "Course name and description cannot be empty.";
        }
    }

    if (isset($_POST['edit_course'])) {
        $edit_id = $_POST['edit_id'];
        $edit_name = $_POST['edit_course_name'];
        $edit_description = $_POST['edit_course_description'];
        if ($admin->editCourse($edit_id, $edit_name, $edit_description)) {
            $_SESSION['success'] = "Course updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update course.";
        }
    }

    if (isset($_POST['delete_course'])) {
        $delete_id = $_POST['delete_id'];
        if ($admin->deleteCourse($delete_id)) {
            $_SESSION['success'] = "Course deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete course.";
        }
    }
    header("Location: courses.php?page=$current_page");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management - Library System</title>
    <style>
        body {
            font-family: 'Times New Roman', sans-serif;
            background-color: #94672b4b;
            margin: 0;
            padding: 40px;
            color: #333;
        }
        h2, h3 {
            text-align: center;
            color: #4a3f35;
        }
        table {
            width: 90%;
            border-collapse: collapse;
            margin: 20px auto;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            font-size: 15px;
        }
        th {
            background-color: #805c41;
            color: #fff;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #e0d6cc;
        }
        button, a.button {
            background-color: #805c41;
            color: #fff;
            padding: 8px 12px;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        button:hover, a.button:hover {
            background-color: #65452f;
        }
        .pagination {
            text-align: center;
            margin-top: 20px;
        }
        .pagination a {
            margin: 0 5px;
            padding: 5px 10px;
            background-color: #805c41;
            color: #fff;
            border-radius: 3px;
            text-decoration: none;
        }
        .pagination a.active {
            font-weight: bold;
            background-color: #65452f;
        }
    </style>
    <script>
        function editCourse(id, currentName, currentDescription) {
            let newName = prompt("Edit course name:", currentName);
            let newDescription = prompt("Edit course description:", currentDescription);
            if (newName && newDescription) {
                let form = document.createElement("form");
                form.method = "POST";
                form.action = "courses.php";

                let inputId = document.createElement("input");
                inputId.type = "hidden";
                inputId.name = "edit_id";
                inputId.value = id;
                form.appendChild(inputId);

                let inputName = document.createElement("input");
                inputName.type = "hidden";
                inputName.name = "edit_course_name";
                inputName.value = newName;
                form.appendChild(inputName);

                let inputDescription = document.createElement("input");
                inputDescription.type = "hidden";
                inputDescription.name = "edit_course_description";
                inputDescription.value = newDescription;
                form.appendChild(inputDescription);

                let inputSubmit = document.createElement("input");
                inputSubmit.type = "hidden";
                inputSubmit.name = "edit_course";
                form.appendChild(inputSubmit);

                document.body.appendChild(form);
                form.submit();
            }
        }

        function deleteCourse(id, name) {
            if (confirm(Are you sure you want to delete the course: ${name}?)) {
                let form = document.createElement("form");
                form.method = "POST";
                form.action = "courses.php";

                let inputId = document.createElement("input");
                inputId.type = "hidden";
                inputId.name = "delete_id";
                inputId.value = id;
                form.appendChild(inputId);

                let inputSubmit = document.createElement("input");
                inputSubmit.type = "hidden";
                inputSubmit.name = "delete_course";
                form.appendChild(inputSubmit);

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    </head>
<body>
    <h2>Course Management</h2>

    <?php
    if (isset($_SESSION['success'])) {
        echo "<p style='color: green; text-align: center;'>{$_SESSION['success']}</p>";
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo "<p style='color: red; text-align: center;'>{$_SESSION['error']}</p>";
        unset($_SESSION['error']);
    }
    ?>

    <h3>Add New Course</h3>
    <form action="courses.php?page=<?php echo $current_page; ?>" method="POST" style="text-align: center;">
        <label>Course Name:</label>
        <input type="text" name="course_name" required>
        <label>Description:</label>
        <textarea name="description" required></textarea>
        <button type="submit" name="add_course">Add Course</button>
    </form>

    <h3>Existing Courses</h3>
    <table>
        <thead>
            <tr>
                <th>Course Name</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($course = $courses->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                    <td><?php echo htmlspecialchars($course['description']); ?></td>
                    <td>
                        <button type="button" onclick="editCourse(<?php echo $course['id']; ?>, '<?php echo addslashes(htmlspecialchars($course['course_name'])); ?>', '<?php echo addslashes(htmlspecialchars($course['description'])); ?>')">Edit</button>
                        <button type="button" onclick="deleteCourse(<?php echo $course['id']; ?>, '<?php echo addslashes(htmlspecialchars($course['course_name'])); ?>')">Delete</button>
                    </td>
                </tr>
            <?php } ?>
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
    

    <a href="dashboard.php" class="button">Back to Dashboard</a>
</body>
</html>