<?php
// api/auth.php
// Authentication API endpoints

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$conn = getDBConnection();

switch ($action) {
    case 'login':
        handleLogin($conn);
        break;
    case 'register':
        handleRegister($conn);
        break;
    case 'logout':
        logout();
        break;
    case 'check_registration':
        checkRegistrationStatus($conn);
        break;
    case 'update_profile':
        updateProfile($conn);
        break;
    case 'get_profile':
        getProfile($conn);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

$conn->close();

function handleLogin($conn) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Email and password required']);
        return;
    }
    
    $stmt = $conn->prepare("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = ? AND u.status = 'Active'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role_name'];
        
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role_name']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
    }
}

function handleRegister($conn) {
    // Check if registration is enabled
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'student_registration_enabled'");
    $stmt->execute();
    $result = $stmt->get_result();
    $setting = $result->fetch_assoc();
    $stmt->close();
    
    if ($setting && $setting['setting_value'] == '0') {
        echo json_encode(['success' => false, 'error' => 'Student registration is currently disabled']);
        return;
    }
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $name = $_POST['name'] ?? explode('@', $email)[0];
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Email and password required']);
        return;
    }
    
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters']);
        return;
    }
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'error' => 'Email already registered']);
        return;
    }
    $stmt->close();
    
    // Create user (default role: Student = 3)
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $role_id = 3; // Student
    $uid = uniqid('user_', true);
    
    $stmt = $conn->prepare("INSERT INTO users (uid, name, email, password, role_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $uid, $name, $email, $hashedPassword, $role_id);
    
    if ($stmt->execute()) {
        $userId = $stmt->insert_id;
        $_SESSION['user_id'] = $userId;
        $_SESSION['role'] = 'Student';
        
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'role' => 'Student'
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Registration failed']);
    }
    $stmt->close();
}

function checkRegistrationStatus($conn) {
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'student_registration_enabled'");
    $stmt->execute();
    $result = $stmt->get_result();
    $setting = $result->fetch_assoc();
    $stmt->close();
    
    $enabled = $setting ? $setting['setting_value'] == '1' : true;
    echo json_encode(['enabled' => $enabled]);
}

function getProfile($conn) {
    $user = getCurrentUser();
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role_name']
        ]
    ]);
}

function updateProfile($conn) {
    $user = getCurrentUser();
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        return;
    }
    
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    
    if (empty($name) || empty($email)) {
        echo json_encode(['success' => false, 'error' => 'Name and email are required']);
        return;
    }
    
    // Check if email is being changed and if it's already taken
    if ($email !== $user['email']) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user['id']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->close();
            echo json_encode(['success' => false, 'error' => 'Email already in use']);
            return;
        }
        $stmt->close();
    }
    
    // If changing password, verify current password
    if (!empty($newPassword)) {
        if (empty($currentPassword)) {
            echo json_encode(['success' => false, 'error' => 'Current password required to change password']);
            return;
        }
        
        if (!password_verify($currentPassword, $user['password'])) {
            echo json_encode(['success' => false, 'error' => 'Current password is incorrect']);
            return;
        }
        
        if (strlen($newPassword) < 6) {
            echo json_encode(['success' => false, 'error' => 'New password must be at least 6 characters']);
            return;
        }
    }
    
    // Update profile
    if (!empty($newPassword)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $email, $hashedPassword, $user['id']);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $email, $user['id']);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update profile']);
    }
    $stmt->close();
}
?>
