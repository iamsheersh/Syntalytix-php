<?php
// api/teacher.php
// Teacher API endpoints

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

header('Content-Type: application/json');

$user = getCurrentUser();
if (!$user || ($user['role_name'] !== 'Teacher' && $user['role_name'] !== 'Admin')) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$conn = getDBConnection();

switch ($action) {
    case 'get_my_content':
        getMyContent($conn, $user['id']);
        break;
    case 'get_my_tests':
        getMyTests($conn, $user['id']);
        break;
    case 'create_content':
        createContent($conn, $user['id']);
        break;
    case 'create_test':
        createTest($conn, $user['id']);
        break;
    case 'update_content':
        updateContent($conn, $user['id']);
        break;
    case 'update_test':
        updateTest($conn, $user['id']);
        break;
    case 'delete_content':
        deleteContent($conn, $user['id']);
        break;
    case 'delete_test':
        deleteTest($conn, $user['id']);
        break;
    case 'get_test_scores':
        getTestScores($conn, $user['id']);
        break;
    case 'get_topics':
        getTopics($conn);
        break;
    case 'get_test_questions':
        getTestQuestions($conn, $user['id']);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

$conn->close();

function getMyContent($conn, $userId) {
    $stmt = $conn->prepare("SELECT * FROM content WHERE uploader_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $content = [];
    while ($row = $result->fetch_assoc()) {
        $content[] = $row;
    }
    $stmt->close();
    echo json_encode(['success' => true, 'content' => $content]);
}

function getMyTests($conn, $userId) {
    $stmt = $conn->prepare("SELECT t.*, COUNT(q.id) as question_count FROM tests t LEFT JOIN questions q ON t.id = q.test_id WHERE t.creator_id = ? GROUP BY t.id ORDER BY t.created_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $tests = [];
    while ($row = $result->fetch_assoc()) {
        $tests[] = $row;
    }
    $stmt->close();
    echo json_encode(['success' => true, 'tests' => $tests]);
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

function updateContent($conn, $userId) {
    $id = $_POST['id'] ?? 0;
    $title = $_POST['title'] ?? '';
    $topic = $_POST['topic'] ?? '';
    $youtubeUrl = $_POST['youtubeUrl'] ?? '';
    $driveUrl = $_POST['driveUrl'] ?? '';
    
    // Verify ownership
    $stmt = $conn->prepare("SELECT uploader_id FROM content WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$result || $result['uploader_id'] != $userId) {
        echo json_encode(['success' => false, 'error' => 'Not authorized']);
        return;
    }
    
    $content_type = (!empty($youtubeUrl)) ? 'video' : 'pdf';
    
    $stmt = $conn->prepare("UPDATE content SET title = ?, topic = ?, youtube_url = ?, drive_url = ?, content_type = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $title, $topic, $youtubeUrl, $driveUrl, $content_type, $id);
    $success = $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => $success]);
}

function updateTest($conn, $userId) {
    $id = $_POST['id'] ?? 0;
    $test_name = $_POST['test_name'] ?? '';
    $topic = $_POST['topic'] ?? '';
    $questions = json_decode($_POST['questions'] ?? '[]', true);
    
    // Verify ownership
    $stmt = $conn->prepare("SELECT creator_id FROM tests WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$result || $result['creator_id'] != $userId) {
        echo json_encode(['success' => false, 'error' => 'Not authorized']);
        return;
    }
    
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

function getTestQuestions($conn, $userId) {
    $testId = $_GET['test_id'] ?? 0;
    
    // Verify ownership
    $stmt = $conn->prepare("SELECT creator_id FROM tests WHERE id = ?");
    $stmt->bind_param("i", $testId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$result || $result['creator_id'] != $userId) {
        echo json_encode(['success' => false, 'error' => 'Not authorized']);
        return;
    }
    
    // Get questions
    $stmt = $conn->prepare("SELECT * FROM questions WHERE test_id = ? ORDER BY id");
    $stmt->bind_param("i", $testId);
    $stmt->execute();
    $result = $stmt->get_result();
    $questions = [];
    while ($row = $result->fetch_assoc()) {
        $row['options'] = json_decode($row['options'], true);
        $row['correct_answers'] = json_decode($row['correct_answers'], true);
        // Handle single choice (correct_answer field)
        if (empty($row['correct_answers']) && !empty($row['correct_answer'])) {
            $row['correct_answers'] = [$row['correct_answer']];
        }
        $questions[] = $row;
    }
    $stmt->close();
    
    echo json_encode(['success' => true, 'questions' => $questions]);
}

function deleteContent($conn, $userId) {
    $id = $_POST['id'] ?? 0;
    
    // Verify ownership
    $stmt = $conn->prepare("SELECT uploader_id FROM content WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$result || $result['uploader_id'] != $userId) {
        echo json_encode(['success' => false, 'error' => 'Not authorized']);
        return;
    }
    
    $stmt = $conn->prepare("DELETE FROM content WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => $success]);
}

function deleteTest($conn, $userId) {
    $id = $_POST['id'] ?? 0;
    
    // Verify ownership
    $stmt = $conn->prepare("SELECT creator_id FROM tests WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$result || $result['creator_id'] != $userId) {
        echo json_encode(['success' => false, 'error' => 'Not authorized']);
        return;
    }
    
    $stmt = $conn->prepare("DELETE FROM tests WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => $success]);
}

function getTopics($conn) {
    $result = $conn->query("SELECT * FROM topics ORDER BY topic_name");
    $topics = [];
    while ($row = $result->fetch_assoc()) {
        $topics[] = $row;
    }
    echo json_encode(['success' => true, 'topics' => $topics]);
}

function getTestScores($conn, $userId) {
    $testId = $_GET['test_id'] ?? 0;
    
    // Verify test ownership
    $stmt = $conn->prepare("SELECT creator_id FROM tests WHERE id = ?");
    $stmt->bind_param("i", $testId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$result || $result['creator_id'] != $userId) {
        echo json_encode(['success' => false, 'error' => 'Not authorized']);
        return;
    }
    
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
