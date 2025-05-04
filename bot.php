<?php
require_once 'config.php';
require_once 'db.php';
include_once 'includes/ad_functions.php';  // Functions to handle ads
include_once 'includes/admin_commands.php';  // Admin commands like approving/rejecting ads
include_once 'includes/bot_commands.php';  // General bot commands like /adstatus

$update = json_decode(file_get_contents('php://input'), true);

if (!$update) {
    exit("No input received");
}

$chat_id = $update['message']['chat']['id'] ?? null;
$user_id = $update['message']['from']['id'] ?? null;
$text = $update['message']['text'] ?? '';
$message_id = $update['message']['message_id'] ?? null;

// Handle @admin mentions in groups
if (isset($update['message']['entities'])) {
    foreach ($update['message']['entities'] as $entity) {
        if ($entity['type'] === 'mention') {
            $mention = substr($text, $entity['offset'], $entity['length']);
            if (strtolower($mention) === '@admin') {
                $alert_chat_id = ALERT_CHAT_ID; // defined in config.php
                $user_name = $update['message']['from']['username'] ?? 'unknown';
                $forwarded = isset($update['message']['reply_to_message']);

                apiRequest("sendMessage", [
                    'chat_id' => $alert_chat_id,
                    'text' => "🚨 @admin mentioned in group $chat_id by @$user_name."
                ]);

                if ($forwarded) {
                    apiRequest("forwardMessage", [
                        'chat_id' => $alert_chat_id,
                        'from_chat_id' => $chat_id,
                        'message_id' => $update['message']['reply_to_message']['message_id']
                    ]);
                }
                break;
            }
        }
    }
}

// Handle commands
if ($text === '/start') {
    apiRequest("sendMessage", [
        'chat_id' => $chat_id,
        'text' => "👋 Hello! I'm your group assistant bot. Use /ad to submit an advert in private chat."
    ]);
    exit;
}

if ($text === '/ad' && $chat_id === $user_id) {
    // Begin ad flow (to be implemented)
    apiRequest("sendMessage", [
        'chat_id' => $chat_id,
        'text' => "📢 Let's create an advert! (Feature under development)"
    ]);
    exit;
}

   // Handle other commands (e.g., /approve_ad, /reject_ad, etc.)
   if ($text == '/approve_ad' && isAdmin($from_id)) {
    handleApproveAd($message, $chat_id);
}

if ($text == '/reject_ad' && isAdmin($from_id)) {
    handleRejectAd($message, $chat_id);
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
