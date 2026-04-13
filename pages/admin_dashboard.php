<?php
// pages/admin_dashboard.php
require_once __DIR__ . '/../includes/session.php';
requireRole('Admin');

$user = getCurrentUser();

if (!$user) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Syntalytix</title>
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
        body.dark .form-group input,
        body.dark .form-group input[type="password"] {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: white !important;
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
        .badge-pending { background: #fef3c7; color: #b45309; }
        .badge-disabled { background: #fee2e2; color: #dc2626; }
        
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
        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            font-size: 1rem;
            outline: none;
            background: #f8fafc;
        }
        body.dark .form-group input[type="text"],
        body.dark .form-group input[type="password"],
        body.dark .form-group select,
        body.dark .dark-input {
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
        
        /* Question Item Styles */
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
        .question-item label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }
        body.dark .question-item label {
            color: #94a3b8;
        }
        .question-item input,
        .question-item select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            font-size: 1rem;
            background: white;
            color: #0f172a;
        }
        body.dark .question-item input,
        body.dark .question-item select {
            background: #0f172a;
            border-color: #334155;
            color: #f1f5f9;
        }
        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .option-row {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .option-row input {
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

            .chart-container {
                height: 280px;
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
            <div style="display: flex; gap: 1rem; align-items: center;">
                <button class="btn btn-primary" id="downloadReportBtn" onclick="downloadReport()" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem;">
                    <svg style="width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Download Report
                </button>
                <button class="theme-toggle" onclick="toggleTheme()" style="padding: 0.75rem 1rem;">🌙</button>
            </div>
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
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3>All Users</h3>
                    <button class="btn btn-primary" onclick="showAddUser()">+ Add User</button>
                </div>
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
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3>All Learning Content</h3>
                    <button class="btn btn-primary" onclick="showAddContent()">+ Add Content</button>
                </div>
                <div class="content-list" id="content-list"></div>
            </div>
            <div class="content-card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3>All Tests</h3>
                    <button class="btn btn-primary" onclick="showAddTest()">+ Create Test</button>
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
                <select id="edit-user-role" style="width: 100%; padding: 1rem; border-radius: 1rem; border: 1px solid #e2e8f0;">
                    <option value="1">Admin</option>
                    <option value="2">Teacher</option>
                    <option value="3">Student</option>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select id="edit-user-status" style="width: 100%; padding: 1rem; border-radius: 1rem; border: 1px solid #e2e8f0;">
                    <option value="Active">Active</option>
                    <option value="Pending">Pending</option>
                    <option value="Disabled">Disabled</option>
                </select>
            </div>
            <div class="form-group">
                <label>New Password (leave blank to keep current)</label>
                <div class="password-wrapper" style="position: relative;">
                    <input type="password" id="edit-user-password" class="dark-input" placeholder="Enter new password (min 6 characters)" style="width: 100%; padding: 1rem 3rem 1rem 1rem; border-radius: 1rem; border: 1px solid #e2e8f0;">
                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility()" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 1.2rem; color: #64748b;">👁️</button>
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn btn-primary" onclick="saveUser()">Save Changes</button>
                <button class="btn btn-secondary" onclick="closeModal('user-modal')">Cancel</button>
            </div>
        </div>
    </div>
    
    <!-- Add User Modal -->
    <div class="modal-overlay" id="add-user-modal">
        <div class="modal">
            <h3>Add New User</h3>
            <div class="form-group">
                <label>Name</label>
                <input type="text" id="add-user-name" placeholder="Enter full name">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="add-user-email" placeholder="Enter email address">
            </div>
            <div class="form-group">
                <label>Role</label>
                <select id="add-user-role" style="width: 100%; padding: 1rem; border-radius: 1rem; border: 1px solid #e2e8f0;">
                    <option value="1">Admin</option>
                    <option value="2">Teacher</option>
                    <option value="3">Student</option>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select id="add-user-status" style="width: 100%; padding: 1rem; border-radius: 1rem; border: 1px solid #e2e8f0;">
                    <option value="Active">Active</option>
                    <option value="Pending">Pending</option>
                    <option value="Disabled">Disabled</option>
                </select>
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="password-wrapper" style="position: relative;">
                    <input type="password" id="add-user-password" class="dark-input" placeholder="Enter password" style="width: 100%; padding: 1rem 3rem 1rem 1rem; border-radius: 1rem; border: 1px solid #e2e8f0;" oninput="checkPasswordStrength()">
                    <button type="button" class="toggle-password" onclick="toggleAddUserPasswordVisibility()" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 1.2rem; color: #64748b;">👁️</button>
                </div>
                <!-- Password Requirements -->
                <div class="password-requirements" style="margin-top: 0.75rem; padding: 0.75rem; background: #f8fafc; border-radius: 0.75rem; font-size: 0.8rem;">
                    <div style="font-weight: 600; margin-bottom: 0.5rem; color: #475569;">Password Requirements:</div>
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
            <div class="modal-actions">
                <button class="btn btn-primary" onclick="saveNewUser()" id="add-user-save-btn">Create User</button>
                <button class="btn btn-secondary" onclick="closeModal('add-user-modal')">Cancel</button>
            </div>
        </div>
    </div>
    
    <!-- Content Modal (Add/Edit) -->
    <div class="modal-overlay" id="content-modal">
        <div class="modal">
            <h3 id="content-modal-title">Edit Content</h3>
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
                <button class="btn btn-primary" onclick="saveContent()" id="content-save-btn">Save Changes</button>
                <button class="btn btn-secondary" onclick="closeModal('content-modal')">Cancel</button>
            </div>
        </div>
    </div>
    
    <!-- Edit Test Modal -->
    <div class="modal-overlay" id="test-modal" style="align-items: flex-start; padding-top: 2rem;">
        <div class="modal" style="max-width: 800px; max-height: 90vh; overflow-y: auto;">
            <h3 id="test-modal-title">Edit Test</h3>
            <div class="form-group">
                <label>Test Name</label>
                <input type="text" id="edit-test-name">
            </div>
            <div class="form-group">
                <label>Topic</label>
                <input type="text" id="edit-test-topic">
            </div>
            <div id="questions-container" style="margin-bottom: 1rem;"></div>
            <button class="btn btn-secondary" onclick="addQuestion()" style="width: 100%; margin-bottom: 1rem;">+ Add Question</button>
            <div class="modal-actions">
                <button class="btn btn-primary" onclick="saveTest()">Save Changes</button>
                <button class="btn btn-secondary" onclick="closeModal('test-modal')">Cancel</button>
            </div>
        </div>
    </div>
    
    
    <!-- View Scores Modal -->
    <div class="modal-overlay" id="scores-modal">
        <div class="modal" style="max-width: 800px;">
            <h3 id="scores-modal-title">Test Scores</h3>
            <div class="stats-grid" id="scores-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            </div>
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
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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
        
        let editingQuestionId = null;
        let currentTestIdForQuestions = null;
        let questions = [];
        let currentTestScoresData = null;

        async function downloadReport() {
            // First ensure we have all data loaded if not already
            if (users.length === 0) await loadUsers();
            if (content.length === 0) await loadContent();
            if (tests.length === 0) await loadTests();
            
            const platformName = document.getElementById('platform-name').value || 'Syntalytix';
            const students = document.getElementById('stat-students').textContent;
            const teachers = document.getElementById('stat-teachers').textContent;
            const contentCount = document.getElementById('stat-content').textContent;
            const testsCount = document.getElementById('stat-tests').textContent;
            const adminsCount = document.getElementById('stat-admins').textContent;
            const generatedAt = new Date();
            
            // Create a temporary container for the report
            const tempDiv = document.createElement('div');
            tempDiv.style.padding = '0';
            tempDiv.style.fontFamily = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            tempDiv.style.color = '#000'; // Force light text for PDF
            tempDiv.style.background = '#fff';
            
            let usersTableRows = users.map(u => `
                <tr>
                    <td style="padding: 12px 10px; border-bottom: 1px solid #334155; vertical-align: top; color: #e2e8f0;">
                        <strong style="color: #f1f5f9;">${u.name}</strong><br>
                        <span style="color: #94a3b8; font-size: 12px;">${u.email}</span>
                    </td>
                    <td style="padding: 12px 10px; border-bottom: 1px solid #334155; vertical-align: top; color: #e2e8f0;">${u.role}</td>
                    <td style="padding: 12px 10px; border-bottom: 1px solid #334155; vertical-align: top; color: #e2e8f0;">${u.status}</td>
                </tr>
            `).join('');

            let contentTableRows = content.map(c => `
                <tr>
                    <td style="padding: 12px 10px; border-bottom: 1px solid #334155; vertical-align: top; color: #e2e8f0;"><strong style="color: #f1f5f9;">${c.title}</strong></td>
                    <td style="padding: 12px 10px; border-bottom: 1px solid #334155; vertical-align: top; color: #e2e8f0; text-transform: capitalize;">${c.content_type}</td>
                    <td style="padding: 12px 10px; border-bottom: 1px solid #334155; vertical-align: top; color: #e2e8f0;">${c.topic || 'General'}</td>
                </tr>
            `).join('');

            let testsTableRows = tests.map(t => `
                <tr>
                    <td style="padding: 12px 10px; border-bottom: 1px solid #334155; vertical-align: top; color: #e2e8f0;"><strong style="color: #f1f5f9;">${t.test_name}</strong></td>
                    <td style="padding: 12px 10px; border-bottom: 1px solid #334155; vertical-align: top; color: #e2e8f0;">${t.question_count || 0}</td>
                    <td style="padding: 12px 10px; border-bottom: 1px solid #334155; vertical-align: top; color: #e2e8f0;">${t.topic || 'General'}</td>
                </tr>
            `).join('');
            
            let logoSrc = '';
            const logoEl = document.querySelector('.sidebar-logo img');
            if(logoEl) {
                logoSrc = new URL(logoEl.getAttribute('src'), window.location.href).href;
            }

            const s = parseInt(students, 10) || 0;
            const t = parseInt(teachers, 10) || 0;
            const a = parseInt(adminsCount, 10) || 0;
            const totalUsers = Math.max(s + t + a, 1);
            const pctStudents = Math.round((s / totalUsers) * 100);
            const pctTeachers = Math.round((t / totalUsers) * 100);
            const pctAdmins = Math.max(0, 100 - pctStudents - pctTeachers);

            tempDiv.innerHTML = `
                <style>
                    * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
                    .report { padding: 30px; max-width: 100%; background: #0f172a; }
                    .header {
                        background: #1e3a8a;
                        color: #ffffff;
                        border-radius: 12px;
                        padding: 20px 24px;
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        gap: 16px;
                        margin-bottom: 20px;
                    }
                    .header-left { display: flex; align-items: center; gap: 14px; }
                    .header-title { margin: 0; font-size: 22px; font-weight: 900; color: #ffffff; }
                    .header-sub { margin-top: 4px; font-size: 12px; color: #93c5fd; }
                    .header-meta { text-align: right; font-size: 11px; color: #93c5fd; }
                    .header-meta strong { color: #ffffff; }
                    .logo {
                        width: 48px; height: 48px;
                        border-radius: 10px;
                        overflow: hidden;
                        background: #ffffff;
                        border: 2px solid #ffffff;
                        display: flex; align-items: center; justify-content: center;
                    }
                    .logo img { width: 48px; height: 48px; object-fit: cover; }
                    .section { margin-top: 16px; }
                    .section-title {
                        color: #f1f5f9;
                        font-size: 15px;
                        font-weight: 900;
                        margin: 0 0 12px 0;
                        display: flex;
                        align-items: center;
                        gap: 8px;
                    }
                    .section-title .pill {
                        display: inline-block;
                        font-size: 10px;
                        font-weight: 900;
                        text-transform: uppercase;
                        letter-spacing: 0.05em;
                        padding: 4px 8px;
                        border-radius: 20px;
                        background: #3b82f6;
                        color: #ffffff;
                        border: none;
                    }
                    .card {
                        background: #1e293b;
                        border: 1px solid #334155;
                        border-radius: 10px;
                        padding: 14px;
                    }
                    .stats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; }
                    .stat {
                        border-radius: 10px;
                        padding: 12px 10px;
                        border: 1px solid #334155;
                        background: #1e293b;
                        text-align: center;
                        min-height: 80px;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                    }
                    .stat-label { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
                    .stat-value { font-size: 28px; font-weight: 900; color: #f1f5f9; line-height: 1; }
                    .stat-accent { color: #60a5fa; }
                    .grid-2 { display: grid; grid-template-columns: 1.2fr 1fr; gap: 10px; }
                    .bar { height: 10px; border-radius: 999px; background: #334155; overflow: hidden; }
                    .bar > span { display: block; height: 100%; }
                    .legend { margin-top: 12px; display: grid; grid-template-columns: 1fr; gap: 8px; font-size: 12px; color: #cbd5e1; }
                    .legend-row { display: flex; justify-content: space-between; align-items: center; gap: 10px; }
                    .dot { width: 10px; height: 10px; border-radius: 999px; display: inline-block; margin-right: 8px; }
                    table { width: 100%; border-collapse: collapse; font-size: 13px; }
                    thead th {
                        text-align: left;
                        font-size: 11px;
                        font-weight: 900;
                        color: #94a3b8;
                        text-transform: uppercase;
                        letter-spacing: 0.06em;
                        background: #1e293b;
                        border-bottom: 1px solid #334155;
                        padding: 12px 10px;
                    }
                    tbody td {
                        padding: 12px 10px;
                        border-bottom: 1px solid #334155;
                        vertical-align: top;
                        color: #e2e8f0;
                    }
                    .badge {
                        display: inline-block;
                        padding: 5px 12px;
                        font-size: 11px;
                        font-weight: 800;
                        border-radius: 999px;
                        border: 1px solid #334155;
                        background: #0f172a;
                        color: #f1f5f9;
                    }
                    .badge.active { background: #14532d; border-color: #166534; color: #4ade80; }
                    .badge.inactive { background: #450a0a; border-color: #991b1b; color: #f87171; }
                    .badge.student { background: #172554; border-color: #1e40af; color: #60a5fa; }
                    .badge.teacher { background: #3b0764; border-color: #6b21a8; color: #c084fc; }
                    .badge.admin { background: #1e293b; border-color: #334155; color: #e2e8f0; }
                    .muted { color: #94a3b8; font-weight: 500; }
                    .small { font-size: 12px; line-height: 1.6; color: #cbd5e1; }
                    .footer {
                        margin-top: 20px;
                        padding-top: 14px;
                        border-top: 1px solid #334155;
                        color: #94a3b8;
                        font-size: 11px;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        gap: 10px;
                        text-transform: uppercase;
                        letter-spacing: 0.06em;
                        font-weight: 800;
                    }
                    .table-section { page-break-inside: avoid; margin-top: 16px; }
                    tr { page-break-inside: avoid !important; }
                    thead { display: table-header-group; }
                    tbody { display: table-row-group; }
                </style>

                <div class="report">
                    <div class="header">
                        <div class="header-left">
                            <div class="logo">${logoSrc ? `<img src="${logoSrc}" alt="Logo">` : '<div style="width:52px;height:52px;"></div>'}</div>
                            <div>
                                <h1 class="header-title">${platformName} - Project Report</h1>
                                <div class="header-sub">Admin Dashboard Snapshot</div>
                            </div>
                        </div>
                        <div class="header-meta">
                            <div><strong>Generated</strong></div>
                            <div>${generatedAt.toLocaleString()}</div>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-title"><span class="pill">Overview</span> System Summary</div>
                        <div class="stats" style="margin-bottom: 12px;">
                            <div class="stat"><div class="stat-label">Students</div><div class="stat-value stat-accent">${students}</div></div>
                            <div class="stat"><div class="stat-label">Teachers</div><div class="stat-value stat-accent">${teachers}</div></div>
                            <div class="stat"><div class="stat-label">Admins</div><div class="stat-value stat-accent">${adminsCount}</div></div>
                            <div class="stat"><div class="stat-label">Content</div><div class="stat-value">${contentCount}</div></div>
                            <div class="stat"><div class="stat-label">Tests</div><div class="stat-value">${testsCount}</div></div>
                        </div>

                        <div class="grid-2">
                            <div class="card">
                                <div style="font-weight: 950; color: #0f172a; margin-bottom: 10px;">User Composition</div>
                                <div class="bar" aria-label="User composition bar">
                                    <span style="width:${pctStudents}%;background:#2563eb;"></span>
                                    <span style="width:${pctTeachers}%;background:#7c3aed;"></span>
                                    <span style="width:${pctAdmins}%;background:#0f172a;"></span>
                                </div>
                                <div class="legend">
                                    <div class="legend-row"><div><span class="dot" style="background:#2563eb"></span><strong>Students</strong> <span class="muted">(${pctStudents}%)</span></div><div class="muted">${students}</div></div>
                                    <div class="legend-row"><div><span class="dot" style="background:#7c3aed"></span><strong>Teachers</strong> <span class="muted">(${pctTeachers}%)</span></div><div class="muted">${teachers}</div></div>
                                    <div class="legend-row"><div><span class="dot" style="background:#0f172a"></span><strong>Admins</strong> <span class="muted">(${pctAdmins}%)</span></div><div class="muted">${adminsCount}</div></div>
                                </div>
                            </div>
                            <div class="card">
                                <div style="font-weight: 950; color: #0f172a; margin-bottom: 8px;">Highlights</div>
                                <div class="small" style="line-height: 1.5;">
                                    <div><strong>Total Users:</strong> ${s + t + a}</div>
                                    <div><strong>Total Learning Items:</strong> ${contentCount}</div>
                                    <div><strong>Total Assessments:</strong> ${testsCount}</div>
                                    <div class="muted" style="margin-top: 10px;">This report captures current platform counts and directory listings.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-section">
                        <div class="section-title"><span class="pill">Directory</span> Users</div>
                        <div class="card" style="padding: 0; overflow: hidden;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${usersTableRows || '<tr><td colspan="3" style="text-align: center; padding: 20px; color: #94a3b8;">No users found.</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="table-section">
                        <div class="section-title"><span class="pill">Directory</span> Learning Content</div>
                        <div class="card" style="padding: 0; overflow: hidden;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Topic</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${contentTableRows || '<tr><td colspan="3" style="text-align: center; padding: 20px; color: #94a3b8;">No content found.</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="table-section">
                        <div class="section-title"><span class="pill">Directory</span> Tests</div>
                        <div class="card" style="padding: 0; overflow: hidden;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Test Name</th>
                                        <th>Questions</th>
                                        <th>Topic</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${testsTableRows || '<tr><td colspan="3" style="text-align: center; padding: 20px; color: #94a3b8;">No tests found.</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="footer">
                        <div>Confidential & Proprietary - ${platformName}</div>
                        <div>${generatedAt.toLocaleDateString()}</div>
                    </div>
                </div>
            `;
            
            tempDiv.style.width = '100%';
            tempDiv.style.maxWidth = '800px';

            // html2canvas REQUIRES the element to be in the live DOM.
            // We wrap it in a fixed position container off-screen
            const wrapper = document.createElement('div');
            wrapper.style.cssText = 'position:fixed;top:-9999px;left:-9999px;width:800px;z-index:-9999;';
            wrapper.appendChild(tempDiv);
            document.body.appendChild(wrapper);

            // Generate PDF with better settings
            const opt = {
                margin:       [0.5, 0.5, 0.5, 0.5], // top, left, bottom, right
                filename:     'Project_Report_' + platformName.replace(/[^a-z0-9]/gi, '_').toLowerCase() + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: false, width: 800 },
                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' },
                pagebreak:    { mode: ['css', 'legacy'], before: '.page-break', avoid: ['table', '.card'] }
            };

            // Change button to indicate loading
            const btn = document.getElementById('downloadReportBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="loading" style="width: 1rem; height: 1rem; border-width: 2px; margin-right: 0.5rem;"></span> Generating...';
            btn.disabled = true;

            try {
                const worker = html2pdf().set(opt).from(tempDiv).toPdf();
                await worker.get('pdf').then((pdf) => {
                    const pageCount = pdf.internal.getNumberOfPages();
                    const pageWidth = pdf.internal.pageSize.getWidth();
                    const pageHeight = pdf.internal.pageSize.getHeight();

                    pdf.setFontSize(9);
                    pdf.setTextColor(148, 163, 184);

                    for (let i = 1; i <= pageCount; i++) {
                        pdf.setPage(i);
                        const text = `Page ${i} of ${pageCount}`;
                        pdf.text(text, pageWidth - 0.4, pageHeight - 0.25, { align: 'right' });
                    }
                });
                await worker.save();
            } catch (err) {
                console.error("Error generating PDF:", err);
                alert("An error occurred while generating the report.");
            } finally {
                document.body.removeChild(wrapper);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        
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

        function togglePasswordVisibility() {
            const input = document.getElementById('edit-user-password');
            const button = document.querySelector('.toggle-password');
            if (input.type === 'password') {
                input.type = 'text';
                button.textContent = '🙈';
            } else {
                input.type = 'password';
                button.textContent = '👁️';
            }
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
                            <button class="btn btn-secondary" onclick="viewTestScores(${t.id})">📊 Scores</button>
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
            const password = document.getElementById('edit-user-password').value;
            let body = `action=update_user&id=${editingUserId}&name=${encodeURIComponent(document.getElementById('edit-user-name').value)}&role_id=${document.getElementById('edit-user-role').value}&status=${document.getElementById('edit-user-status').value}`;
            
            if (password) {
                body += `&password=${encodeURIComponent(password)}`;
            }
            
            const response = await fetch('../api/admin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body
            });
            
            document.getElementById('edit-user-password').value = ''; // Clear password field
            closeModal('user-modal');
            loadUsers();
        }

        function showAddUser() {
            // Clear all fields
            document.getElementById('add-user-name').value = '';
            document.getElementById('add-user-email').value = '';
            document.getElementById('add-user-password').value = '';
            document.getElementById('add-user-role').value = '3';
            document.getElementById('add-user-status').value = 'Active';
            // Reset password requirements
            checkPasswordStrength();
            document.getElementById('add-user-modal').classList.add('show');
        }

        function toggleAddUserPasswordVisibility() {
            const input = document.getElementById('add-user-password');
            const button = document.querySelector('#add-user-modal .toggle-password');
            if (input.type === 'password') {
                input.type = 'text';
                button.textContent = '🙈';
            } else {
                input.type = 'password';
                button.textContent = '👁️';
            }
        }

        function checkPasswordStrength() {
            const password = document.getElementById('add-user-password').value;
            
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
            
            // Enable/disable save button based on all requirements
            const allValid = hasLength && hasUppercase && hasLowercase && hasNumber && hasSpecial;
            const saveBtn = document.getElementById('add-user-save-btn');
            saveBtn.disabled = !allValid;
            saveBtn.style.opacity = allValid ? '1' : '0.5';
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

        async function saveNewUser() {
            const name = document.getElementById('add-user-name').value;
            const email = document.getElementById('add-user-email').value;
            const password = document.getElementById('add-user-password').value;
            const roleId = document.getElementById('add-user-role').value;
            const status = document.getElementById('add-user-status').value;
            
            if (!name || !email || !password) {
                alert('Please fill in all required fields');
                return;
            }
            
            // Validate password requirements
            const hasLength = password.length >= 10;
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
            
            if (!hasLength || !hasUppercase || !hasLowercase || !hasNumber || !hasSpecial) {
                alert('Password does not meet all requirements');
                return;
            }
            
            const response = await fetch('../api/admin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=create_user&name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}&role_id=${roleId}&status=${encodeURIComponent(status)}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                closeModal('add-user-modal');
                loadUsers();
                alert('User created successfully!');
            } else {
                alert('Error creating user: ' + (data.error || 'Unknown error'));
            }
        }
        
        function showAddContent() {
            editingContentId = null;
            document.getElementById('content-modal-title').textContent = 'Add Content';
            document.getElementById('content-save-btn').textContent = 'Add Content';
            document.getElementById('edit-content-title').value = '';
            document.getElementById('edit-content-topic').value = '';
            document.getElementById('edit-content-youtube').value = '';
            document.getElementById('edit-content-drive').value = '';
            document.getElementById('content-modal').classList.add('show');
        }

        function editContent(id) {
            const item = content.find(c => c.id == id);
            if (!item) return;
            editingContentId = id;
            document.getElementById('content-modal-title').textContent = 'Edit Content';
            document.getElementById('content-save-btn').textContent = 'Save Changes';
            document.getElementById('edit-content-title').value = item.title;
            document.getElementById('edit-content-topic').value = item.topic || '';
            document.getElementById('edit-content-youtube').value = item.youtube_url || '';
            document.getElementById('edit-content-drive').value = item.drive_url || '';
            document.getElementById('content-modal').classList.add('show');
        }
        
        async function saveContent() {
            const action = editingContentId ? 'update_content' : 'create_content';
            const idParam = editingContentId ? `&id=${editingContentId}` : '';
            const response = await fetch('../api/admin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=${action}${idParam}&title=${encodeURIComponent(document.getElementById('edit-content-title').value)}&topic=${encodeURIComponent(document.getElementById('edit-content-topic').value)}&youtubeUrl=${encodeURIComponent(document.getElementById('edit-content-youtube').value)}&driveUrl=${encodeURIComponent(document.getElementById('edit-content-drive').value)}`
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
        
        function showAddTest() {
            editingTestId = null;
            document.getElementById('test-modal-title').textContent = 'Create Test';
            document.getElementById('edit-test-name').value = '';
            document.getElementById('edit-test-topic').value = '';
            questions = [];
            renderQuestions();
            document.getElementById('test-modal').classList.add('show');
        }

        async function editTest(id) {
            const item = tests.find(t => t.id == id);
            if (!item) return;
            editingTestId = id;
            document.getElementById('test-modal-title').textContent = 'Edit Test';
            document.getElementById('edit-test-name').value = item.test_name;
            document.getElementById('edit-test-topic').value = item.topic || '';

            // Load existing questions
            try {
                const response = await fetch(`../api/admin.php?action=get_questions&test_id=${id}`);
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

        function updateCorrectAnswer(idx, value) {
            if (questions[idx].question_type === 'checkbox') {
                questions[idx].correct_answers = value.split(',').map(s => parseInt(s.trim())).filter(n => !isNaN(n));
            } else {
                questions[idx].correct_answer = parseInt(value) || 0;
            }
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
                        <span style="font-weight: 700; color: inherit;">Question ${qIdx + 1}</span>
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
            const testName = document.getElementById('edit-test-name').value;
            const topic = document.getElementById('edit-test-topic').value;

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
            const idParam = editingTestId ? `&id=${editingTestId}` : '';
            const body = `action=${action}${idParam}&test_name=${encodeURIComponent(testName)}&topic=${encodeURIComponent(topic)}&questions=${encodeURIComponent(JSON.stringify(formattedQuestions))}`;

            const response = await fetch('../api/admin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body
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
        
        async function viewTestScores(testId) {
            const test = tests.find(t => t.id == testId);
            if (!test) return;
            
            document.getElementById('scores-modal-title').textContent = `Scores: ${test.test_name}`;
            document.getElementById('scores-tbody').innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 2rem;">Loading...</td></tr>';
            document.getElementById('scores-modal').classList.add('show');
            
            try {
                const response = await fetch(`../api/admin.php?action=get_test_scores&test_id=${testId}`);
                const data = await response.json();
                
                if (data.success) {
                    currentTestScoresData = data;
                    // Render stats
                    const stats = data.stats;
                    const percentage = stats.total_marks > 0 ? Math.round((stats.avg_score / stats.total_marks) * 100) : 0;
                    
                    document.getElementById('scores-stats-grid').innerHTML = `
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

            // Create lookup for current questions by ID
            const questionsById = {};
            testQuestions.forEach(q => { questionsById[q.id] = q; });

            // Iterate over stored answer keys (original question IDs) to handle edited tests
            const answerKeys = Object.keys(userAnswers);
            answerKeys.forEach((qId, idx) => {
                const ua = userAnswers[qId];
                const isCorrect = ua.is_correct;
                const userChoice = ua.user_answer;
                
                // Try to find current question, fallback to stored data
                const q = questionsById[qId];
                const questionText = q ? q.question_text : 'Question (may have been edited)';
                const questionType = q ? q.question_type : (Array.isArray(ua.correct_answers) && ua.correct_answers.length > 0 ? 'checkbox' : 'single_choice');
                const options = q ? q.options : [];
                const marks = q ? q.marks : (isCorrect ? historyObj.score : 0); // fallback
                const correctAnswer = q ? q.correct_answer : ua.correct_answer;
                const correctAnswers = q ? q.correct_answers : ua.correct_answers;
                
                let answerDisplay = '';
                
                if (questionType === 'checkbox') {
                    let correctChoices = (correctAnswers || []).map(i => options[i] || `Option ${i}`).join(', ');
                    let userChosen = Array.isArray(userChoice) ? userChoice.map(i => options[i] || `Option ${i}`).join(', ') : 'None';
                    answerDisplay = `
                        <div style="margin-top: 0.5rem; font-size: 0.875rem;">
                            <div style="color: #64748b; margin-bottom: 0.25rem;">Selected: <span style="font-weight: 700; color: ${isCorrect ? '#10b981' : '#ef4444'}">${escapeHtml(userChosen)}</span></div>
                            ${!isCorrect ? `<div style="color: #64748b;">Correct: <span style="font-weight: 700; color: #10b981">${escapeHtml(correctChoices)}</span></div>` : ''}
                        </div>
                    `;
                } else {
                    let correctChoiceStr = options[correctAnswer] || (correctAnswer !== null ? `Option ${correctAnswer}` : 'Unknown');
                    let userChosenStr = userChoice !== null && userChoice !== undefined && userChoice !== '' ? (options[userChoice] || `Option ${userChoice}`) : 'None';
                    answerDisplay = `
                        <div style="margin-top: 0.5rem; font-size: 0.875rem;">
                            <div style="color: #64748b; margin-bottom: 0.25rem;">Selected: <span style="font-weight: 700; color: ${isCorrect ? '#10b981' : '#ef4444'}">${escapeHtml(userChosenStr)}</span></div>
                            ${!isCorrect ? `<div style="color: #64748b;">Correct: <span style="font-weight: 700; color: #10b981">${escapeHtml(correctChoiceStr)}</span></div>` : ''}
                        </div>
                    `;
                }
                
                const displayMarks = q ? q.marks : (isCorrect ? 1 : 0);
                
                html += `
                    <div style="border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.5rem; margin-bottom: 1rem; background: ${isCorrect ? 'rgba(16, 185, 129, 0.05)' : 'rgba(239, 68, 68, 0.05)'};">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <h4 style="font-weight: 700; margin-bottom: 0.5rem; flex: 1; padding-right: 1rem;">Q${idx + 1}: ${escapeHtml(questionText)}</h4>
                            <span style="font-weight: 900; color: ${isCorrect ? '#10b981' : '#ef4444'}">${isCorrect ? '✓ Correct' : '✗ Incorrect'}</span>
                        </div>
                        <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 1rem;">Marks: ${isCorrect ? displayMarks : 0} / ${displayMarks}</div>
                        ${answerDisplay}
                    </div>
                `;
            });
            
            document.getElementById('answers-container').innerHTML = html;
            document.getElementById('answers-modal').classList.add('show');
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
    <?php include __DIR__ . '/../includes/support_popup.php'; ?>
</body>
</html>
