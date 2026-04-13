<?php
// pages/student_dashboard.php
require_once __DIR__ . '/../includes/session.php';
requireRole('Student');

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Syntalytix</title>
    <link rel="icon" type="image/png" href="../assets/logo.png">
    <link rel="apple-touch-icon" href="../assets/logo.png">
    <meta name="theme-color" content="#0f172a">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            background: #f1f5f9;
            display: flex;
            transition: all 0.5s;
        }
        body.dark {
            background: #020617;
            color: #f1f5f9;
        }
        
        .sidebar {
            width: 288px;
            background: #0f172a;
            color: white;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
        }
        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2.5rem;
            padding-left: 0.5rem;
        }
        .sidebar-logo {
            width: 40px;
            height: 40px;
            background: #2563eb;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 1.25rem;
        }
        .sidebar-title h1 {
            font-size: 1.25rem;
            font-weight: 900;
        }
        .sidebar-title p {
            font-size: 0.625rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 700;
        }
        
        .nav-menu {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .nav-item {
            padding: 1rem;
            border-radius: 1rem;
            text-decoration: none;
            color: #94a3b8;
            font-weight: 700;
            font-size: 0.875rem;
            transition: all 0.3s;
            border: none;
            background: none;
            cursor: pointer;
            text-align: left;
        }
        .nav-item:hover {
            background: #1e293b;
            color: white;
        }
        .nav-item.active {
            background: #2563eb;
            color: white;
        }
        
        .logout-btn {
            margin-top: auto;
            padding: 1rem;
            border-radius: 1rem;
            color: #64748b;
            font-weight: 700;
            font-size: 0.875rem;
            background: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            text-align: left;
        }
        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        
        .profile-btn {
            margin-top: 0.5rem;
            padding: 1rem;
            border-radius: 1rem;
            color: #64748b;
            font-weight: 700;
            font-size: 0.875rem;
            background: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            text-align: left;
        }
        .profile-btn:hover {
            background: rgba(37, 99, 235, 0.1);
            color: #2563eb;
        }
        
        /* Profile Modal */
        .profile-modal {
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            background: white;
            border-radius: 2rem;
            padding: 2rem;
        }
        body.dark .profile-modal {
            background: #0f172a;
        }
        .profile-modal h2 {
            font-size: 1.5rem;
            font-weight: 900;
            color: #0f172a;
            margin-bottom: 1.5rem;
        }
        body.dark .profile-modal h2 { color: #f1f5f9; }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 0.5rem;
        }
        body.dark .form-group label { color: #94a3b8; }
        .form-group input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s;
            background: #f8fafc;
        }
        body.dark .form-group input {
            background: #1e293b;
            border-color: #334155;
            color: white;
        }
        .form-group input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }
        .profile-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        .success-message {
            padding: 1rem;
            background: #dcfce7;
            border: 1px solid #86efac;
            color: #15803d;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: none;
        }
        .success-message.show { display: block; }
        .error-message-profile {
            padding: 1rem;
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: none;
        }
        .error-message-profile.show { display: block; }
        
        .main-content {
            margin-left: 288px;
            flex: 1;
            padding: 2rem 3rem;
            overflow-y: auto;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
        }
        .header h2 {
            font-size: 1.875rem;
            font-weight: 900;
            color: #0f172a;
        }
        body.dark .header h2 { color: #f1f5f9; }
        
        .theme-toggle {
            padding: 0.625rem;
            border-radius: 0.75rem;
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
        
        /* Welcome Section */
        .welcome-card {
            background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
            border-radius: 2rem;
            padding: 2rem;
            color: white;
            margin-bottom: 2rem;
        }
        .welcome-card h3 {
            font-size: 1.5rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
        }
        .welcome-card p {
            opacity: 0.9;
        }
        
        /* Content Cards */
        .content-card {
            background: white;
            border-radius: 2rem;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            margin-bottom: 2rem;
        }
        body.dark .content-card {
            background: #0f172a;
            border-color: #1e293b;
        }
        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        body.dark .card-header { border-color: #1e293b; }
        .card-header h3 {
            font-size: 1.25rem;
            font-weight: 900;
            color: #0f172a;
        }
        body.dark .card-header h3 { color: #f1f5f9; }
        
        .topic-filter {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }
        body.dark .topic-filter { border-color: #1e293b; }
        .topic-btn {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            border: 1px solid #e2e8f0;
            background: white;
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.3s;
        }
        body.dark .topic-btn {
            background: #1e293b;
            border-color: #334155;
            color: #94a3b8;
        }
        .topic-btn:hover, .topic-btn.active {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
        }
        
        .content-list {
            padding: 1.5rem;
        }
        .content-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        body.dark .content-item {
            background: #1e293b;
            border-color: #334155;
        }
        .content-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .content-icon {
            width: 48px;
            height: 48px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1rem;
        }
        .content-icon.video { background: #fee2e2; }
        .content-icon.pdf { background: #dbeafe; }
        .content-info {
            flex: 1;
        }
        .content-info h4 {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.25rem;
        }
        body.dark .content-info h4 { color: #f1f5f9; }
        .content-meta {
            font-size: 0.75rem;
            color: #64748b;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #2563eb;
            color: white;
        }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-secondary {
            background: #f1f5f9;
            color: #64748b;
        }
        body.dark .btn-secondary {
            background: #334155;
            color: #94a3b8;
        }
        
        /* Video Player Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.9);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 50;
        }
        .modal-overlay.show { display: flex; }
        .video-modal {
            width: 90%;
            max-width: 900px;
            background: #0f172a;
            border-radius: 1rem;
            overflow: hidden;
        }
        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
        }
        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        .video-info {
            padding: 1.5rem;
            color: white;
        }
        .video-info h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .video-actions {
            display: flex;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-top: 1px solid #334155;
        }
        
        /* Test Taking Modal */
        .test-modal {
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            background: white;
            border-radius: 2rem;
            padding: 2rem;
        }
        body.dark .test-modal {
            background: #0f172a;
        }
        .test-modal h2 {
            font-size: 1.5rem;
            font-weight: 900;
            color: #0f172a;
            margin-bottom: 1.5rem;
        }
        body.dark .test-modal h2 { color: #f1f5f9; }
        .question-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        body.dark .question-box {
            background: #1e293b;
            border-color: #334155;
        }
        .question-box h4 {
            font-weight: 700;
            margin-bottom: 1rem;
            color: #0f172a;
        }
        body.dark .question-box h4 { color: #f1f5f9; }
        .option-label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .option-label:hover {
            background: #e2e8f0;
        }
        body.dark .option-label:hover {
            background: #334155;
        }
        
        /* Results */
        .result-card {
            background: white;
            border-radius: 2rem;
            padding: 2rem;
            margin-bottom: 1rem;
        }
        body.dark .result-card {
            background: #0f172a;
        }
        .result-score {
            font-size: 3rem;
            font-weight: 900;
            color: #2563eb;
        }
        .result-item {
            padding: 1rem;
            border-radius: 1rem;
            margin-bottom: 0.5rem;
        }
        .result-item.correct {
            background: #dcfce7;
            color: #15803d;
        }
        .result-item.incorrect {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .hidden { display: none !important; }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #64748b;
        }

        @media (max-width: 768px) {
            body {
                display: block;
            }

            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                padding: 1rem;
            }

            .sidebar-header {
                margin-bottom: 1rem;
                padding-left: 0;
            }

            .nav-menu {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .nav-item {
                flex: 1;
                min-width: 140px;
                padding: 0.75rem;
            }

            .profile-btn,
            .logout-btn {
                width: 100%;
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .content-item {
                flex-direction: column;
                align-items: stretch;
                gap: 0.75rem;
            }

            .content-actions,
            .profile-actions {
                flex-direction: column;
            }

            .content-actions .btn,
            .profile-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="../index.php" class="sidebar-logo" style="padding: 0; overflow: hidden; display: block;">
                <img src="../assets/logo.png" alt="Syntalytix" style="width: 100%; height: 100%; object-fit: cover;">
            </a>
            <div class="sidebar-title">
                <h1>Student</h1>
                <p>Learning Portal</p>
            </div>
        </div>
        
        <nav class="nav-menu">
            <button class="nav-item active" onclick="setTab('study')">📚 Study Materials</button>
            <button class="nav-item" onclick="setTab('tests')">📝 Take Tests</button>
            <button class="nav-item" onclick="setTab('progress')">📊 My Progress</button>
        </nav>
        
        <button class="profile-btn" onclick="openProfile()">👤 My Profile</button>
        <button class="logout-btn" onclick="logout()">🚪 Log out</button>
    </aside>
    
    <main class="main-content">
        <div class="header">
            <h2 id="pageTitle">Study Materials</h2>
            <button class="theme-toggle" onclick="toggleTheme()">🌙</button>
        </div>
        
        <!-- Study Tab -->
        <div id="tab-study" class="tab-content">
            <div class="welcome-card">
                <h3>Welcome back, <?php echo htmlspecialchars($user['name']); ?>! 👋</h3>
                <p>Continue your learning journey. Select a topic below to explore study materials.</p>
            </div>
            
            <div class="content-card">
                <div class="topic-filter" id="topic-filter">
                    <button class="topic-btn active" onclick="filterTopic('all')">All Topics</button>
                </div>
                <div class="content-list" id="materials-list">
                    <div class="empty-state">
                        <p>Loading materials...</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tests Tab -->
        <div id="tab-tests" class="tab-content hidden">
            <div class="content-card">
                <div class="card-header">
                    <h3>Available Tests</h3>
                </div>
                <div class="content-list" id="tests-list">
                    <div class="empty-state">
                        <p>Loading tests...</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Progress Tab -->
        <div id="tab-progress" class="tab-content hidden">
            <div class="content-card">
                <div class="card-header">
                    <h3>My Test History</h3>
                </div>
                <div id="test-history"></div>
            </div>
        </div>
    </main>
    
    <!-- Video Player Modal -->
    <div class="modal-overlay" id="video-modal">
        <div class="video-modal">
            <div class="video-container">
                <iframe id="video-frame" allowfullscreen></iframe>
            </div>
            <div class="video-info">
                <h3 id="video-title"></h3>
                <p id="video-topic"></p>
            </div>
            <div class="video-actions">
                <button class="btn btn-secondary" onclick="closeVideo()">Close</button>
            </div>
        </div>
    </div>
    
    <!-- Test Taking Modal -->
    <div class="modal-overlay" id="test-modal">
        <div class="test-modal">
            <h2 id="test-title"></h2>
            <div id="test-questions"></div>
            <div class="modal-actions" style="margin-top: 2rem;">
                <button class="btn btn-primary" onclick="submitTest()">Submit Test</button>
                <button class="btn btn-secondary" onclick="closeTest()">Cancel</button>
            </div>
        </div>
    </div>
    
    <!-- Test Result Modal -->
    <div class="modal-overlay" id="result-modal">
        <div class="test-modal">
            <h2>Test Results</h2>
            <div class="result-card" style="text-align: center;">
                <div class="result-score" id="result-score">0/0</div>
                <p style="color: #64748b; margin-top: 0.5rem;">Your Score</p>
            </div>
            <div id="result-details"></div>
            <div class="modal-actions">
                <button class="btn btn-primary" onclick="closeResult()">Close</button>
            </div>
        </div>
    </div>
    
    <!-- Profile Modal -->
    <div class="modal-overlay" id="profile-modal">
        <div class="profile-modal">
            <h2>My Profile</h2>
            <div class="success-message" id="profile-success"></div>
            <div class="error-message-profile" id="profile-error"></div>
            <form id="profile-form">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" id="profile-name" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="profile-email" required>
                </div>
                <div class="form-group">
                    <label>Current Password (required to change password)</label>
                    <input type="password" id="profile-current-password" placeholder="Enter current password">
                </div>
                <div class="form-group">
                    <label>New Password (leave blank to keep current)</label>
                    <input type="password" id="profile-new-password" placeholder="Enter new password (min 6 characters)">
                </div>
                <div class="profile-actions">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-secondary" onclick="closeProfile()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        let currentTab = 'study';
        let currentTopic = 'all';
        let materials = [];
        let tests = [];
        let currentTest = null;
        let testAnswers = {};
        
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark');
            document.querySelector('.theme-toggle').textContent = '☀️';
        }
        
        function toggleTheme() {
            const isDark = document.body.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            document.querySelector('.theme-toggle').textContent = isDark ? '☀️' : '🌙';
        }
        
        function setTab(tab) {
            currentTab = tab;
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
            event.target.classList.add('active');
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.getElementById('tab-' + tab).classList.remove('hidden');
            
            const titles = {
                'study': 'Study Materials',
                'tests': 'Take Tests',
                'progress': 'My Progress'
            };
            document.getElementById('pageTitle').textContent = titles[tab];
            
            if (tab === 'study') loadMaterials();
            if (tab === 'tests') loadTests();
            if (tab === 'progress') loadTestHistory();
        }
        
        async function loadMaterials(topic = 'all') {
            const url = topic === 'all' 
                ? '../api/student.php?action=get_materials'
                : `../api/student.php?action=get_materials&topic=${encodeURIComponent(topic)}`;
            
            const response = await fetch(url);
            const data = await response.json();
            if (data.success) {
                materials = data.materials;
                renderMaterials();
            }
        }
        
        function renderMaterials() {
            const list = document.getElementById('materials-list');
            if (materials.length === 0) {
                list.innerHTML = '<div class="empty-state"><p>No materials available yet.</p></div>';
                return;
            }
            
            list.innerHTML = materials.map(m => `
                <div class="content-item" onclick="openMaterial(${m.id})">
                    <div class="content-icon ${m.content_type}">
                        ${m.content_type === 'video' ? '🎥' : '📄'}
                    </div>
                    <div class="content-info">
                        <h4>${m.title}</h4>
                        <div class="content-meta">Topic: ${m.topic || 'General'}</div>
                    </div>
                    <button class="btn btn-primary">${m.content_type === 'video' ? 'Watch' : 'View'}</button>
                </div>
            `).join('');
        }
        
        function filterTopic(topic) {
            currentTopic = topic;
            document.querySelectorAll('.topic-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            loadMaterials(topic);
        }
        
        function openMaterial(id) {
            const material = materials.find(m => m.id == id);
            if (!material) return;
            
            if (material.content_type === 'video' && material.youtube_url) {
                document.getElementById('video-title').textContent = material.title;
                document.getElementById('video-topic').textContent = material.topic || 'General';
                
                // Extract YouTube video ID
                let videoId = '';
                const match = material.youtube_url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
                if (match) videoId = match[1];
                
                document.getElementById('video-frame').src = `https://www.youtube.com/embed/${videoId}`;
                document.getElementById('video-modal').classList.add('show');
            } else if (material.drive_url) {
                window.open(material.drive_url, '_blank');
            }
        }
        
        function closeVideo() {
            document.getElementById('video-frame').src = '';
            document.getElementById('video-modal').classList.remove('show');
        }
        
        async function loadTests() {
            const response = await fetch('../api/student.php?action=get_tests');
            const data = await response.json();
            if (data.success) {
                tests = data.tests;
                const list = document.getElementById('tests-list');
                if (tests.length === 0) {
                    list.innerHTML = '<div class="empty-state"><p>No tests available yet.</p></div>';
                } else {
                    list.innerHTML = tests.map(t => `
                        <div class="content-item" onclick="startTest(${t.id})">
                            <div class="content-icon" style="background: #f3e8ff;">📝</div>
                            <div class="content-info">
                                <h4>${t.test_name}</h4>
                                <div class="content-meta">${t.question_count || 0} questions • Topic: ${t.topic || 'General'}</div>
                            </div>
                            <button class="btn btn-primary">Start Test</button>
                        </div>
                    `).join('');
                }
            }
        }
        
        async function startTest(testId) {
            const response = await fetch(`../api/student.php?action=get_test&id=${testId}`);
            const data = await response.json();
            if (!data.success) return;
            
            currentTest = data.test;
            testAnswers = {};
            
            document.getElementById('test-title').textContent = currentTest.test_name;
            
            // Helper function to escape HTML
            const escapeHtml = (text) => {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            };
            
            const container = document.getElementById('test-questions');
            container.innerHTML = currentTest.questions.map((q, idx) => `
                <div class="question-box">
                    <h4>Question ${idx + 1}: ${escapeHtml(q.question_text)}</h4>
                    ${q.options.map((opt, oIdx) => `
                        <label class="option-label">
                            <input type="${q.question_type === 'checkbox' ? 'checkbox' : 'radio'}" 
                                   name="q${q.id}" 
                                   value="${oIdx}"
                                   onchange="updateAnswer(${q.id}, ${oIdx}, '${q.question_type}')">
                            <span>${escapeHtml(opt)}</span>
                        </label>
                    `).join('')}
                </div>
            `).join('');
            
            document.getElementById('test-modal').classList.add('show');
        }
        
        function updateAnswer(questionId, optionIdx, type) {
            if (type === 'checkbox') {
                if (!testAnswers[questionId]) testAnswers[questionId] = [];
                const idx = testAnswers[questionId].indexOf(optionIdx);
                if (idx > -1) {
                    testAnswers[questionId].splice(idx, 1);
                } else {
                    testAnswers[questionId].push(optionIdx);
                }
            } else {
                testAnswers[questionId] = optionIdx;
            }
        }
        
        async function submitTest() {
            const response = await fetch('../api/student.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=submit_test&test_id=${currentTest.id}&answers=${encodeURIComponent(JSON.stringify(testAnswers))}`
            });
            
            const data = await response.json();
            if (data.success) {
                closeTest();
                showResult(data);
            }
        }
        
        function showResult(data) {
            document.getElementById('result-score').textContent = `${data.score}/${data.total_marks}`;
            
            // Build detailed answer review
            const escapeHtml = (text) => {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            };
            
            let detailsHtml = '';
            if (data.questions && data.answers) {
                detailsHtml = data.questions.map((q, idx) => {
                    const answerInfo = data.answers[q.id];
                    const isCorrect = answerInfo.is_correct;
                    const userAnswer = answerInfo.user_answer;
                    const correctAnswer = q.correct_answer;
                    const options = q.options;
                    
                    return `
                        <div class="result-item ${isCorrect ? 'correct' : 'incorrect'}">
                            <div style="font-weight: 700; margin-bottom: 0.5rem;">
                                ${isCorrect ? '✅' : '❌'} Question ${idx + 1}: ${escapeHtml(q.question_text)}
                            </div>
                            <div style="font-size: 0.875rem; margin-left: 1.5rem;">
                                <div style="color: ${isCorrect ? '#15803d' : '#dc2626'};">
                                    Your answer: ${userAnswer !== null && userAnswer !== undefined ? escapeHtml(options[userAnswer] || 'Not answered') : 'Not answered'}
                                </div>
                                ${!isCorrect ? `<div style="color: #15803d; margin-top: 0.25rem;">Correct answer: ${escapeHtml(options[correctAnswer])}</div>` : ''}
                            </div>
                        </div>
                    `;
                }).join('');
            }
            
            document.getElementById('result-details').innerHTML = detailsHtml;
            document.getElementById('result-modal').classList.add('show');
        }
        
        function closeTest() {
            document.getElementById('test-modal').classList.remove('show');
            currentTest = null;
        }
        
        function closeResult() {
            document.getElementById('result-modal').classList.remove('show');
        }
        
        async function loadTestHistory() {
            const response = await fetch('../api/student.php?action=get_test_history');
            const data = await response.json();
            if (data.success) {
                const container = document.getElementById('test-history');
                if (data.history.length === 0) {
                    container.innerHTML = '<div class="empty-state"><p>No tests taken yet.</p></div>';
                } else {
                    container.innerHTML = data.history.map(h => `
                        <div class="content-item">
                            <div class="content-icon" style="background: ${h.score >= h.total_marks * 0.6 ? '#dcfce7' : '#fee2e2'};">
                                ${h.score >= h.total_marks * 0.6 ? '✅' : '❌'}
                            </div>
                            <div class="content-info">
                                <h4>${h.test_name}</h4>
                                <div class="content-meta">${new Date(h.submitted_at).toLocaleDateString()}</div>
                            </div>
                            <div style="font-weight: 700; color: ${h.score >= h.total_marks * 0.6 ? '#15803d' : '#dc2626'};">
                                ${h.score}/${h.total_marks}
                            </div>
                        </div>
                    `).join('');
                }
            }
        }
        
        // Profile functions
        async function openProfile() {
            const response = await fetch('../api/auth.php?action=get_profile');
            const data = await response.json();
            if (data.success) {
                document.getElementById('profile-name').value = data.user.name;
                document.getElementById('profile-email').value = data.user.email;
                document.getElementById('profile-current-password').value = '';
                document.getElementById('profile-new-password').value = '';
                document.getElementById('profile-success').classList.remove('show');
                document.getElementById('profile-error').classList.remove('show');
                document.getElementById('profile-modal').classList.add('show');
            }
        }
        
        function closeProfile() {
            document.getElementById('profile-modal').classList.remove('show');
        }
        
        document.getElementById('profile-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = document.getElementById('profile-name').value;
            const email = document.getElementById('profile-email').value;
            const currentPassword = document.getElementById('profile-current-password').value;
            const newPassword = document.getElementById('profile-new-password').value;
            
            const successDiv = document.getElementById('profile-success');
            const errorDiv = document.getElementById('profile-error');
            successDiv.classList.remove('show');
            errorDiv.classList.remove('show');
            
            const body = `action=update_profile&name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}`;
            const bodyWithPassword = currentPassword ? `${body}&current_password=${encodeURIComponent(currentPassword)}&new_password=${encodeURIComponent(newPassword)}` : body;
            
            const response = await fetch('../api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: bodyWithPassword
            });
            
            const data = await response.json();
            if (data.success) {
                successDiv.textContent = data.message;
                successDiv.classList.add('show');
                document.getElementById('profile-current-password').value = '';
                document.getElementById('profile-new-password').value = '';
            } else {
                errorDiv.textContent = data.error;
                errorDiv.classList.add('show');
            }
        });
        
        // Load topics for filter
        async function loadTopics() {
            const response = await fetch('../api/student.php?action=get_topics');
            const data = await response.json();
            if (data.success) {
                const filter = document.getElementById('topic-filter');
                data.topics.forEach(t => {
                    const btn = document.createElement('button');
                    btn.className = 'topic-btn';
                    btn.textContent = t.topic_name;
                    btn.onclick = () => filterTopic(t.topic_name);
                    filter.appendChild(btn);
                });
            }
        }
        
        function logout() {
            fetch('../api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=logout'
            }).then(() => window.location.href = 'login.php');
        }
        
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal && modal.id !== 'video-modal') modal.classList.remove('show');
            });
        });
        
        loadTopics();
        loadMaterials();
    </script>
    <?php include __DIR__ . '/../includes/support_popup.php'; ?>
</body>
</html>
