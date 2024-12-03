<?php


class Database {
    private $host = 'localhost';
    private $db = 'librarysystem';
    private $user = 'root';
    private $password = '';
    private $conn;

    public function __construct() {
        $this->connect();
    }

    public function connect() {
        $this->conn = new mysqli($this->host, $this->user, $this->password, $this->db);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function getConnection() {
        return $this->conn;
    }

// Add a query method for compatibility
public function query($sql) {
    return $this->conn->query($sql); // Use mysqli's query method
}
public function prepare($query) {
    return $this->conn->prepare($query); // Delegate to mysqli
}

}
?>
