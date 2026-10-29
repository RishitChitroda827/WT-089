-- ==============================================================
-- StudentHub Portal Database Schema & Seed Data
-- CHAROTAR UNIVERSITY OF SCIENCE AND TECHNOLOGY (CHARUSAT)
-- Subject: Web Development Frameworks (AIUE203)
-- Covers: Practicals 8, 9, 10, 11, 12, 13, 14, 15
-- ==============================================================

CREATE DATABASE IF NOT EXISTS `studenthub` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `studenthub`;

-- 1. USERS TABLE (Practicals 9, 10, 14)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'student') NOT NULL DEFAULT 'student',
    `remember_token` VARCHAR(64) NULL,
    `last_login` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. STUDENTS TABLE (Practicals 8, 11)
CREATE TABLE IF NOT EXISTS `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `name` VARCHAR(100) NOT NULL,
    `enrollment_no` VARCHAR(20) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `mobile` VARCHAR(15) NOT NULL,
    `course` VARCHAR(50) NOT NULL,
    `year` INT NOT NULL,
    `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
    `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. EVENTS TABLE (Practicals 8, 12, 13)
CREATE TABLE IF NOT EXISTS `events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `event_date` DATE NOT NULL,
    `venue` VARCHAR(150) NOT NULL,
    `description` TEXT NOT NULL,
    `capacity` INT NOT NULL DEFAULT 100,
    `poster` VARCHAR(255) NULL,
    `status` ENUM('Open', 'Closed', 'Completed') DEFAULT 'Open',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. REGISTRATIONS TABLE (Practicals 8, 14)
CREATE TABLE IF NOT EXISTS `registrations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `event_id` INT NOT NULL,
    `status` ENUM('Registered', 'Attended', 'Cancelled') DEFAULT 'Registered',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_reg_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reg_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_student_event` (`student_id`, `event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. AUDIT LOGS TABLE (Practical 14)
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `action` VARCHAR(50) NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45) DEFAULT '127.0.0.1',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. CONTACT MESSAGES TABLE (Practicals 2, 7)
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `subject` VARCHAR(150) NOT NULL,
    `message` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==============================================================
-- SEED DATA
-- ==============================================================

-- Seed Users:
-- admin / admin123  (role: admin)
-- student / student123 (role: student)
-- rishit / student123 (role: student, studenthub roll 089)
INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`) VALUES
(1, 'admin', 'admin@charusat.edu.in', '$2y$10$BsbNEt8uRIDNVoKjoS1QB.FhuP5fQnOfjb8oJ/FhoV/MCuzM6ATby', 'admin'),
(2, 'student', 'student@charusat.edu.in', '$2y$10$kxA4ZTPUMmG99nwfJJuyWu3FmqdjJmMmf7rhkplx/gDTu8b0PbEMi', 'student'),
(3, 'rishit', 'd26aiml089@charusat.edu.in', '$2y$10$kxA4ZTPUMmG99nwfJJuyWu3FmqdjJmMmf7rhkplx/gDTu8b0PbEMi', 'student')
ON DUPLICATE KEY UPDATE `username` = VALUES(`username`);

-- Seed Students: 20+ records across departments (Practical 11 Requirement)
INSERT INTO `students` (`id`, `user_id`, `name`, `enrollment_no`, `email`, `mobile`, `course`, `year`, `gender`, `status`) VALUES
(1, 3, 'Rishit Patel', '23DCS089', 'd26aiml089@charusat.edu.in', '9876543210', 'B.Tech AI-ML', 3, 'Male', 'Active'),
(2, 2, 'Aarav Shah', '23DCS001', 'aarav.shah@charusat.edu.in', '9825011223', 'B.Tech CSE', 3, 'Male', 'Active'),
(3, NULL, 'Diya Mehta', '23DCS002', 'diya.mehta@charusat.edu.in', '9825022334', 'B.Tech IT', 3, 'Female', 'Active'),
(4, NULL, 'Kavya Sharma', '23DCS003', 'kavya.sharma@charusat.edu.in', '9825033445', 'B.Tech AI-ML', 3, 'Female', 'Active'),
(5, NULL, 'Rohan Joshi', '23DCS004', 'rohan.joshi@charusat.edu.in', '9825044556', 'B.Tech CSE', 3, 'Male', 'Active'),
(6, NULL, 'Ananya Trivedi', '23DCS005', 'ananya.t@charusat.edu.in', '9825055667', 'B.Tech IT', 2, 'Female', 'Active'),
(7, NULL, 'Dhruv Desai', '23DCS006', 'dhruv.desai@charusat.edu.in', '9825066778', 'BCA', 2, 'Male', 'Active'),
(8, NULL, 'Pooja Varma', '23DCS007', 'pooja.v@charusat.edu.in', '9825077889', 'B.Tech CSE', 4, 'Female', 'Active'),
(9, NULL, 'Manan Bhatt', '23DCS008', 'manan.bhatt@charusat.edu.in', '9825088990', 'B.Tech AI-ML', 2, 'Male', 'Active'),
(10, NULL, 'Tanvi Dave', '23DCS009', 'tanvi.dave@charusat.edu.in', '9825099001', 'B.Tech IT', 3, 'Female', 'Active'),
(11, NULL, 'Harsh Parekh', '23DCS010', 'harsh.p@charusat.edu.in', '9876112233', 'BCA', 3, 'Male', 'Active'),
(12, NULL, 'Isha Panchal', '23DCS011', 'isha.panchal@charusat.edu.in', '9876223344', 'B.Tech CSE', 1, 'Female', 'Active'),
(13, NULL, 'Yash Solanki', '23DCS012', 'yash.s@charusat.edu.in', '9876334455', 'B.Tech AI-ML', 1, 'Male', 'Active'),
(14, NULL, 'Riya Soni', '23DCS013', 'riya.soni@charusat.edu.in', '9876445566', 'B.Tech IT', 2, 'Female', 'Active'),
(15, NULL, 'Kunal Raval', '23DCS014', 'kunal.raval@charusat.edu.in', '9876556677', 'B.Tech CSE', 3, 'Male', 'Active'),
(16, NULL, 'Sneha Chauhan', '23DCS015', 'sneha.c@charusat.edu.in', '9876667788', 'BCA', 1, 'Female', 'Active'),
(17, NULL, 'Aditya Vyas', '23DCS016', 'aditya.vyas@charusat.edu.in', '9876778899', 'B.Tech AI-ML', 4, 'Male', 'Active'),
(18, NULL, 'Bhavya Parikh', '23DCS017', 'bhavya.p@charusat.edu.in', '9876889900', 'B.Tech CSE', 2, 'Male', 'Active'),
(19, NULL, 'Maitri Rana', '23DCS018', 'maitri.rana@charusat.edu.in', '9876990011', 'B.Tech IT', 4, 'Female', 'Active'),
(20, NULL, 'Vivek Pandya', '23DCS019', 'vivek.p@charusat.edu.in', '9876001122', 'BCA', 2, 'Male', 'Active'),
(21, NULL, 'Nidhi Shukla', '23DCS020', 'nidhi.s@charusat.edu.in', '9876002233', 'B.Tech AI-ML', 3, 'Female', 'Active')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Seed Events: (Practicals 12 & 13)
INSERT INTO `events` (`id`, `title`, `category`, `event_date`, `venue`, `description`, `capacity`, `poster`, `status`) VALUES
(1, 'CHARUSAT Annual Hackathon 2026', 'Technical', '2026-11-15', 'CSPIT Computer Center', '36-hour state-level hackathon solving real-world challenges in AI, Web3, and IoT.', 150, NULL, 'Open'),
(2, 'Modern Web Development with React & PHP', 'Workshop', '2026-11-18', 'Seminar Hall 1', 'Hands-on practical workshop covering full-stack web engineering best practices.', 80, NULL, 'Open'),
(3, 'FTE Inter-College Sports Championship', 'Sports', '2026-11-22', 'University Sports Complex', 'Inter-department cricket, football, and table tennis tournaments.', 200, NULL, 'Open'),
(4, 'Spoural Cultural Fest 2026', 'Cultural', '2026-11-28', 'Central Auditorium', 'Flagship university cultural festival featuring dance, music, drama, and fine arts.', 300, NULL, 'Open'),
(5, 'AI & Generative Models in Engineering', 'Seminar', '2026-12-02', 'Auditorium B', 'Expert keynote from industry researchers on foundation models and prompt engineering.', 120, NULL, 'Open'),
(6, 'Competitive Coding Contest (CodeBlitz)', 'Technical', '2026-12-06', 'Lab 3, CSPIT', 'Algorithmic problem solving and data structures coding battle.', 100, NULL, 'Open'),
(7, 'Cloud Computing with AWS & Azure', 'Workshop', '2026-12-10', 'Computer Lab 2', 'Introductory session on provisioning serverless cloud environments.', 75, NULL, 'Open'),
(8, 'Photography & Creative Design Boot Camp', 'Workshop', '2026-12-14', 'Media Lab, FTE', 'Hands-on session on visual storytelling, camera optics, and UI wireframing.', 60, NULL, 'Open')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- Seed Registrations: (Practical 14)
INSERT INTO `registrations` (`id`, `student_id`, `event_id`, `status`) VALUES
(1, 1, 1, 'Registered'),
(2, 1, 2, 'Registered'),
(3, 2, 1, 'Registered'),
(4, 2, 3, 'Registered'),
(5, 3, 2, 'Registered'),
(6, 4, 1, 'Registered'),
(7, 5, 6, 'Registered'),
(8, 6, 4, 'Registered')
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`);

-- Seed Audit Logs: (Practical 14)
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `description`, `ip_address`) VALUES
(1, 1, 'SYSTEM_INIT', 'StudentHub database initialized with master schema', '127.0.0.1'),
(2, 1, 'LOGIN', 'Admin logged into administration control panel', '127.0.0.1'),
(3, 1, 'CREATE_EVENT', 'Added CHARUSAT Annual Hackathon 2026 event', '127.0.0.1'),
(4, 3, 'LOGIN', 'Student Rishit Patel (23DCS089) logged in', '127.0.0.1'),
(5, 3, 'REGISTER_EVENT', 'Registered for CHARUSAT Annual Hackathon 2026', '127.0.0.1')
ON DUPLICATE KEY UPDATE `action` = VALUES(`action`);
