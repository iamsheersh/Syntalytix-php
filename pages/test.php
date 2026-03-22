<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PHP Test Page</h1>";
echo "<p>PHP is working!</p>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";

// Test database connection
require_once __DIR__ . '/../config/database.php';
$conn = getDBConnection();

if ($conn) {
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    $conn->close();
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>";
}

// Test session
session_start();
if (isset($_SESSION['user_id'])) {
    echo "<p style='color: green;'>✓ Session active - User ID: " . $_SESSION['user_id'] . "</p>";
} else {
    echo "<p style='color: orange;'>⚠ No active session</p>";
}

echo "<p><a href='login.php'>Go to Login</a></p>";
?>
