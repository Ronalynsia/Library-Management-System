<?php
session_start();
require_once '../Config/database.php';
require_once 'admin-class.php';

$db = new Database();
$admin = new Admin($db);

$transactions = $admin->getAllBorrowTransactions();
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

    if ($admin->addBorrowTransaction($student_id, $student_name, $isbn)) {
        $_SESSION['success'] = "Book borrowed successfully!";
    } else {
        $_SESSION['error'] = "Failed to borrow book.";
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
    <title>Borrow Transactions</title>
    <link rel="stylesheet" href="css/borrow.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 5px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover {
            color: black;
            cursor: pointer;
        }
    </style>
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
    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
