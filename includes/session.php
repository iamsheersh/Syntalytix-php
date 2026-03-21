<?php
// includes/session.php
// Session and authentication handling

session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Get current user
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    require_once __DIR__ . '/../config/database.php';
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    
    return $user;
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /lms-php/pages/login.php');
        exit();
    }
}

// Require specific role
function requireRole($role) {
    requireLogin();
    $user = getCurrentUser();
    if (!$user || $user['role_name'] !== $role) {
        header('Location: /lms-php/pages/' . strtolower($user['role_name']) . '_dashboard.php');
        exit();
    }
}

// Logout function
function logout() {
    session_destroy();
    header('Location: /lms-php/pages/login.php');
    exit();
}
?>
