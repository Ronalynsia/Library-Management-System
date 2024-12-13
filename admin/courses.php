<?php
session_start();
require_once '../Config/database.php';
require_once 'admin-class.php';

$db = new Database();
$admin = new Admin($db);

// Handle Pagination
$limit = 5; // Rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1); // Ensure page is at least 1
$offset = ($page - 1) * $limit;

$totalCourses = $admin->getCourseCount();
$totalPages = ceil($totalCourses / $limit);

$courses_result = $admin->getCoursesWithLimit($offset, $limit);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['add_course'])) {
        $course_name = trim($_POST['course_name']);
        $description = trim($_POST['description']);
        if (!empty($course_name) && !empty($description)) {

            $course_name = htmlspecialchars($course_name);
            $description = htmlspecialchars($description);
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
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/courses.css">
    <title>Course List - Library System</title>

<style>
    body {
        font-family: 'Times New Roman', sans-serif;
        background-color: #f4e3cf;
        margin: 0;
        padding: 0;
        color: #5e4033;
        padding: 40px;
    }

    h3 {
        margin-bottom: 5px;
        color: #5e4033;
    }

    table {
        width: 90%;
        border-collapse: collapse;
        margin-top: 5px;
        background: #f7e7d1;
        border-radius: 8px;
        overflow: hidden;
        margin-left: auto;
        margin-right: auto;
    }

    th, td {
        border: 1px solid #d1b894;
        padding: 8px 10px;
        text-align: center;
        font-size: 15px;
    }

    th {
        background-color: #b4846c;
        color: #fff;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    tr:hover {
        background-color: #ecd4c3;
    }


    button, a.button {
        display: inline-block;
        background-color: #b4846c;
        color: #fff;
        padding: 8px 12px;
        margin: 5px;
        border: none;
        border-radius: 5px;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
        font-size: 15px;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    button:hover, a.button:hover {
        background-color: #a06e59;
        transform: scale(1.05);
    }

    button:active, a.button:active {
        transform: scale(0.98);
    }

    .pagination {
        margin: 20px auto;
        text-align: center;
    }

    .pagination a {
        display: inline-block;
        padding: 8px 12px;
        margin: 0 5px;
        background-color: #b4846c;
        color: #fff;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    .pagination a:hover {
        background-color: #a06e59;
    }

    .pagination a.active {
        background-color: #8b5947;
        pointer-events: none;
    }
</style>
</head>
<body>

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

    <h3>Add New Course</h3>
    <form action="courses.php" method="POST">
        <label for="course_name">Course Name:</label>
        <input type="text" name="course_name" required><br><br>
        <label for="description">Description:</label>
        <textarea name="description" rows="4" cols="50" required></textarea><br><br>
        <button type="submit" name="add_course">Add Course</button>
    </form>
    <hr>

    <h3>Existing Courses</h3>
    <table>
        <thead>
            <tr>
                <th>Course Name</th>
                <th>Description</th>
                <th>Controls</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($course = $courses_result->fetch_assoc()) { ?>
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

    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
            <a href="?page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php } ?>
    </div>



    <script>
        function editCourse(id, currentName, currentDescription){
            var newName = prompt("Edit course name:", currentName);
            var newDescription = prompt("Edit course description:", currentDescription);
            if (newName && newDescription){
                var form = document.createElement("form");
                form.method = "POST";
                form.action = "courses.php";

                var inputId = document.createElement("input");
                inputId.type = "hidden";
                inputId.name = "edit_id";
                inputId.value = id;
                form.appendChild(inputId);
                
                var inputName = document.createElement("input");
                inputName.type = "hidden";
                inputName.name = "edit_course_name";
                inputName.value = newName;
                form.appendChild(inputName);
            }
        }
</script>
        <a href="dashboard.php" class="button">Dashboard</a>

</body>
</html>
