<?php
session_start();
include_once '../Config/database.php';
include_once 'admin-class.php';

$db = new Database();
$admin = new Admin($db);

// Pagination Logic
$limit = 5; // Number of courses per page
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $limit;

// Fetch paginated courses and total count
$total_courses = $admin->getCourseCount();
$total_pages = ceil($total_courses / $limit);
$courses = $admin->getCoursesPaginated($limit, $offset);

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
        $edit_name = trim($_POST['edit_course_name']);
        $edit_description = trim($_POST['edit_course_description']);
        if (!empty($edit_name) && !empty($edit_description)) {
            $edit_name = htmlspecialchars($edit_name);
            $edit_description = htmlspecialchars($edit_description);
            if ($admin->editCourse($edit_id, $edit_name, $edit_description)) {
                $_SESSION['success'] = "Course updated successfully!";
            } else {
                $_SESSION['error'] = "Failed to update course.";
            }
        } else {
            $_SESSION['error'] = "Course name and description cannot be empty.";
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
$courses_result = $admin->getCourses(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Dashboard - Library System</title>
    <style>
        body {
            font-family: 'Times New Roman', sans-serif;
            background-color: #c6dce769;
            margin: 0;
            padding: 40px;
            color: #333;
        }

        h3 {
            margin-bottom: 5px;
            color: #4a3f35;
        }

        table {
            width: 90%;
            border-collapse: collapse;
            margin-top: 5px;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            margin-left: auto;
            margin-right: auto;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px 10px;
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
            background-color: #886a527e;
        }

        button, a.button {
            display: inline-block;
            background-color: #805c41;
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
            background-color: #65452f;
            transform: scale(1.05);
        }

        button:active, a.button:active {
            transform: scale(0.98);
        }
    </style>
</head>
<body>
    <h2>Course Dashboard</h2>

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
        <input type="text" name="course_name" id="course_name" required><br><br>
        <label for="description">Description:</label>
        <textarea name="description" rows="3" cols="40" required></textarea><br><br>
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

    <!-- Pagination Links -->
    <div style="text-align: center; margin-top: 20px;">
        <?php if ($total_pages > 1): ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" 
                   style="margin: 0 5px; padding: 5px 10px; background: <?php echo $current_page == $i ? '#65452f' : '#805c41'; ?>;
                    color: #fff; border-radius: 3px; text-decoration: none;">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        <?php endif; ?>
    </div>

    <script>
        function editCourse(id, currentName, currentDescription) {
            var newName = prompt("Edit course name:", currentName);
            var newDescription = prompt("Edit course description:", currentDescription);
            if (newName && newDescription) {
                var form = document.createElement("form");
                form.method = "POST";
                form.action = "courses.php";

                form.appendChild(createInput('edit_id', id));
                form.appendChild(createInput('edit_course_name', newName));
                form.appendChild(createInput('edit_course_description', newDescription));
                form.appendChild(createInput('edit_course', '1'));

                document.body.appendChild(form);
                form.submit();
            }
        }

        function deleteCourse(id, name) {
            if (confirm("Are you sure you want to delete the course: " + name + "?")) {
                var form = document.createElement("form");
                form.method = "POST";
                form.action = "courses.php";

                form.appendChild(createInput('delete_id', id));
                form.appendChild(createInput('delete_course', '1'));

                document.body.appendChild(form);
                form.submit();
            }
        }

        function createInput(name, value) {
            var input = document.createElement("input");
            input.type = "hidden";
            input.name = name;
            input.value = value;
            return input;
        }
    </script>
</body>
<a href="dashboard.php" class="button">Dashboard</a>
</html>
