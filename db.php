<?php
// db.php - Database connection handler

$host = 'localhost';
$db   = 'telegram_bot';
$user = 'bot_user';
$pass = 'your_password';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    http_response_code(500);
    exit('Database connection failed.');
}

// Example function to get user's ad count in last 7 days
function getUserAdCount($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ad_submissions WHERE user_id = ? AND created_at > NOW() - INTERVAL 7 DAY");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

// Example function to check if user is banned
function isUserBanned($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT 1 FROM banned_users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return (bool)$stmt->fetch();
}
