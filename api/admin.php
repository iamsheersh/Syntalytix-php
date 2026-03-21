<?php
// api/admin.php
// Admin API endpoints

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

header('Content-Type: application/json');

// Require admin role
$user = getCurrentUser();
if (!$user || $user['role_name'] !== 'Admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$conn = getDBConnection();

switch ($action) {
    case 'get_users':
        getUsers($conn);
        break;
    case 'get_content':
        getContent($conn);
        break;
    case 'get_tests':
        getTests($conn);
        break;
    case 'get_topics':
        getTopics($conn);
        break;
    case 'get_stats':
        getStats($conn);
        break;
    case 'update_user':
        updateUser($conn);
        break;
    case 'update_content':
        updateContent($conn);
        break;
    case 'update_test':
        updateTest($conn);
        break;
    case 'delete_content':
        deleteContent($conn);
        break;
    case 'delete_test':
        deleteTest($conn);
        break;
    case 'get_settings':
        getSettings($conn);
        break;
    case 'update_settings':
        updateSettings($conn);
        break;
    case 'get_analytics':
        getAnalytics($conn);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

$conn->close();

function getUsers($conn) {
    $result = $conn->query("SELECT u.id, u.name, u.email, u.status, u.created_at, r.role_name as role FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.created_at DESC");
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $row['date'] = date('d M Y', strtotime($row['created_at']));
        $users[] = $row;
    }
    echo json_encode(['success' => true, 'users' => $users]);
}

function getContent($conn) {
    $result = $conn->query("SELECT c.*, u.name as uploader_name FROM content c JOIN users u ON c.uploader_id = u.id ORDER BY c.created_at DESC");
    $content = [];
    while ($row = $result->fetch_assoc()) {
        $content[] = $row;
    }
    echo json_encode(['success' => true, 'content' => $content]);
}

function getTests($conn) {
    $result = $conn->query("SELECT t.*, u.name as creator_name FROM tests t JOIN users u ON t.creator_id = u.id ORDER BY t.created_at DESC");
    $tests = [];
    while ($row = $result->fetch_assoc()) {
        // Get question count
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM questions WHERE test_id = ?");
        $stmt->bind_param("i", $row['id']);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();
        $row['question_count'] = $count;
        $tests[] = $row;
    }
    echo json_encode(['success' => true, 'tests' => $tests]);
}

function getTopics($conn) {
    $result = $conn->query("SELECT * FROM topics ORDER BY topic_name");
    $topics = [];
    while ($row = $result->fetch_assoc()) {
        $topics[] = $row;
    }
    echo json_encode(['success' => true, 'topics' => $topics]);
}

function getStats($conn) {
    $stats = [];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role_id = 3");
    $stats['total_students'] = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role_id = 2");
    $stats['total_teachers'] = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role_id = 1");
    $stats['total_admins'] = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM content");
    $stats['total_content'] = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM tests");
    $stats['total_tests'] = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM topics");
    $stats['total_topics'] = $result->fetch_assoc()['count'];
    
    echo json_encode(['success' => true, 'stats' => $stats]);
}

function updateUser($conn) {
    $id = $_POST['id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $role_id = $_POST['role_id'] ?? 3;
    $status = $_POST['status'] ?? 'Active';
    
    $stmt = $conn->prepare("UPDATE users SET name = ?, role_id = ?, status = ? WHERE id = ?");
    $stmt->bind_param("sisi", $name, $role_id, $status, $id);
    $success = $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => $success]);
}

function updateContent($conn) {
    $id = $_POST['id'] ?? 0;
    $title = $_POST['title'] ?? '';
    $topic = $_POST['topic'] ?? '';
    $youtubeUrl = $_POST['youtubeUrl'] ?? '';
    $driveUrl = $_POST['driveUrl'] ?? '';
    
    // Determine content type
    $content_type = (!empty($youtubeUrl)) ? 'video' : 'pdf';
    
    $stmt = $conn->prepare("UPDATE content SET title = ?, topic = ?, youtube_url = ?, drive_url = ?, content_type = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $title, $topic, $youtubeUrl, $driveUrl, $content_type, $id);
    $success = $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => $success]);
}

function updateTest($conn) {
    $id = $_POST['id'] ?? 0;
    $test_name = $_POST['test_name'] ?? '';
    $topic = $_POST['topic'] ?? '';
    
    $stmt = $conn->prepare("UPDATE tests SET test_name = ?, topic = ? WHERE id = ?");
    $stmt->bind_param("ssi", $test_name, $topic, $id);
    $success = $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => $success]);
}

function deleteContent($conn) {
    $id = $_POST['id'] ?? 0;
    
    $stmt = $conn->prepare("DELETE FROM content WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => $success]);
}

function deleteTest($conn) {
    $id = $_POST['id'] ?? 0;
    
    $stmt = $conn->prepare("DELETE FROM tests WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => $success]);
}

function getSettings($conn) {
    $result = $conn->query("SELECT * FROM settings");
    $settings = [];
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    echo json_encode(['success' => true, 'settings' => $settings]);
}

function updateSettings($conn) {
    $platformName = $_POST['platform_name'] ?? 'Syntalytix';
    $registrationEnabled = $_POST['student_registration_enabled'] ?? '1';
    
    $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('platform_name', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->bind_param("ss", $platformName, $platformName);
    $stmt->execute();
    $stmt->close();
    
    $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('student_registration_enabled', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->bind_param("ss", $registrationEnabled, $registrationEnabled);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => true]);
}

function getAnalytics($conn) {
    $xAxis = $_GET['x_axis'] ?? 'time';
    $yAxis = $_GET['y_axis'] ?? 'users';
    
    $labels = [];
    $values = [];
    $label = '';
    
    switch ($yAxis) {
        case 'users':
            $label = 'Active Users';
            break;
        case 'logins':
            $label = 'User Logins';
            break;
        case 'tests':
            $label = 'Tests Taken';
            break;
        case 'scores':
            $label = 'Average Score (%)';
            break;
        case 'content':
            $label = 'Content Views';
            break;
    }
    
    switch ($xAxis) {
        case 'time':
            // Last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $labels[] = date('M d', strtotime("-$i days"));
                
                switch ($yAxis) {
                    case 'users':
                        $stmt = $conn->prepare("SELECT COUNT(DISTINCT user_id) as count FROM test_history WHERE DATE(submitted_at) = ?");
                        $stmt->bind_param("s", $date);
                        break;
                    case 'logins':
                        // For demo, count users who took tests as proxy for login activity
                        $stmt = $conn->prepare("SELECT COUNT(DISTINCT user_id) as count FROM test_history WHERE DATE(submitted_at) = ?");
                        $stmt->bind_param("s", $date);
                        break;
                    case 'tests':
                        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM test_history WHERE DATE(submitted_at) = ?");
                        $stmt->bind_param("s", $date);
                        break;
                    case 'scores':
                        $stmt = $conn->prepare("SELECT AVG((score/total_marks)*100) as avg FROM test_history WHERE DATE(submitted_at) = ?");
                        $stmt->bind_param("s", $date);
                        break;
                    case 'content':
                        // For demo, use video_progress as content view proxy
                        $stmt = $conn->prepare("SELECT COUNT(DISTINCT user_id) as count FROM video_progress WHERE DATE(last_watched) = ?");
                        $stmt->bind_param("s", $date);
                        break;
                }
                
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                $values[] = round($result[array_keys($result)[0]] ?? 0);
                $stmt->close();
            }
            break;
            
        case 'day':
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $labels = $days;
            
            foreach ($days as $dayIndex => $dayName) {
                $dayNum = $dayIndex + 1; // MySQL: 1=Sunday, 7=Saturday
                
                switch ($yAxis) {
                    case 'users':
                        $result = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM test_history WHERE DAYOFWEEK(submitted_at) = $dayNum");
                        break;
                    case 'logins':
                        $result = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM test_history WHERE DAYOFWEEK(submitted_at) = $dayNum");
                        break;
                    case 'tests':
                        $result = $conn->query("SELECT COUNT(*) as count FROM test_history WHERE DAYOFWEEK(submitted_at) = $dayNum");
                        break;
                    case 'scores':
                        $result = $conn->query("SELECT AVG((score/total_marks)*100) as avg FROM test_history WHERE DAYOFWEEK(submitted_at) = $dayNum");
                        break;
                    case 'content':
                        $result = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM video_progress WHERE DAYOFWEEK(last_watched) = $dayNum");
                        break;
                }
                
                $row = $result->fetch_assoc();
                $values[] = round($row[array_keys($row)[0]] ?? 0);
            }
            break;
            
        case 'hour':
            for ($hour = 0; $hour < 24; $hour += 3) {
                $labels[] = sprintf("%02d:00", $hour);
                $nextHour = $hour + 3;
                
                switch ($yAxis) {
                    case 'users':
                        $result = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM test_history WHERE HOUR(submitted_at) >= $hour AND HOUR(submitted_at) < $nextHour");
                        break;
                    case 'logins':
                        $result = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM test_history WHERE HOUR(submitted_at) >= $hour AND HOUR(submitted_at) < $nextHour");
                        break;
                    case 'tests':
                        $result = $conn->query("SELECT COUNT(*) as count FROM test_history WHERE HOUR(submitted_at) >= $hour AND HOUR(submitted_at) < $nextHour");
                        break;
                    case 'scores':
                        $result = $conn->query("SELECT AVG((score/total_marks)*100) as avg FROM test_history WHERE HOUR(submitted_at) >= $hour AND HOUR(submitted_at) < $nextHour");
                        break;
                    case 'content':
                        $result = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM video_progress WHERE HOUR(last_watched) >= $hour AND HOUR(last_watched) < $nextHour");
                        break;
                }
                
                $row = $result->fetch_assoc();
                $values[] = round($row[array_keys($row)[0]] ?? 0);
            }
            break;
            
        case 'topic':
            $topics = $conn->query("SELECT topic_name FROM topics ORDER BY topic_name");
            while ($topic = $topics->fetch_assoc()) {
                $topicName = $topic['topic_name'];
                $labels[] = $topicName;
                
                switch ($yAxis) {
                    case 'users':
                        $stmt = $conn->prepare("SELECT COUNT(DISTINCT th.user_id) as count FROM test_history th JOIN tests t ON th.test_id = t.id WHERE t.topic = ?");
                        $stmt->bind_param("s", $topicName);
                        break;
                    case 'logins':
                        $stmt = $conn->prepare("SELECT COUNT(DISTINCT th.user_id) as count FROM test_history th JOIN tests t ON th.test_id = t.id WHERE t.topic = ?");
                        $stmt->bind_param("s", $topicName);
                        break;
                    case 'tests':
                        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM test_history th JOIN tests t ON th.test_id = t.id WHERE t.topic = ?");
                        $stmt->bind_param("s", $topicName);
                        break;
                    case 'scores':
                        $stmt = $conn->prepare("SELECT AVG((th.score/th.total_marks)*100) as avg FROM test_history th JOIN tests t ON th.test_id = t.id WHERE t.topic = ?");
                        $stmt->bind_param("s", $topicName);
                        break;
                    case 'content':
                        $stmt = $conn->prepare("SELECT COUNT(DISTINCT user_id) as count FROM video_progress vp JOIN content c ON vp.content_id = c.id WHERE c.topic = ?");
                        $stmt->bind_param("s", $topicName);
                        break;
                }
                
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                $values[] = round($result[array_keys($result)[0]] ?? 0);
                $stmt->close();
            }
            break;
    }
    
    echo json_encode(['success' => true, 'labels' => $labels, 'values' => $values, 'label' => $label]);
}
?>
