<?php
// index.php
require_once __DIR__ . '/includes/session.php';

if (isLoggedIn()) {
    $role = $_SESSION['role'] ?? 'Student';
    header('Location: pages/' . strtolower($role) . '_dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syntalytix - The Future of Learning</title>
    
    <!-- Meta tags for SEO & Accessibility -->
    <meta name="description" content="Syntalytix is a premier Learning Management System that connects students, teachers, and admins through robust educational tools and analytics.">
    <link rel="icon" type="image/png" href="assets/logo.png">
    <link rel="apple-touch-icon" href="assets/logo.png">
    
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #1e40af; /* Deep tech blue for SYNT */
            --primary-hover: #1e3a8a;
            --secondary: #84cc16; /* Vibrant lime green for ALYTIX */
            --bg-light: #f8fafc;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --card-light: #ffffff;
            --border-light: #e2e8f0;
            
            --bg-dark: #020617;
            --text-light: #f1f5f9;
            --text-muted-dark: #94a3b8;
            --card-dark: #0f172a;
            --border-dark: #1e293b;
            
            --transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-light);
            color: var(--text-dark);
            overflow-x: hidden;
            transition: var(--transition);
            background-image: 
                linear-gradient(to right, rgba(0,0,0,0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0,0,0,0.03) 1px, transparent 1px);
            background-size: 50px 50px;
        }

        body.dark {
            background-color: var(--bg-dark);
            color: var(--text-light);
            background-image: 
                linear-gradient(to right, rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255,255,255,0.03) 1px, transparent 1px);
        }
        
        /* Glassmorphism Navigation */
        nav {
            position: fixed;
            top: 0; width: 100%;
            padding: 1.5rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            background: rgba(255, 255, 255, 0.7);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            transition: var(--transition);
        }
        
        body.dark nav {
            background: rgba(2, 6, 23, 0.7);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
            cursor: pointer;
        }

        .logo img {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .nav-links a.btn {
            font-weight: 600;
            text-decoration: none;
            padding: 0.6rem 1.75rem;
            border-radius: 2rem;
            transition: var(--transition);
        }

        .btn-login {
            color: var(--text-dark);
        }
        
        body.dark .btn-login {
            color: var(--text-light);
        }

        .btn-login:hover {
            color: var(--primary);
        }

        .btn-signup {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
        }

        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
        }

        .theme-toggle {
            background: none;
            border: none;
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.5rem;
            color: inherit;
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 7rem 5% 4rem 5%;
            position: relative;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -20%; left: -10%;
            width: 60%; height: 60%;
            background: radial-gradient(circle, rgba(30, 64, 175, 0.25) 0%, transparent 70%);
            z-index: -1;
            filter: blur(80px);
        }
        
        .hero::after {
            content: '';
            position: absolute;
            bottom: -20%; right: -10%;
            width: 60%; height: 60%;
            background: radial-gradient(circle, rgba(132, 204, 22, 0.2) 0%, transparent 70%);
            z-index: -1;
            filter: blur(80px);
        }

        .hero-content {
            max-width: 800px;
            animation: fadeIn 1s ease-out;
        }

        .hero h1 {
            font-size: 4rem;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: 1.25rem;
            color: var(--text-muted);
            margin-bottom: 3rem;
            line-height: 1.6;
        }

        body.dark .hero p {
            color: var(--text-muted-dark);
        }

        .highlight-banner {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 999px;
            border: 1px solid rgba(30, 64, 175, 0.25);
            background: rgba(30, 64, 175, 0.06);
            color: var(--text-dark);
            font-weight: 600;
            margin: -1.5rem auto 2rem auto;
            max-width: 760px;
        }

        body.dark .highlight-banner {
            border-color: rgba(132, 204, 22, 0.22);
            background: rgba(132, 204, 22, 0.08);
            color: var(--text-light);
        }

        .highlight-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            box-shadow: 0 10px 25px rgba(30, 64, 175, 0.2);
            flex: 0 0 auto;
        }

        .highlight-text {
            text-align: left;
            font-size: 0.95rem;
            line-height: 1.4;
        }

        .highlight-text strong {
            font-weight: 800;
        }

        .hero-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
        }

        .hero .btn-primary {
            padding: 1rem 2.5rem;
            font-size: 1.125rem;
            font-weight: 700;
            border-radius: 3rem;
            background: var(--text-dark);
            color: var(--card-light);
            text-decoration: none;
            transition: var(--transition);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        body.dark .hero .btn-primary {
            background: var(--text-light);
            color: var(--bg-dark);
        }

        .hero .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .hero .btn-secondary {
            padding: 1rem 2.5rem;
            font-size: 1.125rem;
            font-weight: 700;
            border-radius: 3rem;
            background: transparent;
            color: var(--text-dark);
            border: 2px solid var(--border-light);
            text-decoration: none;
            transition: var(--transition);
        }

        body.dark .hero .btn-secondary {
            color: var(--text-light);
            border-color: var(--border-dark);
        }

        .hero .btn-secondary:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(79, 70, 229, 0.05);
        }

        /* Features Section */
        .features {
            padding: 5rem 5%;
            background: var(--card-light);
            position: relative;
            z-index: 10;
        }

        body.dark .features {
            background: var(--card-dark);
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 4rem;
        }

        .section-title span {
            color: var(--primary);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: var(--bg-light);
            padding: 2.5rem 2rem;
            border-radius: 1.5rem;
            border: 1px solid var(--border-light);
            transition: var(--transition);
            text-align: left;
            position: relative;
            overflow: hidden;
        }

        body.dark .feature-card {
            background: rgba(30, 41, 59, 0.5);
            border-color: var(--border-dark);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            border-color: var(--primary);
        }
        
        body.dark .feature-card:hover {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            display: inline-block;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            padding: 0.5rem 0;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: var(--text-muted);
            line-height: 1.6;
            font-size: 1.05rem;
        }

        body.dark .feature-card p {
            color: var(--text-muted-dark);
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 2rem 5%;
            font-size: 0.875rem;
            color: var(--text-muted);
            border-top: 1px solid var(--border-light);
        }
        
        body.dark footer {
            color: var(--text-muted-dark);
            border-top-color: var(--border-dark);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 { font-size: 2.75rem; }
            .hero p { font-size: 1.125rem; }
            .hero-buttons { flex-direction: column; width: 100%; max-width: 300px; margin: 0 auto; }
            .nav-links { gap: 1rem; }
            .btn-login { display: none; }
            .highlight-banner { border-radius: 1.25rem; width: 100%; }
        }

        /* Interactive Cursor Blob */
        .cursor-blob {
            position: fixed;
            pointer-events: none;
            top: 0; left: 0;
            width: 400px; height: 400px;
            background: radial-gradient(circle, var(--primary) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            z-index: -1;
            opacity: 0;
            transition: opacity 0.5s ease;
        }
        body:hover .cursor-blob {
            opacity: 0.15;
        }
    </style>
</head>
<body id="home">

    <nav aria-label="Main Navigation">
        <a href="#home" class="logo">
            <img src="assets/logo.png" alt="Syntalytix Icon">
            Syntalytix
        </a>
        <div class="nav-links">
            <button class="theme-toggle" onclick="toggleTheme()" id="themeIcon" aria-label="Toggle Dark Mode">🌙</button>
            <a href="pages/login.php" class="btn btn-login">Sign In</a>
            <a href="pages/signup.php" class="btn btn-signup">Get Started</a>
        </div>
    </nav>

    <header class="hero">
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:60%; height:60%; background:radial-gradient(circle, rgba(236, 72, 153, 0.1) 0%, transparent 70%); filter:blur(100px); z-index:-1; border-radius:50%; pointer-events:none;"></div>
        <div class="hero-content">
            <h1 id="hero-heading">Empowering Education with Intelligent Learning.</h1>
            <p>A unified, data-driven platform designed to connect students, empower teachers, and provide robust management for administrators. Master your subjects anywhere, anytime.</p>
            <div class="highlight-banner" role="note" aria-label="Free education highlight">
                <span class="highlight-badge">Free</span>
                <span class="highlight-text"><strong>Free education is available</strong> on Syntalytix — access study materials and practice tests anytime.</span>
            </div>
            <div class="hero-buttons">
                <a href="pages/signup.php" class="btn btn-primary">Start Learning Now</a>
                <a href="#features" class="btn btn-secondary">Explore Features</a>
            </div>
        </div>
    </header>

    <section id="features" class="features" aria-labelledby="features-heading">
        <h2 id="features-heading" class="section-title">Why Choose <span>Syntalytix?</span></h2>
        
        <div class="features-grid">
            
            <div class="feature-card">
                <div class="feature-icon" aria-hidden="true">📚</div>
                <h3>Structured Content</h3>
                <p>Access high-quality study materials, pick up where you left off on educational videos, and download PDFs easily across specialized topics.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon" aria-hidden="true">📝</div>
                <h3>Dynamic Testing</h3>
                <p>Teachers can create single-choice or multi-choice quizzes. Students can instantly test their knowledge and evaluate their performance.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon" aria-hidden="true">📊</div>
                <h3>Deep Analytics</h3>
                <p>Unlock profound insights. Review exact answers from past tests, monitor class averages, and visualize overall progression via custom dashboards.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon" aria-hidden="true">👥</div>
                <h3>Role-Based Hubs</h3>
                <p>Dedicated tools designed specifically for Students, isolated creation suites for Teachers, and full-scale moderation scopes for Administrators.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon" aria-hidden="true">📈</div>
                <h3>Progress Tracking</h3>
                <p>Visualize your educational journey. Dedicated progress tracking monitors your video completion rates and overall test engagement throughout the course.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon" aria-hidden="true">🔒</div>
                <h3>Secure Platform</h3>
                <p>Enterprise-grade security standards with encrypted credentials, robust session validation, and strict role isolation protecting your private data.</p>
            </div>

        </div>
    </section>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Syntalytix. All rights reserved.</p>
    </footer>

    <script>
        // Check local storage for theme
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark');
            document.getElementById('themeIcon').textContent = '☀️';
        }

        // Toggle Theme
        function toggleTheme() {
            const isDark = document.body.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            document.getElementById('themeIcon').textContent = isDark ? '☀️' : '🌙';
        }
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Interactive cursor trailing glow
        const cursorBlob = document.createElement('div');
        cursorBlob.classList.add('cursor-blob');
        document.body.appendChild(cursorBlob);

        document.addEventListener('mousemove', (e) => {
            cursorBlob.animate({
                left: `${e.clientX}px`,
                top: `${e.clientY}px`
            }, { duration: 3000, fill: "forwards" });
        });
    </script>
    <?php include __DIR__ . '/includes/support_popup.php'; ?>
</body>
</html>
