# Banking System - Database Setup
# Copy and paste this entire SQL into phpMyAdmin to create all tables

-- Create Database
CREATE DATABASE IF NOT EXISTS banking_system;
USE banking_system;

-- Users Table (For Admin, Staff, Customers)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    aadhar VARCHAR(20) UNIQUE,
    pan VARCHAR(20) UNIQUE,
    phone VARCHAR(15) NOT NULL,
    role ENUM('admin', 'manager', 'staff', 'customer', 'branch', 'csp') NOT NULL,
    branch_id INT,
    status ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Branches Table
CREATE TABLE branches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    branch_name VARCHAR(150) NOT NULL,
    branch_code VARCHAR(50) UNIQUE NOT NULL,
    address VARCHAR(255) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(100) NOT NULL,
    manager_id INT,
    is_head_branch BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Customer Profile Table
CREATE TABLE customer_profile (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    phone VARCHAR(15) NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    pincode VARCHAR(10) NOT NULL,
    aadhar_number VARCHAR(20) UNIQUE NOT NULL,
    pan_number VARCHAR(20) UNIQUE,
    date_of_birth DATE NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    occupation VARCHAR(100),
    annual_income DECIMAL(15, 2),
    kyc_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    kyc_document_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Bank Accounts Table
CREATE TABLE bank_accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    account_number VARCHAR(20) UNIQUE NOT NULL,
    account_type ENUM('savings', 'current') NOT NULL,
    branch_id INT NOT NULL,
    balance DECIMAL(15, 2) DEFAULT 0,
    status ENUM('active', 'inactive', 'closed') DEFAULT 'active',
    opening_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id)
);

-- Fixed Deposit Table
CREATE TABLE fixed_deposits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    account_id INT NOT NULL,
    principal_amount DECIMAL(15, 2) NOT NULL,
    interest_rate DECIMAL(5, 2) NOT NULL,
    tenure_months INT NOT NULL,
    maturity_date DATE NOT NULL,
    status ENUM('active', 'matured', 'closed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (account_id) REFERENCES bank_accounts(id)
);

-- Recurring Deposit Table
CREATE TABLE recurring_deposits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    account_id INT NOT NULL,
    monthly_amount DECIMAL(15, 2) NOT NULL,
    interest_rate DECIMAL(5, 2) NOT NULL,
    tenure_months INT NOT NULL,
    maturity_date DATE NOT NULL,
    status ENUM('active', 'matured', 'closed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (account_id) REFERENCES bank_accounts(id)
);

-- Loans Table
CREATE TABLE loans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    account_id INT NOT NULL,
    loan_type ENUM('car', 'home', 'education', 'personal', 'gold', 'wedding') NOT NULL,
    requested_amount DECIMAL(15, 2) NOT NULL,
    approved_amount DECIMAL(15, 2),
    interest_rate DECIMAL(5, 2),
    tenure_months INT,
    purpose TEXT,
    status ENUM('pending', 'approved', 'rejected', 'active', 'closed') DEFAULT 'pending',
    cibil_score INT,
    credit_assessment LONGTEXT,
    approval_remarks TEXT,
    approved_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (account_id) REFERENCES bank_accounts(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- ATM Card Request Table
CREATE TABLE atm_card_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    account_id INT NOT NULL,
    card_type VARCHAR(50) NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'issued', 'cancelled') DEFAULT 'pending',
    requested_date DATE NOT NULL,
    approved_date DATE,
    approval_remarks TEXT,
    approved_by INT,
    card_number VARCHAR(20),
    card_pdf_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (account_id) REFERENCES bank_accounts(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- Cheque Book Request Table
CREATE TABLE cheque_book_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    account_id INT NOT NULL,
    number_of_leaves INT DEFAULT 50,
    status ENUM('pending', 'approved', 'rejected', 'issued', 'cancelled') DEFAULT 'pending',
    requested_date DATE NOT NULL,
    approved_date DATE,
    approval_remarks TEXT,
    approved_by INT,
    cheque_pdf_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (account_id) REFERENCES bank_accounts(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- Transactions Table
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    from_account_id INT,
    to_account_id INT,
    transaction_type ENUM('deposit', 'withdrawal', 'transfer', 'loan_payment') NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    description TEXT,
    status ENUM('completed', 'pending', 'failed') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_account_id) REFERENCES bank_accounts(id),
    FOREIGN KEY (to_account_id) REFERENCES bank_accounts(id)
);

-- Interest Rates Master Table
CREATE TABLE interest_rates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    account_type VARCHAR(50) NOT NULL,
    product_type VARCHAR(50) NOT NULL,
    rate DECIMAL(5, 2) NOT NULL,
    min_tenure INT,
    max_tenure INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Loan Documents Table (For loan assessment questions)
CREATE TABLE loan_documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    loan_id INT NOT NULL,
    document_name VARCHAR(150) NOT NULL,
    document_path VARCHAR(255) NOT NULL,
    document_type VARCHAR(50),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (loan_id) REFERENCES loans(id)
);

-- Activity Log Table
CREATE TABLE activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert Sample Data
INSERT INTO branches (branch_name, branch_code, address, phone, email, is_head_branch) VALUES
('Head Office', 'HO001', '123 Banking Street, Downtown', '9876543210', 'head@bank.com', TRUE),
('Mumbai Branch', 'MB001', '456 Business Avenue, Mumbai', '9876543211', 'mumbai@bank.com', FALSE),
('Delhi Branch', 'DB001', '789 Capital Road, Delhi', '9876543212', 'delhi@bank.com', FALSE);

INSERT INTO interest_rates (account_type, product_type, rate, min_tenure, max_tenure) VALUES
('savings', 'sb', 4.5, 0, 0),
('current', 'cb', 0, 0, 0),
('fixed_deposit', 'fd_1yr', 6.5, 12, 12),
('fixed_deposit', 'fd_2yr', 7.0, 24, 24),
('fixed_deposit', 'fd_3yr', 7.25, 36, 36),
('recurring_deposit', 'rd_5yr', 5.75, 60, 60);

-- Create Admin User (username: admin, password: admin123 - bcrypt hashed)
INSERT INTO users (username, email, full_name, password, role, branch_id, status) VALUES
('admin', 'admin@bank.com', 'Admin User', '$2y$10$WXjt1YKBhJWDvJXD2B8qO.XkxqLRvqN5qGwqLiM5c5LqPvd3PnJ9K', 'admin', 1, 'active');
