<?php
// config/database.php
// Database configuration - supports local XAMPP and Railway.app

// Railway provides MySQL via environment variables
// MYSQLHOST, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE, MYSQLPORT
if (getenv('MYSQLHOST')) {
    // Railway.app environment
    define('DB_HOST', getenv('MYSQLHOST'));
    define('DB_PORT', getenv('MYSQLPORT') ?: 3306);
    define('DB_USER', getenv('MYSQLUSER'));
    define('DB_PASS', getenv('MYSQLPASSWORD'));
    define('DB_NAME', getenv('MYSQLDATABASE'));
} else {
    // Local XAMPP environment
    define('DB_HOST', 'localhost');
    define('DB_PORT', 3309);
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'lms_db');
}

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
