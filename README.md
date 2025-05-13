# Telegram Bot for Multi-Tenant Ad Management and Abuse Monitoring

## Overview
This project is a **Telegram bot** designed to help manage ads in multiple Telegram groups (multi-tenancy). The bot supports ad submission, approval workflows, user banning, abuse monitoring, and more, with an easy-to-use admin interface. The codebase can support multiple groups with isolated data for each group, ensuring that admins and users are kept separate.

### Features:
- **Ad Submission Workflow**: Users can submit ads with a title, description, and up to four images.
- **Ad Approval/Rejection**: Admins can approve or reject ads, with users being notified of the status.
- **Abuse Monitoring**: The bot tracks and limits excessive sticker usage or spam, and users who violate the rules can be banned for a period of time.
- **Multi-Tenant Support**: Multiple Telegram groups can use the same bot, and data is kept isolated for each group.
- **Admin Commands**: Admins can manage ads, view logs, and manage user activity across their group.
- **Ad Status Tracking**: Users can check the status of their ads via `/adstatus` in private chat.
- **Database**: All data (ads, admins, banned users, activity logs, etc.) is stored in a central MySQL database.

---

## Installation

### Prerequisites:
- PHP 7.4 or later
- MySQL or MariaDB database
- Composer (for managing dependencies)
- A web server like Apache or Nginx (for handling HTTP requests)

### 1. Clone the repository:
```bash
git clone https://github.com/yourusername/telegram-bot.git
cd telegram-bot
```

### 2. Install dependencies:
Run the following command to install necessary PHP packages:

```bash
composer install
```
### 3. Configure the database:
Set up the database by running the provided SQL script:

```bash
mysql -u your_username -p telegram_bot < setup_database.sql
```
Update the database connection settings in the config.php file:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'telegram_bot');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
```
### 4. Set up the Telegram bot:
Create a new bot on Telegram by chatting with the BotFather.

After creating the bot, you'll receive a Bot Token.

Update the config.php with the Bot Token:

```php
define('BOT_TOKEN', 'your_bot_token_here');
```
### 5. Set up webhook:
You need to configure a webhook for the bot to interact with your server. Use the following URL structure:

```arduino
https://your-server.com/webhook.php
```
This is done by sending the following API request to Telegram:

```bash
https://api.telegram.org/bot<your_bot_token>/setWebhook?url=https://your-server.com/webhook.php
```

### 6. (Optional) Set up a cron job for scheduled tasks:
If you're handling recurring ads or other scheduled tasks, you may want to set up a cron job to run every minute or so to handle background operations.

Example cron job for checking pending ads:

```bash
* * * * * /usr/bin/php /path/to/your/project/scheduled_task.php
```

## Configuration
### 1. Multi-Tenancy Setup
The bot supports multiple groups (tenants). Each group can have its own admins, ads, and user data. Group data is isolated using a group_id field in the database. Admins are linked to a specific group, and only they can manage their group’s data.

### 2. Admin Setup
Admins must be manually added to the database with their Telegram user IDs and group IDs:

```sql
INSERT INTO admins (user_id, group_id) VALUES (123456789, 1);
```
Replace 123456789 with the Telegram user ID of the admin and 1 with the appropriate group_id.

### Usage
#### 1. User Commands
/ad: Submit an ad. This will prompt the user to enter the title, description, images (up to 4), and confirm agreement to the terms.

/adstatus: Check the status of a submitted ad (only works in private chat with the bot).

/cancel: Cancel the ad submission process.

#### 2. Admin Commands
/approve_ad <ad_id>: Approve an ad submission.

/reject_ad <ad_id>: Reject an ad submission.

/ban_user <user_id>: Ban a user from submitting ads for a specified period.

/unban_user <user_id>: Unban a user.

/user_activity <user_id>: View user activity logs.

/adlist: List all ads for the group.

/adstatus <ad_id>: View the status of a particular ad.

#### 3. Abuse Monitoring:
The bot will monitor for abuse, such as excessive sticker usage, and will automatically ban users for violating the rules (e.g., sending too many stickers).

## Database Schema
Tables:
ad_submissions: Stores ads and their statuses.

ad_images: Stores images uploaded with ads.

banned_users: Tracks banned users.

user_activity: Logs user actions and behaviors for monitoring.

ad_slots: Stores configurations for recurring ads.

admins: Contains the list of admin users for each group.

ad_sent_log: Logs when ads are sent to users.

## Security Considerations
Input Validation:
All user inputs (ad descriptions, images, etc.) are validated to ensure they do not exceed the character limits or violate other rules.

Users who violate the rules (e.g., spamming stickers) are flagged and banned as necessary.

### Admin Authentication:
Admins are identified by their Telegram user IDs. Commands that require admin privileges will verify that the user executing them is an admin for the relevant group.

## Future Improvements
Multi-Language Support: Adding multi-language capabilities for a more global audience.

Analytics Dashboard: An admin dashboard for managing ads, users, and analytics.

Payment Integration: Add options for monetizing ads with payment gateway integration.

# License
This project is licensed under the MIT License - see the LICENSE file for details.

# Support
For issues or feature requests, please open an issue in the GitHub repository. This is currently not recommended for deployment as can false-flag as malware.
