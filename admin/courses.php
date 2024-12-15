<?php
session_start();
require_once '../Config/database.php';
require_once 'admin-class.php';

$db = new Database();
$admin = new Admin($db);

// Pagination variables
$limit = 5; // Records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total course count and fetch paginated courses
$totalCourses = $admin->getCourseCount();
$totalPages = ceil($totalCourses / $limit);
$courses_result = $admin->getCourses($limit, $offset);

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
    header("Location: courses.php?page=$page");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course List - Library System</title>
    <style>
        body {
            font-family: 'Times New Roman', sans-serif;
            background-color: #94672b4b;
            margin: 0;
            padding: 40px;
            color: #333;
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
    </style>
</head>
<body>
    <h2>Course Management</h2>

    <?php
    if (isset($_SESSION['success'])) {
        echo "<p style='color: green'>{$_SESSION['success']}</p>";
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo "<p style='color: red'>{$_SESSION['error']}</p>";
        unset($_SESSION['error']);
    }
    ?>

    <!-- Add Course Form -->
    <h3>Add New Course</h3>
    <form action="courses.php?page=<?php echo $page; ?>" method="POST">
        <label>Course Name:</label>
        <input type="text" name="course_name" required>
        <label>Description:</label>
        <textarea name="description" required></textarea>
        <button type="submit" name="add_course">Add Course</button>
    </form>

    <!-- Course Table -->
    <h3>Existing Courses</h3>
    <table>
        <tr>
            <th>Course Name</th>
            <th>Description</th>
            <th>Controls</th>
        </tr>
        <?php while ($course = $courses_result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                <td><?php echo htmlspecialchars($course['description']); ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?php echo $course['id']; ?>">
                        <button type="submit" name="delete_course">Delete</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>

    <!-- Pagination Controls -->
    <div style="text-align: center;">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>" class="button">Previous</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" class="button" style="font-weight: <?php echo ($i == $page) ? 'bold' : 'normal'; ?>;">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>" class="button">Next</a>
        <?php endif; ?>
    </div>
    <br>
    <a href="dashboard.php" class="button">Back to Dashboard</a>
</body>
</html>
