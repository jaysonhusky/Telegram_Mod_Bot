<?php
// ad_flow.php - Handles /ad command logic via private messages
require_once 'db.php';
require_once 'config.php';

session_start();

$user_id = $_POST['user_id'] ?? null;
$text = $_POST['text'] ?? '';

if (!$user_id) exit;

$session_file = __DIR__ . "/session/{$user_id}.json";
$session = file_exists($session_file) ? json_decode(file_get_contents($session_file), true) : [];

function resetAdSession($user_id) {
    $file = __DIR__ . "/session/{$user_id}.json";
    if (file_exists($file)) unlink($file);
}

function saveAdSession($user_id, $data) {
    file_put_contents(__DIR__ . "/session/{$user_id}.json", json_encode($data));
}

function sendMessage($chat_id, $text) {
    apiRequest("sendMessage", ['chat_id' => $chat_id, 'text' => $text]);
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

// Cancel command
if (strtolower($text) === '/cancel') {
    resetAdSession($user_id);
    sendMessage($user_id, "❌ Ad submission canceled.");
    exit;
}

// Start
if (empty($session)) {
    $rules = $pdo->query("SELECT rule_text FROM ad_rules")->fetchAll(PDO::FETCH_COLUMN);
    sendMessage($user_id, "📋 Ad Submission Rules:\n" . implode("\n", $rules) . "\n\nPlease send your ad text (max 2000 characters):");
    saveAdSession($user_id, ['step' => 'text']);
    exit;
}

switch ($session['step']) {
    case 'text':
        if (mb_strlen($text) > 2000) {
            sendMessage($user_id, "🚫 Your message is too long. Please limit it to 2000 characters or type /cancel.");
            exit;
        }
        $session['text'] = $text;
        $session['step'] = 'images';
        $session['images'] = [];
        sendMessage($user_id, "🖼 Please send up to 4 images. Type /done when finished or /cancel to abort.");
        break;

    case 'images':
        if (strtolower($text) === '/done') {
            $session['step'] = 'repeat';
            sendMessage($user_id, "🔁 Do you want a one-time or repeating ad? Type 'one-time' or 'repeat'.");
            break;
        }

        if (isset($_FILES['photo'])) {
            $upload_dir = __DIR__ . "/ads/{$user_id}";
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $tmp_name = $_FILES['photo']['tmp_name'];
            $filename = basename($_FILES['photo']['name']);
            $dest = "$upload_dir/$filename";

            if (move_uploaded_file($tmp_name, $dest)) {
                $session['images'][] = $filename;

                if (count($session['images']) >= 4) {
                    $session['step'] = 'repeat';
                    sendMessage($user_id, "✅ 4 images received. Type 'one-time' or 'repeat' to continue.");
                } else {
                    sendMessage($user_id, "📸 Image received (" . count($session['images']) . "/4). Send more or type /done.");
                }
            } else {
                sendMessage($user_id, "❌ Failed to upload image. Try again.");
            }
        } else {
            sendMessage($user_id, "❌ No image detected. Please attach an image or type /done.");
        }
        break;

    case 'repeat':
        $choice = strtolower(trim($text));
        if (!in_array($choice, ['one-time', 'repeat'])) {
            sendMessage($user_id, "Please type 'one-time' or 'repeat'.");
            exit;
        }
        $session['is_repeat'] = $choice === 'repeat';
        $session['step'] = 'confirm';
        sendMessage($user_id, "✍️ Please type 'I agree to the terms' to confirm your ad submission.");
        break;

    case 'confirm':
        if (stripos($text, 'i agree to the terms') === false) {
            sendMessage($user_id, "❗ You must type 'I agree to the terms' to finish submission.");
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO ad_submissions (user_id, ad_text, images, is_repeat, send_count, next_send, created_at) VALUES (?, ?, ?, ?, 0, NOW(), NOW())");
        $stmt->execute([
            $user_id,
            $session['text'],
            json_encode($session['images']),
            $session['is_repeat']
        ]);

        resetAdSession($user_id);
        sendMessage($user_id, "✅ Your ad has been submitted successfully!");
        break;

    default:
        resetAdSession($user_id);
        sendMessage($user_id, "⚠️ An error occurred. Please start again with /ad.");
}

saveAdSession($user_id, $session);
