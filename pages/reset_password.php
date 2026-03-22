<?php
// pages/reset_password.php
require_once __DIR__ . '/../config/database.php';

$message = '';
$error = '';
$valid_token = false;
$user_id = null;

// Check if token is provided in URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = 'Invalid or missing reset token.';
} else {
    // Verify token exists and is not expired
    $stmt = $conn->prepare("SELECT id, email, role_id FROM users WHERE reset_token = ? AND reset_token_expires > NOW() AND status = 'Active'");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if ($user) {
        // Check if user is admin - don't allow reset
        $stmt = $conn->prepare("SELECT role_name FROM roles WHERE id = ?");
        $stmt->bind_param("i", $user['role_id']);
        $stmt->execute();
        $role_result = $stmt->get_result();
        $role = $role_result->fetch_assoc();
        $stmt->close();
        
        if ($role && $role['role_name'] === 'Admin') {
            $error = 'Password reset is not available for admin accounts.';
        } else {
            $valid_token = true;
            $user_id = $user['id'];
        }
    } else {
        $error = 'Invalid or expired reset token. Please request a new password reset.';
    }
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password) || empty($confirm_password)) {
        $error = 'Please enter and confirm your new password.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Hash and update password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            $message = 'Your password has been reset successfully. You can now login with your new password.';
            $valid_token = false; // Hide form after success
        } else {
            $error = 'Failed to reset password. Please try again.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Syntalytix</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            transition: background 0.5s;
        }
        body.dark { background: #020617; }
        
        .theme-toggle {
            position: absolute;
            top: 2rem;
            right: 2rem;
            padding: 0.75rem;
            border-radius: 50%;
            border: 1px solid #e2e8f0;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
        }
        body.dark .theme-toggle {
            background: #1e293b;
            border-color: #334155;
            color: #fbbf24;
        }
        
        .card {
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            border-radius: 1.5rem;
            background: white;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            transition: all 0.5s;
        }
        body.dark .card {
            background: #0f172a;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        
        h1 {
            text-align: center;
            color: #2563eb;
            font-size: 1.875rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
        }
        p.subtitle {
            text-align: center;
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 2rem;
        }
        body.dark p.subtitle { color: #64748b; }
        
        .form-group { margin-bottom: 1rem; }
        
        input[type="password"] {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s;
            background: #f8fafc;
        }
        body.dark input[type="password"] {
            background: #1e293b;
            border-color: #334155;
            color: white;
        }
        input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }
        
        button[type="submit"] {
            width: 100%;
            padding: 1rem;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 1rem;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
        }
        button[type="submit"]:hover { background: #1d4ed8; }
        
        .message {
            padding: 1rem;
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #16a34a;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .error-message {
            padding: 1rem;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .links {
            text-align: center;
            margin-top: 1.5rem;
        }
        .links a {
            color: #64748b;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .links a:hover { color: #1e293b; }
        body.dark .links a:hover { color: white; }
    </style>
</head>
<body>
    <button class="theme-toggle" onclick="toggleTheme()">🌙</button>
    
    <div class="card">
        <h1>Reset Password</h1>
        <p class="subtitle"><?php echo $valid_token ? 'Enter your new password' : 'Password Reset'; ?></p>
        
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($valid_token): ?>
        <form method="POST" action="">
            <div class="form-group">
                <input type="password" name="password" placeholder="New Password" required>
            </div>
            <div class="form-group">
                <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
            </div>
            <button type="submit">Reset Password</button>
        </form>
        <?php endif; ?>
        
        <div class="links">
            <a href="login.php">← Back to Login</a>
        </div>
    </div>
    
    <script>
        // Theme handling
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark');
            document.querySelector('.theme-toggle').textContent = '☀️';
        }
        
        function toggleTheme() {
            const isDark = document.body.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            document.querySelector('.theme-toggle').textContent = isDark ? '☀️' : '🌙';
        }
    </script>
</body>
</html>
