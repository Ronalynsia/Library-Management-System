<?php
session_start();
require_once '../Config/databases.php';
require_once 'admin-class.php';

$db = new Databases();
$admin = new Admin($db);

$transaction = $admin->getALLBorrowTransaction();
$books = $admin->getALLBooks();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset ($_POST['delete_transaction']))
{
    $transaction_id = $_POST['delete_id'];

    if ($admin->deleteTransaction($transaction_id)) {
        $_SESSION['success'] = "Tranasaction deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete transaction";
    }

    header("Location: borrow.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset ($_POST['add_borrow'])) {
    $student_id = $_POST['student_id'];
    $student_name = $_POST['student_name'];
    $isbn = $_POST['isbn'];

    if ($admin->addBorrowTransaction($student_id, $student_name, $isbn)) {
        $_SESSION['success'] = "Book borrowed successfully!";
    } else {
        $_SESSION['error'] = "Failed to borrow book";
    }  

    header("Location: borrow.php");
    exit();
}
?>

<!DOCTYPE htmt>
<html lang="em">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device=width, initial-scale=1.0">
    <title>Borrow Transaction</title>
    <style>
        table {
            width: 100%
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #e7d7c1;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 80%;
            height: 500px;
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
            cursor: pointer
        }
    </style>
    <script>
        function openModal() {
            document.getElementById('add-borrow-modal').style.display = 'block';
        }
        function closeModal(){
            document.getElementById('add-borrow-modal').style.display = 'none';
        }
        function confirmDelete(transactionId) {
            if (comfirm("Are you sure you want to delete this transaction")) {
                const deleteForm = document.getElementById('delete-form');
                deleteForm.innerHTML = `
                <input type="hidden" name= "delete_id" value="${transactionId}">
                <input type="hidden" name="delete_transaction" value="1"> `;
                deleteForm.sumbit();
            }
        }
        </script>
        </head>
        <body>
            <h2>Borrow Transaction</h2>
            <button onclick="openModal()" class="add-borrow-btn">+ Borrow a Book</Button>
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</spam>
                <h3>Add Borrow Transaction </h3>
                <form method="POST">
                    <label for="student_id">Student ID:</label><br>
                    <input type="text" id="student_id"name="student_id" required><br><br>
                    <label for="student_name">Student Name:</label><br>
                    <input type="text" id="student_name"name="student_name" required><br><br>
                    <label for="isbn">Select Book:</label><br>
                    <select id="isbn" name="isbn" required>
                        <option values="">Select a Book</option>
                        <?php while ($book = $books->fetch_assoc()); ?>
                        <option value="<?php echo $book['isbn']; ?>"><?php echo htmlspecialchars($book['title']); ?></option>
                        <?php endwhile; ?>
                </select><br><br>
                <button type="submit" name="add_borrow">Borrow Book</button>
    </form>
    </div>
 </div>
<?php if (isset($_SESSION['success'])); ?>
<p style="color: green:"><?php echo $_SESSION['success'];
unset($_SESSION['success']); ?></p>
    <?php elseif (isset($_SESSION['error'])); ?>
    <p style="color: red:"><?php echo $_SESSION['error'];
unset($_SESSION['error']); ?></p>
    <?php endif; ?>
    <h3>Transaction List</h3>
    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>ISBN</th>
                <th>Book Title</th>
                <th>Borrow Book</th>
                <th>Status</th>
                <th>Actions</th>
           </tr>
        </thead>
        <tbody>
            <?php while($transaction = $transaction->fetch_assoc()): ?>
                <?php $book_title = $admin->getBookTitleByIsbn($transaction['isbn']); ?>
                <tr>
                    <td><?php echo htmlspecialchars($transaction['student_id']); ?></td>
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
            <form id="delete-form" method="POST" style="display: none:;">
                <input type=hidden name="delete_transaction" values="1">
            </form>
            <br>
            <a href="dashboard.php">Back to Dashboard</a>
            </body>
            </html>