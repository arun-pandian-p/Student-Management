-- ============================================================
-- Student Management System Database Schema
-- Database: student_management
-- Stack: PHP 8+ / MySQL / PDO
-- ============================================================

CREATE DATABASE IF NOT EXISTS `student_management` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `student_management`;

-- ------------------------------------------------------------
-- Table structure for table `students`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `students`;

CREATE TABLE `students` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `student_id` VARCHAR(20) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(25) NOT NULL,
    `department` VARCHAR(100) NOT NULL,
    `date_of_birth` DATE NOT NULL,
    `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
    `photo` VARCHAR(255) NULL,
    `address` TEXT NULL,
    `enrollment_date` DATE NOT NULL,
    `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_student_id` (`student_id`),
    UNIQUE KEY `uk_email` (`email`),
    INDEX `idx_department` (`department`),
    INDEX `idx_status` (`status`),
    INDEX `idx_enrollment_date` (`enrollment_date`),
    INDEX `idx_full_name` (`full_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table structure for table `admins`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `admins`;

CREATE TABLE `admins` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(50) NOT NULL DEFAULT 'Super Admin',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default Admin Credential:
-- Email: arun@gmail.com | Password: arun4709s
INSERT INTO `admins` (`name`, `email`, `password`, `role`) VALUES
('Arun', 'arun@gmail.com', '$2y$12$UD6sk0eUkrA29AGZ8CS62eA4GIhsy/DEdr0Cyj5GuPekLD3ikO59a', 'Super Admin');

-- ------------------------------------------------------------
-- Sample Student Records
-- ------------------------------------------------------------
INSERT INTO `students` (`student_id`, `full_name`, `email`, `phone`, `department`, `date_of_birth`, `gender`, `address`, `enrollment_date`, `status`) VALUES
('STU-2024-001', 'Alexander Hayes', 'alexander.hayes@example.edu', '+1 (555) 234-5678', 'Computer Science', '2002-04-14', 'Male', '742 Evergreen Terrace, Springfield, IL', '2024-08-20', 'Active'),
('STU-2024-002', 'Sophia Ramirez', 'sophia.ramirez@example.edu', '+1 (555) 345-6789', 'Information Technology', '2003-09-22', 'Female', '124 Conch Street, Austin, TX', '2024-08-20', 'Active'),
('STU-2024-003', 'Liam Chen', 'liam.chen@example.edu', '+1 (555) 456-7890', 'Electrical Engineering', '2001-11-05', 'Male', '456 Elm Grove, Seattle, WA', '2024-01-15', 'Active'),
('STU-2024-004', 'Emily Watson', 'emily.watson@example.edu', '+1 (555) 567-8901', 'Business Administration', '2002-01-30', 'Female', '89 Oakridge Ave, Boston, MA', '2024-08-20', 'Active'),
('STU-2024-005', 'Marcus Aurelius Vance', 'marcus.vance@example.edu', '+1 (555) 678-9012', 'Mechanical Engineering', '2000-07-19', 'Male', '312 Pine Crest Blvd, Denver, CO', '2023-08-25', 'Inactive'),
('STU-2024-006', 'Olivia Taylor', 'olivia.taylor@example.edu', '+1 (555) 789-0123', 'Civil Engineering', '2003-03-12', 'Female', '501 Maple Court, Portland, OR', '2024-08-20', 'Active'),
('STU-2024-007', 'Daniel Kim', 'daniel.kim@example.edu', '+1 (555) 890-1234', 'Computer Science', '2002-12-08', 'Male', '67 Silicon Way, San Jose, CA', '2024-01-15', 'Active'),
('STU-2024-008', 'Aaliyah Patel', 'aaliyah.patel@example.edu', '+1 (555) 901-2345', 'Information Technology', '2001-05-27', 'Female', '15 Peachtree Street, Atlanta, GA', '2023-08-25', 'Active'),
('STU-2024-009', 'Noah Bennett', 'noah.bennett@example.edu', '+1 (555) 012-3456', 'Business Administration', '2002-08-16', 'Male', '920 Sunset Blvd, Los Angeles, CA', '2024-08-20', 'Inactive'),
('STU-2024-010', 'Chloe Morales', 'chloe.morales@example.edu', '+1 (555) 123-4560', 'Electrical Engineering', '2003-06-03', 'Female', '48 Lakeview Drive, Chicago, IL', '2024-08-20', 'Active'),
('STU-2024-011', 'Ethan Brooks', 'ethan.brooks@example.edu', '+1 (555) 234-5671', 'Computer Science', '2001-10-10', 'Male', '210 River Road, Minneapolis, MN', '2023-01-10', 'Active'),
('STU-2024-012', 'Isabella Santos', 'isabella.santos@example.edu', '+1 (555) 345-6782', 'Mechanical Engineering', '2002-02-28', 'Female', '77 Magnolia Lane, Miami, FL', '2024-01-15', 'Active'),
('STU-2024-013', 'Lucas Jenkins', 'lucas.jenkins@example.edu', '+1 (555) 456-7893', 'Civil Engineering', '2000-09-15', 'Male', '340 Cedar Ridge, Raleigh, NC', '2023-08-25', 'Inactive'),
('STU-2024-014', 'Zara Al-Mansoor', 'zara.almansoor@example.edu', '+1 (555) 567-8904', 'Information Technology', '2003-11-20', 'Female', '888 Broadway, New York, NY', '2024-08-20', 'Active'),
('STU-2024-015', 'Benjamin Miller', 'benjamin.miller@example.edu', '+1 (555) 678-9015', 'Business Administration', '2002-04-05', 'Male', '100 Heritage Square, Philadelphia, PA', '2024-08-20', 'Active');

