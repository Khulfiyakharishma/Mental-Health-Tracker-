-- db.sql
CREATE DATABASE IF NOT EXISTS mental_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mental_tracker;

-- users table
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  dob DATE NULL,
  gender VARCHAR(20) NULL,
  interests TEXT NULL,
  friend_phone VARCHAR(30) NULL, -- phone number to notify
  signup_answers TEXT NULL, -- JSON or text of question answers
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- journals table (daily entries)
CREATE TABLE journals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  entry_date DATE NOT NULL,
  mood ENUM('happy','neutral','sad') NOT NULL,
  mood_score INT NOT NULL, -- numeric score derived, e.g., happy=2 neutral=1 sad=0
  text TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- mood_summary (cached daily/weekly summary; optional)
CREATE TABLE mood_summary (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  period_start DATE NOT NULL,
  period_end DATE NOT NULL,
  avg_mood_score FLOAT NOT NULL,
  mood_counts JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
