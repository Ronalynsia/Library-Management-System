<?php
session_start();
require_once '../Config/database.php';
require_once 'admin-class.php';

$db = new Database();
$admin = new Admin($db);
$books = $admin->getAllBooks();

// Pagination setup
$limit = 5; // Records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_transactions = $admin->getBorrowTransactionCount();
$total_pages = ceil($total_transactions / $limit);

$transactions = $admin->getBorrowTransactionsPaginated($limit, $offset);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_transaction'])) {
    $transaction_id = $_POST['delete_id'];
    if ($admin->deleteBorrowTransaction($transaction_id)) {
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
    $borrow_date = date("Y-m-d");
    $status = "Borrowed";

    if ($admin->addBorrowTransaction($student_id, $student_name, $isbn, $borrow_date, $status)) {
        $_SESSION['success'] = "Book borrowed successfully!";
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
    <style>
        body {
            font-family: 'Times New Roman', sans-serif;
            background-color: #b8bb9580;
            margin: 0;
            padding: 40px;
            color: #333;
        }
        h2, h3 {
            margin-bottom: 20px;
            color: #4a3135;
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
            padding: 8px;
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
        .pagination {
            text-align: center;
            margin-top: 20px;
        }
        .pagination a {
            display: inline-block;
            padding: 8px 12px;
            margin: 0 5px;
            background-color: #805c41;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .pagination a:hover {
            background-color: #65452f;
        }
    </style>
    <script>
        function openModal() {
            document.getElementById("add-borrow-modal").style.display = "block";
        }
        function closeModal() {
            document.getElementById("add-borrow-modal").style.display = "none";
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
    <h2>Borrow Transactions</h2>
    <button onclick="openModal()">+ Borrow a Book</button>

    <div id="add-borrow-modal" class="modal">
        <form method="POST">
            <h3>Add Borrow Transaction</h3>
            <label for="student_id">Student ID:</label>
            <input type="text" name="student_id" required>
            <label for="student_name">Student Name:</label>
            <input type="text" name="student_name" required>
            <label for="isbn">Select Book:</label>
            <select name="isbn" required>
                <option value="">Select a book</option>
                <?php while ($book = $books->fetch_assoc()): ?>
                    <option value="<?php echo $book['isbn']; ?>"><?php echo htmlspecialchars($book['title']); ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" name="add_borrow">Borrow Book</button>
            <button type="button" onclick="closeModal()">Close</button>
        </form>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <p style="color: green;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php elseif (isset($_SESSION['error'])): ?>
        <p style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>

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
                <tr>
                    <td><?php echo htmlspecialchars($transaction['student_id']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['student_name']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['isbn']); ?></td>
                    <td><?php echo htmlspecialchars($admin->getBookTitleByIsbn($transaction['isbn'])); ?></td>
                    <td><?php echo htmlspecialchars($transaction['borrow_date']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['status']); ?></td>
                    <td>
                        <button onclick="confirmDelete(<?php echo $transaction['id']; ?>)">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>

    <form id="delete-form" method="POST" style="display: none;">
        <input type="hidden" name="delete_transaction" value="1">
    </form>
    <a href="dashboard.php" class="button">Dashboard</a>
</body>
</html>
