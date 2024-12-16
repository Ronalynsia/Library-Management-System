<?php
session_start();
require_once '../Config/database.php';
require_once 'admin-class.php';

$db = new Database();
$admin = new Admin($db);

$limit = 5; // Records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_transactions = $admin->getBorrowTransactionCount();
$total_pages = ceil($total_transactions / $limit);

$transactions = $admin->getBorrowTransactionsPaginated($limit, $offset);
$books = $admin->getAllBooks();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_transaction'])) {
    $transaction_id = $_POST['delete_id'];

    if ($admin->deleteTransaction($transaction_id)) {
        $_SESSION['success'] = "Transaction deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete transaction.";
    }

    header("Location: borrow.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_borrow'])) {
    $student_id = $_POST['student_id'];
    $student_name = $_POST['student_name'];
    $isbn = $_POST['isbn'];
    $quantity = (int)$_POST['quantity']; // Get the number of books to borrow

    if ($admin->addBorrowTransaction($student_id, $student_name, $isbn, $quantity)) {
        $_SESSION['success'] = "Successfully borrowed $quantity book(s)!";
    } else {
        $_SESSION['error'] = "Failed to borrow books.";
    }

    header("Location: borrow.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/borrow.css">
    <title>Borrow Transactions</title>
    <script>
        function openModal() {
            document.getElementById('add-borrow-modal').style.display = 'block';
        }
        function closeModal() {
            document.getElementById('add-borrow-modal').style.display = 'none';
        }
        function confirmDelete(transactionId) {
            if (confirm("Are you sure you want to delete this transaction?")) {
                const deleteForm = document.getElementById('delete-form');
                deleteForm.innerHTML = `
                    <input type="hidden" name="delete_id" value="${transactionId}">
                    <input type="hidden" name="delete_transaction" value="1">
                `;
                deleteForm.submit();
            }
        }
    </script>
</head>
<body>
    <h2>Borrow Dashboard</h2>
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

                <label for="quantity">Number of Books to Borrow:</label><br>
                <input type="number" id="quantity" name="quantity" min="1" value="1" required><br><br>

                <button type="submit" name="add_borrow">Borrow Book</button>
            </form>
        </div>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
        <p style="color: green;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php elseif (isset($_SESSION['error'])): ?>
        <p style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <h3>Borrow List</h3>
    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>ISBN</th>
                <th>Book Title</th>
                <th>Borrow Date</th>
                <th>Quantity</th>
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
                    <td><?php echo htmlspecialchars($transaction['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['status']); ?></td>
                    <td>
                        <button type="button" onclick="confirmDelete(<?php echo $transaction['id']; ?>)">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div style="text-align: center; margin-top: 20px;">
        <?php if ($total_pages > 1): ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" style="
                    display: inline-block;
                    margin: 0 5px;
                    padding: 5px 10px;
                    background: <?php echo $current_page == $i ? '#5a3b2e' : '#8b6f5e'; ?>;
                    color: #fff;
                    border-radius: 5px;
                    text-decoration: none;
                    font-size: 16px;
                    font-weight: bold;
                    transition: background-color 0.3s;">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        <?php endif; ?>
    </div>

    <form id="delete-form" method="POST" style="display: none;"></form>
    <a href="dashboard.php" class="button">Back to Dashboard</a>
</body>
</html>
