<?php
session_start();
require_once '../Config/database.php';
require_once 'admin-class.php';

$db = new Database();
$admin = new Admin($db);

$books = $admin->getAllBooks();
$categories = $admin->getAllCategories();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_book'])) {
    $isbn = $_POST['isbn'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $published_date = $_POST['published_date'];
    $quantity = $_POST['quantity'];
    $category_id = $_POST['category_id'];

    if ($admin->addBook($isbn, $title, $author, $published_date, $quantity, $category_id)) {
        $_SESSION['success'] = "Book added successfully!";
    } else {
        $_SESSION['error'] = "Failed to add book.";
    }

    header("Location: total-books.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_book'])) {
    $id = $_POST['edit_id'];
    $isbn = $_POST['edit_isbn'];
    $title = $_POST['edit_title'];
    $author = $_POST['edit_author'];
    $published_date = $_POST['edit_published_date'];
    $quantity = $_POST['edit_quantity'];
    $category_id = $_POST['edit_category_id'];

    if ($admin->updateBook($id, $isbn, $title, $author, $published_date, $quantity, $category_id)) {
        $_SESSION['success'] = "Book updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update book.";
    }

    header("Location: total-books.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_book'])) {
    $id = $_POST['delete_book'];

    if ($admin->deleteBook($id)) {
        $_SESSION['success'] = "Book deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete book.";
    }

    header("Location: total-books.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/total-books.css">
    <title>Books List - Library System</title>
    <style>
         body {
            font-family: 'Times New Roman', sans-serif;
            background-color: #94672b4b;
            ;
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
              padding: 8px 12px; 
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
            padding: 10px 15px;
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

        .edit-form, .add-form { 
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 30px;
            border-radius: 8px;
            background-color: #fff;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            
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
            margin-bottom: 5px;
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
<?php if (isset($_SESSION['success'])): ?>
    <p style="color: green;"><?= $_SESSION['success']; unset($_SESSION['success']); ?></p>
<?php elseif (isset($_SESSION['error'])): ?>
    <p style="color: red;"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
<?php endif; ?>

      <h3>Add New</h3>
        <button onclick="showAddForm()"> +Add New Book</button>

    <h3>Books List</h3>
      <table>
    <thead>
        <tr>
            <th>Category</th>
            <th>ISBN</th>
            <th>Title</th>
            <th>Author</th>
            <th>Published Date</th>
            <th>Quantity</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($book = $books->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($book['category_name']); ?></td>
                <td><?= htmlspecialchars($book['isbn']); ?></td>
                <td><?= htmlspecialchars($book['title']); ?></td>
                <td><?= htmlspecialchars($book['author']); ?></td>
                <td><?= htmlspecialchars($book['published_date']); ?></td>
                <td><?= htmlspecialchars($book['quantity']); ?></td>
                <td><?= $book['quantity'] > 0 ? "Available" : "Not Available"; ?></td>
                <td>
                    <button onclick="showEditForm(<?= $book['id']; ?>)">Edit</button>
                    <button onclick="confirmDelete(<?= $book['id']; ?>)">Delete</button>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<div id="add-form" class="add-form">
    <form method="POST">
    <h3>Add New Book</h3>
        <label>ISBN:</label><input type="text" name="isbn" required><br>
        <label>Title:</label><input type="text" name="title" required><br>
        <label>Author:</label><input type="text" name="author" required><br>
        <label>Published Date:</label><input type="date" name="published_date" required><br>
        <label>Quantity:</label><input type="number" name="quantity" required><br>
        <label>Category:</label>
        <select name="category_id" required>
            <option value="">Select a category</option>
            <?php while ($category = $categories->fetch_assoc()): ?>
                <option value="<?= $category['id']; ?>"><?= htmlspecialchars($category['category_name']); ?></option>
            <?php endwhile; ?>
        </select><br>
        <button type="submit" name="add_book">Add Book</button>
        <button type="button" onclick="hideAddForm()">Cancel</button>
    </form>
</div>

<div id="edit-form" class="edit-form">
    <form method="POST">
    <h3>Edit Book</h3>
        <input type="hidden" id="edit-id" name="edit_id">
        <label>ISBN:</label><input type="text" id="edit-isbn" name="edit_isbn" required><br>
        <label>Title:</label><input type="text" id="edit-title" name="edit_title" required><br>
        <label>Author:</label><input type="text" id="edit-author" name="edit_author" required><br>
        <label>Published Date:</label><input type="date" id="edit-published-date" name="edit_published_date" required><br>
        <label>Quantity:</label><input type="number" id="edit-quantity" name="edit_quantity" required><br>
        <label>Category:</label>
        <select id="edit-category" name="edit_category_id" required>
            <option value="" disabled>Select Category</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id']; ?>"><?= htmlspecialchars($category['category_name']); ?></option>
            <?php endforeach; ?>
        </select><br>
        <button type="submit" name="edit_book">Save Changes</button>
        <button type="button" onclick="hideEditForm()">Cancel</button>
    </form>
</div>

<div class="overlay" onclick="hideAddForm()"></div>

<script>
    function showAddForm() {
        document.getElementById('add-form').style.display = 'block';
        document.querySelector('.overlay').style.display = 'block';
    }

    function hideAddForm() {
        document.getElementById('add-form').style.display = 'none';
        document.querySelector('.overlay').style.display = 'none';
    }

    function showEditForm(id) {
        document.getElementById('edit-id').value = id;
        document.getElementById('edit-form').style.display = 'block';
        document.querySelector('.overlay').style.display = 'block';

        fetch('total-books.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                document.getElementById('edit-isbn').value = data.isbn;
                document.getElementById('edit-title').value = data.title;
                document.getElementById('edit-author').value = data.author;
                document.getElementById('edit-published-date').value = data.published_date;
                document.getElementById('edit-quantity').value = data.quantity;
                document.getElementById('edit-category').value = data.category_id;
            });
    }

    function hideEditForm() {
        document.getElementById('edit-form').style.display = 'none';
        document.querySelector('.overlay').style.display = 'none';
    }

    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this book?')) {
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = 'total-books.php';

            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'delete_book';
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