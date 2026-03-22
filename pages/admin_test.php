<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/session.php';

echo "Session included<br>";

if (!isLoggedIn()) {
    die("Not logged in - <a href='login.php'>Go to Login</a>");
}

echo "User is logged in, ID: " . $_SESSION['user_id'] . "<br>";

$user = getCurrentUser();
if (!$user) {
    die("Could not load user data from database");
}

echo "User loaded: " . $user['name'] . " (Role: " . $user['role_name'] . ")<br>";

if ($user['role_name'] !== 'Admin') {
    die("User is not Admin. Role: " . $user['role_name']);
}

echo "<h1>Admin Dashboard Test - SUCCESS!</h1>";
echo "<p>All checks passed. The admin dashboard should work.</p>";
echo "<a href='admin_dashboard.php'>Go to Admin Dashboard</a> | ";
echo "<a href='logout.php'>Logout</a>";
?>
