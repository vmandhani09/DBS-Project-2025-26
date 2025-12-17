-- ============================================================================
-- BBMS LEGACY SCHEMA (for backward compatibility)
-- For full advanced schema, use: database/bbms_complete.sql
-- ============================================================================

DROP DATABASE IF EXISTS bbms;
CREATE DATABASE bbms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bbms;

-- Blood Groups Master
CREATE TABLE blood_groups (
    group_code VARCHAR(5) PRIMARY KEY,
    description VARCHAR(255)
);

-- Donors
CREATE TABLE donors (
    donor_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    age INT,
    gender VARCHAR(10),
    blood_group VARCHAR(5),
    phone VARCHAR(20),
    email VARCHAR(150),
    address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (blood_group) REFERENCES blood_groups(group_code)
);

-- Blood Stock
CREATE TABLE blood_stock (
    stock_id INT AUTO_INCREMENT PRIMARY KEY,
    group_code VARCHAR(5) NOT NULL,
    units INT DEFAULT 0,
    last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_group (group_code),
    FOREIGN KEY (group_code) REFERENCES blood_groups(group_code)
);

-- Patients
CREATE TABLE patients (
    patient_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    age INT,
    gender VARCHAR(10),
    blood_group_needed VARCHAR(5),
    phone VARCHAR(20),
    admitted_on DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (blood_group_needed) REFERENCES blood_groups(group_code)
);

-- Blood Issue
CREATE TABLE blood_issue (
    issue_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    group_code VARCHAR(5) NOT NULL,
    units_issued INT NOT NULL,
    issued_by VARCHAR(100),
    issue_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id),
    FOREIGN KEY (group_code) REFERENCES blood_groups(group_code)
);

-- Admins
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) UNIQUE,
    password_hash VARCHAR(255)
);

-- ============================================================================
-- SAMPLE DATA
-- ============================================================================

-- Blood Groups
INSERT INTO blood_groups (group_code, description) VALUES
('A+', 'A positive'),
('A-', 'A negative'),
('B+', 'B positive'),
('B-', 'B negative'),
('O+', 'O positive'),
('O-', 'O negative'),
('AB+', 'AB positive'),
('AB-', 'AB negative');

-- Blood Stock (10 units each)
INSERT INTO blood_stock (group_code, units)
SELECT group_code, 10 FROM blood_groups;

-- Sample Donor
INSERT INTO donors (name, age, gender, blood_group, phone, address)
VALUES ('Rahul Sharma', 29, 'Male', 'A+', '9876543210', 'Pune');

-- Sample Patient
INSERT INTO patients (name, age, gender, blood_group_needed, phone)
VALUES ('Raju Patel', 50, 'Male', 'A+', '9012345678');

-- Admin (Password: admin123)
INSERT INTO admins (username, password_hash)
VALUES ('admin', '$2y$10$UHOI/ITeprmN1e6bSGFDP.FZ4OhWmF8N5G94oqHqLm/XK69cyVBle');

-- ============================================================================
-- NOTE: For advanced features (triggers, procedures, views), 
-- import: database/bbms_complete.sql
-- ============================================================================
