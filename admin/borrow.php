<?php
session_start();
require_once '../Config/database.php';
require_once 'admin-class.php';

$db = new Database();
$admin = new Admin($db);

// Pagination logic
$limit = 5; // Limit of 5 rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page
$offset = ($page - 1) * $limit; // Calculate the offset for the query

// Fetch transactions with limit and offset
$transactions = $admin->getBorrowTransactionsPaginated($limit, $offset);
$total_transactions = $admin->getTotalTransactions(); // Get the total number of transactions
$total_pages = ceil($total_transactions / $limit); // Calculate total pages

// Fetch all books for dropdown
$books = $admin->getAllBooks();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_transaction'])) {
    $transaction_id = $_POST['delete_id'];

    if ($admin->deleteTransaction($transaction_id)) {
        $_SESSION['success'] = "Transaction deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete transaction.";
    }

    header("Location: borrow.php?page=$page");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_borrow'])) {
    $student_id = $_POST['student_id'];
    $student_name = $_POST['student_name'];
    $isbn = $_POST['isbn'];

    if ($admin->addBorrowTransaction($student_id, $student_name, $isbn)) {
        $_SESSION['success'] = "Book borrowed successfully!";
    } else {
        $_SESSION['error'] = "Failed to borrow book.";
    }

    header("Location: borrow.php?page=$page");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Transactions</title>
    <style>
        /* Include your existing CSS styles here */
    </style>
    <script>
        // Include your existing JavaScript functions here
    </script>
</head>
<body>
    <h2>Borrow Transactions</h2>
    <button onclick="openModal()" class="add-borrow-btn">+ Borrow a Book</button>
    <div id="add-borrow-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Add Borrow Transaction</h3>
            <form method="POST">
                <label for="student_id">Student ID:</label><br>
                <input type="text" id="student_id" name="student_id" required><br><br>
                <label for="student_name">Student Name:</label><br>
                <input type="text" id="student_name" name="student_name" required><br><br>
                <label for="isbn">Select Book:</label><br>
                <select id="isbn" name="isbn" required>
                    <option value="">Select a book</option>
                    <?php while ($book = $books->fetch_assoc()): ?>
                        <option value="<?php echo $book['isbn']; ?>"><?php echo htmlspecialchars($book['title']); ?></option>
                    <?php endwhile; ?>
                </select><br><br>
                <button type="submit" name="add_borrow">Borrow Book</button>
            </form>
        </div>
    </div>
    <?php if (isset($_SESSION['success'])): ?>
        <p style="color: green;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php elseif (isset($_SESSION['error'])): ?>
        <p style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>
    <h3>Transaction List</h3>
    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>ISBN</th>
                <th>Book Title</th>
                <th>Borrow Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($transaction = $transactions->fetch_assoc()): ?>
                <?php $book_title = $admin->getBookTitleByIsbn($transaction['isbn']); ?>
                <tr>
                    <td><?php echo htmlspecialchars($transaction['student_id']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['student_name']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['isbn']); ?></td>
                    <td><?php echo htmlspecialchars($book_title); ?></td>
                    <td><?php echo htmlspecialchars($transaction['borrow_date']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['status']); ?></td>
                    <td>
                        <button type="button" onclick="confirmDelete(<?php echo $transaction['id']; ?>)">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <form id="delete-form" method="POST" style="display: none;">
        <input type="hidden" name="delete_transaction" value="1">
    </form>
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="borrow.php?page=<?php echo $i; ?>" class="button" <?php if ($i == $page) echo 'style="background-color: #65452f;"'; ?>>
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
    <a href="dashboard.php" class="button">Dashboard</a>
</body>
</html>
