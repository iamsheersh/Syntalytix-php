-- Syntalytix Database Schema (MySQL)
-- Run this to set up your database

CREATE DATABASE IF NOT EXISTS syntalytix_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE syntalytix_db;

-- Roles table
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO roles (id, role_name) VALUES 
(1, 'Admin'),
(2, 'Teacher'),
(3, 'Student');

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uid VARCHAR(255) UNIQUE,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT DEFAULT 3,
    status ENUM('Active', 'Disabled') DEFAULT 'Active',
    reset_token VARCHAR(255) NULL,
    reset_token_expires TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Platform Settings
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Default settings
INSERT INTO settings (setting_key, setting_value) VALUES
('platform_name', 'Syntalytix'),
('student_registration_enabled', '1');

-- Content/Study Materials
CREATE TABLE content (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uploader_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    topic VARCHAR(100),
    content_type ENUM('video', 'pdf') DEFAULT 'video',
    youtube_url VARCHAR(500),
    drive_url VARCHAR(500),
    published BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (uploader_id) REFERENCES users(id)
);

-- Tests
CREATE TABLE tests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    creator_id INT NOT NULL,
    test_name VARCHAR(255) NOT NULL,
    topic VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_id) REFERENCES users(id)
);

-- Test Questions
CREATE TABLE questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    test_id INT NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('single', 'checkbox') DEFAULT 'single',
    options JSON,
    correct_answer VARCHAR(255),
    correct_answers JSON,
    marks INT DEFAULT 1,
    FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE
);

-- Test History
CREATE TABLE test_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    test_id INT NOT NULL,
    score INT,
    total_marks INT,
    answers JSON,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (test_id) REFERENCES tests(id)
);

-- Video Progress
CREATE TABLE video_progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    content_id INT NOT NULL,
    progress_seconds INT DEFAULT 0,
    total_seconds INT,
    completed BOOLEAN DEFAULT FALSE,
    last_watched TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (content_id) REFERENCES content(id),
    UNIQUE KEY unique_user_content (user_id, content_id)
);

-- Topics
CREATE TABLE topics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    topic_name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default topics
INSERT INTO topics (topic_name) VALUES 
('General'),
('Python'),
('Java'),
('JavaScript'),
('HTML'),
('CSS'),
('PHP'),
('C'),
('C++'),
('SQL'),
('React'),
('Node.js');
