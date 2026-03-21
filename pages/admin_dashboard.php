<?php
// pages/admin_dashboard.php
require_once __DIR__ . '/../includes/session.php';
requireRole('Admin');

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Syntalytix</title>
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
        
        /* Sidebar */
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
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .nav-item:hover {
            background: #1e293b;
            color: white;
        }
        .nav-item.active {
            background: #2563eb;
            color: white;
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
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
        
        /* Main Content */
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
            text-transform: capitalize;
        }
        body.dark .header h2 { color: #f1f5f9; }
        .header p {
            color: #64748b;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
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
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }
        .stat-card {
            padding: 1.5rem;
            border-radius: 2rem;
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.3s;
        }
        body.dark .stat-card {
            background: #0f172a;
            border-color: #1e293b;
        }
        .stat-card:hover {
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        .stat-label {
            font-size: 0.625rem;
            font-weight: 900;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: 900;
            color: #0f172a;
            margin-top: 0.25rem;
        }
        body.dark .stat-value { color: #f1f5f9; }
        
        /* Content Cards */
        .content-card {
            background: white;
            border-radius: 2.5rem;
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
        }
        body.dark .card-header { border-color: #1e293b; }
        .card-header h3 {
            font-size: 1.25rem;
            font-weight: 900;
            color: #0f172a;
        }
        body.dark .card-header h3 { color: #f1f5f9; }
        
        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            padding: 1.5rem;
            text-align: left;
            font-size: 0.625rem;
            font-weight: 900;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            background: #f8fafc;
        }
        body.dark th { background: #1e293b; }
        td {
            padding: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }
        body.dark td { border-color: #1e293b; }
        tr:hover { background: #f8fafc; }
        body.dark tr:hover { background: #1e293b; }
        
        .badge {
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.625rem;
            font-weight: 900;
            text-transform: uppercase;
        }
        .badge-student { background: #dbeafe; color: #1d4ed8; }
        .badge-teacher { background: #e0e7ff; color: #4338ca; }
        .badge-admin { background: #f1f5f9; color: #475569; }
        body.dark .badge-admin { background: #334155; color: #94a3b8; }
        .badge-active { background: #dcfce7; color: #15803d; }
        .badge-disabled { background: #fef3c7; color: #b45309; }
        
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.75rem;
            font-size: 0.75rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: -0.025em;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #2563eb;
            color: white;
        }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        .btn-danger:hover { background: #dc2626; }
        .btn-secondary {
            background: #f1f5f9;
            color: #64748b;
        }
        body.dark .btn-secondary {
            background: #334155;
            color: #94a3b8;
        }
        
        /* Settings Form */
        .settings-form {
            max-width: 600px;
            padding: 2.5rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            font-size: 0.625rem;
            font-weight: 900;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 0.5rem;
        }
        .form-group input[type="text"] {
            width: 100%;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            font-size: 1rem;
            outline: none;
            background: #f8fafc;
        }
        body.dark .form-group input[type="text"] {
            background: #1e293b;
            border-color: #334155;
            color: white;
        }
        
        .toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 1rem;
        }
        body.dark .toggle-row { background: #1e293b; }
        .toggle-row span {
            font-weight: 700;
            font-size: 0.875rem;
            color: #334155;
        }
        body.dark .toggle-row span { color: #cbd5e1; }
        
        .toggle-switch {
            width: 48px;
            height: 24px;
            background: #cbd5e1;
            border-radius: 12px;
            position: relative;
            cursor: pointer;
            transition: all 0.3s;
        }
        .toggle-switch.active {
            background: #2563eb;
        }
        .toggle-switch::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            background: white;
            border-radius: 50%;
            top: 4px;
            left: 4px;
            transition: all 0.3s;
        }
        .toggle-switch.active::after {
            left: 28px;
        }
        
        /* Content List */
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
        .content-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        /* Modal */
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
            max-width: 500px;
            background: white;
            border-radius: 2rem;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
        }
        body.dark .modal {
            background: #0f172a;
            border-color: #334155;
        }
        .modal h3 {
            font-size: 1.25rem;
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
        
        .hidden { display: none !important; }
        
        /* Analytics Chart */
        .analytics-card {
            background: white;
            border-radius: 2.5rem;
            border: 1px solid #e2e8f0;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        body.dark .analytics-card {
            background: #0f172a;
            border-color: #1e293b;
        }
        .analytics-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .analytics-title {
            font-size: 1.25rem;
            font-weight: 900;
            color: #0f172a;
        }
        body.dark .analytics-title { color: #f1f5f9; }
        .analytics-controls {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .chart-toggle {
            display: flex;
            gap: 0.5rem;
            background: #f1f5f9;
            padding: 0.25rem;
            border-radius: 0.75rem;
        }
        body.dark .chart-toggle {
            background: #1e293b;
        }
        .chart-toggle button {
            padding: 0.5rem 1rem;
            border: none;
            background: transparent;
            color: #64748b;
            font-weight: 600;
            font-size: 0.875rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        .chart-toggle button.active {
            background: white;
            color: #2563eb;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        body.dark .chart-toggle button.active {
            background: #334155;
            color: #60a5fa;
        }
        .axis-select {
            padding: 0.5rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            background: white;
            color: #0f172a;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            outline: none;
        }
        body.dark .axis-select {
            background: #1e293b;
            border-color: #334155;
            color: #f1f5f9;
        }
        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }
        .chart-loading {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.9);
            border-radius: 1rem;
        }
        body.dark .chart-loading {
            background: rgba(15,23,42,0.9);
        }
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo" style="padding: 0; overflow: hidden;">
                <img src="../assets/logo.png" alt="Syntalytix" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <div class="sidebar-title">
                <h1>Admin</h1>
                <p>Syntalytix Controller</p>
            </div>
        </div>
        
        <nav class="nav-menu">
            <button class="nav-item active" onclick="setTab('overview')">📊 System Overview</button>
            <button class="nav-item" onclick="setTab('users')">👥 Manage Users</button>
            <button class="nav-item" onclick="setTab('content')">📚 Global Content</button>
            <button class="nav-item" onclick="setTab('settings')">⚙️ Site Settings</button>
        </nav>
        
        <button class="profile-btn" onclick="openProfile()">👤 My Profile</button>
        <button class="logout-btn" onclick="logout()">🚪 Log out</button>
    </aside>
    
    <main class="main-content">
        <div class="header">
            <div>
                <h2 id="pageTitle">System Overview</h2>
                <p>Manage your platform controls and data</p>
            </div>
            <button class="theme-toggle" onclick="toggleTheme()">🌙</button>
        </div>
        
        <!-- Overview Tab -->
        <div id="tab-overview" class="tab-content">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #dbeafe; color: #1d4ed8;">👨‍🎓</div>
                    <div class="stat-label">Total Students</div>
                    <div class="stat-value" id="stat-students">0</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e0e7ff; color: #4338ca;">👨‍🏫</div>
                    <div class="stat-label">Total Teachers</div>
                    <div class="stat-value" id="stat-teachers">0</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fef3c7; color: #b45309;">📄</div>
                    <div class="stat-label">Total Content</div>
                    <div class="stat-value" id="stat-content">0</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #f3e8ff; color: #7c3aed;">📝</div>
                    <div class="stat-label">Total Tests</div>
                    <div class="stat-value" id="stat-tests">0</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #f1f5f9; color: #475569;">⚙️</div>
                    <div class="stat-label">Total Admins</div>
                    <div class="stat-value" id="stat-admins">0</div>
                </div>
            </div>
        </div>
        
        <!-- Users Tab -->
        <div id="tab-users" class="tab-content hidden">
            <div class="content-card">
                <table>
                    <thead>
                        <tr>
                            <th>User Details</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-table"></tbody>
                </table>
            </div>
        </div>
        
        <!-- Content Tab -->
        <div id="tab-content" class="tab-content hidden">
            <div class="content-card">
                <div class="card-header">
                    <h3>All Learning Content</h3>
                </div>
                <div class="content-list" id="content-list"></div>
            </div>
            <div class="content-card">
                <div class="card-header">
                    <h3>All Tests</h3>
                </div>
                <div class="content-list" id="tests-list"></div>
            </div>
        </div>
        
        <!-- Settings Tab -->
        <div id="tab-settings" class="tab-content hidden">
            <div class="content-card">
                <div class="settings-form">
                    <div class="form-group">
                        <label>Platform Name</label>
                        <input type="text" id="platform-name" value="Syntalytix">
                    </div>
                    <div class="form-group">
                        <div class="toggle-row">
                            <span>Enable Student Registrations</span>
                            <div class="toggle-switch active" id="registration-toggle" onclick="toggleRegistration()"></div>
                        </div>
                    </div>
                    <button class="btn btn-primary" style="width: 100%; padding: 1rem;" onclick="saveSettings()" id="save-settings-btn">
                        Save All Changes
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Analytics Chart Card -->
        <div class="analytics-card">
            <div class="analytics-header">
                <h3 class="analytics-title">📈 Analytics Dashboard</h3>
                <div class="analytics-controls">
                    <div class="chart-toggle">
                        <button id="chart-line" class="active" onclick="setChartType('line')">Line</button>
                        <button id="chart-bar" onclick="setChartType('bar')">Bar</button>
                    </div>
                    <select id="x-axis" class="axis-select" onchange="updateChart()">
                        <option value="time">Time (Date)</option>
                        <option value="day">Day of Week</option>
                        <option value="hour">Hour of Day</option>
                        <option value="topic">Topic</option>
                    </select>
                    <select id="y-axis" class="axis-select" onchange="updateChart()">
                        <option value="users">Active Users</option>
                        <option value="logins">User Logins</option>
                        <option value="tests">Tests Taken</option>
                        <option value="scores">Average Scores</option>
                        <option value="content">Content Views</option>
                    </select>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="analyticsChart"></canvas>
                <div id="chart-loading" class="chart-loading hidden">
                    <div class="loading"></div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Edit User Modal -->
    <div class="modal-overlay" id="user-modal">
        <div class="modal">
            <h3>Edit User</h3>
            <div class="form-group">
                <label>Name</label>
                <input type="text" id="edit-user-name">
            </div>
            <div class="form-group">
                <label>Role</label>
                <select id="edit-user-role" style="width: 100%; padding: 1rem; border-radius: 1rem; border: 1px solid #e2e8f0; background: #f8fafc;">
                    <option value="1">Admin</option>
                    <option value="2">Teacher</option>
                    <option value="3">Student</option>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select id="edit-user-status" style="width: 100%; padding: 1rem; border-radius: 1rem; border: 1px solid #e2e8f0; background: #f8fafc;">
                    <option value="Active">Active</option>
                    <option value="Disabled">Disabled</option>
                </select>
            </div>
            <div class="modal-actions">
                <button class="btn btn-primary" onclick="saveUser()">Save Changes</button>
                <button class="btn btn-secondary" onclick="closeModal('user-modal')">Cancel</button>
            </div>
        </div>
    </div>
    
    <!-- Edit Content Modal -->
    <div class="modal-overlay" id="content-modal">
        <div class="modal">
            <h3>Edit Content</h3>
            <div class="form-group">
                <label>Title</label>
                <input type="text" id="edit-content-title">
            </div>
            <div class="form-group">
                <label>Topic</label>
                <input type="text" id="edit-content-topic">
            </div>
            <div class="form-group">
                <label>YouTube URL</label>
                <input type="text" id="edit-content-youtube">
            </div>
            <div class="form-group">
                <label>Google Drive URL</label>
                <input type="text" id="edit-content-drive">
            </div>
            <div class="modal-actions">
                <button class="btn btn-primary" onclick="saveContent()">Save Changes</button>
                <button class="btn btn-secondary" onclick="closeModal('content-modal')">Cancel</button>
            </div>
        </div>
    </div>
    
    <!-- Edit Test Modal -->
    <div class="modal-overlay" id="test-modal">
        <div class="modal">
            <h3>Edit Test</h3>
            <div class="form-group">
                <label>Test Name</label>
                <input type="text" id="edit-test-name">
            </div>
            <div class="form-group">
                <label>Topic</label>
                <input type="text" id="edit-test-topic">
            </div>
            <div class="modal-actions">
                <button class="btn btn-primary" onclick="saveTest()">Save Changes</button>
                <button class="btn btn-secondary" onclick="closeModal('test-modal')">Cancel</button>
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
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let currentTab = 'overview';
        let users = [];
        let content = [];
        let tests = [];
        let topics = [];
        let editingUserId = null;
        let editingContentId = null;
        let editingTestId = null;
        let registrationEnabled = true;
        
        // Theme
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
                'overview': 'System Overview',
                'users': 'Manage Users',
                'content': 'Global Content',
                'settings': 'Site Settings'
            };
            document.getElementById('pageTitle').textContent = titles[tab];
            
            if (tab === 'users') loadUsers();
            if (tab === 'content') {
                loadContent();
                loadTests();
            }
            if (tab === 'settings') loadSettings();
        }
        
        async function loadStats() {
            const response = await fetch('../api/admin.php?action=get_stats');
            const data = await response.json();
            if (data.success) {
                document.getElementById('stat-students').textContent = data.stats.total_students;
                document.getElementById('stat-teachers').textContent = data.stats.total_teachers;
                document.getElementById('stat-content').textContent = data.stats.total_content;
                document.getElementById('stat-tests').textContent = data.stats.total_tests;
                document.getElementById('stat-admins').textContent = data.stats.total_admins;
            }
        }
        
        async function loadUsers() {
            const response = await fetch('../api/admin.php?action=get_users');
            const data = await response.json();
            if (data.success) {
                users = data.users;
                const tbody = document.getElementById('users-table');
                tbody.innerHTML = users.map(u => `
                    <tr>
                        <td>
                            <div style="font-weight: 700; color: inherit;">${u.name}</div>
                            <div style="font-size: 0.75rem; color: #94a3b8;">${u.email}</div>
                        </td>
                        <td><span class="badge badge-${u.role.toLowerCase()}">${u.role}</span></td>
                        <td><span class="badge badge-${u.status.toLowerCase()}">${u.status}</span></td>
                        <td>
                            <button class="btn btn-primary" onclick="editUser(${u.id})">Edit</button>
                        </td>
                    </tr>
                `).join('');
            }
        }
        
        async function loadContent() {
            const response = await fetch('../api/admin.php?action=get_content');
            const data = await response.json();
            if (data.success) {
                content = data.content;
                const list = document.getElementById('content-list');
                list.innerHTML = content.map(c => `
                    <div class="content-item">
                        <div class="content-info">
                            <h4>${c.title}</h4>
                            <div class="content-meta">
                                <span class="badge badge-${c.content_type === 'video' ? 'student' : 'teacher'}">${c.content_type === 'video' ? 'Video' : 'Document'}</span>
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
        
        async function loadTests() {
            const response = await fetch('../api/admin.php?action=get_tests');
            const data = await response.json();
            if (data.success) {
                tests = data.tests;
                const list = document.getElementById('tests-list');
                list.innerHTML = tests.map(t => `
                    <div class="content-item">
                        <div class="content-info">
                            <h4>${t.test_name}</h4>
                            <div class="content-meta">
                                <span class="badge" style="background: #f3e8ff; color: #7c3aed;">Test</span>
                                <span>${t.question_count || 0} questions</span>
                                <span>Topic: ${t.topic || 'General'}</span>
                            </div>
                        </div>
                        <div class="content-actions">
                            <button class="btn btn-secondary" onclick="editTest(${t.id})">Edit</button>
                            <button class="btn btn-danger" onclick="deleteTest(${t.id})">Delete</button>
                        </div>
                    </div>
                `).join('');
            }
        }
        
        async function loadSettings() {
            const response = await fetch('../api/admin.php?action=get_settings');
            const data = await response.json();
            if (data.success) {
                document.getElementById('platform-name').value = data.settings.platform_name || 'Syntalytix';
                registrationEnabled = data.settings.student_registration_enabled == '1';
                document.getElementById('registration-toggle').classList.toggle('active', registrationEnabled);
            }
        }
        
        function toggleRegistration() {
            registrationEnabled = !registrationEnabled;
            document.getElementById('registration-toggle').classList.toggle('active', registrationEnabled);
        }
        
        async function saveSettings() {
            const btn = document.getElementById('save-settings-btn');
            btn.innerHTML = '<span class="loading"></span>';
            btn.disabled = true;
            
            const response = await fetch('../api/admin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=update_settings&platform_name=${encodeURIComponent(document.getElementById('platform-name').value)}&student_registration_enabled=${registrationEnabled ? '1' : '0'}`
            });
            
            btn.textContent = 'Save All Changes';
            btn.disabled = false;
            
            if (response.ok) {
                alert('Settings saved successfully!');
            }
        }
        
        function editUser(id) {
            const user = users.find(u => u.id == id);
            if (!user) return;
            editingUserId = id;
            document.getElementById('edit-user-name').value = user.name;
            document.getElementById('edit-user-role').value = user.role === 'Admin' ? 1 : user.role === 'Teacher' ? 2 : 3;
            document.getElementById('edit-user-status').value = user.status;
            document.getElementById('user-modal').classList.add('show');
        }
        
        async function saveUser() {
            const response = await fetch('../api/admin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=update_user&id=${editingUserId}&name=${encodeURIComponent(document.getElementById('edit-user-name').value)}&role_id=${document.getElementById('edit-user-role').value}&status=${document.getElementById('edit-user-status').value}`
            });
            
            closeModal('user-modal');
            loadUsers();
        }
        
        function editContent(id) {
            const item = content.find(c => c.id == id);
            if (!item) return;
            editingContentId = id;
            document.getElementById('edit-content-title').value = item.title;
            document.getElementById('edit-content-topic').value = item.topic || '';
            document.getElementById('edit-content-youtube').value = item.youtube_url || '';
            document.getElementById('edit-content-drive').value = item.drive_url || '';
            document.getElementById('content-modal').classList.add('show');
        }
        
        async function saveContent() {
            const response = await fetch('../api/admin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=update_content&id=${editingContentId}&title=${encodeURIComponent(document.getElementById('edit-content-title').value)}&topic=${encodeURIComponent(document.getElementById('edit-content-topic').value)}&youtubeUrl=${encodeURIComponent(document.getElementById('edit-content-youtube').value)}&driveUrl=${encodeURIComponent(document.getElementById('edit-content-drive').value)}`
            });
            
            closeModal('content-modal');
            loadContent();
        }
        
        async function deleteContent(id) {
            if (!confirm('Delete this content?')) return;
            await fetch('../api/admin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete_content&id=${id}`
            });
            loadContent();
        }
        
        function editTest(id) {
            const item = tests.find(t => t.id == id);
            if (!item) return;
            editingTestId = id;
            document.getElementById('edit-test-name').value = item.test_name;
            document.getElementById('edit-test-topic').value = item.topic || '';
            document.getElementById('test-modal').classList.add('show');
        }
        
        async function saveTest() {
            const response = await fetch('../api/admin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=update_test&id=${editingTestId}&test_name=${encodeURIComponent(document.getElementById('edit-test-name').value)}&topic=${encodeURIComponent(document.getElementById('edit-test-topic').value)}`
            });
            
            closeModal('test-modal');
            loadTests();
        }
        
        async function deleteTest(id) {
            if (!confirm('Delete this test?')) return;
            await fetch('../api/admin.php', {
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
        
        // Close modals on backdrop click
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
        
        // Analytics Chart
        let chartInstance = null;
        let currentChartType = 'line';
        
        function setChartType(type) {
            currentChartType = type;
            document.querySelectorAll('.chart-toggle button').forEach(btn => btn.classList.remove('active'));
            document.getElementById(`chart-${type}`).classList.add('active');
            updateChart();
        }
        
        async function updateChart() {
            const xAxis = document.getElementById('x-axis').value;
            const yAxis = document.getElementById('y-axis').value;
            
            document.getElementById('chart-loading').classList.remove('hidden');
            
            try {
                const response = await fetch(`../api/admin.php?action=get_analytics&x_axis=${xAxis}&y_axis=${yAxis}`);
                const data = await response.json();
                
                if (data.success) {
                    renderChart(data.labels, data.values, data.label);
                }
            } catch (err) {
                console.error('Failed to load analytics:', err);
            } finally {
                document.getElementById('chart-loading').classList.add('hidden');
            }
        }
        
        function renderChart(labels, values, datasetLabel) {
            const ctx = document.getElementById('analyticsChart').getContext('2d');
            
            if (chartInstance) {
                chartInstance.destroy();
            }
            
            const isDark = document.body.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)';
            const textColor = isDark ? '#94a3b8' : '#64748b';
            
            chartInstance = new Chart(ctx, {
                type: currentChartType,
                data: {
                    labels: labels,
                    datasets: [{
                        label: datasetLabel,
                        data: values,
                        borderColor: '#2563eb',
                        backgroundColor: currentChartType === 'line' ? 'rgba(37, 99, 235, 0.1)' : 'rgba(37, 99, 235, 0.8)',
                        borderWidth: 2,
                        fill: currentChartType === 'line',
                        tension: 0.4,
                        pointBackgroundColor: '#2563eb',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                color: textColor,
                                font: { weight: '600' }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: gridColor },
                            ticks: { color: textColor }
                        },
                        y: {
                            grid: { color: gridColor },
                            ticks: { color: textColor },
                            beginAtZero: true
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }
        
        // Load initial data
        loadStats();
        updateChart();
    </script>
</body>
</html>
