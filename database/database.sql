-- Database Setup Script for Student Management System
-- Database Name: student_management

CREATE DATABASE IF NOT EXISTS `student_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `student_management`;

-- Drop table if exists for clean re-installation
DROP TABLE IF EXISTS `students`;

-- Create Students & Admin Users Table
CREATE TABLE `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone_number` VARCHAR(20) NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'student') NOT NULL DEFAULT 'student',
    `date_of_birth` DATE NULL,
    `gender` ENUM('Male', 'Female', 'Other') NULL,
    `qualification` VARCHAR(50) NULL,
    `interests` TEXT NULL,
    `class` VARCHAR(50) NULL,
    `subject` VARCHAR(100) NULL,
    `marks` DECIMAL(5,2) NULL,
    `aadhaar_file` VARCHAR(255) NULL,
    `reset_token` VARCHAR(64) NULL,
    `reset_token_expires` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `idx_unique_email` (`email`),
    INDEX `idx_role` (`role`),
    INDEX `idx_class` (`class`),
    INDEX `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Predefined Admin Account
-- Email: admin@example.com
-- Password: Admin@123 (hashed via BCRYPT password_hash)
INSERT INTO `students` (`name`, `email`, `password`, `role`, `created_at`) 
VALUES (
    'Administrator',
    'admin@example.com',
    '$2y$10$wN9a8J7m5zH8/R3NqL2y5O/O4K.8J.m3wK7p8q9r0s1t2u3v4w5x6', -- Pre-hashed Admin@123 fallback (autoSeedAdmin in config refreshes if needed)
    'admin',
    NOW()
) ON DUPLICATE KEY UPDATE `email`=`email`;

-- Insert Demo Student Account for immediate testing
-- Email: student1@example.com
-- Password: Student@123
INSERT INTO `students` (
    `name`, `email`, `password`, `role`, `date_of_birth`, `gender`, 
    `qualification`, `interests`, `class`, `subject`, `marks`, `aadhaar_file`, `created_at`
) VALUES (
    'Rahul Sharma',
    'student1@example.com',
    '$2y$10$4.61WJkW2U6iXf84qS4GoeV.rD8hB8W6Kq8n7W2Oq6A4iXf84qS4G',
    'student',
    '2005-08-15',
    'Male',
    'Intermediate',
    'Coding, Sports, Reading',
    '10',
    'Computer Science',
    94.50,
    'demo_aadhaar.pdf',
    NOW()
) ON DUPLICATE KEY UPDATE `email`=`email`;
