-- ============================================================================
-- BLOOD BANK MANAGEMENT SYSTEM (BBMS) - SIMPLIFIED SCHEMA
-- Version: 2.1 - Compatible with PHP Application
-- MySQL 8+ Compatible
-- ============================================================================

DROP DATABASE IF EXISTS bbms;
CREATE DATABASE bbms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bbms;

-- ============================================================================
-- BLOOD GROUPS TABLE
-- ============================================================================
CREATE TABLE blood_groups (
    group_code VARCHAR(5) PRIMARY KEY,
    description VARCHAR(255) NOT NULL,
    can_donate_to VARCHAR(50),
    can_receive_from VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO blood_groups (group_code, description, can_donate_to, can_receive_from) VALUES
('A+', 'A Positive', 'A+, AB+', 'A+, A-, O+, O-'),
('A-', 'A Negative', 'A+, A-, AB+, AB-', 'A-, O-'),
('B+', 'B Positive', 'B+, AB+', 'B+, B-, O+, O-'),
('B-', 'B Negative', 'B+, B-, AB+, AB-', 'B-, O-'),
('AB+', 'AB Positive (Universal Recipient)', 'AB+', 'All'),
('AB-', 'AB Negative', 'AB+, AB-', 'A-, B-, AB-, O-'),
('O+', 'O Positive', 'A+, B+, AB+, O+', 'O+, O-'),
('O-', 'O Negative (Universal Donor)', 'All', 'O-');

-- ============================================================================
-- ADMINS TABLE
-- ============================================================================
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    email VARCHAR(150),
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(120),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin: admin / admin123
INSERT INTO admins (username, email, password_hash, full_name) VALUES
('admin', 'admin@bbms.local', '$2y$10$tGbyiIOTTdJW95VdjmHiy.PI0CuD/.YxIaSLkMs36cxWNy8t7y5QC', 'System Administrator');

-- ============================================================================
-- DONORS TABLE
-- ============================================================================
CREATE TABLE donors (
    donor_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150),
    date_of_birth DATE,
    age INT,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    blood_group VARCHAR(5) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    weight DECIMAL(5,2) DEFAULT 50,
    address TEXT,
    medical_notes TEXT,
    last_donation_date DATE,
    status ENUM('Active', 'Inactive', 'Deferred') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (blood_group) REFERENCES blood_groups(group_code) ON DELETE RESTRICT,
    INDEX idx_blood_group (blood_group),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ============================================================================
-- HOSPITALS TABLE
-- ============================================================================
CREATE TABLE hospitals (
    hospital_id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_name VARCHAR(200) NOT NULL,
    contact_person VARCHAR(120),
    phone VARCHAR(20),
    email VARCHAR(150),
    address TEXT,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================================
-- PATIENTS TABLE
-- ============================================================================
CREATE TABLE patients (
    patient_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    age INT NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    blood_group VARCHAR(5) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    disease VARCHAR(255),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (blood_group) REFERENCES blood_groups(group_code) ON DELETE RESTRICT,
    INDEX idx_blood_group (blood_group)
) ENGINE=InnoDB;

-- ============================================================================
-- BLOOD STOCK TABLE
-- ============================================================================
CREATE TABLE blood_stock (
    stock_id INT AUTO_INCREMENT PRIMARY KEY,
    group_code VARCHAR(5) NOT NULL UNIQUE,
    units_available INT DEFAULT 0,
    minimum_threshold INT DEFAULT 10,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (group_code) REFERENCES blood_groups(group_code) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Initialize stock for all blood groups
INSERT INTO blood_stock (group_code, units_available, minimum_threshold) VALUES
('A+', 25, 10), ('A-', 15, 5), ('B+', 20, 10), ('B-', 10, 5),
('AB+', 12, 5), ('AB-', 8, 3), ('O+', 30, 15), ('O-', 20, 10);

-- ============================================================================
-- DONATIONS TABLE
-- ============================================================================
CREATE TABLE donations (
    donation_id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    donation_date DATE NOT NULL,
    units_donated INT DEFAULT 1,
    collection_center VARCHAR(200),
    notes TEXT,
    status ENUM('Pending', 'Completed', 'Rejected') DEFAULT 'Completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (donor_id) REFERENCES donors(donor_id) ON DELETE CASCADE,
    INDEX idx_donor (donor_id),
    INDEX idx_date (donation_date)
) ENGINE=InnoDB;

-- ============================================================================
-- BLOOD REQUESTS TABLE
-- ============================================================================
CREATE TABLE blood_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    hospital_id INT,
    blood_group VARCHAR(5) NOT NULL,
    units_required INT DEFAULT 1,
    priority ENUM('Normal', 'High', 'Critical') DEFAULT 'Normal',
    reason TEXT,
    required_date DATE,
    status ENUM('Pending', 'Approved', 'Rejected', 'Fulfilled') DEFAULT 'Pending',
    approved_by INT,
    approved_date TIMESTAMP NULL,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(hospital_id) ON DELETE SET NULL,
    FOREIGN KEY (blood_group) REFERENCES blood_groups(group_code) ON DELETE RESTRICT,
    FOREIGN KEY (approved_by) REFERENCES admins(admin_id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_priority (priority)
) ENGINE=InnoDB;

-- ============================================================================
-- BLOOD ISSUE TABLE
-- ============================================================================
CREATE TABLE blood_issue (
    issue_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    blood_group VARCHAR(5) NOT NULL,
    units_issued INT DEFAULT 1,
    hospital_id INT,
    issued_to VARCHAR(120) NOT NULL,
    issue_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (blood_group) REFERENCES blood_groups(group_code) ON DELETE RESTRICT,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(hospital_id) ON DELETE SET NULL,
    INDEX idx_date (issue_date),
    INDEX idx_blood_group (blood_group)
) ENGINE=InnoDB;

-- ============================================================================
-- NOTIFICATION LOGS TABLE
-- ============================================================================
CREATE TABLE notification_logs (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    message TEXT,
    severity ENUM('Info', 'Warning', 'Critical') DEFAULT 'Info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================================
-- ACTIVITY LOGS TABLE
-- ============================================================================
CREATE TABLE activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    user_type ENUM('Admin', 'System') DEFAULT 'System',
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    ip_address VARCHAR(45),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES admins(admin_id) ON DELETE SET NULL,
    INDEX idx_action (action),
    INDEX idx_table (table_name)
) ENGINE=InnoDB;

-- ============================================================================
-- TRIGGERS
-- ============================================================================

-- Auto update stock when donation is recorded
DELIMITER //
CREATE TRIGGER after_donation_insert
AFTER INSERT ON donations
FOR EACH ROW
BEGIN
    DECLARE donor_blood_group VARCHAR(5);
    
    IF NEW.status = 'Completed' THEN
        SELECT blood_group INTO donor_blood_group FROM donors WHERE donor_id = NEW.donor_id;
        
        UPDATE blood_stock 
        SET units_available = units_available + NEW.units_donated,
            last_updated = NOW()
        WHERE group_code = donor_blood_group;
    END IF;
END//

-- Auto decrease stock when blood is issued
CREATE TRIGGER after_issue_insert
AFTER INSERT ON blood_issue
FOR EACH ROW
BEGIN
    UPDATE blood_stock 
    SET units_available = units_available - NEW.units_issued,
        last_updated = NOW()
    WHERE group_code = NEW.blood_group;
    
    -- Alert if stock is low
    INSERT INTO notification_logs (title, message, severity)
    SELECT 
        CONCAT('Low Stock Alert: ', NEW.blood_group),
        CONCAT('Stock for ', NEW.blood_group, ' is now at ', units_available, ' units'),
        CASE WHEN units_available < 5 THEN 'Critical' ELSE 'Warning' END
    FROM blood_stock 
    WHERE group_code = NEW.blood_group 
    AND units_available <= minimum_threshold;
END//

DELIMITER ;

-- ============================================================================
-- SAMPLE DATA
-- ============================================================================

-- Sample Hospitals
INSERT INTO hospitals (hospital_name, contact_person, phone, email, address) VALUES
('City General Hospital', 'Dr. Smith', '9876543210', 'citygeneral@hospital.com', '123 Main Street'),
('Apollo Hospital', 'Dr. Sharma', '9876543211', 'apollo@hospital.com', '456 Health Road'),
('Red Cross Center', 'Nurse Mary', '9876543212', 'redcross@center.org', '789 Care Avenue');

-- Sample Donors
INSERT INTO donors (name, email, age, gender, blood_group, phone, weight, address, status) VALUES
('John Doe', 'john@email.com', 28, 'Male', 'O+', '9876543001', 75.5, '101 Donor Street', 'Active'),
('Jane Smith', 'jane@email.com', 32, 'Female', 'A+', '9876543002', 62.0, '202 Blood Lane', 'Active'),
('Bob Wilson', 'bob@email.com', 45, 'Male', 'B+', '9876543003', 80.0, '303 Health Road', 'Active'),
('Alice Brown', 'alice@email.com', 25, 'Female', 'AB-', '9876543004', 55.0, '404 Care Avenue', 'Active'),
('Charlie Davis', 'charlie@email.com', 38, 'Male', 'O-', '9876543005', 70.0, '505 Life Street', 'Active');

-- Sample Patients
INSERT INTO patients (name, age, gender, blood_group, phone, disease, address) VALUES
('Patient One', 45, 'Male', 'A+', '9876543101', 'Surgery Required', '111 Patient Lane'),
('Patient Two', 32, 'Female', 'O+', '9876543102', 'Anemia', '222 Care Road'),
('Patient Three', 28, 'Male', 'B+', '9876543103', 'Accident', '333 Health Street');

-- Sample Donations
INSERT INTO donations (donor_id, donation_date, units_donated, collection_center, status) VALUES
(1, CURDATE() - INTERVAL 60 DAY, 1, 'Main Blood Bank', 'Completed'),
(2, CURDATE() - INTERVAL 30 DAY, 1, 'City Hospital', 'Completed'),
(3, CURDATE() - INTERVAL 15 DAY, 1, 'Main Blood Bank', 'Completed');

-- Welcome notification
INSERT INTO notification_logs (title, message, severity) VALUES
('Welcome to BBMS', 'Blood Bank Management System is now active!', 'Info');

-- ============================================================================
-- VIEWS
-- ============================================================================

CREATE VIEW view_available_blood AS
SELECT 
    s.group_code,
    g.description,
    s.units_available,
    s.minimum_threshold,
    CASE 
        WHEN s.units_available < 5 THEN 'Critical'
        WHEN s.units_available <= s.minimum_threshold THEN 'Low'
        ELSE 'Adequate'
    END as stock_status
FROM blood_stock s
JOIN blood_groups g ON s.group_code = g.group_code
ORDER BY s.units_available ASC;

CREATE VIEW view_pending_requests AS
SELECT 
    r.request_id,
    p.name as patient_name,
    r.blood_group,
    r.units_required,
    r.priority,
    r.request_date,
    h.hospital_name
FROM blood_requests r
JOIN patients p ON r.patient_id = p.patient_id
LEFT JOIN hospitals h ON r.hospital_id = h.hospital_id
WHERE r.status = 'Pending'
ORDER BY 
    FIELD(r.priority, 'Critical', 'High', 'Normal'),
    r.request_date;

-- ============================================================================
-- END OF SCHEMA
-- ============================================================================
