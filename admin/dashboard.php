<?php
session_start();
include_once '../Config/database.php';
include_once 'admin-class.php';

if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit();
}

$db = new Database();
$admin = new Admin($db);

$admin_id = $_SESSION['admin'];
$admin_details = $admin->getAdminDetails($admin_id);
$total_books = $admin->getTotalBooks();
$total_students = $admin->getTotalStudents();
$total_borrowed = $admin->getTotalBorrowed();
$total_returned = $admin->getTotalReturned();

// Calculate the available books
$available_books = $total_books - $total_borrowed + $total_returned;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <!-- Logo Section -->
            <div class="logo-section">
                <img src="images/logo.png" alt="Logo">
            </div>

            <!-- Profile Section -->
            <div class="profile-section">
                <h3>Admin Profile</h3>
                <?php if (isset($admin_details['username'])): ?>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($admin_details['username']); ?></p>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($admin_details['first_name']) . ' ' . htmlspecialchars($admin_details['last_name']); ?></p>
                <?php else: ?>
                    <p>Admin profile not found.</p>
                <?php endif; ?>
            </div>

            <hr>
            <nav class="sidebar-menu">
                <ul>
                    <li><a href="dashboard.php"><i class="fa fa-tachometer-alt"></i> Dashboard</a></li>
                    <hr>
                    <li class="dropdown-toggle">
                        <a href="javascript:void(0)"><i class="fa fa-exchange-alt"></i> Transactions</a>
                        <ul class="dropdown-menu">
                            <li><a href="borrow.php"><i class="fa fa-bookmark"></i> Borrow </a></li>
                            <li><a href="return.php"><i class="fa fa-undo"></i> Return</a></li>
                        </ul>
                    </li>
                    <hr>
                    <li class="dropdown-toggle">
                        <a href="javascript:void(0)"><i class="fa fa-book"></i> Books</a>
                        <ul class="dropdown-menu">
                            <li><a href="total-books.php"><i class="fa fa-list"></i> Book List</a></li>
                            <li><a href="category.php"><i class="fa fa-tags"></i> Categories</a></li>
                        </ul>
                    </li>
                    <hr>
                    <li class="dropdown-toggle">
                        <a href="javascript:void(0)"><i class="fa fa-users"></i> Students</a>
                        <ul class="dropdown-menu">
                            <li><a href="student-list.php"><i class="fa fa-list"></i> Student List</a></li>
                            <li><a href="courses.php"><i class="fa fa-list"></i> Courses</a></li>
                        </ul>
                    </li>
                    <hr>
                    <li><a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <section id="dashboard">
                <h2>Dashboard</h2>
                <div class="card-container">
                    <div class="card card-1">
                        <div class="card-header"><i class="fa fa-book"></i> Total Available Books</div>
                        <div class="card-body">
                            <p><strong><?php echo $available_books; ?></strong></p> <!-- Display the calculated available books -->
                            <a href="total-books.php">see more</a>
                        </div>
                    </div>

                    <div class="card card-2">
                        <div class="card-header"><i class="fa fa-users"></i> Total Students</div>
                        <div class="card-body">
                            <p><strong><?php echo $total_students; ?></strong></p>
                            <a href="student-list.php">see more</a>
                        </div>
                    </div>

                    <div class="card card-3">
                        <div class="card-header"><i class="fa fa-bookmark"></i> Total Borrowed Books</div>
                        <div class="card-body">
                            <p><strong><?php echo $total_borrowed; ?></strong></p>
                            <a href="borrow.php">see more</a>
                        </div>
                    </div>

                    <!-- Total Returned Books Card -->
                    <div class="card card-4">
                        <div class="card-header"><i class="fa fa-undo"></i> Total Returned Books</div>
                        <div class="card-body">
                            <p><strong><?php echo $total_returned; ?></strong></p>
                            <a href="return.php">see more</a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Books Showcase Section -->
            <section id="books-showcase">
                <h3>Books in the Library</h3>
                <div class="books-img">
                    <!-- Example of a book image -->
                    <div class="book">
                        <img src="images/dashboard.jpg" alt="Book">
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        // JavaScript to toggle dropdown
        document.querySelectorAll('.dropdown-toggle').forEach(function(menu) {
            menu.addEventListener('click', function() {
                // Toggle the 'active' class to show or hide the dropdown menu
                this.classList.toggle('active');
                // Close other dropdowns when clicking on a new one
                document.querySelectorAll('.dropdown-toggle').forEach(function(otherMenu) {
                    if (otherMenu !== menu) {
                        otherMenu.classList.remove('active');
                    }
                });
            });
        });
    </script>

</body>
</html>
