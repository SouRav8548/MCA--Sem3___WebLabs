-- Create Database
CREATE DATABASE IF NOT EXISTS marks_portal;
USE marks_portal;
-- Admin Table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);
-- Insert Default Admin (Username: admin, Password: admin123)
-- Note: The password hash below corresponds to 'admin123'
INSERT INTO admins (username, password) VALUES ('admin', '$2y$10$8WkQv..'); 

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    roll_no VARCHAR(20) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    photo VARCHAR(255) DEFAULT 'default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Marks Table
CREATE TABLE IF NOT EXISTS marks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_1 INT DEFAULT 0,
    subject_2 INT DEFAULT 0,
    subject_3 INT DEFAULT 0,
    subject_4 INT DEFAULT 0,
    subject_5 INT DEFAULT 0,
    total INT DEFAULT 0,
    grade VARCHAR(5) DEFAULT 'F',
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);