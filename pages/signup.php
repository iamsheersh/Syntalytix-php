<?php
// pages/signup.php
require_once __DIR__ . '/../includes/session.php';

if (isLoggedIn()) {
    header('Location: ' . strtolower($_SESSION['role']) . '_dashboard.php');
    exit();
}

// Check if registration is enabled
require_once __DIR__ . '/../config/database.php';
$conn = getDBConnection();
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'student_registration_enabled'");
$setting = $result->fetch_assoc();
$registrationEnabled = $setting ? $setting['setting_value'] == '1' : true;
$conn->close();

// Redirect to login if registration is disabled
if (!$registrationEnabled) {
    header('Location: login.php?error=registration_disabled');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Syntalytix</title>
    <link rel="icon" type="image/png" href="../assets/logo.png">
    <link rel="apple-touch-icon" href="../assets/logo.png">
    <meta name="theme-color" content="#0f172a">
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
        
        .signup-card {
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            border-radius: 1.5rem;
            background: white;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            transition: all 0.5s;
        }
        body.dark .signup-card {
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
        
        .role-selector {
            display: flex;
            gap: 0.5rem;
            padding: 0.375rem;
            background: #f1f5f9;
            border-radius: 1rem;
            margin-bottom: 1.5rem;
        }
        body.dark .role-selector { background: #020617; }
        
        .role-btn {
            flex: 1;
            padding: 0.625rem;
            border: none;
            border-radius: 0.75rem;
            font-weight: 700;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.3s;
            color: #64748b;
            background: transparent;
        }
        .role-btn.active {
            background: white;
            color: #2563eb;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        body.dark .role-btn.active {
            background: #1e293b;
            color: #60a5fa;
        }
        .role-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .form-group { margin-bottom: 1rem; }
        
        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s;
            background: #f8fafc;
        }
        body.dark input[type="email"],
        body.dark input[type="password"],
        body.dark input[type="text"] {
            background: #1e293b;
            border-color: #334155;
            color: white;
        }
        input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }
        input:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .password-wrapper { position: relative; }
        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
        }
        .toggle-password:disabled {
            opacity: 0.5;
            cursor: not-allowed;
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
        }
        button[type="submit"]:hover:not(:disabled) { background: #1d4ed8; }
        button[type="submit"]:disabled {
            opacity: 0.5;
            cursor: not-allowed;
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
            display: none;
        }
        .error-message.show { display: block; }
        
        .warning-message {
            padding: 1rem;
            background: #fffbeb;
            border: 1px solid #fed7aa;
            color: #c2410c;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: none;
        }
        .warning-message.show { display: block; }
        
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
        body.dark .password-requirements { background: #1e293b; border-color: #334155; }
        body.dark .password-requirements div { color: #94a3b8; }
        
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <button class="theme-toggle" onclick="toggleTheme()">🌙</button>
    
    <div class="signup-card">
        <div class="logo-container" style="text-align: center; margin-bottom: 1.5rem;">
            <a href="../index.php">
                <img src="../assets/logo.png" alt="Syntalytix Logo" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
            </a>
        </div>
        <h1>Create Account</h1>
        <p class="subtitle">Join the Syntalytix platform today</p>
        
        <div class="error-message" id="error"></div>
        
        <form id="signupForm">
            <div class="role-selector">
                <button type="button" class="role-btn active" data-role="Student" onclick="setRole('Student')">Student</button>
                <button type="button" class="role-btn" data-role="Teacher" onclick="setRole('Teacher')">Teacher</button>
            </div>
            
            <div class="form-group">
                <input type="text" id="name" placeholder="Full Name" required>
            </div>
            <div class="form-group">
                <input type="email" id="email" placeholder="Email Address" required>
            </div>
            <div class="form-group">
                <div class="password-wrapper">
                    <input type="password" id="password" placeholder="Password (min 10 characters)" required oninput="checkPasswordStrength()">
                    <button type="button" class="toggle-password" onclick="togglePassword()">👁️</button>
                </div>
                <!-- Password Requirements -->
                <div class="password-requirements" style="margin-top: 0.75rem; padding: 0.75rem; background: #f1f5f9; border-radius: 0.75rem; font-size: 0.8rem; border: 1px solid #e2e8f0;">
                    <div style="font-weight: 700; margin-bottom: 0.5rem; color: #475569; font-size: 0.85rem;">Password Requirements:</div>
                    <div class="req-item" id="req-length" style="display: flex; align-items: center; gap: 0.5rem; color: #64748b; margin-bottom: 0.25rem;">
                        <span class="req-icon">⭕</span>
                        <span>Minimum 10 characters</span>
                    </div>
                    <div class="req-item" id="req-uppercase" style="display: flex; align-items: center; gap: 0.5rem; color: #64748b; margin-bottom: 0.25rem;">
                        <span class="req-icon">⭕</span>
                        <span>One uppercase letter (A-Z)</span>
                    </div>
                    <div class="req-item" id="req-lowercase" style="display: flex; align-items: center; gap: 0.5rem; color: #64748b; margin-bottom: 0.25rem;">
                        <span class="req-icon">⭕</span>
                        <span>One lowercase letter (a-z)</span>
                    </div>
                    <div class="req-item" id="req-number" style="display: flex; align-items: center; gap: 0.5rem; color: #64748b; margin-bottom: 0.25rem;">
                        <span class="req-icon">⭕</span>
                        <span>One number (0-9)</span>
                    </div>
                    <div class="req-item" id="req-special" style="display: flex; align-items: center; gap: 0.5rem; color: #64748b;">
                        <span class="req-icon">⭕</span>
                        <span>One special symbol (!@#$%^&*)</span>
                    </div>
                </div>
            </div>
            <button type="submit" id="submitBtn" disabled style="opacity: 0.5;">Register as Student</button>
        </form>
        
        <div class="links">
            <a href="login.php">Already have an account? Sign In</a>
        </div>
    </div>
    
    <script>
        let selectedRole = 'Student';
        
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
        
        function togglePassword() {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            
            // Check each requirement
            const hasLength = password.length >= 10;
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
            
            // Update UI for each requirement
            updateReqItem('req-length', hasLength);
            updateReqItem('req-uppercase', hasUppercase);
            updateReqItem('req-lowercase', hasLowercase);
            updateReqItem('req-number', hasNumber);
            updateReqItem('req-special', hasSpecial);
            
            // Enable/disable submit button based on all requirements
            const allValid = hasLength && hasUppercase && hasLowercase && hasNumber && hasSpecial;
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = !allValid;
            submitBtn.style.opacity = allValid ? '1' : '0.5';
        }

        function updateReqItem(id, isValid) {
            const item = document.getElementById(id);
            if (isValid) {
                item.style.color = '#16a34a'; // Green
                item.querySelector('.req-icon').textContent = '✅';
            } else {
                item.style.color = '#64748b'; // Gray
                item.querySelector('.req-icon').textContent = '⭕';
            }
        }
        
        function setRole(role) {
            selectedRole = role;
            document.querySelectorAll('.role-btn').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.role === role);
            });
            document.getElementById('submitBtn').textContent = 'Register as ' + role;
        }
        
        document.getElementById('signupForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const errorDiv = document.getElementById('error');
            const submitBtn = document.getElementById('submitBtn');
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading"></span>';
            errorDiv.classList.remove('show');
            
            try {
                const response = await fetch('../api/auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=register&name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}&role=${encodeURIComponent(selectedRole)}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (data.pending) {
                        // Teacher pending approval - redirect to login with message
                        window.location.href = 'login.php?info=pending_teacher';
                    } else {
                        // Student - go to dashboard
                        window.location.href = 'student_dashboard.php';
                    }
                } else {
                    errorDiv.textContent = data.error || 'Registration failed';
                    errorDiv.classList.add('show');
                }
            } catch (err) {
                errorDiv.textContent = 'Network error. Please try again.';
                errorDiv.classList.add('show');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Register as ' + selectedRole;
            }
        });
    </script>
    <?php include __DIR__ . '/../includes/support_popup.php'; ?>
</body>
</html>
