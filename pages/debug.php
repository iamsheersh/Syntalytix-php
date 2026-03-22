<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Step 1: Starting...<br>";

require_once __DIR__ . '/../config/database.php';
echo "Step 2: Database config loaded<br>";

$conn = getDBConnection();
if ($conn) {
    echo "Step 3: Database connected<br>";
} else {
    echo "Step 3: Database connection FAILED<br>";
}

require_once __DIR__ . '/../includes/session.php';
echo "Step 4: Session loaded<br>";

if (isLoggedIn()) {
    echo "Step 5: User is logged in, ID: " . $_SESSION['user_id'] . "<br>";
    $user = getCurrentUser();
    if ($user) {
        echo "Step 6: User loaded: " . $user['name'] . " (" . $user['role_name'] . ")<br>";
    } else {
        echo "Step 6: User data NOT loaded<br>";
    }
} else {
    echo "Step 5: User is NOT logged in<br>";
}

echo "<br><a href='login.php'>Go to Login</a>";
?>
