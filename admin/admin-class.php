<?php

class Admin {

    private $db;
    private $conn;

    public function __construct($db) {
        $this->db = $db;
        $this->conn = $db->getConnection();
    }

     // Admin Login Method
     public function login($username, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows < 1) {
            return 'Cannot find account with the username';
        } else {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['admin'] = $row['id'];
                $this->logAction($row['id'], 'login');
                return 'success';
            } else {
                return 'Incorrect password';
            }
        }
    }

    // Admin Registration Method
    public function register($username, $password, $first_name, $last_name) {
        $stmt = $this->conn->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $query = $stmt->get_result();

        if ($query->num_rows > 0) { // Check if username already exists
            return 'Username already exists';
        } else {
            $stmt = $this->conn->prepare("INSERT INTO admin (username, password, first_name, last_name) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $password, $first_name, $last_name);
            if ($stmt->execute()) {
                return 'Registration successful. You can now log in.';
            } else {
                return 'Registration failed. Please try again.';
            }
        }
    }

    // Fetch admin details
    public function getAdminDetails($admin_id) {
        $stmt = $this->conn->prepare("SELECT username, first_name, last_name FROM admin WHERE id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
      
// Add a new book
public function addBook($isbn, $title, $author, $published_date, $quantity, $category_id, $status = "Available") {
    $query = "INSERT INTO books (isbn, title, author, published_date, quantity, category_id, status) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("sssssis", $isbn, $title, $author, $published_date, $quantity, $category_id, $status);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

// Update an existing book
public function updateBook($id, $isbn, $title, $author, $published_date, $quantity, $category_id) {
    $query = "UPDATE books SET isbn = ?, title = ?, author = ?, published_date = ?, quantity = ?, category_id = ? 
              WHERE id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("ssssssi", $isbn, $title, $author, $published_date, $quantity, $category_id, $id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

public function getPaginatedBooks($limit, $offset) {
    $query = "SELECT b.id, b.isbn, b.title, b.author, b.published_date, b.quantity, c.category_name, c.id AS category_id
              FROM books b
              JOIN categories c ON b.category_id = c.id
              LIMIT ? OFFSET ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    return $stmt->get_result();
}

public function getBookCount() {
    $query = "SELECT COUNT(*) AS total FROM books";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'];
}

// Delete a book
public function deleteBook($id) {
    $query = "DELETE FROM books WHERE id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}
// Fetch all books with their category names
public function getBooks() {
    $query = "SELECT books.*, categories.category_name 
              FROM books 
              LEFT JOIN categories ON books.category_id = categories.id";
    return $this->conn->query($query);
}
// Get a book's current status
public function getBookStatus($id) {
    $query = "SELECT status FROM books WHERE id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['status'] ?? null;
}
// Fetch book title by ISBN
public function getBookTitleByIsbn($isbn) {
    $stmt = $this->db->prepare("SELECT title FROM books WHERE isbn = ?");
    $stmt->bind_param("s", $isbn);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
    return $book ? $book['title'] : 'Unknown Title';
}

public function getCategoryCount() {
    $query = "SELECT COUNT(*) as total FROM categories";
    $result = $this->conn->query($query);
    $row = $result->fetch_assoc();
    return $row['total'];
}

public function getCategoriesPaginated($limit, $offset) {
    $query = "SELECT * FROM categories ORDER BY category_name ASC LIMIT ? OFFSET ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    return $stmt->get_result();
}



// Add a new category
public function addCategory($category_name) {
    $query = "INSERT INTO categories (category_name) VALUES (?)";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("s", $category_name);
    return $stmt->execute();

}
 // Edit a category
 public function editCategory($id, $category_name) {
    $query = "UPDATE categories SET category_name = ? WHERE id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("si", $category_name, $id);
    return $stmt->execute();
}

// Delete a category
public function deleteCategory($id) {
    $query = "DELETE FROM categories WHERE id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Fetch all categories
public function getCategories() {
    $query = "SELECT * FROM categories";
    return $this->conn->query($query);
}


    // Add a new student
public function addStudent($first_name, $last_name, $course_id, $student_id = null) {
    // If student_id is not passed, generate it or handle accordingly
    // Assuming student_id can be auto-generated by DB (if set to AUTO_INCREMENT)
    $query = "INSERT INTO students (student_id, first_name, last_name, course_id) VALUES (?, ?, ?, ?)";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("issi", $student_id, $first_name, $last_name, $course_id);
    return $stmt->execute();
}

// Update a student
public function updateStudent($id, $first_name, $last_name, $course_id) {
    $query = "UPDATE students SET first_name = ?, last_name = ?, course_id = ? WHERE student_id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("ssii", $first_name, $last_name, $course_id, $id);
    return $stmt->execute();
}

// Delete a student
public function deleteStudent($id) {
    $query = "DELETE FROM students WHERE student_id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

public function getPaginatedStudents($limit, $offset) {
    $query = "SELECT students.student_id, students.first_name, students.last_name, courses.course_name 
              FROM students 
              LEFT JOIN courses ON students.course_id = courses.id 
              LIMIT ? OFFSET ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    return $stmt->get_result();
}

// Get total students count
public function getStudentCount() {
    $stmt = $this->conn->prepare("SELECT COUNT(*) AS total_students FROM students");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total_students'];
}

   

// Add a new course
public function addCourse($course_name, $description) {
    $query = "INSERT INTO courses (course_name, description) VALUES (?, ?)";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("ss", $course_name, $description);
    return $stmt->execute();
}
// Edit a course
public function editCourse($id, $course_name, $description) {
    $query = "UPDATE courses SET course_name = ?, description = ? WHERE id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("ssi", $course_name, $description, $id);
    return $stmt->execute();
}

// Delete a course
public function deleteCourse($id) {
    $query = "DELETE FROM courses WHERE id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}


// Fetch all courses
public function getCourses() {
    $query = "SELECT * FROM courses";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt->get_result();
}

public function getBorrowTransactionCount() {
    $query = "SELECT COUNT(*) as total FROM borrow_transactions";
    $result = $this->conn->query($query);
    $row = $result->fetch_assoc();
    return $row['total'];
}

// Fetch paginated borrow transactions
public function getBorrowTransactionsPaginated($limit, $offset) {
    $query = "SELECT * FROM borrow_transactions ORDER BY borrow_date DESC LIMIT ? OFFSET ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    return $stmt->get_result();
}
// Insert a new borrow transaction
public function borrowBook($student_id, $student_name, $isbn, $borrow_date, $status) {
    // Prepare SQL query to insert the borrow transaction
    $stmt = $this->db->prepare("INSERT INTO borrow_transactions (student_id, student_name, isbn, borrow_date, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $student_id, $student_name, $isbn, $borrow_date, $status);

    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Add a borrow transaction
public function addBorrowTransaction($student_id, $student_name, $isbn) {
    // Ensure the data is safe (use prepared statements to prevent SQL injection)
    $query = "INSERT INTO borrow_transactions (student_id, student_name, isbn, borrow_date, status) 
              VALUES (?, ?, ?, NOW(), 'Borrowed')";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("sss", $student_id, $student_name, $isbn);

    // Execute the query and return the result
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}
 // Delete a borrow transaction by ID
 public function deleteTransaction($transaction_id) {
    $stmt = $this->db->prepare("DELETE FROM borrow_transactions WHERE id = ?");
    $stmt->bind_param("i", $transaction_id);
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}




    // Return book (increase the quantity)
    public function returnBook($book_id, $quantity_returned) {
        // Check current available quantity
        $stmt = $this->db->prepare("SELECT quantity FROM books WHERE id = ?");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $book = $result->fetch_assoc();

        // Increase the quantity in the books table
        $new_quantity = $book['quantity'] + $quantity_returned;
        $stmt = $this->db->prepare("UPDATE books SET quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_quantity, $book_id);
        return $stmt->execute();
    }
public function addReturnTransaction($student_id, $student_name, $isbn, $return_date) {
    $query = "INSERT INTO return_transactions (student_id, student_name, isbn, return_date, status) 
              VALUES (?, ?, ?, ?, 'returned')";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("ssss", $student_id, $student_name, $isbn, $return_date); // Bind parameters
    return $stmt->execute();  // Execute and return true if successful
     // Execute the query and return the result
     if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}
// Delete a return transaction
public function deleteReturnTransaction($transaction_id) {
    $sql = "DELETE FROM return_transactions WHERE id = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param('i', $transaction_id);
    return $stmt->execute();
}



public function increaseAvailableBooks($isbn) {
    $sql = "UPDATE books SET available = available + 1 WHERE isbn = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param("s", $isbn);
    return $stmt->execute();
}

public function decreaseAvailableBooks($isbn) {
    $sql = "UPDATE books SET available = available - 1 WHERE isbn = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param('s', $isbn);
    return $stmt->execute();
}

public function getAvailableBooks() {
    $query = "SELECT books.*, categories.category_name FROM books 
              JOIN categories ON books.category_id = categories.id
              WHERE books.quantity > 0";
    return $this->db->query($query);
}

public function logAction($admin_id, $action) {
    try {
        $sql = "INSERT INTO logs (admin_id, action) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("is", $admin_id, $action);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        // Handle exceptions (log error, etc.)
        error_log("Failed to log action: " . $e->getMessage());
    }
}



// Get all books
public function getAllBooks() {
    $query = "SELECT books.*, categories.category_name FROM books LEFT JOIN categories ON books.category_id = categories.id";
    $result = $this->db->query($query); // Using the query method from the Database class
    return $result; // Return the result directly
}
 // Fetch all students with their course names
 public function getAllStudents() {
    $query = "SELECT students.student_id, students.first_name, students.last_name, courses.course_name 
              FROM students 
              LEFT JOIN courses ON students.course_id = courses.id";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt->get_result();
}

// Get all categories
public function getAllCategories() {
    $query = "SELECT * FROM categories";
    $result = $this->db->query($query); // Using the query method from the Database class
    return $result; // Return the result directly
}

 
// Fetch all courses
public function getAllCourses() {
    $query = "SELECT * FROM courses";
    return $this->conn->query($query);
}

    // Fetch all borrow transactions
    public function getAllBorrowTransactions() {
        $stmt = $this->db->prepare("SELECT bt.id, bt.student_id, bt.student_name, bt.isbn, bt.borrow_date, bt.status FROM borrow_transactions bt");
        $stmt->execute();
        return $stmt->get_result();
    }


// Fetch all return transactions
public function getAllReturnTransactions() {
    $sql = "SELECT * FROM return_transactions";  // Modify this with the actual table name
    return $this->db->query($sql);
}





    // Fetch statistics (Books, Students, Borrowed, Returned)
    public function getTotalBooks() {
        $stmt = $this->conn->prepare("SELECT SUM(quantity) AS total_books FROM books");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total_books'];
    }

    public function getTotalStudents() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total_students FROM students");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total_students'];
    }

    public function getTotalBorrowed() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total_borrowed FROM borrow_transactions WHERE status = 'Borrowed'");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total_borrowed'];
    }

    public function getTotalReturned() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total_returned FROM return_transactions WHERE status = 'Returned'");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total_returned'];
    }

}

?>
