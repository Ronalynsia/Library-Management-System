<?php

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        $this->connect();
    }

    public function connect() {
        // Check if the server is running on localhost
        if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_ADDR'] == '::1') {
            // Localhost connection
            $this->host = 'localhost';
            $this->db_name = 'librarysystem';
            $this->username = 'root';
            $this->password = '';
        } else {
            // Live server connection
            $this->host = 'localhost';
            $this->db_name = 'u772084991_library';
            $this->username = 'u772084991_library';
            $this->password = 'Libmanage2024'; // Fixed the missing closing quote here
        }

        // Create a connection
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
        
        // Check for connection error
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
