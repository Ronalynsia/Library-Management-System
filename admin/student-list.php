<?php
session_start();
require_once '../Config/database.php';
require_once 'admin-class.php';


$db = new Database();
$admin = new Admin($db);


$students = $admin->getAllStudents();
$courses = $admin->getAllCourses();


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
    <title>Student List</title>
    <style>
        
         body {
            font-family: 'Times New Roman', sans-serif;
            background-color: #c6dce769;
            margin: 0;
            padding: 40px;
            color: #333;
        }

        h3 {
            margin-bottom: 20px;
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
           padding: 12px 10px;
         text-align: center;
           font-size: 15px;
        }

        th {
         background-color: #805c41;
         padding: 8px 12px;
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
            padding: 10px 15px;
            margin: 20px;
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

        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            border-radius: 8px;
            background-color: #fff;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        
        input, select {
            width: 100%;
            padding: 5px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        input:focus, select:focus {
            border-color: #805c41;
            outline: none;
            box-shadow: 0 0 5px rgba(166, 123, 91, 0.5);
        }

        form button {
            margin-top: 10px;
        }
            

    </style>
</head>
<body>
<h3>Student List</h3>

<?php if (isset($_SESSION['success'])): ?>
    <p style="color: green;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
<?php elseif (isset($_SESSION['error'])): ?>
    <p style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
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
            <?php while ($course = $courses->fetch_assoc()): ?>
                <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
            <?php endwhile; ?>
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
        <label for="course">Course:</label>
<select id="course" name="course_id" required>
  
    <?php while ($course = $courses->fetch_assoc()): ?>
        <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
    <?php endwhile; ?>
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

  
    function showEditForm(id, last_name, first_name) {
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
        console.log("Delete student ID:", id); 
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
<br>
    <br>
    <br>
    <br>
    <br>
<a href="dashboard.php" class="button">Dashboard</a>

</body>
</html>

