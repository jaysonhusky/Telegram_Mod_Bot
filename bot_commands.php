<?php
// bot_commands.php - Handles general bot commands like /adstatus

// Function to handle /adstatus command
function handleAdStatus($user_id, $chat_id) {
    // Get ad ID associated with the user
    $ad_id = getAdIdByUserId($user_id);  // From ad_functions.php

    if ($ad_id) {
        // Get the status of the ad
        $status = getAdStatus($ad_id);  // From ad_functions.php
        sendTelegramMessage($chat_id, "Your ad status is: " . ucfirst($status));
    } else {
        sendTelegramMessage($chat_id, "You have no active ads.");
    }
}