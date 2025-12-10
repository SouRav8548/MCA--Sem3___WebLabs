-- sql/schema.sql
CREATE DATABASE IF NOT EXISTS marks_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE marks_portal;

CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id VARCHAR(50) UNIQUE NOT NULL,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(120),
  dob DATE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE subjects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) UNIQUE NOT NULL,
  name VARCHAR(100) NOT NULL,
  max_marks INT NOT NULL DEFAULT 100,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE marks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  subject_id INT NOT NULL,
  marks_obtained INT NOT NULL,
  exam_name VARCHAR(100) NOT NULL,
  term VARCHAR(50) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_marks_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_marks_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
  CONSTRAINT uc_unique_exam UNIQUE (student_id, subject_id, exam_name, term)
);

-- Optional helper view for totals by student + exam + term
CREATE OR REPLACE VIEW v_student_totals AS
SELECT
  s.id AS student_pk,
  s.student_id,
  s.name,
  m.exam_name,
  m.term,
  SUM(m.marks_obtained) AS total_obtained,
  SUM(sub.max_marks) AS total_max
FROM students s
JOIN marks m ON m.student_id = s.id
JOIN subjects sub ON sub.id = m.subject_id
GROUP BY s.id, m.exam_name, m.term;

-- Seed an admin (replace hash in app with a setup page; for demo only)
INSERT INTO admins (username, password_hash)
VALUES ('admin', '$2y$10$JzvKJbWv8O2d3dXWJkI9rO1D1kqB9I6B5m9oKkq8K3V0oYJ6sYHNa'); -- placeholder
