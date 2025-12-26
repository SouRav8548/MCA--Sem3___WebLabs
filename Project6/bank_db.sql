DROP DATABASE IF EXISTS bank_db;
CREATE DATABASE bank_db;
USE bank_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    -- Core Auth
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255), -- Storing Plain Text as per previous request
    role ENUM('manager', 'staff', 'consumer') NOT NULL,
    status ENUM('pending', 'active') DEFAULT 'pending',
    
    -- Banking Info
    account_number BIGINT UNIQUE DEFAULT NULL,
    balance DECIMAL(15, 2) DEFAULT 0.00,
    
    -- Personal Info
    dob DATE,
    gender VARCHAR(10),
    father_name VARCHAR(100),
    
    -- Communication Info
    address TEXT,
    city VARCHAR(50),
    zip_code VARCHAR(10),
    phone VARCHAR(15),
    
    -- Identity Info
    pan_number VARCHAR(20),
    aadhaar_number VARCHAR(20),
    
    -- Education Info
    qualification VARCHAR(50),
    occupation VARCHAR(50),
    
    -- Profile Media
    profile_pic VARCHAR(255) DEFAULT 'default.png',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS deposits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    type ENUM('FD', 'RD'),
    amount DECIMAL(15, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    request_type ENUM('cheque_book', 'atm_card', 'account_activation') NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    loan_type VARCHAR(50),
    amount DECIMAL(15, 2),
    justification TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id)
);


CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT DEFAULT NULL,    -- NULL if it's a Cash Deposit by Staff
    receiver_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    type ENUM('transfer', 'cash_deposit') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (receiver_id) REFERENCES users(id)
);

-- Default Manager
INSERT INTO users (name, email, password, role, status, account_number) VALUES 
('Head Manager', 'admin@bank.com', 'admin123', 'manager', 'active', '1000000001');