<?php
// auth.php - Telegram Login Authentication Handler

require_once 'config.php';
require_once 'db.php';

session_start();

// Check if all required parameters are present in the GET request
if (isset($_POST['id']) && isset($_POST['first_name']) && isset($_POST['username']) && isset($_POST['auth_date']) && isset($_POST['hash'])) {

    // Verify the authenticity of the request
    $data = [
        'id' => $_POST['id'],
        'first_name' => $_POST['first_name'],
        'username' => $_POST['username'],
        'auth_date' => $_POST['auth_date'],
        'hash' => $_POST['hash']
    ];

    // Prepare the data for signature verification
    $secret = hash('SHA256', $botToken, true);
    $check_hash = hash_hmac('sha256', http_build_query($data), $secret);

    // Check if the received hash matches the calculated hash
    if ($_POST['hash'] !== $check_hash) {
        die('Invalid request');
    }

    // Authentication passed, user is logged in via Telegram
    // Store user details in session
    $_SESSION['user_id'] = $_POST['id'];
    $_SESSION['first_name'] = $_POST['first_name'];
    $_SESSION['username'] = $_POST['username'];
    $_SESSION['admin_logged_in'] = true;

    // Redirect to the admin dashboard
    header('Location: dashboard.php');
    exit;

} else {
    // Invalid request, redirect back to the login page
    header('Location: login.php');
    exit;
}
?>
