-- Create the database (if not already created)
CREATE DATABASE IF NOT EXISTS telegram_bot;
USE telegram_bot;

-- Table to store user data and ad submissions
CREATE TABLE IF NOT EXISTS ad_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table to store ad images
CREATE TABLE IF NOT EXISTS ad_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ad_id) REFERENCES ad_submissions(id) ON DELETE CASCADE
);

-- Table for banned users (for abuse or other reasons)
CREATE TABLE IF NOT EXISTS banned_users (
    user_id INT PRIMARY KEY,
    banned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reason VARCHAR(255) DEFAULT 'Violation of terms'
);

-- Table to track user activity and abuses (optional for abuse detection)
CREATE TABLE IF NOT EXISTS user_activity (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table to store ad slots (optional, for managing repeating ads)
CREATE TABLE IF NOT EXISTS ad_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    slot_type ENUM('one_off', 'repeating') DEFAULT 'one_off',
    start_date DATE,
    end_date DATE,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES ad_submissions(user_id)
);

-- Table to track admins (useful for admin-based commands)
CREATE TABLE IF NOT EXISTS admins (
    user_id INT PRIMARY KEY
);

-- Example insert statements to initialize some admin users
INSERT INTO admins (user_id) VALUES (123456789);  -- Replace with actual admin Telegram user IDs
INSERT INTO admins (user_id) VALUES (987654321);

-- Optionally, if you're tracking when ads are sent to users, add an 'ad_sent' log table (this can be useful for scheduled ads)
CREATE TABLE IF NOT EXISTS ad_sent_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_id INT NOT NULL,
    user_id INT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ad_id) REFERENCES ad_submissions(id),
    FOREIGN KEY (user_id) REFERENCES ad_submissions(user_id)
);
