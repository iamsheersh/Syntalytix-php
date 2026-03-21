<?php
// config/database.php
// Database configuration

define('DB_HOST', 'localhost');
define('DB_PORT', 3309);
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'lms_db');

// Create database connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>
