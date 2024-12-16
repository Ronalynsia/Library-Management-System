<?php
session_start();
require_once '../Config/database.php';
require_once 'admin-class.php';

$db = new Database();
$admin = new Admin($db);

$books = $admin->getAllBooks();
$categories = $admin->getAllCategories();


// Pagination setup
$limit = 5; // Books per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch paginated books and total book count
$books = $admin->getPaginatedBooks($limit, $offset);
$totalBooks = $admin->getBookCount();
$totalPages = ceil($totalBooks / $limit);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_book'])) {
        // Handle adding a new book
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
    } elseif (isset($_POST['edit_book'])) {
        // Handle editing an existing book
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
    } elseif (isset($_POST['delete_book'])) {
        // Handle deleting a book
        $id = $_POST['delete_book'];

        if ($admin->deleteBook($id)) {
            $_SESSION['success'] = "Book deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete book.";
        }

        header("Location: total-books.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Books List - Library System</title>
    <link rel="stylesheet" href="css/total-books.css">
</head>
<body>
<h2>Book Dashboard</h2>
<!-- Overlay -->
<div class="overlay" id="overlay" onclick="hideModal()"></div>

<!-- Add Book Modal -->
<div class="modal" id="add-form">
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
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id']; ?>"><?= htmlspecialchars($category['category_name']); ?></option>
            <?php endforeach; ?>
        </select><br>
        <button type="submit" name="add_book">Add Book</button>
        <button type="button" onclick="hideModal()">Cancel</button>
    </form>
</div>

<!-- Edit Book Modal -->
<div class="modal" id="edit-form">
    <form method="POST">
        <h3>Edit Book</h3>
        <input type="hidden" name="edit_id" id="edit_id">
        <label>ISBN:</label><input type="text" name="edit_isbn" id="edit_isbn" required><br>
        <label>Title:</label><input type="text" name="edit_title" id="edit_title" required><br>
        <label>Author:</label><input type="text" name="edit_author" id="edit_author" required><br>
        <label>Published Date:</label><input type="date" name="edit_published_date" id="edit_published_date" required><br>
        <label>Quantity:</label><input type="number" name="edit_quantity" id="edit_quantity" required><br>
        <label>Category:</label>
        <select name="edit_category_id" id="edit_category_id" required>
            <option value="">Select a category</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id']; ?>"><?= htmlspecialchars($category['category_name']); ?></option>
            <?php endforeach; ?>
        </select><br>
        <button type="submit" name="edit_book">Update Book</button>
        <button type="button" onclick="hideModal()">Cancel</button>
    </form>
</div>

<!-- Book List -->
<button onclick="showModal('add-form')">Add New Book</button>
<h3>Book List</h3>
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
        <?php foreach ($books as $book): ?>
            <tr>
                <td><?= htmlspecialchars($book['category_name']); ?></td>
                <td><?= htmlspecialchars($book['isbn']); ?></td>
                <td><?= htmlspecialchars($book['title']); ?></td>
                <td><?= htmlspecialchars($book['author']); ?></td>
                <td><?= htmlspecialchars($book['published_date']); ?></td>
                <td><?= htmlspecialchars($book['quantity']); ?></td>
                <td style="color: <?= $book['quantity'] > 0 ? 'green' : 'red'; ?>; font-weight: bold;">
                    <?= $book['quantity'] > 0 ? "Available" : "Not Available"; ?>
                <td>
                    <button onclick="showEditForm(
                        <?= $book['id']; ?>,
                        '<?= htmlspecialchars($book['isbn']); ?>',
                        '<?= htmlspecialchars($book['title']); ?>',
                        '<?= htmlspecialchars($book['author']); ?>',
                        '<?= htmlspecialchars($book['published_date']); ?>',
                        <?= $book['quantity']; ?>,
                        <?= $book['category_id']; ?>
                    )">Edit</button>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="delete_book" value="<?= $book['id']; ?>">
                        <button type="submit" onclick="return confirm('Are you sure?')">Delete</button>
                        
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<div style="text-align: center; margin-top: 20px;">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i; ?>" style="
            display: inline-block;
            margin: 0 5px;
            text-decoration: none;
            background-color: <?= ($i === $page) ? '#5a3b2e' : '#8b6f5e'; ?>;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            padding: 5px 10px;
            transition: background-color 0.3s;">
            <?= $i; ?>
        </a>
    <?php endfor; ?>
</div>

<script>
function showModal(modalId) {
    document.getElementById('overlay').classList.add('active');
    document.getElementById(modalId).classList.add('active');
}

function hideModal() {
    document.getElementById('overlay').classList.remove('active');
    document.querySelectorAll('.modal').forEach(modal => modal.classList.remove('active'));
}

function showEditForm(id, isbn, title, author, publishedDate, quantity, categoryId) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_isbn').value = isbn;
    document.getElementById('edit_title').value = title;
    document.getElementById('edit_author').value = author;
    document.getElementById('edit_published_date').value = publishedDate;
    document.getElementById('edit_quantity').value = quantity;
    document.getElementById('edit_category_id').value = categoryId;
    showModal('edit-form');
}
</script>

<a href="dashboard.php" class="button">Back to Dashboard</a>

</body>
</html>
