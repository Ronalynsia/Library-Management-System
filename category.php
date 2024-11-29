<?php 
session_start(); 
include_once '../Config/database.php';
include_once 'admin-class.php'; 

$db = new Database(); 
$admin = new Admin($db); 

if ($_SERVER['REQUEST_METHOD'] == 'POST'){  
    if (isset($_POST['add_category'])){ 
        $category_name = trim($_POST['category_name']);
        if (!empty($category_name)) { 
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

    if (isset($_POST['edit_category'])){
        $edit_id = $_POST['edit_id']; 
        $edit_name = trim($_POST['edit_category_name']);
        if (!empty($edit_name)){
            $edit_name = htmlspecialchars($edit_name); 
            if ($admin->editCategory($edit_id, $edit_name)){ 
                $_SESSION['success'] = "Category updated successfully!";
            }
            else {
                $_SESSION['error'] = "Failed to update category.";
            }
        }
        else { 
            $_SESSION['error'] = "Category name cannot be empty.";
        }
    }
    if (isset($_POST['delete_category'])){
        $delete_id = $_POST['delete_id'];
        if ($admin->deleteCategory($delete_id)){
            $_SESSION['success'] = "Category deleted successfully!";
        }
        else {
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
    <style> 
        body {
            font-family: 'Times New Roman', sans-serif; 
            background-color: #c6dce769; 
            margin: 0; 
            padding: 0; 
            padding: 40px; 
            color: #333;
        }
        h3{
            margin-bottom: 5px; 
            color: #4a3f35;
        }
        table{
            width: 90%;
            border-collapse: collapse;
            margin-top: 5px; 
            background: #fff; 
            border-radius: 8px; 
            overflow: hidden; 
            margin-left: auto; 
            margin-right: auto; 
        }
        th, td{ 
            border: 1px solid #ddd; 
            padding: 8px 10px;
            text-align: center; 
            font-size: 13px;
        }
        th{ 
            background-color: #805c41; 
            color: #fff; 
        }
        tr:nth-child(even){
            background-color: #f9f9f9; 
        }
        tr:hover { 
            background-color: #886a527e;
        }
        button, a.button{
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
            font-size: 12px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        button:hover, a.button:hover{
            background-color: #65452f;
            transform: scale(1.05);
        }
        button:active, a.button:active{
            transform: scale(0.98);
        }
    </style>

</head>

<body>
    <h3>Category Dashboard</h3>
  
    <?php if(isset($_SESSION['success'])): ?>
        <div style="color: green;">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php elseif(isset($_SESSION['error'])): ?>
        <div style="color: red;">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="category_name" placeholder="Enter category name" required>
        <button type="submit" name="add_category">Add Category</button>
    </form>
    <h3>Existing Categories</h3>
    <table>
        <tr>
            <th>Category Name</th>
            <th>Actions</th>
        </tr>
      
        <?php foreach($categories as $category): ?>
        <tr>
            <td><?php echo htmlspecialchars($category['name']); ?></td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="edit_id" value="<?php echo $category['id']; ?>">
                    <input type="text" name="edit_category_name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                    <button type="submit" name="edit_category">Edit</button>
                </form>
              
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="delete_id" value="<?php echo $category['id']; ?>">
                    <button type="submit" name="delete_category">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>

