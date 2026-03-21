<?php
// pages/login.php
require_once __DIR__ . '/../includes/session.php';

if (isLoggedIn()) {
    $role = $_SESSION['role'] ?? 'Student';
    header('Location: /lms-php/pages/' . strtolower($role) . '_dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Syntalytix</title>
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
        
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            border-radius: 1.5rem;
            background: white;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            transition: all 0.5s;
        }
        body.dark .login-card {
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
        
        input[type="email"],
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
        body.dark input[type="email"],
        body.dark input[type="password"] {
            background: #1e293b;
            border-color: #334155;
            color: white;
        }
        input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
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
        button[type="submit"]:disabled {
            opacity: 0.7;
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
    
    <div class="login-card">
        <div class="logo-container" style="text-align: center; margin-bottom: 1.5rem;">
            <img src="../assets/logo.png" alt="Syntalytix Logo" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
        </div>
        <h1>Welcome Back</h1>
        <p class="subtitle">Sign in to your Syntalytix account</p>
        
        <div class="error-message" id="error"></div>
        
        <form id="loginForm">
            <div class="form-group">
                <input type="email" id="email" placeholder="Email Address" required>
            </div>
            <div class="form-group">
                <div class="password-wrapper">
                    <input type="password" id="password" placeholder="Password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword()">👁️</button>
                </div>
            </div>
            <button type="submit" id="submitBtn">Sign In</button>
        </form>
        
        <div class="links">
            <a href="signup.php">Don't have an account? Sign Up</a>
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
        
        function togglePassword() {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
        
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
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
                    body: `action=login&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = '/lms-php/pages/' + data.user.role.toLowerCase() + '_dashboard.php';
                } else {
                    errorDiv.textContent = data.error || 'Login failed';
                    errorDiv.classList.add('show');
                }
            } catch (err) {
                errorDiv.textContent = 'Network error. Please try again.';
                errorDiv.classList.add('show');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Sign In';
            }
        });
    </script>
</body>
</html>
