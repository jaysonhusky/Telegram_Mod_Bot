<?php
// abuse_monitoring.php - Handles abuse monitoring and input validation

require_once 'db.php';
require_once 'config.php';

// Track abuse patterns (rate-limiting, excessive sticker use, etc.)
function checkAbusePatterns($user_id, $chat_id) {
    // Example: Too many messages in a short time
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE user_id = ? AND chat_id = ? AND created_at > NOW() - INTERVAL 1 MINUTE");
    $stmt->execute([$user_id, $chat_id]);
    $count = $stmt->fetchColumn();

    if ($count > 5) {
        // Mark as potential abuse
        markAsAbuse($user_id, $chat_id);
    }
}

// Mark user as abuser and take action (e.g., mute for 24 hours)
function markAsAbuse($user_id, $chat_id) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO abuse_reports (user_id, chat_id, reported_at) VALUES (?, ?, NOW())");
    $stmt->execute([$user_id, $chat_id]);

    // Mute user for 24 hours
    apiRequest('restrictChatMember', [
        'chat_id' => $chat_id,
        'user_id' => $user_id,
        'until_date' => time() + 86400, // 24 hours
        'permissions' => ['can_send_messages' => false]
    ]);

    apiRequest('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "⚠️ User @$user_id has been muted due to suspected abuse (excessive messages)."
    ]);
}

// Validate input (e.g., reject messages over 2000 characters, reject sticker spam)
function validateInput($text, $type) {
    // Example validation: reject messages over 2000 characters
    if ($type === 'text' && mb_strlen($text) > 2000) {
        return "Your message is too long. Please limit it to 2000 characters.";
    }

    // Example validation: reject stickers in quick succession (spam)
    if ($type === 'sticker') {
        return "You are sending too many stickers in a row. Please take a break.";
    }

    return null; // No validation errors
}

// Usage example
// Check abuse patterns when a new message is received
if ($message_type === 'text' || $message_type === 'sticker') {
    $validation_result = validateInput($text, $message_type);
    if ($validation_result) {
        // Send feedback to user
        apiRequest('sendMessage', [
            'chat_id' => $chat_id,
            'text' => $validation_result
        ]);
        exit;
    }

    // Check abuse patterns
    checkAbusePatterns($user_id, $chat_id);
}

function apiRequest($method, $parameters) {
    $url = TELEGRAM_API_URL . $method;
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);
    curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($parameters));

    $response = curl_exec($handle);
    curl_close($handle);
    return $response;
}
