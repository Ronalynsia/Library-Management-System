<?php
session_start();
require_once '../Config/database.php';
require_once 'admin-class.php';

$db = new Database();
$admin = new Admin($db);

$transactions = $admin->getAllReturnTransactions();
$books = $admin->getAllBooks();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_transaction'])) {
    $transaction_id = $_POST['delete_id'];

    if ($admin->deleteReturnTransaction($transaction_id)) {
        $_SESSION['success'] = "Transaction deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete transaction.";
    }

    header("Location: return.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_return'])) {
    $student_id = $_POST['student_id'];
    $student_name = $_POST['student_name'];
    $isbn = $_POST['isbn'];
    $return_date = date('Y-m-d');

    if ($admin->addReturnTransaction($student_id, $student_name, $isbn, $return_date)) {
        $_SESSION['success'] = "Book returned successfully!";
    }

    header("Location: return.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Transactions</title>
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
            background-color: rgba(0, 0, 0, 0.4);
        }
        .modal-content {
            background-color: #f9f9f9;
            margin: 15% auto;
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
            document.getElementById("add-return-modal").style.display = "block";
        }
        function closeModal() {
            document.getElementById("add-return-modal").style.display = "none";
        }
        window.onclick = function(event) {
            if (event.target === document.getElementById("add-return-modal")) {
                closeModal();
            }
        }
        function confirmDelete(transactionId) {
            if (confirm("Are you sure you want to delete this transaction?")) {
                const form = document.getElementById('delete-form');
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_id';
                input.value = transactionId;
                form.appendChild(input);
                form.submit();
            }
        }
    </script>
</head>
<body>
    <h2>Return Transactions</h2>
    <button onclick="openModal()" class="add-return-btn">+ Return a Book</button>

    <div id="add-return-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Add Return Transaction</h3>
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
                <button type="submit" name="add_return">Return Book</button>
            </form>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <p style="color: green;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php elseif (isset($_SESSION['error'])): ?>
        <p style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <h3>Return Transaction List</h3>
    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>ISBN</th>
                <th>Book Title</th>
                <th>Return Date</th>
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
                    <td><?php echo htmlspecialchars($transaction['return_date']); ?></td>
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
