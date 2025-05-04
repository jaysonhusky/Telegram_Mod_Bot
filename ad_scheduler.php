<?php
// ad_scheduler.php - Cron job to send scheduled repeat ads
require_once 'config.php';
require_once 'db.php';

$now = date('Y-m-d H:i:s');

$stmt = $pdo->prepare("SELECT * FROM ad_submissions WHERE is_repeat = 1 AND next_send <= ? AND send_count < 5");
$stmt->execute([$now]);
$ads = $stmt->fetchAll();

foreach ($ads as $ad) {
    $chat_id = $ad['target_group_id'];
    $user_id = $ad['user_id'];
    $message = $ad['ad_text'];
    $images = json_decode($ad['images'], true);

    if (!empty($images)) {
        $media = [];
        foreach ($images as $i => $img) {
            $media[] = [
                'type' => 'photo',
                'media' => TELEGRAM_FILE_URL . "/ads/{$user_id}/{$img}",
                'caption' => $i === 0 ? $message : null
            ];
        }
        apiRequest("sendMediaGroup", [
            'chat_id' => $chat_id,
            'media' => json_encode($media)
        ]);
    } else {
        apiRequest("sendMessage", [
            'chat_id' => $chat_id,
            'text' => $message
        ]);
    }

    // Update ad record
    $update = $pdo->prepare("UPDATE ad_submissions SET send_count = send_count + 1, next_send = DATE_ADD(NOW(), INTERVAL 1 DAY) WHERE id = ?");
    $update->execute([$ad['id']]);
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
