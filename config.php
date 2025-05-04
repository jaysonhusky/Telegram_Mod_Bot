<?php
// config.php - Configuration file for the bot

// Telegram Bot API URL
define('TELEGRAM_API_URL', 'https://api.telegram.org/botYOUR_BOT_TOKEN/');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'telegram_bot');
define('DB_USER', 'bot_user');
define('DB_PASS', 'your_password');
define('DB_CHARSET', 'utf8mb4');

// Error Logging Configuration
define('ERROR_LOG_FILE', __DIR__ . '/logs/error.log');

// Abuse Reporting Configuration
define('ABUSE_REPORT_EMAIL', 'admin@example.com');

// Folder locations
define('SESSION_DIR', __DIR__ . '/session'); // User session data folder
define('AD_IMAGES_DIR', __DIR__ . '/ads'); // Folder to store ad images