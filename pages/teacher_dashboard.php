<?php
// pages/teacher_dashboard.php
require_once __DIR__ . '/../includes/session.php';
requireRole('Teacher');

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Syntalytix</title>
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
            background: #7c3aed;
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
            background: #7c3aed;
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
            background: rgba(124, 58, 237, 0.1);
            color: #7c3aed;
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
            border-color: #7c3aed;
            box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1);
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
            background: #7c3aed;
            color: white;
        }
        .btn-primary:hover { background: #6d28d9; }
        .btn-secondary {
            background: #f1f5f9;
            color: #64748b;
        }
        body.dark .btn-secondary {
            background: #334155;
            color: #94a3b8;
        }
        .btn-danger {
            background: #ef4444;
            color: white;
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
        }
        body.dark .content-item {
            background: #1e293b;
            border-color: #334155;
        }
        .content-info h4 {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.25rem;
        }
        body.dark .content-info h4 { color: #f1f5f9; }
        .content-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.75rem;
            color: #64748b;
        }
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .badge-video { background: #fee2e2; color: #dc2626; }
        .badge-pdf { background: #dbeafe; color: #1d4ed8; }
        .badge-test { background: #f3e8ff; color: #7c3aed; }
        
        .content-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            font-size: 1rem;
            outline: none;
            background: #f8fafc;
        }
        body.dark .form-group input,
        body.dark .form-group select,
        body.dark .form-group textarea {
            background: #1e293b;
            border-color: #334155;
            color: white;
        }
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 50;
        }
        .modal-overlay.show { display: flex; }
        .modal {
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            background: white;
            border-radius: 2rem;
            padding: 2rem;
        }
        body.dark .modal {
            background: #0f172a;
        }
        .modal h3 {
            font-size: 1.5rem;
            font-weight: 900;
            color: #0f172a;
            margin-bottom: 1.5rem;
        }
        body.dark .modal h3 { color: #f1f5f9; }
        .modal-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }
        .modal-actions button {
            flex: 1;
        }
        
        .question-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        body.dark .question-item {
            background: #1e293b;
            border-color: #334155;
        }
        .question-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .option-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .hidden { display: none !important; }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #64748b;
        }
        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
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
                min-width: 160px;
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
            .modal-actions {
                flex-direction: column;
            }

            .content-actions .btn,
            .modal-actions .btn {
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
                <h1>Teacher</h1>
                <p>Content Manager</p>
            </div>
        </div>
        
        <nav class="nav-menu">
            <button class="nav-item active" onclick="setTab('content')">📚 My Content</button>
            <button class="nav-item" onclick="setTab('tests')">📝 My Tests</button>
        </nav>
        
        <button class="profile-btn" onclick="openProfile()">👤 My Profile</button>
        <button class="logout-btn" onclick="logout()">🚪 Log out</button>
    </aside>
    
    <main class="main-content">
        <div class="header">
            <h2 id="pageTitle">My Published Content</h2>
            <button class="theme-toggle" onclick="toggleTheme()">🌙</button>
        </div>
        
        <!-- Content Tab -->
        <div id="tab-content" class="tab-content">
            <div class="content-card">
                <div class="card-header">
                    <h3>Learning Materials</h3>
                    <button class="btn btn-primary" onclick="showAddContent()">+ Add Content</button>
                </div>
                <div class="content-list" id="content-list">
                    <div class="empty-state">
                        <div class="empty-state-icon">📚</div>
                        <p>Loading...</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tests Tab -->
        <div id="tab-tests" class="tab-content hidden">
            <div class="content-card">
                <div class="card-header">
                    <h3>My Tests</h3>
                    <button class="btn btn-primary" onclick="showAddTest()">+ Create Test</button>
                </div>
                <div class="content-list" id="tests-list">
                    <div class="empty-state">
                        <div class="empty-state-icon">📝</div>
                        <p>Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Add/Edit Content Modal -->
    <div class="modal-overlay" id="content-modal">
        <div class="modal">
            <h3 id="content-modal-title">Add Content</h3>
            <div class="form-group">
                <label>Title</label>
                <input type="text" id="content-title" placeholder="Enter content title">
            </div>
            <div class="form-group">
                <label>Topic</label>
                <select id="content-topic"></select>
            </div>
            <div class="form-group">
                <label>YouTube URL (for videos)</label>
                <input type="url" id="content-youtube" placeholder="https://youtube.com/watch?v=...">
            </div>
            <div class="form-group">
                <label>Google Drive URL (for PDFs)</label>
                <input type="url" id="content-drive" placeholder="https://drive.google.com/...">
            </div>
            <div class="modal-actions">
                <button class="btn btn-primary" onclick="saveContent()">Save Content</button>
                <button class="btn btn-secondary" onclick="closeModal('content-modal')">Cancel</button>
            </div>
        </div>
    </div>
    
    <!-- Add/Edit Test Modal -->
    <div class="modal-overlay" id="test-modal">
        <div class="modal">
            <h3 id="test-modal-title">Create Test</h3>
            <div class="form-group">
                <label>Test Name</label>
                <input type="text" id="test-name" placeholder="Enter test name">
            </div>
            <div class="form-group">
                <label>Topic</label>
                <select id="test-topic"></select>
            </div>
            <div id="questions-container"></div>
            <button class="btn btn-secondary" onclick="addQuestion()" style="width: 100%; margin-bottom: 1rem;">+ Add Question</button>
            <div class="modal-actions">
                <button class="btn btn-primary" onclick="saveTest()">Save Test</button>
                <button class="btn btn-secondary" onclick="closeModal('test-modal')">Cancel</button>
            </div>
        </div>
    </div>
    
    <!-- View Scores Modal -->
    <div class="modal-overlay" id="scores-modal">
        <div class="modal" style="max-width: 800px;">
            <h3 id="scores-modal-title">Test Scores</h3>
            
            <!-- Stats Cards -->
            <div class="stats-grid" id="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            </div>
            
            <!-- Scores Table -->
            <div class="scores-table-container" style="max-height: 400px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 1rem;">
                <table class="scores-table" style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f8fafc; position: sticky; top: 0;">
                        <tr>
                            <th style="padding: 1rem; text-align: left; font-weight: 700;">Student</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 700;">Score</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 700;">Percentage</th>
                            <th style="padding: 1rem; text-align: right; font-weight: 700;">Submitted</th>
                        </tr>
                    </thead>
                    <tbody id="scores-tbody">
                    </tbody>
                </table>
            </div>
            
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeModal('scores-modal')">Close</button>
            </div>
        </div>
    </div>
    
    <!-- View Answers Modal -->
    <div class="modal-overlay" id="answers-modal">
        <div class="modal" style="max-width: 800px; max-height: 90vh; overflow-y: auto;">
            <h3 id="answers-modal-title">Student Answers</h3>
            <div id="answers-container"></div>
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeModal('answers-modal')">Close</button>
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
        let currentTab = 'content';
        let content = [];
        let tests = [];
        let topics = [];
        let editingContentId = null;
        let editingTestId = null;
        let questions = [];
        let currentTestScoresData = null;
        
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
            document.getElementById('pageTitle').textContent = tab === 'content' ? 'My Published Content' : 'My Tests';
            
            if (tab === 'content') loadContent();
            if (tab === 'tests') loadTests();
        }
        
        async function loadTopics() {
            const response = await fetch('../api/teacher.php?action=get_topics');
            const data = await response.json();
            if (data.success) {
                topics = data.topics;
                const options = topics.map(t => `<option value="${t.topic_name}">${t.topic_name}</option>`).join('');
                document.getElementById('content-topic').innerHTML = options;
                document.getElementById('test-topic').innerHTML = options;
            }
        }
        
        async function loadContent() {
            const response = await fetch('../api/teacher.php?action=get_my_content');
            const data = await response.json();
            if (data.success) {
                content = data.content;
                const list = document.getElementById('content-list');
                if (content.length === 0) {
                    list.innerHTML = `
                        <div class="empty-state">
                            <div class="empty-state-icon">📚</div>
                            <p>No content yet. Click "Add Content" to get started!</p>
                        </div>
                    `;
                } else {
                    list.innerHTML = content.map(c => `
                        <div class="content-item">
                            <div class="content-info">
                                <h4>${c.title}</h4>
                                <div class="content-meta">
                                    <span class="badge badge-${c.content_type}">${c.content_type === 'video' ? '🎥 Video' : '📄 PDF'}</span>
                                    <span>Topic: ${c.topic || 'General'}</span>
                                </div>
                            </div>
                            <div class="content-actions">
                                <button class="btn btn-secondary" onclick="editContent(${c.id})">Edit</button>
                                <button class="btn btn-danger" onclick="deleteContent(${c.id})">Delete</button>
                            </div>
                        </div>
                    `).join('');
                }
            }
        }
        
        async function loadTests() {
            const response = await fetch('../api/teacher.php?action=get_my_tests');
            const data = await response.json();
            if (data.success) {
                tests = data.tests;
                const list = document.getElementById('tests-list');
                if (tests.length === 0) {
                    list.innerHTML = `
                        <div class="empty-state">
                            <div class="empty-state-icon">📝</div>
                            <p>No tests yet. Click "Create Test" to get started!</p>
                        </div>
                    `;
                } else {
                    list.innerHTML = tests.map(t => `
                        <div class="content-item">
                            <div class="content-info">
                                <h4>${t.test_name}</h4>
                                <div class="content-meta">
                                    <span class="badge badge-test">📝 Test</span>
                                    <span>${t.question_count || 0} questions</span>
                                    <span>Topic: ${t.topic || 'General'}</span>
                                </div>
                            </div>
                            <div class="content-actions">
                                <button class="btn btn-secondary" onclick="viewTestScores(${t.id})">📊 Scores</button>
                                <button class="btn btn-secondary" onclick="editTest(${t.id})">Edit</button>
                                <button class="btn btn-danger" onclick="deleteTest(${t.id})">Delete</button>
                            </div>
                        </div>
                    `).join('');
                }
            }
        }
        
        function showAddContent() {
            editingContentId = null;
            document.getElementById('content-modal-title').textContent = 'Add Content';
            document.getElementById('content-title').value = '';
            document.getElementById('content-youtube').value = '';
            document.getElementById('content-drive').value = '';
            document.getElementById('content-modal').classList.add('show');
        }
        
        function editContent(id) {
            const item = content.find(c => c.id == id);
            if (!item) return;
            editingContentId = id;
            document.getElementById('content-modal-title').textContent = 'Edit Content';
            document.getElementById('content-title').value = item.title;
            document.getElementById('content-topic').value = item.topic || 'General';
            document.getElementById('content-youtube').value = item.youtube_url || '';
            document.getElementById('content-drive').value = item.drive_url || '';
            document.getElementById('content-modal').classList.add('show');
        }
        
        async function saveContent() {
            const title = document.getElementById('content-title').value;
            const topic = document.getElementById('content-topic').value;
            const youtubeUrl = document.getElementById('content-youtube').value;
            const driveUrl = document.getElementById('content-drive').value;
            
            if (!title || (!youtubeUrl && !driveUrl)) {
                alert('Please provide a title and at least one URL');
                return;
            }
            
            const action = editingContentId ? 'update_content' : 'create_content';
            const body = `action=${action}&title=${encodeURIComponent(title)}&topic=${encodeURIComponent(topic)}&youtubeUrl=${encodeURIComponent(youtubeUrl)}&driveUrl=${encodeURIComponent(driveUrl)}`;
            const idParam = editingContentId ? `&id=${editingContentId}` : '';
            
            await fetch('../api/teacher.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body + idParam
            });
            
            closeModal('content-modal');
            loadContent();
        }
        
        async function deleteContent(id) {
            if (!confirm('Delete this content?')) return;
            await fetch('../api/teacher.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete_content&id=${id}`
            });
            loadContent();
        }
        
        function showAddTest() {
            editingTestId = null;
            questions = [];
            document.getElementById('test-modal-title').textContent = 'Create Test';
            document.getElementById('test-name').value = '';
            renderQuestions();
            document.getElementById('test-modal').classList.add('show');
        }
        
        async function editTest(id) {
            const item = tests.find(t => t.id == id);
            if (!item) return;
            editingTestId = id;
            document.getElementById('test-modal-title').textContent = 'Edit Test';
            document.getElementById('test-name').value = item.test_name;
            document.getElementById('test-topic').value = item.topic || 'General';

            // Load existing questions
            try {
                const response = await fetch(`../api/teacher.php?action=get_test_questions&test_id=${id}`);
                const data = await response.json();
                if (data.success) {
                    questions = data.questions.map(q => ({
                        id: q.id,
                        question_text: q.question_text,
                        question_type: q.question_type === 'single_choice' ? 'single' : 'checkbox',
                        options: q.options || [],
                        correct_answer: q.correct_answer,
                        correct_answers: q.correct_answers || [],
                        marks: q.marks || 1
                    }));
                } else {
                    questions = [];
                }
            } catch (err) {
                console.error('Failed to load questions:', err);
                questions = [];
            }

            renderQuestions();
            document.getElementById('test-modal').classList.add('show');
        }
        
        function addQuestion() {
            questions.push({
                question_text: '',
                question_type: 'single',
                options: ['', ''],
                correct_answer: '',
                correct_answers: [],
                marks: 1
            });
            renderQuestions();
        }
        
        function removeQuestion(idx) {
            questions.splice(idx, 1);
            renderQuestions();
        }
        
        function addOption(qIdx) {
            questions[qIdx].options.push('');
            renderQuestions();
        }
        
        function removeOption(qIdx, oIdx) {
            questions[qIdx].options.splice(oIdx, 1);
            renderQuestions();
        }
        
        function updateQuestion(idx, field, value) {
            questions[idx][field] = value;
        }
        
        function updateOption(qIdx, oIdx, value) {
            questions[qIdx].options[oIdx] = value;
        }
        
        function renderQuestions() {
            const container = document.getElementById('questions-container');
            if (questions.length === 0) {
                container.innerHTML = '<p style="color: #64748b; text-align: center; padding: 2rem;">No questions yet. Click "Add Question" to start.</p>';
                return;
            }
            
            container.innerHTML = questions.map((q, qIdx) => `
                <div class="question-item">
                    <div class="question-header">
                        <span style="font-weight: 700;">Question ${qIdx + 1}</span>
                        <button class="btn btn-danger" onclick="removeQuestion(${qIdx})">Remove</button>
                    </div>
                    <div class="form-group">
                        <input type="text" placeholder="Question text" value="${q.question_text}" onchange="updateQuestion(${qIdx}, 'question_text', this.value)">
                    </div>
                    <div class="form-group">
                        <select onchange="updateQuestion(${qIdx}, 'question_type', this.value)">
                            <option value="single" ${q.question_type === 'single' ? 'selected' : ''}>Single Choice</option>
                            <option value="checkbox" ${q.question_type === 'checkbox' ? 'selected' : ''}>Multiple Choice</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Options</label>
                        ${q.options.map((opt, oIdx) => `
                            <div class="option-row">
                                <input type="text" placeholder="Option ${oIdx + 1}" value="${opt}" onchange="updateOption(${qIdx}, ${oIdx}, this.value)">
                                ${q.options.length > 2 ? `<button class="btn btn-secondary" onclick="removeOption(${qIdx}, ${oIdx})">-</button>` : ''}
                            </div>
                        `).join('')}
                        <button class="btn btn-secondary" onclick="addOption(${qIdx})">+ Add Option</button>
                    </div>
                    <div class="form-group">
                        <label>${q.question_type === 'checkbox' ? 'Correct Answers (comma-separated indices)' : 'Correct Answer (index)'}</label>
                        <input type="text" placeholder="e.g., 0 or 0,1,2" value="${q.question_type === 'checkbox' ? (q.correct_answers || []).join(',') : (q.correct_answer || '')}" onchange="updateCorrectAnswer(${qIdx}, this.value)">
                    </div>
                    <div class="form-group">
                        <label>Marks</label>
                        <input type="number" value="${q.marks}" min="1" onchange="updateQuestion(${qIdx}, 'marks', parseInt(this.value))">
                    </div>
                </div>
            `).join('');
        }
        
        async function saveTest() {
            const testName = document.getElementById('test-name').value;
            const topic = document.getElementById('test-topic').value;
            
            if (!testName) {
                alert('Please provide a test name');
                return;
            }
            
            // Format questions properly
            const formattedQuestions = questions.map(q => {
                if (q.question_type === 'checkbox') {
                    return {
                        ...q,
                        correct_answer: '',
                        correct_answers: (q.correct_answers || []).toString().split(',').map(s => parseInt(s.trim())).filter(n => !isNaN(n))
                    };
                } else {
                    return {
                        ...q,
                        correct_answer: parseInt(q.correct_answer) || 0,
                        correct_answers: []
                    };
                }
            });
            
            const action = editingTestId ? 'update_test' : 'create_test';
            const body = `action=${action}&test_name=${encodeURIComponent(testName)}&topic=${encodeURIComponent(topic)}&questions=${encodeURIComponent(JSON.stringify(formattedQuestions))}`;
            const idParam = editingTestId ? `&id=${editingTestId}` : '';
            
            await fetch('../api/teacher.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body + idParam
            });
            
            closeModal('test-modal');
            loadTests();
        }
        
        async function deleteTest(id) {
            if (!confirm('Delete this test?')) return;
            await fetch('../api/teacher.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete_test&id=${id}`
            });
            loadTests();
        }
        
        function closeModal(id) {
            document.getElementById(id).classList.remove('show');
        }
        
        function logout() {
            fetch('../api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=logout'
            }).then(() => window.location.href = 'login.php');
        }
        
        async function viewTestScores(testId) {
            const test = tests.find(t => t.id == testId);
            if (!test) return;
            
            document.getElementById('scores-modal-title').textContent = `Scores: ${test.test_name}`;
            document.getElementById('scores-tbody').innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 2rem;">Loading...</td></tr>';
            document.getElementById('scores-modal').classList.add('show');
            
            try {
                const response = await fetch(`../api/teacher.php?action=get_test_scores&test_id=${testId}`);
                const data = await response.json();
                
                if (data.success) {
                    currentTestScoresData = data;
                    // Render stats
                    const stats = data.stats;
                    const percentage = stats.total_marks > 0 ? Math.round((stats.avg_score / stats.total_marks) * 100) : 0;
                    
                    document.getElementById('stats-grid').innerHTML = `
                        <div style="background: #7c3aed; color: white; padding: 1rem; border-radius: 1rem; text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 900;">${stats.total_students}</div>
                            <div style="font-size: 0.75rem; opacity: 0.9;">Students</div>
                        </div>
                        <div style="background: #10b981; color: white; padding: 1rem; border-radius: 1rem; text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 900;">${stats.avg_score}</div>
                            <div style="font-size: 0.75rem; opacity: 0.9;">Avg Score</div>
                        </div>
                        <div style="background: #f59e0b; color: white; padding: 1rem; border-radius: 1rem; text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 900;">${stats.max_score}</div>
                            <div style="font-size: 0.75rem; opacity: 0.9;">High Score</div>
                        </div>
                        <div style="background: #3b82f6; color: white; padding: 1rem; border-radius: 1rem; text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 900;">${percentage}%</div>
                            <div style="font-size: 0.75rem; opacity: 0.9;">Avg %</div>
                        </div>
                    `;
                    
                    // Render scores table
                    if (data.scores.length === 0) {
                        document.getElementById('scores-tbody').innerHTML = `
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 2rem; color: #64748b;">
                                    No students have taken this test yet.
                                </td>
                            </tr>
                        `;
                    } else {
                        document.getElementById('scores-tbody').innerHTML = data.scores.map(s => {
                            const pct = s.total_marks > 0 ? Math.round((s.score / s.total_marks) * 100) : 0;
                            const date = new Date(s.submitted_at).toLocaleDateString();
                            return `
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 1rem;">
                                        <div style="font-weight: 700;">${s.name}</div>
                                        <div style="font-size: 0.75rem; color: #64748b;">${s.email}</div>
                                    </td>
                                    <td style="padding: 1rem; text-align: center; font-weight: 700;">${s.score} / ${s.total_marks}</td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <span style="background: ${pct >= 70 ? '#10b981' : pct >= 50 ? '#f59e0b' : '#ef4444'}; color: white; padding: 0.25rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 700;">${pct}%</span>
                                    </td>
                                    <td style="padding: 1rem; text-align: right;">
                                        <div style="color: #64748b; font-size: 0.875rem; margin-bottom: 0.5rem;">${date}</div>
                                        <button class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" onclick="viewStudentAnswers(${s.id}, '${s.name.replace(/'/g, "\\'")}')">View Answers</button>
                                    </td>
                                </tr>
                            `;
                        }).join('');
                    }
                } else {
                    document.getElementById('scores-tbody').innerHTML = `
                        <tr><td colspan="4" style="text-align: center; padding: 2rem; color: #ef4444;">Failed to load scores</td></tr>
                    `;
                }
            } catch (err) {
                document.getElementById('scores-tbody').innerHTML = `
                    <tr><td colspan="4" style="text-align: center; padding: 2rem; color: #ef4444;">Error loading scores</td></tr>
                `;
            }
        }
        
        function viewStudentAnswers(historyId, studentName) {
            if (!currentTestScoresData) return;
            
            const historyObj = currentTestScoresData.scores.find(s => s.id == historyId);
            if (!historyObj) return;
            
            document.getElementById('answers-modal-title').textContent = `${studentName}'s Answers`;
            
            let html = '';
            const testQuestions = currentTestScoresData.questions;
            const answersJson = historyObj.answers;
            
            let userAnswers = {};
            try {
                userAnswers = typeof answersJson === 'string' ? JSON.parse(answersJson) : answersJson;
            } catch(e) {}

            const escapeHtml = (unsafe) => {
                if (unsafe == null) return 'None';
                return unsafe.toString().replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
            };

            testQuestions.forEach((q, idx) => {
                const ua = userAnswers[q.id] || {};
                const isCorrect = ua.is_correct;
                const userChoice = ua.user_answer;
                
                let answerDisplay = '';
                
                if (q.question_type === 'checkbox') {
                    let correctChoices = (q.correct_answers || []).map(i => q.options[i]).join(', ');
                    let userChosen = Array.isArray(userChoice) ? userChoice.map(i => q.options[i]).join(', ') : 'None';
                    answerDisplay = `
                        <div style="margin-top: 0.5rem; font-size: 0.875rem;">
                            <div style="color: #64748b; margin-bottom: 0.25rem;">Selected: <span style="font-weight: 700; color: ${isCorrect ? '#10b981' : '#ef4444'}">${escapeHtml(userChosen)}</span></div>
                            ${!isCorrect ? `<div style="color: #64748b;">Correct: <span style="font-weight: 700; color: #10b981">${escapeHtml(correctChoices)}</span></div>` : ''}
                        </div>
                    `;
                } else {
                    let correctChoiceStr = q.options[q.correct_answer] || 'Unknown';
                    let userChosenStr = userChoice !== null && userChoice !== undefined && userChoice !== '' ? q.options[userChoice] : 'None';
                    answerDisplay = `
                        <div style="margin-top: 0.5rem; font-size: 0.875rem;">
                            <div style="color: #64748b; margin-bottom: 0.25rem;">Selected: <span style="font-weight: 700; color: ${isCorrect ? '#10b981' : '#ef4444'}">${escapeHtml(userChosenStr)}</span></div>
                            ${!isCorrect ? `<div style="color: #64748b;">Correct: <span style="font-weight: 700; color: #10b981">${escapeHtml(correctChoiceStr)}</span></div>` : ''}
                        </div>
                    `;
                }
                
                html += `
                    <div style="border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.5rem; margin-bottom: 1rem; background: ${isCorrect ? 'rgba(16, 185, 129, 0.05)' : 'rgba(239, 68, 68, 0.05)'};">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <h4 style="font-weight: 700; margin-bottom: 0.5rem; flex: 1; padding-right: 1rem;">Q${idx + 1}: ${escapeHtml(q.question_text)}</h4>
                            <span style="font-weight: 900; color: ${isCorrect ? '#10b981' : '#ef4444'}">${isCorrect ? '✓ Correct' : '✗ Incorrect'}</span>
                        </div>
                        <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 1rem;">Marks: ${isCorrect ? q.marks : 0} / ${q.marks}</div>
                        ${answerDisplay}
                    </div>
                `;
            });
            
            document.getElementById('answers-container').innerHTML = html;
            document.getElementById('answers-modal').classList.add('show');
        }
        
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.classList.remove('show');
            });
        });
        
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
        
        loadTopics();
        loadContent();
    </script>
    <?php include __DIR__ . '/../includes/support_popup.php'; ?>
</body>
</html>
