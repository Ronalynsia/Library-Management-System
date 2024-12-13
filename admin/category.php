<?php
session_start();
include_once '../Config/database.php';
include_once 'admin-class.php';

$db = new Database();
$admin = new Admin($db);

$limit = 5;
$categories = $admin->getCategories();
$totalPages = ceil($totalCategories / $limit);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, min($_page, $total_categories));
$offset = ($page - 1) * $limit;

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    if (isset($_POST['add_category'])) {
        $category_name = trim($_POST['category_name']);
        if (!empty($category_name)){
            $category_name = htmlspecialchars($category_name);
            if ($admin->addCategory($category_name)) {
                $_SESSION['success'] = "Category added successfully!";
            }
            else{
                $_SESSION['error'] = "Failed to add category.";
            }
        }
        else{
            $_SESSION['error'] = "Category name cannot be empty.";
        }
    }
  
    if (isset($_POST['edit_category'])) {
        $edit_id = $_POST['edit_id'];
        $edit_name = trim($_POST['edit_category_name']);
        if (!empty($edit_name)){
            $edit_name = htmlspecialchars($edit_name);
            if ($admin->editCategory($edit_id, $edit_name)) {
                $_SESSION['success'] = "Category updated successfully!";
            }
            else{
                $_SESSION['error'] = "Failed to update category.";
            }
        }
        else{
            $_SESSION['error'] = "Category name cannot be empty.";
        }
    }
    if (isset($_POST['delete_category'])) {
        $delete_id = $_POST['delete_id'];
        if ($admin->deleteCategory($delete_id)) {
            $_SESSION['success'] = "Category deleted successfully!";
        }
        else{
            $_SESSION['error'] = "Failed to delete category.";
        }
    }
}

$categories = $admin->getCategories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Dashboard - Library System</title>

    <script>
        function editCategory(id, currentName) {
            var newName = prompt("Edit category name:", currentName);
            if (newName && newName.trim() !== "") {
                var form = document.createElement("form");
                form.method = "POST";
                form.action = "category.php";

                var inputId = document.createElement("input");
                inputId.type = "hidden";
                inputId.name = "edit_id";
                inputId.value = id;
                form.appendChild(inputId);

                var inputName = document.createElement("input");
                inputName.type = "hidden";
                inputName.name = "edit_category_name";
                inputName.value = newName;
                form.appendChild(inputName);

                var inputSubmit = document.createElement("input");
                inputSubmit.type = "hidden";
                inputSubmit.name = "edit_category";
                form.appendChild(inputSubmit);

                document.body.appendChild(form);
                form.submit();
            }
        }

        function deleteCategory(id, name) {
            if (confirm("Are you sure you want to delete the category: " + name + "?")) {
                var form = document.createElement("form");
                form.method = "POST";
                form.action = "category.php";

                var inputId = document.createElement("input");
                inputId.type = "hidden";
                inputId.name = "delete_id";
                inputId.value = id;
                form.appendChild(inputId);

                var inputSubmit = document.createElement("input");
                inputSubmit.type = "hidden";
                inputSubmit.name = "delete_category";
                form.appendChild(inputSubmit);

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</head>
<body>
    <h2>Category Dashboard</h2>
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

    <h3>Add New Category</h3>
    <form action="category.php" method="POST">
        <label for="category_name">Category Name:</label>
        <input type="text" name="category_name" id="category_name" required>
        <button type="submit" name="add_category">Add Category</button>
    </form>
    <hr>
    
    <h3>Existing Categories</h3>
    <table>
        <thead>
            <tr>
                <th>Category Name</th>
                <th>Controls</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($category = $categories->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                    <td>
                        <button type="button" onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo addslashes(htmlspecialchars($category['category_name'])); ?>')">Edit</button>
                        <button type="button" onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo addslashes(htmlspecialchars($category['category_name'])); ?>')">Delete</button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <br>
    <br>
    <br>
    <br>
    <br>
    <a href="dashboard.php" class="button">Dashboard</a>

</body>
</html>