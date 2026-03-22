<?php
// pages/forgot_password.php
require_once __DIR__ . '/../config/database.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } else {
        // Check if email exists and is not an admin
        $stmt = $conn->prepare("SELECT u.id, u.name, u.email, r.role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = ? AND u.status = 'Active'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        if ($user) {
            if ($user['role_name'] === 'Admin') {
                $error = 'Password reset is not available for admin accounts. Please contact system administrator.';
            } else {
                // Generate a simple reset token (in production, use better token generation and email)
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store token in database
                $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
                $stmt->bind_param("ssi", $token, $expires, $user['id']);
                $stmt->execute();
                $stmt->close();
                
                // For now, show success message with token (in production, send email)
                $reset_link = 'https://' . $_SERVER['HTTP_HOST'] . '/lms-php/pages/reset_password.php?token=' . $token;
                $message = 'Password reset instructions have been sent to your email.<br><br>';
                $message .= '<strong>For testing:</strong> <a href="' . htmlspecialchars($reset_link) . '" style="color: #2563eb; text-decoration: underline;">Click here to reset password</a><br>';
                $message .= '<small style="font-size: 0.75rem; color: #64748b;">Link expires in 1 hour</small>';
                // Store token in session for demo purposes
                $_SESSION['reset_token_' . $user['id']] = $token;
                $_SESSION['reset_user_id'] = $user['id'];
            }
        } else {
            // Don't reveal if email exists
            $message = 'If an account exists with this email, password reset instructions have been sent.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Syntalytix</title>
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
        
        input[type="email"] {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s;
            background: #f8fafc;
        }
        body.dark input[type="email"] {
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
        <h1>Forgot Password</h1>
        <p class="subtitle">Enter your email to reset your password</p>
        
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!$message): ?>
        <form method="POST" action="">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <button type="submit">Send Reset Link</button>
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
