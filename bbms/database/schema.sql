-- ============================================================================
-- BLOOD BANK MANAGEMENT SYSTEM (BBMS) - COMPLETE DATABASE SCHEMA
-- Version: 2.0
-- MySQL 8+ Compatible
-- Normalized to 3NF
-- ============================================================================

-- Drop existing database and create fresh
DROP DATABASE IF EXISTS bbms;
CREATE DATABASE bbms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bbms;

-- ============================================================================
-- 1. BLOOD GROUPS TABLE (Master Table)
-- ============================================================================
CREATE TABLE blood_groups (
    group_code VARCHAR(5) PRIMARY KEY,
    group_name VARCHAR(20) NOT NULL,
    can_donate_to VARCHAR(50) NOT NULL,
    can_receive_from VARCHAR(50) NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================================
-- 2. ADMIN TABLE
-- ============================================================================
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    email VARCHAR(150) UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(120),
    phone VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================================
-- 3. DONORS TABLE
-- ============================================================================
CREATE TABLE donors (
    donor_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(60) NOT NULL,
    last_name VARCHAR(60) NOT NULL,
    date_of_birth DATE,
    age INT,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    blood_group VARCHAR(5) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(150),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    pincode VARCHAR(10),
    weight DECIMAL(5,2),
    last_donation_date DATE,
    total_donations INT DEFAULT 0,
    is_eligible BOOLEAN DEFAULT TRUE,
    medical_conditions TEXT,
    emergency_contact_name VARCHAR(120),
    emergency_contact_phone VARCHAR(20),
    status ENUM('Active', 'Inactive', 'Blacklisted') DEFAULT 'Active',
    registered_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (blood_group) REFERENCES blood_groups(group_code) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (registered_by) REFERENCES admins(admin_id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    INDEX idx_blood_group (blood_group),
    INDEX idx_city (city),
    INDEX idx_status (status),
    INDEX idx_phone (phone)
) ENGINE=InnoDB;

-- ============================================================================
-- 4. HOSPITALS TABLE
-- ============================================================================
CREATE TABLE hospitals (
    hospital_id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_name VARCHAR(200) NOT NULL,
    hospital_type ENUM('Government', 'Private', 'Trust', 'Other') DEFAULT 'Private',
    registration_number VARCHAR(50) UNIQUE,
    contact_person VARCHAR(120),
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(150),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    pincode VARCHAR(10),
    is_verified BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_city (city),
    INDEX idx_hospital_name (hospital_name)
) ENGINE=InnoDB;

-- ============================================================================
-- 5. PATIENTS TABLE
-- ============================================================================
CREATE TABLE patients (
    patient_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(60) NOT NULL,
    last_name VARCHAR(60) NOT NULL,
    date_of_birth DATE,
    age INT,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    blood_group_needed VARCHAR(5) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(150),
    address TEXT,
    city VARCHAR(100),
    hospital_id INT,
    ward_number VARCHAR(20),
    disease_diagnosis TEXT,
    doctor_name VARCHAR(120),
    attendant_name VARCHAR(120),
    attendant_phone VARCHAR(20),
    admitted_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    discharged_on TIMESTAMP NULL,
    status ENUM('Admitted', 'Discharged', 'Deceased') DEFAULT 'Admitted',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (blood_group_needed) REFERENCES blood_groups(group_code) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(hospital_id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    INDEX idx_blood_group (blood_group_needed),
    INDEX idx_status (status),
    INDEX idx_hospital (hospital_id)
) ENGINE=InnoDB;

-- ============================================================================
-- 6. BLOOD STOCK TABLE
-- ============================================================================
CREATE TABLE blood_stock (
    stock_id INT AUTO_INCREMENT PRIMARY KEY,
    group_code VARCHAR(5) NOT NULL UNIQUE,
    units_available INT DEFAULT 0 CHECK (units_available >= 0),
    units_reserved INT DEFAULT 0 CHECK (units_reserved >= 0),
    minimum_threshold INT DEFAULT 5,
    maximum_capacity INT DEFAULT 100,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (group_code) REFERENCES blood_groups(group_code) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================================
-- 7. DONATIONS TABLE
-- ============================================================================
CREATE TABLE donations (
    donation_id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    blood_group VARCHAR(5) NOT NULL,
    units_donated INT DEFAULT 1 CHECK (units_donated > 0),
    donation_date DATE NOT NULL,
    donation_time TIME,
    hemoglobin_level DECIMAL(4,2),
    blood_pressure VARCHAR(20),
    pulse_rate INT,
    temperature DECIMAL(4,2),
    donation_type ENUM('Whole Blood', 'Plasma', 'Platelets', 'RBC') DEFAULT 'Whole Blood',
    collection_center VARCHAR(200),
    collected_by VARCHAR(120),
    bag_number VARCHAR(50) UNIQUE,
    expiry_date DATE,
    test_result ENUM('Pending', 'Safe', 'Unsafe', 'Expired') DEFAULT 'Pending',
    test_date DATE,
    tested_by VARCHAR(120),
    notes TEXT,
    status ENUM('Collected', 'Tested', 'Available', 'Issued', 'Expired', 'Discarded') DEFAULT 'Collected',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (donor_id) REFERENCES donors(donor_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (blood_group) REFERENCES blood_groups(group_code) ON DELETE RESTRICT ON UPDATE CASCADE,
    
    INDEX idx_donor (donor_id),
    INDEX idx_blood_group (blood_group),
    INDEX idx_donation_date (donation_date),
    INDEX idx_status (status),
    INDEX idx_expiry (expiry_date)
) ENGINE=InnoDB;

-- ============================================================================
-- 8. BLOOD REQUESTS TABLE
-- ============================================================================
CREATE TABLE blood_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT,
    hospital_id INT,
    requester_name VARCHAR(120) NOT NULL,
    requester_phone VARCHAR(20) NOT NULL,
    requester_email VARCHAR(150),
    blood_group_requested VARCHAR(5) NOT NULL,
    units_requested INT NOT NULL CHECK (units_requested > 0),
    units_approved INT DEFAULT 0,
    urgency_level ENUM('Normal', 'Urgent', 'Critical') DEFAULT 'Normal',
    purpose TEXT,
    required_date DATE NOT NULL,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_date TIMESTAMP NULL,
    approved_by INT,
    issued_date TIMESTAMP NULL,
    issued_by INT,
    rejection_reason TEXT,
    status ENUM('Pending', 'Approved', 'Partially Approved', 'Rejected', 'Issued', 'Cancelled', 'Expired') DEFAULT 'Pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(hospital_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (blood_group_requested) REFERENCES blood_groups(group_code) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES admins(admin_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (issued_by) REFERENCES admins(admin_id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    INDEX idx_blood_group (blood_group_requested),
    INDEX idx_status (status),
    INDEX idx_urgency (urgency_level),
    INDEX idx_request_date (request_date)
) ENGINE=InnoDB;

-- ============================================================================
-- 9. BLOOD ISSUE TABLE (Transaction Record)
-- ============================================================================
CREATE TABLE blood_issues (
    issue_id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT,
    donation_id INT,
    patient_id INT,
    blood_group VARCHAR(5) NOT NULL,
    units_issued INT NOT NULL CHECK (units_issued > 0),
    issued_to VARCHAR(200) NOT NULL,
    issued_by INT,
    issue_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    receiver_name VARCHAR(120),
    receiver_phone VARCHAR(20),
    receiver_relation VARCHAR(50),
    receiver_id_type VARCHAR(50),
    receiver_id_number VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (request_id) REFERENCES blood_requests(request_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (donation_id) REFERENCES donations(donation_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (blood_group) REFERENCES blood_groups(group_code) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (issued_by) REFERENCES admins(admin_id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    INDEX idx_request (request_id),
    INDEX idx_issue_date (issue_date)
) ENGINE=InnoDB;

-- ============================================================================
-- 10. NOTIFICATION LOGS TABLE
-- ============================================================================
CREATE TABLE notification_logs (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    notification_type ENUM('Low Stock', 'Expiry Alert', 'Request Alert', 'Donation Reminder', 'System', 'Other') NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    severity ENUM('Info', 'Warning', 'Critical') DEFAULT 'Info',
    related_table VARCHAR(50),
    related_id INT,
    is_read BOOLEAN DEFAULT FALSE,
    read_by INT,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (read_by) REFERENCES admins(admin_id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    INDEX idx_type (notification_type),
    INDEX idx_severity (severity),
    INDEX idx_is_read (is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ============================================================================
-- 11. ACTIVITY LOGS TABLE (Audit Trail)
-- ============================================================================
CREATE TABLE activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    user_type ENUM('Admin', 'System', 'API') DEFAULT 'Admin',
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES admins(admin_id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_table (table_name),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ============================================================================
-- 12. DONOR ELIGIBILITY LOG TABLE
-- ============================================================================
CREATE TABLE donor_eligibility_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    check_date DATE NOT NULL,
    is_eligible BOOLEAN NOT NULL,
    reason TEXT,
    checked_by INT,
    next_eligible_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (donor_id) REFERENCES donors(donor_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (checked_by) REFERENCES admins(admin_id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    INDEX idx_donor (donor_id),
    INDEX idx_check_date (check_date)
) ENGINE=InnoDB;

-- ============================================================================
-- END OF SCHEMA
-- ============================================================================
