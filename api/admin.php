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
    case 'create_user':
        createUser($conn);
        break;
    case 'update_user':
        updateUser($conn);
        break;
    case 'create_content':
        createContent($conn, $user['id']);
        break;
    case 'update_content':
        updateContent($conn);
        break;
    case 'create_test':
        createTest($conn, $user['id']);
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
    case 'get_test_scores':
        getTestScores($conn);
        break;
    case 'get_questions':
        getQuestions($conn);
        break;
    case 'update_question':
        updateQuestion($conn);
        break;
    case 'add_question':
        addQuestion($conn);
        break;
    case 'delete_question':
        deleteQuestion($conn);
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

function createContent($conn, $userId) {
    $title = $_POST['title'] ?? '';
    $topic = $_POST['topic'] ?? '';
    $youtubeUrl = $_POST['youtubeUrl'] ?? '';
    $driveUrl = $_POST['driveUrl'] ?? '';
    
    $content_type = (!empty($youtubeUrl)) ? 'video' : 'pdf';
    
    $stmt = $conn->prepare("INSERT INTO content (uploader_id, title, topic, content_type, youtube_url, drive_url) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $userId, $title, $topic, $content_type, $youtubeUrl, $driveUrl);
    $success = $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();
    
    echo json_encode(['success' => $success, 'id' => $id]);
}

function createTest($conn, $userId) {
    $test_name = $_POST['test_name'] ?? '';
    $topic = $_POST['topic'] ?? '';
    $questions = json_decode($_POST['questions'] ?? '[]', true);
    
    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("INSERT INTO tests (creator_id, test_name, topic) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $userId, $test_name, $topic);
        $stmt->execute();
        $testId = $stmt->insert_id;
        $stmt->close();
        
        // Insert questions
        $stmt = $conn->prepare("INSERT INTO questions (test_id, question_text, question_type, options, correct_answer, correct_answers, marks) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($questions as $q) {
            $options = json_encode($q['options'] ?? []);
            $correctAnswers = json_encode($q['correct_answers'] ?? []);
            $stmt->bind_param("isssssi", $testId, $q['question_text'], $q['question_type'], $options, $q['correct_answer'], $correctAnswers, $q['marks']);
            $stmt->execute();
        }
        $stmt->close();
        
        $conn->commit();
        echo json_encode(['success' => true, 'id' => $testId]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function createUser($conn) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $role_id = $_POST['role_id'] ?? 3;
    $status = $_POST['status'] ?? 'Active';
    $password = $_POST['password'] ?? '';
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'error' => 'Email already exists']);
        return;
    }
    $stmt->close();
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role_id, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssis", $name, $email, $hashedPassword, $role_id, $status);
    $success = $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();
    
    echo json_encode(['success' => $success, 'id' => $id]);
}

function updateUser($conn) {
    $id = $_POST['id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $role_id = $_POST['role_id'] ?? 3;
    $status = $_POST['status'] ?? 'Active';
    $password = $_POST['password'] ?? '';
    
    // If password provided, update it too
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name = ?, role_id = ?, status = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sisssi", $name, $role_id, $status, $hashedPassword, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, role_id = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sisi", $name, $role_id, $status, $id);
    }
    
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
    $questions = json_decode($_POST['questions'] ?? '[]', true);
    
    $conn->begin_transaction();
    
    try {
        // Update test details
        $stmt = $conn->prepare("UPDATE tests SET test_name = ?, topic = ? WHERE id = ?");
        $stmt->bind_param("ssi", $test_name, $topic, $id);
        $stmt->execute();
        $stmt->close();
        
        // Delete existing questions and re-insert
        $stmt = $conn->prepare("DELETE FROM questions WHERE test_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        // Insert updated questions
        $stmt = $conn->prepare("INSERT INTO questions (test_id, question_text, question_type, options, correct_answer, correct_answers, marks) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($questions as $q) {
            $options = json_encode($q['options'] ?? []);
            $correctAnswers = json_encode($q['correct_answers'] ?? []);
            $stmt->bind_param("isssssi", $id, $q['question_text'], $q['question_type'], $options, $q['correct_answer'], $correctAnswers, $q['marks']);
            $stmt->execute();
        }
        $stmt->close();
        
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
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

function getQuestions($conn) {
    $testId = $_GET['test_id'] ?? 0;
    $stmt = $conn->prepare("SELECT * FROM questions WHERE test_id = ? ORDER BY id");
    $stmt->bind_param("i", $testId);
    $stmt->execute();
    $result = $stmt->get_result();
    $questions = [];
    while ($row = $result->fetch_assoc()) {
        $row['options'] = json_decode($row['options'], true);
        $row['correct_answers'] = json_decode($row['correct_answers'], true);
        $questions[] = $row;
    }
    $stmt->close();
    echo json_encode(['success' => true, 'questions' => $questions]);
}

function addQuestion($conn) {
    $testId = $_POST['test_id'] ?? 0;
    $questionText = $_POST['question_text'] ?? '';
    $questionType = $_POST['question_type'] ?? 'single_choice';
    $options = $_POST['options'] ?? '[]';
    $correctAnswer = $_POST['correct_answer'] ?? null;
    $correctAnswers = $_POST['correct_answers'] ?? '[]';
    $marks = $_POST['marks'] ?? 1;
    
    $stmt = $conn->prepare("INSERT INTO questions (test_id, question_text, question_type, options, correct_answer, correct_answers, marks) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssi", $testId, $questionText, $questionType, $options, $correctAnswer, $correctAnswers, $marks);
    $success = $stmt->execute();
    $questionId = $stmt->insert_id;
    $stmt->close();
    
    echo json_encode(['success' => $success, 'question_id' => $questionId]);
}

function updateQuestion($conn) {
    $id = $_POST['id'] ?? 0;
    $questionText = $_POST['question_text'] ?? '';
    $questionType = $_POST['question_type'] ?? 'single_choice';
    $options = $_POST['options'] ?? '[]';
    $correctAnswer = $_POST['correct_answer'] ?? null;
    $correctAnswers = $_POST['correct_answers'] ?? '[]';
    $marks = $_POST['marks'] ?? 1;
    
    $stmt = $conn->prepare("UPDATE questions SET question_text = ?, question_type = ?, options = ?, correct_answer = ?, correct_answers = ?, marks = ? WHERE id = ?");
    $stmt->bind_param("ssssiii", $questionText, $questionType, $options, $correctAnswer, $correctAnswers, $marks, $id);
    $success = $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => $success]);
}

function deleteQuestion($conn) {
    $id = $_POST['id'] ?? 0;
    
    $stmt = $conn->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => $success]);
}

function getTestScores($conn) {
    $testId = $_GET['test_id'] ?? 0;
    
    // Get test info
    $stmt = $conn->prepare("SELECT test_name, topic FROM tests WHERE id = ?");
    $stmt->bind_param("i", $testId);
    $stmt->execute();
    $testInfo = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Get all scores with student info
    $stmt = $conn->prepare("SELECT th.*, u.name, u.email FROM test_history th JOIN users u ON th.user_id = u.id WHERE th.test_id = ? ORDER BY th.submitted_at DESC");
    $stmt->bind_param("i", $testId);
    $stmt->execute();
    $result = $stmt->get_result();
    $scores = [];
    $totalScore = 0;
    $count = 0;
    $maxScore = 0;
    
    while ($row = $result->fetch_assoc()) {
        $scores[] = $row;
        $totalScore += $row['score'];
        $maxScore = max($maxScore, $row['score']);
        $count++;
    }
    $stmt->close();
    
    // Calculate statistics
    $avgScore = $count > 0 ? round($totalScore / $count, 2) : 0;
    $totalMarks = $count > 0 ? $scores[0]['total_marks'] : 0;
    
    // Get total questions count
    $stmt = $conn->prepare("SELECT COUNT(*) as q_count FROM questions WHERE test_id = ?");
    $stmt->bind_param("i", $testId);
    $stmt->execute();
    $qCount = $stmt->get_result()->fetch_assoc()['q_count'];
    $stmt->close();
    
    // Get questions
    $stmt = $conn->prepare("SELECT * FROM questions WHERE test_id = ? ORDER BY id");
    $stmt->bind_param("i", $testId);
    $stmt->execute();
    $resQ = $stmt->get_result();
    $questions = [];
    while ($rowQ = $resQ->fetch_assoc()) {
        $rowQ['options'] = json_decode($rowQ['options'], true);
        $rowQ['correct_answers'] = json_decode($rowQ['correct_answers'], true);
        $questions[] = $rowQ;
    }
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'test_info' => $testInfo,
        'scores' => $scores,
        'stats' => [
            'total_students' => $count,
            'avg_score' => $avgScore,
            'max_score' => $maxScore,
            'total_marks' => $totalMarks,
            'total_questions' => $qCount
        ],
        'questions' => $questions
    ]);
}
?>
