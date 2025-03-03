<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle CORS preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Prevent multiple inclusions
if (!class_exists('Database')) {
    class Database {
        private $host = "localhost";   // Database Host
        private $db_name = "saas_platform"; // Your Database Name
        private $username = "root";    // Database Username (default: root in XAMPP)
        private $password = "";        // Database Password (leave blank if using XAMPP)
        public $conn;

        public function connect() {
            $this->conn = null;
            try {
                $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $exception) {
                echo "Database Connection Error: " . $exception->getMessage();
            }
            return $this->conn;
        }
    }
}
?>
