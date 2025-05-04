<?php
// dashboard.php - Admin Dashboard
session_start();

// Check if user is logged in as an admin
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}
require_once 'config.php';
require_once 'db.php';

session_start();

// Ensure only admins can access the dashboard
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Fetch all ads submitted in the last 7 days
function getRecentAds() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM ad_submissions WHERE created_at > NOW() - INTERVAL 7 DAY");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Fetch all abuse reports
function getAbuseReports() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM abuse_reports ORDER BY reported_at DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Fetch all users and their ad counts in the last 7 days
function getUsersWithAdCounts() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT u.user_id, u.username, COUNT(a.id) as ad_count
                           FROM users u
                           LEFT JOIN ad_submissions a ON u.user_id = a.user_id
                           WHERE a.created_at > NOW() - INTERVAL 7 DAY
                           GROUP BY u.user_id");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Mark user as banned
function banUser($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO banned_users (user_id, banned_at) VALUES (?, NOW())");
    $stmt->execute([$user_id]);
    return true;
}

// Unban user
function unbanUser($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM banned_users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return true;
}

// Handle action requests
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $user_id = $_GET['user_id'] ?? null;

    switch ($action) {
        case 'ban':
            if ($user_id) {
                banUser($user_id);
                header('Location: dashboard.php?message=User+has+been+banned');
                exit;
            }
            break;

        case 'unban':
            if ($user_id) {
                unbanUser($user_id);
                header('Location: dashboard.php?message=User+has+been+unbanned');
                exit;
            }
            break;

        default:
            header('Location: dashboard.php');
            exit;
    }
}

// Fetch necessary data for the dashboard
$ads = getRecentAds();
$abuse_reports = getAbuseReports();
$users_with_ads = getUsersWithAdCounts();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Telegram Bot Admin Dashboard</h1>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2>Recent Ads (Last 7 Days)</h2>
        <table>
            <thead>
                <tr>
                    <th>Ad ID</th>
                    <th>User ID</th>
                    <th>Text</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ads as $ad): ?>
                    <tr>
                        <td><?= $ad['id'] ?></td>
                        <td><?= $ad['user_id'] ?></td>
                        <td><?= htmlspecialchars($ad['text']) ?></td>
                        <td><?= $ad['created_at'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Abuse Reports</h2>
        <table>
            <thead>
                <tr>
                    <th>Report ID</th>
                    <th>User ID</th>
                    <th>Chat ID</th>
                    <th>Reported At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($abuse_reports as $report): ?>
                    <tr>
                        <td><?= $report['id'] ?></td>
                        <td><?= $report['user_id'] ?></td>
                        <td><?= $report['chat_id'] ?></td>
                        <td><?= $report['reported_at'] ?></td>
                        <td>
                            <a href="dashboard.php?action=ban&user_id=<?= $report['user_id'] ?>">Ban</a> | 
                            <a href="dashboard.php?action=unban&user_id=<?= $report['user_id'] ?>">Unban</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Users with Ads in Last 7 Days</h2>
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Ads Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users_with_ads as $user): ?>
                    <tr>
                        <td><?= $user['user_id'] ?></td>
                        <td><?= $user['username'] ?></td>
                        <td><?= $user['ad_count'] ?></td>
                        <td>
                            <a href="dashboard.php?action=ban&user_id=<?= $user['user_id'] ?>">Ban</a> | 
                            <a href="dashboard.php?action=unban&user_id=<?= $user['user_id'] ?>">Unban</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>