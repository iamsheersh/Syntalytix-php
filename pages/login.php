<?php
// pages/login.php
require_once __DIR__ . '/../includes/session.php';

if (isLoggedIn()) {
    $role = $_SESSION['role'] ?? 'Student';
    header('Location: /lms-php/pages/' . strtolower($role) . '_dashboard.php');
    exit();
}

$error = '';
$info = '';
if (isset($_GET['error']) && $_GET['error'] === 'registration_disabled') {
    $error = 'Student registration is currently disabled by the administrator.';
}
if (isset($_GET['info']) && $_GET['info'] === 'pending_teacher') {
    $info = 'Your teacher account has been created and is pending admin approval. Please check back later or contact support.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Syntalytix</title>
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
        .info-message {
            padding: 1rem;
            background: #fef3c7;
            border: 1px solid #fbbf24;
            color: #b45309;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: none;
        }
        .info-message.show { display: block; }
        body.dark .info-message {
            background: #451a03;
            border-color: #92400e;
            color: #fbbf24;
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
        
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(2, 6, 23, 0.65);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            z-index: 1000;
        }
        .modal-overlay.show { display: flex; }
        .modal {
            width: 100%;
            max-width: 420px;
            background: white;
            border-radius: 1.25rem;
            padding: 1.5rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.25);
        }
        body.dark .modal { background: #0f172a; }
        .modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 0.75rem;
        }
        .modal-close {
            border: 1px solid #e2e8f0;
            background: transparent;
            color: #64748b;
            width: 36px;
            height: 36px;
            border-radius: 0.9rem;
            cursor: pointer;
            font-size: 1rem;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        body.dark .modal-close { border-color: #334155; color: #94a3b8; }
        .modal-close:hover { color: #0f172a; background: rgba(15, 23, 42, 0.04); }
        body.dark .modal-close:hover { color: #fff; background: rgba(255, 255, 255, 0.06); }
        .modal h2 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 900;
            color: #0f172a;
        }
        body.dark .modal h2 { color: white; }
        .modal p {
            margin-top: 0.5rem;
            color: #64748b;
            font-size: 0.875rem;
        }
        .modal-form {
            margin-top: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }
        .field-label {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 0.75rem;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        body.dark .field-label { color: #94a3b8; }
        .modal input[type="email"],
        .modal input[type="password"],
        .modal input[type="text"] {
            padding: 0.95rem 1.1rem;
        }
        .otp-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.85rem;
        }
        .otp-help {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.6rem;
            margin-top: 0.5rem;
            line-height: 1;
        }
        .otp-hint {
            font-size: 0.75rem;
            color: #64748b;
            flex: 1;
        }
        body.dark .otp-hint { color: #94a3b8; }
        .resend-btn {
            border: none;
            background: transparent;
            color: #2563eb;
            font-weight: 800;
            font-size: 0.75rem;
            cursor: pointer;
            padding: 0;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
        }
        body.dark .resend-btn { color: #60a5fa; }
        .resend-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .resend-btn:hover:not(:disabled) { text-decoration: underline; }
        .modal-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-top: 0.2rem;
        }
        .btn-secondary {
            width: 100%;
            padding: 1rem;
            background: transparent;
            color: #0f172a;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }
        body.dark .btn-secondary {
            border-color: #334155;
            color: white;
        }
        .btn-secondary:hover { background: rgba(15, 23, 42, 0.04); }
        body.dark .btn-secondary:hover { background: rgba(255, 255, 255, 0.06); }

        .success-message {
            padding: 1rem;
            background: #ecfdf5;
            border: 1px solid #bbf7d0;
            color: #166534;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: none;
        }
        .success-message.show { display: block; }
        
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
            <a href="../index.php" style="display: inline-block;">
                <img src="../assets/logo.png" alt="Syntalytix Logo" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
            </a>
        </div>
        <h1>Welcome Back</h1>
        <p class="subtitle">Sign in to your Syntalytix account</p>
        
        <div class="error-message <?php echo $error ? 'show' : ''; ?>" id="error">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <div class="info-message <?php echo $info ? 'show' : ''; ?>" id="info">
            <?php echo htmlspecialchars($info); ?>
        </div>
        
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
            <div style="margin-bottom: 0.5rem;">
                <a href="#" onclick="openForgotPassword(event)">Forgot password?</a>
            </div>
            <a href="signup.php">Don't have an account? Sign Up</a>
        </div>
    </div>

    <div class="modal-overlay" id="forgot-modal" aria-hidden="true">
        <div class="modal">
            <div class="modal-header">
                <div>
                    <h2>Reset your password</h2>
                    <p>Enter your email to receive a 6-digit OTP.</p>
                </div>
                <button type="button" class="modal-close" onclick="closeForgotPassword()" aria-label="Close">✕</button>
            </div>

            <div class="success-message" id="forgot-success"></div>
            <div class="error-message" id="forgot-error"></div>

            <form id="forgotForm" class="modal-form">
                <div>
                    <label class="field-label" for="forgot-email">Email</label>
                    <input type="email" id="forgot-email" placeholder="name@example.com" required>
                </div>

                <div id="otp-step" style="display:none;" class="otp-row">
                    <div>
                        <label class="field-label" for="forgot-otp">OTP</label>
                        <input type="text" id="forgot-otp" placeholder="6-digit code" inputmode="numeric" autocomplete="one-time-code">
                        <div class="otp-help">
                            <div class="otp-hint" id="resend-hint">Didn’t get the code?</div>
                            <button type="button" class="resend-btn" id="resend-otp" onclick="resendOtp()" disabled>Resend OTP</button>
                        </div>
                    </div>
                    <div>
                        <label class="field-label" for="forgot-new-password">New password</label>
                        <div class="password-wrapper">
                            <input type="password" id="forgot-new-password" placeholder="Minimum 6 characters">
                            <button type="button" class="toggle-password" onclick="toggleForgotPassword()">👁️</button>
                        </div>
                    </div>
                </div>

                <button type="submit" id="forgot-submit">Send OTP</button>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="forgot-back" onclick="forgotBack()" style="display:none;">Back</button>
                    <button type="button" class="btn-secondary" onclick="closeForgotPassword()">Cancel</button>
                </div>
            </form>
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

        function openForgotPassword(e) {
            if (e) e.preventDefault();
            const modal = document.getElementById('forgot-modal');
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');

            document.getElementById('forgot-success').classList.remove('show');
            document.getElementById('forgot-error').classList.remove('show');

            document.getElementById('otp-step').style.display = 'none';
            document.getElementById('forgot-submit').textContent = 'Send OTP';
            document.getElementById('forgot-back').style.display = 'none';
            setResendCooldown(0);

            document.getElementById('forgot-email').value = document.getElementById('email').value || '';
            document.getElementById('forgot-otp').value = '';
            document.getElementById('forgot-new-password').value = '';
        }

        function closeForgotPassword() {
            const modal = document.getElementById('forgot-modal');
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
            if (resendTimerId) {
                clearInterval(resendTimerId);
                resendTimerId = null;
            }
        }

        function forgotBack() {
            document.getElementById('otp-step').style.display = 'none';
            document.getElementById('forgot-submit').textContent = 'Send OTP';
            document.getElementById('forgot-back').style.display = 'none';
            document.getElementById('forgot-error').classList.remove('show');
            document.getElementById('forgot-success').classList.remove('show');
            setResendCooldown(0);
        }

        function toggleForgotPassword() {
            const input = document.getElementById('forgot-new-password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        let resendTimerId = null;
        let resendRemaining = 0;

        function formatSeconds(sec) {
            const s = Math.max(0, parseInt(sec, 10) || 0);
            const m = Math.floor(s / 60);
            const r = s % 60;
            return `${m}:${String(r).padStart(2, '0')}`;
        }

        function setResendCooldown(seconds) {
            const btn = document.getElementById('resend-otp');
            const hint = document.getElementById('resend-hint');

            if (resendTimerId) {
                clearInterval(resendTimerId);
                resendTimerId = null;
            }

            resendRemaining = Math.max(0, parseInt(seconds, 10) || 0);
            if (resendRemaining <= 0) {
                btn.disabled = false;
                hint.textContent = 'Didn’t get the code?';
                return;
            }

            btn.disabled = true;
            hint.textContent = `You can resend in ${formatSeconds(resendRemaining)}`;

            resendTimerId = setInterval(() => {
                resendRemaining -= 1;
                if (resendRemaining <= 0) {
                    clearInterval(resendTimerId);
                    resendTimerId = null;
                    btn.disabled = false;
                    hint.textContent = 'Didn’t get the code?';
                    return;
                }
                hint.textContent = `You can resend in ${formatSeconds(resendRemaining)}`;
            }, 1000);
        }

        async function resendOtp() {
            const email = document.getElementById('forgot-email').value;
            const btn = document.getElementById('resend-otp');
            const errorDiv = document.getElementById('forgot-error');
            const successDiv = document.getElementById('forgot-success');

            errorDiv.classList.remove('show');
            successDiv.classList.remove('show');

            btn.disabled = true;

            try {
                const response = await fetch('../api/auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=request_reset_otp&email=${encodeURIComponent(email)}`
                });
                const raw = await response.text();
                let data;
                try {
                    data = JSON.parse(raw);
                } catch (e) {
                    throw new Error(`Invalid server response (${response.status}): ${raw.slice(0, 200)}`);
                }

                if (data.success) {
                    successDiv.textContent = data.message || 'If the email exists, an OTP has been sent.';
                    successDiv.classList.add('show');
                    setResendCooldown(data.retry_after ?? 120);
                } else {
                    errorDiv.textContent = data.error || 'Unable to resend OTP';
                    errorDiv.classList.add('show');
                    setResendCooldown(10);
                }
            } catch (err) {
                errorDiv.textContent = err && err.message ? err.message : 'Network error. Please try again.';
                errorDiv.classList.add('show');
                setResendCooldown(10);
            }
        }

        document.getElementById('forgot-modal').addEventListener('click', (e) => {
            if (e.target && e.target.id === 'forgot-modal') closeForgotPassword();
        });

        document.getElementById('forgotForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const email = document.getElementById('forgot-email').value;
            const otpStep = document.getElementById('otp-step');
            const otp = document.getElementById('forgot-otp').value;
            const newPassword = document.getElementById('forgot-new-password').value;

            const submitBtn = document.getElementById('forgot-submit');
            const backBtn = document.getElementById('forgot-back');
            const errorDiv = document.getElementById('forgot-error');
            const successDiv = document.getElementById('forgot-success');

            errorDiv.classList.remove('show');
            successDiv.classList.remove('show');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading"></span>';

            try {
                if (otpStep.style.display === 'none') {
                    const response = await fetch('../api/auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=request_reset_otp&email=${encodeURIComponent(email)}`
                    });
                    const raw = await response.text();
                    let data;
                    try {
                        data = JSON.parse(raw);
                    } catch (e) {
                        throw new Error(`Invalid server response (${response.status}): ${raw.slice(0, 200)}`);
                    }

                    if (data.success) {
                        successDiv.textContent = data.message || 'If the email exists, an OTP has been sent.';
                        successDiv.classList.add('show');
                        otpStep.style.display = 'block';
                        submitBtn.textContent = 'Reset Password';
                        backBtn.style.display = 'block';
                        setResendCooldown(data.retry_after ?? 120);
                    } else {
                        errorDiv.textContent = data.error || 'Unable to send OTP';
                        errorDiv.classList.add('show');
                    }
                } else {
                    const response = await fetch('../api/auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=reset_password_otp&email=${encodeURIComponent(email)}&otp=${encodeURIComponent(otp)}&new_password=${encodeURIComponent(newPassword)}`
                    });
                    const raw = await response.text();
                    let data;
                    try {
                        data = JSON.parse(raw);
                    } catch (e) {
                        throw new Error(`Invalid server response (${response.status}): ${raw.slice(0, 200)}`);
                    }

                    if (data.success) {
                        successDiv.textContent = data.message || 'Password updated successfully.';
                        successDiv.classList.add('show');
                        setTimeout(() => {
                            closeForgotPassword();
                        }, 900);
                    } else {
                        errorDiv.textContent = data.error || 'Unable to reset password';
                        errorDiv.classList.add('show');
                    }
                }
            } catch (err) {
                errorDiv.textContent = err && err.message ? err.message : 'Network error. Please try again.';
                errorDiv.classList.add('show');
            } finally {
                submitBtn.disabled = false;
                if (otpStep.style.display === 'none') {
                    submitBtn.textContent = 'Send OTP';
                } else {
                    submitBtn.textContent = 'Reset Password';
                }
            }
        });
        
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
    <?php include __DIR__ . '/../includes/support_popup.php'; ?>
</body>
</html>
