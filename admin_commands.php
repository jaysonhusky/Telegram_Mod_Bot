<?php
// admin_commands.php - Handles admin commands like approving/rejecting ads

// Handle approval of ad
function handleApproveAd($message, $chat_id) {
    $args = explode(' ', $message['text']);  // Get command arguments (e.g., /approve_ad <ad_id>)
    $ad_id = $args[1];  // Ad ID passed as argument

    if ($ad_id && updateAdStatus($ad_id, 'approved')) {  // From ad_functions.php
        $user_id = getUserIdByAdId($ad_id);  // Get user ID who submitted the ad
        notifyUserAboutAdStatus($user_id, 'approved');  // Notify user
        sendTelegramMessage($chat_id, "Ad approved successfully.");
    } else {
        sendTelegramMessage($chat_id, "Error: Unable to approve the ad.");
    }
}

// Handle rejection of ad
function handleRejectAd($message, $chat_id) {
    $args = explode(' ', $message['text']);
    $ad_id = $args[1];  // Ad ID passed as argument

    if ($ad_id && updateAdStatus($ad_id, 'rejected')) {  // From ad_functions.php
        $user_id = getUserIdByAdId($ad_id);  // Get user ID who submitted the ad
        notifyUserAboutAdStatus($user_id, 'rejected');  // Notify user
        sendTelegramMessage($chat_id, "Ad rejected successfully.");
    } else {
        sendTelegramMessage($chat_id, "Error: Unable to reject the ad.");
    }
}
