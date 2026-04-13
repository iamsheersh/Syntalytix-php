<?php
// config/database.php
// Database configuration for local XAMPP

// Global XAMPP environment
define('DB_HOST', 'localhost');
define('DB_PORT', 3309);
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'lms_db');

// Create database connection
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        
        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            return null;
        }
        
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}
?>
