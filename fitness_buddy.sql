CREATE DATABASE fitness_buddy;
USE fitness_buddy;

CREATE TABLE users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    membership_tier VARCHAR(20) DEFAULT 'free',
    profile_completed TINYINT(1) DEFAULT 0 COMMENT 'Indicates whether user has completed their profile',
);

CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
);

CREATE TABLE user_profiles (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    fitness_goals VARCHAR(255) DEFAULT NULL,
    experience_level VARCHAR(50) DEFAULT NULL,
    workout_types VARCHAR(255) DEFAULT NULL,
    availability VARCHAR(255) DEFAULT NULL,
    gym_location VARCHAR(255) DEFAULT NULL,
    share_location TINYINT(1) DEFAULT 0,
    bio TEXT DEFAULT NULL,
    membership_tier VARCHAR(50) DEFAULT 'free',
    created_at DATETIME DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    profile_picture VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
