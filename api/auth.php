<?php
// api/auth.php
// Authentication API endpoints

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

$phpMailerBase = __DIR__ . '/../vendor/phpmailer/phpmailer/src/';
if (is_dir($phpMailerBase)) {
    require_once $phpMailerBase . 'Exception.php';
    require_once $phpMailerBase . 'PHPMailer.php';
    require_once $phpMailerBase . 'SMTP.php';
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$conn = getDBConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

switch ($action) {
    case 'login':
        handleLogin($conn);
        break;
    case 'register':
        handleRegister($conn);
        break;
    case 'request_reset_otp':
        handleRequestResetOtp($conn);
        break;
    case 'reset_password_otp':
        handleResetPasswordOtp($conn);
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

function getPlatformName($conn) {
    $platformName = 'Syntalytix';
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'platform_name' LIMIT 1");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $setting = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        if ($setting && !empty($setting['setting_value'])) {
            $platformName = $setting['setting_value'];
        }
    }
    return $platformName;
}

function sendOtpEmail($toEmail, $toName, $otp, $platformName) {
    $subject = $platformName . ' Password Reset OTP';
    $bodyText = "Your OTP for password reset is: {$otp}\n\nThis OTP will expire in 10 minutes. If you did not request this, you can ignore this email.";

    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $smtpHost = getenv('SMTP_HOST') ?: '';
            $smtpUser = getenv('SMTP_USER') ?: '';
            $smtpPass = getenv('SMTP_PASS') ?: '';
            $smtpPort = getenv('SMTP_PORT') ?: '';
            $smtpSecure = getenv('SMTP_SECURE') ?: '';
            $fromEmail = getenv('SMTP_FROM_EMAIL') ?: $smtpUser;
            $fromName = getenv('SMTP_FROM_NAME') ?: $platformName;

            if (!$smtpHost || !$smtpUser || !$smtpPass) {
                $headers = 'From: ' . ($fromEmail ?: 'no-reply@localhost');
                return @mail($toEmail, $subject, $bodyText, $headers);
            }

            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUser;
            $mail->Password = $smtpPass;
            if ($smtpPort !== '') {
                $mail->Port = (int)$smtpPort;
            }
            if ($smtpSecure) {
                $mail->SMTPSecure = $smtpSecure;
            }

            $mail->setFrom($fromEmail ?: $smtpUser, $fromName);
            $mail->addAddress($toEmail, $toName ?: '');

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = '<div style="font-family: Arial, sans-serif; line-height: 1.5; color: #0f172a;">'
                . '<h2 style="margin: 0 0 12px 0;">Password reset</h2>'
                . '<p style="margin: 0 0 12px 0;">Use this OTP to reset your password:</p>'
                . '<div style="font-size: 28px; font-weight: 800; letter-spacing: 4px; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 12px; display: inline-block;">'
                . htmlspecialchars($otp)
                . '</div>'
                . '<p style="margin: 14px 0 0 0; color: #64748b;">This OTP expires in 10 minutes. If you did not request this, you can ignore this email.</p>'
                . '</div>';
            $mail->AltBody = $bodyText;

            return $mail->send();
        } catch (Exception $e) {
            return false;
        }
    }

    $headers = 'From: no-reply@localhost';
    return @mail($toEmail, $subject, $bodyText, $headers);
}

function handleRequestResetOtp($conn) {
    $email = $_POST['email'] ?? '';
    if (empty($email)) {
        echo json_encode(['success' => false, 'error' => 'Email is required']);
        return;
    }

    $stmt = $conn->prepare("SELECT id, name, email, reset_token_expires FROM users WHERE email = ? AND status = 'Active' LIMIT 1");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Server error']);
        return;
    }
    $stmt->bind_param('s', $email);
    if (!$stmt->execute()) {
        $stmt->close();
        echo json_encode(['success' => false, 'error' => 'Server error']);
        return;
    }
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$user) {
        echo json_encode(['success' => true, 'message' => 'If the email exists, an OTP has been sent.']);
        return;
    }

    if (!empty($user['reset_token_expires'])) {
        $stmt = $conn->prepare("SELECT (reset_token_expires > NOW()) AS valid, GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), DATE_SUB(reset_token_expires, INTERVAL 8 MINUTE))) AS retry_after FROM users WHERE id = ? LIMIT 1");
        if (!$stmt) {
            return;
        }
        $stmt->bind_param('i', $user['id']);
        if (!$stmt->execute()) {
            $stmt->close();
            return;
        }
        $validResult = $stmt->get_result();
        $validRow = $validResult ? $validResult->fetch_assoc() : null;
        $stmt->close();

        if ($validRow && (int)$validRow['valid'] === 1) {
            $retryAfter = (int)($validRow['retry_after'] ?? 0);
            if ($retryAfter > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Please wait before requesting another OTP.',
                    'retry_after' => $retryAfter
                ]);
                return;
            }
        }
    }

    $otp = (string)random_int(100000, 999999);
    $otpHash = password_hash($otp, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires = (NOW() + INTERVAL 10 MINUTE) WHERE id = ?");
    if (!$stmt) {
        return;
    }
    $stmt->bind_param('si', $otpHash, $user['id']);
    $stmt->execute();
    $stmt->close();

    $platformName = getPlatformName($conn);
    sendOtpEmail($user['email'], $user['name'], $otp, $platformName);

    echo json_encode(['success' => true, 'message' => 'If the email exists, an OTP has been sent.', 'retry_after' => 120]);
}

function handleResetPasswordOtp($conn) {
    $email = $_POST['email'] ?? '';
    $otp = $_POST['otp'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';

    if (empty($email) || empty($otp) || empty($newPassword)) {
        echo json_encode(['success' => false, 'error' => 'Email, OTP and new password are required']);
        return;
    }
    if (strlen($newPassword) < 6) {
        echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters']);
        return;
    }

    $stmt = $conn->prepare("SELECT id, password, reset_token, reset_token_expires FROM users WHERE email = ? AND status = 'Active' LIMIT 1");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Server error']);
        return;
    }
    $stmt->bind_param('s', $email);
    if (!$stmt->execute()) {
        $stmt->close();
        echo json_encode(['success' => false, 'error' => 'Server error']);
        return;
    }
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$user || empty($user['reset_token']) || empty($user['reset_token_expires'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid or expired OTP']);
        return;
    }

    $stmt = $conn->prepare("SELECT (reset_token_expires > NOW()) AS valid FROM users WHERE id = ? LIMIT 1");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Server error']);
        return;
    }
    $stmt->bind_param('i', $user['id']);
    if (!$stmt->execute()) {
        $stmt->close();
        echo json_encode(['success' => false, 'error' => 'Server error']);
        return;
    }
    $validResult = $stmt->get_result();
    $validRow = $validResult ? $validResult->fetch_assoc() : null;
    $stmt->close();

    if (!$validRow || (int)$validRow['valid'] !== 1) {
        echo json_encode(['success' => false, 'error' => 'Invalid or expired OTP']);
        return;
    }

    if (!password_verify($otp, $user['reset_token'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid OTP']);
        return;
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Server error']);
        return;
    }
    $stmt->bind_param('si', $hashedPassword, $user['id']);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update password']);
    }
    $stmt->close();
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
    $name = $_POST['name'] ?? '';
    $role = $_POST['role'] ?? 'Student';
    
    if (empty($name) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Name, email and password required']);
        return;
    }
    
    // Password validation
    if (strlen($password) < 10) {
        echo json_encode(['success' => false, 'error' => 'Password must be at least 10 characters']);
        return;
    }
    if (!preg_match('/[A-Z]/', $password)) {
        echo json_encode(['success' => false, 'error' => 'Password must contain at least one uppercase letter']);
        return;
    }
    if (!preg_match('/[a-z]/', $password)) {
        echo json_encode(['success' => false, 'error' => 'Password must contain at least one lowercase letter']);
        return;
    }
    if (!preg_match('/[0-9]/', $password)) {
        echo json_encode(['success' => false, 'error' => 'Password must contain at least one number']);
        return;
    }
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
        echo json_encode(['success' => false, 'error' => 'Password must contain at least one special symbol']);
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
    
    // Determine role_id and status
    $role_id = 3; // Default Student
    $status = 'Active';
    if ($role === 'Teacher') {
        $role_id = 2; // Teacher
        $status = 'Pending'; // Teachers need approval
    }
    
    // Create user
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $uid = uniqid('user_', true);
    
    $stmt = $conn->prepare("INSERT INTO users (uid, name, email, password, role_id, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssis", $uid, $name, $email, $hashedPassword, $role_id, $status);
    
    if ($stmt->execute()) {
        $userId = $stmt->insert_id;
        
        // If teacher is pending, don't auto-login
        if ($status === 'Pending') {
            echo json_encode([
                'success' => true,
                'pending' => true,
                'message' => 'Your teacher account has been created and is pending admin approval.',
                'user' => [
                    'id' => $userId,
                    'name' => $name,
                    'email' => $email,
                    'role' => 'Teacher',
                    'status' => 'Pending'
                ]
            ]);
        } else {
            // Student - auto login
            $_SESSION['user_id'] = $userId;
            $_SESSION['role'] = 'Student';
            
            echo json_encode([
                'success' => true,
                'pending' => false,
                'user' => [
                    'id' => $userId,
                    'name' => $name,
                    'email' => $email,
                    'role' => 'Student'
                ]
            ]);
        }
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
