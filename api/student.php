<?php
// api/student.php
// Student API endpoints

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

header('Content-Type: application/json');

$user = getCurrentUser();
if (!$user || $user['role_name'] !== 'Student') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$conn = getDBConnection();

switch ($action) {
    case 'get_materials':
        getMaterials($conn);
        break;
    case 'get_tests':
        getTests($conn);
        break;
    case 'get_test':
        getTest($conn);
        break;
    case 'submit_test':
        submitTest($conn, $user['id']);
        break;
    case 'get_test_history':
        getTestHistory($conn, $user['id']);
        break;
    case 'get_topics':
        getTopics($conn);
        break;
    case 'get_test_result':
        getTestResult($conn, $user['id']);
        break;
    case 'save_video_progress':
        saveVideoProgress($conn, $user['id']);
        break;
    case 'get_video_progress':
        getVideoProgress($conn, $user['id']);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

$conn->close();

function getMaterials($conn) {
    $topic = $_GET['topic'] ?? null;
    
    if ($topic) {
        $stmt = $conn->prepare("SELECT * FROM content WHERE topic = ? AND published = 1 ORDER BY created_at DESC");
        $stmt->bind_param("s", $topic);
    } else {
        $stmt = $conn->prepare("SELECT * FROM content WHERE published = 1 ORDER BY created_at DESC");
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $materials = [];
    while ($row = $result->fetch_assoc()) {
        $materials[] = $row;
    }
    $stmt->close();
    echo json_encode(['success' => true, 'materials' => $materials]);
}

function getTests($conn) {
    $result = $conn->query("SELECT t.*, COUNT(q.id) as question_count FROM tests t LEFT JOIN questions q ON t.id = q.test_id GROUP BY t.id ORDER BY t.created_at DESC");
    $tests = [];
    while ($row = $result->fetch_assoc()) {
        $tests[] = $row;
    }
    echo json_encode(['success' => true, 'tests' => $tests]);
}

function getTest($conn) {
    $testId = $_GET['id'] ?? 0;
    
    $stmt = $conn->prepare("SELECT * FROM tests WHERE id = ?");
    $stmt->bind_param("i", $testId);
    $stmt->execute();
    $test = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$test) {
        echo json_encode(['success' => false, 'error' => 'Test not found']);
        return;
    }
    
    $stmt = $conn->prepare("SELECT * FROM questions WHERE test_id = ?");
    $stmt->bind_param("i", $testId);
    $stmt->execute();
    $result = $stmt->get_result();
    $questions = [];
    while ($row = $result->fetch_assoc()) {
        $row['options'] = json_decode($row['options'], true);
        unset($row['correct_answer']);
        unset($row['correct_answers']);
        $questions[] = $row;
    }
    $stmt->close();
    
    $test['questions'] = $questions;
    echo json_encode(['success' => true, 'test' => $test]);
}

function submitTest($conn, $userId) {
    $testId = $_POST['test_id'] ?? 0;
    $answers = json_decode($_POST['answers'] ?? '{}', true);
    
    // Get questions with correct answers
    $stmt = $conn->prepare("SELECT * FROM questions WHERE test_id = ?");
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
    
    $score = 0;
    $totalMarks = 0;
    $processedAnswers = [];
    
    foreach ($questions as $q) {
        $totalMarks += $q['marks'];
        $userAnswer = $answers[$q['id']] ?? null;
        $isCorrect = false;
        
        if ($q['question_type'] === 'checkbox') {
            $correctAnswers = $q['correct_answers'] ?? [];
            if (is_array($userAnswer)) {
                sort($userAnswer);
                sort($correctAnswers);
                $isCorrect = $userAnswer == $correctAnswers;
            }
        } else {
            $isCorrect = $userAnswer == $q['correct_answer'];
        }
        
        if ($isCorrect) {
            $score += $q['marks'];
        }
        
        $processedAnswers[$q['id']] = [
            'user_answer' => $userAnswer,
            'is_correct' => $isCorrect,
            'correct_answer' => $q['correct_answer'],
            'correct_answers' => $q['correct_answers']
        ];
    }
    
    // Save test history
    $answersJson = json_encode($processedAnswers);
    $stmt = $conn->prepare("INSERT INTO test_history (user_id, test_id, score, total_marks, answers) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiis", $userId, $testId, $score, $totalMarks, $answersJson);
    $stmt->execute();
    $historyId = $stmt->insert_id;
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'score' => $score,
        'total_marks' => $totalMarks,
        'history_id' => $historyId,
        'questions' => $questions,
        'answers' => $processedAnswers
    ]);
}

function getTestHistory($conn, $userId) {
    $stmt = $conn->prepare("SELECT th.*, t.test_name, t.topic FROM test_history th JOIN tests t ON th.test_id = t.id WHERE th.user_id = ? ORDER BY th.submitted_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    $stmt->close();
    echo json_encode(['success' => true, 'history' => $history]);
}

function getTestResult($conn, $userId) {
    $historyId = $_GET['history_id'] ?? 0;
    
    $stmt = $conn->prepare("SELECT th.*, t.test_name FROM test_history th JOIN tests t ON th.test_id = t.id WHERE th.id = ? AND th.user_id = ?");
    $stmt->bind_param("ii", $historyId, $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$result) {
        echo json_encode(['success' => false, 'error' => 'Result not found']);
        return;
    }
    
    $result['answers'] = json_decode($result['answers'], true);
    echo json_encode(['success' => true, 'result' => $result]);
}

function saveVideoProgress($conn, $userId) {
    $contentId = $_POST['content_id'] ?? 0;
    $progressSeconds = $_POST['progress_seconds'] ?? 0;
    $totalSeconds = $_POST['total_seconds'] ?? 0;
    $completed = $_POST['completed'] ?? false;
    
    $stmt = $conn->prepare("INSERT INTO video_progress (user_id, content_id, progress_seconds, total_seconds, completed) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE progress_seconds = ?, total_seconds = ?, completed = ?");
    $completedVal = $completed ? 1 : 0;
    $stmt->bind_param("iiiisiiis", $userId, $contentId, $progressSeconds, $totalSeconds, $completedVal, $progressSeconds, $totalSeconds, $completedVal);
    $success = $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => $success]);
}

function getVideoProgress($conn, $userId) {
    $contentId = $_GET['content_id'] ?? 0;
    
    $stmt = $conn->prepare("SELECT * FROM video_progress WHERE user_id = ? AND content_id = ?");
    $stmt->bind_param("ii", $userId, $contentId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    echo json_encode(['success' => true, 'progress' => $result]);
}

function getTopics($conn) {
    $result = $conn->query("SELECT * FROM topics ORDER BY topic_name");
    $topics = [];
    while ($row = $result->fetch_assoc()) {
        $topics[] = $row;
    }
    echo json_encode(['success' => true, 'topics' => $topics]);
}
?>
