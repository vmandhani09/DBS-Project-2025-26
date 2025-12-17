-- ============================================================================
-- BLOOD BANK MANAGEMENT SYSTEM (BBMS) - COMPLETE DATABASE
-- Single file for easy import via phpMyAdmin
-- MySQL 8+ Compatible | Normalized to 3NF
-- ============================================================================

-- Create and use database
DROP DATABASE IF EXISTS bbms;
CREATE DATABASE bbms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bbms;

-- ============================================================================
-- TABLES
-- ============================================================================

-- Blood Groups (Master)
CREATE TABLE blood_groups (
    group_code VARCHAR(5) PRIMARY KEY,
    group_name VARCHAR(20) NOT NULL,
    can_donate_to VARCHAR(50) NOT NULL,
    can_receive_from VARCHAR(50) NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Admins
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

-- Donors
CREATE TABLE donors (
    donor_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(60) NOT NULL,
    last_name VARCHAR(60) NOT NULL,
    date_of_birth DATE,
    age INT GENERATED ALWAYS AS (TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE())) STORED,
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
    INDEX idx_phone (phone)
) ENGINE=InnoDB;

-- Hospitals
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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Patients
CREATE TABLE patients (
    patient_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(60) NOT NULL,
    last_name VARCHAR(60) NOT NULL,
    date_of_birth DATE,
    age INT GENERATED ALWAYS AS (TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE())) STORED,
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
    FOREIGN KEY (hospital_id) REFERENCES hospitals(hospital_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Blood Stock
CREATE TABLE blood_stock (
    stock_id INT AUTO_INCREMENT PRIMARY KEY,
    group_code VARCHAR(5) NOT NULL UNIQUE,
    units_available INT DEFAULT 0,
    units_reserved INT DEFAULT 0,
    minimum_threshold INT DEFAULT 5,
    maximum_capacity INT DEFAULT 100,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (group_code) REFERENCES blood_groups(group_code) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Donations
CREATE TABLE donations (
    donation_id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    blood_group VARCHAR(5) NOT NULL,
    units_donated INT DEFAULT 1,
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
    INDEX idx_status (status),
    INDEX idx_expiry (expiry_date)
) ENGINE=InnoDB;

-- Blood Requests
CREATE TABLE blood_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT,
    hospital_id INT,
    requester_name VARCHAR(120) NOT NULL,
    requester_phone VARCHAR(20) NOT NULL,
    requester_email VARCHAR(150),
    blood_group_requested VARCHAR(5) NOT NULL,
    units_requested INT NOT NULL,
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
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Blood Issues
CREATE TABLE blood_issues (
    issue_id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT,
    donation_id INT,
    patient_id INT,
    blood_group VARCHAR(5) NOT NULL,
    units_issued INT NOT NULL,
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
    FOREIGN KEY (issued_by) REFERENCES admins(admin_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Notification Logs
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
    INDEX idx_is_read (is_read)
) ENGINE=InnoDB;

-- Activity Logs
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
    INDEX idx_action (action)
) ENGINE=InnoDB;

-- Donor Eligibility Logs
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
    FOREIGN KEY (checked_by) REFERENCES admins(admin_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================================
-- VIEWS
-- ============================================================================

-- View: Available Blood Stock
CREATE OR REPLACE VIEW view_available_blood AS
SELECT 
    bg.group_code,
    bg.group_name,
    bg.can_donate_to,
    bg.can_receive_from,
    COALESCE(bs.units_available, 0) AS units_available,
    COALESCE(bs.units_reserved, 0) AS units_reserved,
    (COALESCE(bs.units_available, 0) - COALESCE(bs.units_reserved, 0)) AS units_free,
    COALESCE(bs.minimum_threshold, 5) AS min_threshold,
    CASE 
        WHEN COALESCE(bs.units_available, 0) = 0 THEN 'Out of Stock'
        WHEN COALESCE(bs.units_available, 0) < COALESCE(bs.minimum_threshold, 5) THEN 'Critical'
        WHEN COALESCE(bs.units_available, 0) <= (COALESCE(bs.minimum_threshold, 5) + 2) THEN 'Low'
        ELSE 'Available'
    END AS availability_status,
    bs.last_updated
FROM blood_groups bg
LEFT JOIN blood_stock bs ON bg.group_code = bs.group_code
ORDER BY bg.group_code;

-- View: Pending Blood Requests
CREATE OR REPLACE VIEW view_pending_requests AS
SELECT 
    r.request_id,
    r.requester_name,
    r.requester_phone,
    r.blood_group_requested,
    r.units_requested,
    r.urgency_level,
    r.required_date,
    r.request_date,
    DATEDIFF(r.required_date, CURDATE()) AS days_until_required,
    CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
    h.hospital_name,
    COALESCE(bs.units_available, 0) AS current_stock
FROM blood_requests r
LEFT JOIN patients p ON r.patient_id = p.patient_id
LEFT JOIN hospitals h ON r.hospital_id = h.hospital_id
LEFT JOIN blood_stock bs ON r.blood_group_requested = bs.group_code
WHERE r.status = 'Pending'
ORDER BY 
    CASE r.urgency_level WHEN 'Critical' THEN 1 WHEN 'Urgent' THEN 2 ELSE 3 END,
    r.required_date ASC;

-- View: Donation Summary
CREATE OR REPLACE VIEW view_donation_summary AS
SELECT 
    d.donation_id,
    CONCAT(dn.first_name, ' ', dn.last_name) AS donor_name,
    dn.phone AS donor_phone,
    d.blood_group,
    d.units_donated,
    d.donation_type,
    d.donation_date,
    d.expiry_date,
    DATEDIFF(d.expiry_date, CURDATE()) AS days_until_expiry,
    d.bag_number,
    d.test_result,
    d.status
FROM donations d
JOIN donors dn ON d.donor_id = dn.donor_id
ORDER BY d.donation_date DESC;

-- View: Blood Group Statistics
CREATE OR REPLACE VIEW view_blood_group_statistics AS
SELECT 
    bg.group_code,
    bg.group_name,
    COUNT(DISTINCT d.donor_id) AS total_donors,
    COUNT(DISTINCT CASE WHEN d.is_eligible = TRUE THEN d.donor_id END) AS eligible_donors,
    COALESCE(bs.units_available, 0) AS current_stock,
    COUNT(DISTINCT CASE WHEN br.status = 'Pending' THEN br.request_id END) AS pending_requests
FROM blood_groups bg
LEFT JOIN donors d ON bg.group_code = d.blood_group AND d.status = 'Active'
LEFT JOIN blood_stock bs ON bg.group_code = bs.group_code
LEFT JOIN blood_requests br ON bg.group_code = br.blood_group_requested
GROUP BY bg.group_code, bg.group_name, bs.units_available
ORDER BY bg.group_code;

-- View: Donor Directory
CREATE OR REPLACE VIEW view_donor_directory AS
SELECT 
    d.donor_id,
    CONCAT(d.first_name, ' ', d.last_name) AS full_name,
    d.blood_group,
    d.gender,
    d.age,
    d.phone,
    d.email,
    d.city,
    d.is_eligible,
    d.total_donations,
    d.last_donation_date,
    d.status
FROM donors d
WHERE d.status = 'Active'
ORDER BY d.first_name, d.last_name;

-- View: Recent Activity
CREATE OR REPLACE VIEW view_recent_activity AS
SELECT 
    al.log_id,
    al.action,
    al.table_name,
    al.description,
    COALESCE(a.username, 'System') AS performed_by,
    al.ip_address,
    al.created_at
FROM activity_logs al
LEFT JOIN admins a ON al.user_id = a.admin_id
ORDER BY al.created_at DESC
LIMIT 100;

-- View: Unread Notifications
CREATE OR REPLACE VIEW view_unread_notifications AS
SELECT 
    n.notification_id,
    n.notification_type,
    n.title,
    n.message,
    n.severity,
    n.created_at
FROM notification_logs n
WHERE n.is_read = FALSE
ORDER BY 
    CASE n.severity WHEN 'Critical' THEN 1 WHEN 'Warning' THEN 2 ELSE 3 END,
    n.created_at DESC;

-- ============================================================================
-- TRIGGERS
-- ============================================================================

DELIMITER //

-- Trigger: Update stock when donation marked available
CREATE TRIGGER trg_after_donation_available
AFTER UPDATE ON donations
FOR EACH ROW
BEGIN
    IF NEW.status = 'Available' AND OLD.status != 'Available' THEN
        UPDATE blood_stock 
        SET units_available = units_available + NEW.units_donated,
            last_updated = CURRENT_TIMESTAMP
        WHERE group_code = NEW.blood_group;
        
        INSERT INTO activity_logs (user_type, action, table_name, record_id, description)
        VALUES ('System', 'STOCK_INCREASE', 'blood_stock', NULL, 
                CONCAT('Stock increased by ', NEW.units_donated, ' units for ', NEW.blood_group));
    END IF;
    
    IF NEW.status = 'Issued' AND OLD.status = 'Available' THEN
        UPDATE blood_stock 
        SET units_available = units_available - NEW.units_donated,
            last_updated = CURRENT_TIMESTAMP
        WHERE group_code = NEW.blood_group;
    END IF;
END//

-- Trigger: Update donor stats after donation
CREATE TRIGGER trg_after_donation_insert
AFTER INSERT ON donations
FOR EACH ROW
BEGIN
    UPDATE donors 
    SET total_donations = total_donations + 1,
        last_donation_date = NEW.donation_date,
        is_eligible = FALSE,
        updated_at = CURRENT_TIMESTAMP
    WHERE donor_id = NEW.donor_id;
    
    INSERT INTO donor_eligibility_logs (donor_id, check_date, is_eligible, reason, next_eligible_date)
    VALUES (NEW.donor_id, CURDATE(), FALSE, 
            CONCAT('Donated on ', NEW.donation_date), 
            DATE_ADD(NEW.donation_date, INTERVAL 90 DAY));
END//

-- Trigger: Low stock alert
CREATE TRIGGER trg_low_stock_alert
AFTER UPDATE ON blood_stock
FOR EACH ROW
BEGIN
    IF NEW.units_available < NEW.minimum_threshold AND 
       OLD.units_available >= OLD.minimum_threshold THEN
        INSERT INTO notification_logs (notification_type, title, message, severity, related_table, related_id)
        VALUES ('Low Stock', CONCAT('Low Stock Alert: ', NEW.group_code),
                CONCAT('Blood group ', NEW.group_code, ' stock is low. Current: ', NEW.units_available, ' units.'),
                'Critical', 'blood_stock', NEW.stock_id);
    END IF;
END//

-- Trigger: Request approval stock reservation
CREATE TRIGGER trg_after_request_approved
AFTER UPDATE ON blood_requests
FOR EACH ROW
BEGIN
    IF NEW.status = 'Approved' AND OLD.status = 'Pending' THEN
        UPDATE blood_stock 
        SET units_reserved = units_reserved + NEW.units_approved
        WHERE group_code = NEW.blood_group_requested;
        
        INSERT INTO notification_logs (notification_type, title, message, severity, related_table, related_id)
        VALUES ('Request Alert', CONCAT('Request Approved #', NEW.request_id),
                CONCAT(NEW.units_approved, ' units of ', NEW.blood_group_requested, ' approved'),
                'Info', 'blood_requests', NEW.request_id);
    END IF;
    
    IF NEW.status = 'Issued' AND OLD.status = 'Approved' THEN
        UPDATE blood_stock 
        SET units_reserved = units_reserved - NEW.units_approved,
            units_available = units_available - NEW.units_approved
        WHERE group_code = NEW.blood_group_requested;
    END IF;
END//

-- Trigger: Urgent request notification
CREATE TRIGGER trg_urgent_request_notification
AFTER INSERT ON blood_requests
FOR EACH ROW
BEGIN
    IF NEW.urgency_level IN ('Urgent', 'Critical') THEN
        INSERT INTO notification_logs (notification_type, title, message, severity, related_table, related_id)
        VALUES ('Request Alert', CONCAT(NEW.urgency_level, ' Request - ', NEW.blood_group_requested),
                CONCAT(NEW.urgency_level, ' request for ', NEW.units_requested, ' units of ', NEW.blood_group_requested),
                IF(NEW.urgency_level = 'Critical', 'Critical', 'Warning'),
                'blood_requests', NEW.request_id);
    END IF;
END//

DELIMITER ;

-- ============================================================================
-- STORED PROCEDURES
-- ============================================================================

DELIMITER //

-- Procedure: Register Donor
CREATE PROCEDURE sp_RegisterDonor(
    IN p_first_name VARCHAR(60), IN p_last_name VARCHAR(60), IN p_dob DATE,
    IN p_gender ENUM('Male', 'Female', 'Other'), IN p_blood_group VARCHAR(5),
    IN p_phone VARCHAR(20), IN p_email VARCHAR(150), IN p_address TEXT,
    IN p_city VARCHAR(100), IN p_state VARCHAR(100), IN p_pincode VARCHAR(10),
    IN p_weight DECIMAL(5,2), IN p_registered_by INT,
    OUT p_donor_id INT, OUT p_status VARCHAR(20), OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_status = 'ERROR';
        SET p_message = 'Database error during registration';
        SET p_donor_id = 0;
    END;
    
    START TRANSACTION;
    
    IF EXISTS (SELECT 1 FROM donors WHERE phone = p_phone) THEN
        SET p_status = 'ERROR';
        SET p_message = 'Phone number already exists';
        SET p_donor_id = 0;
        ROLLBACK;
    ELSEIF TIMESTAMPDIFF(YEAR, p_dob, CURDATE()) < 18 OR TIMESTAMPDIFF(YEAR, p_dob, CURDATE()) > 65 THEN
        SET p_status = 'ERROR';
        SET p_message = 'Age must be between 18-65';
        SET p_donor_id = 0;
        ROLLBACK;
    ELSEIF p_weight < 45 THEN
        SET p_status = 'ERROR';
        SET p_message = 'Weight must be at least 45 kg';
        SET p_donor_id = 0;
        ROLLBACK;
    ELSE
        INSERT INTO donors (first_name, last_name, date_of_birth, gender, blood_group,
            phone, email, address, city, state, pincode, weight, registered_by)
        VALUES (p_first_name, p_last_name, p_dob, p_gender, p_blood_group,
            p_phone, p_email, p_address, p_city, p_state, p_pincode, p_weight, p_registered_by);
        
        SET p_donor_id = LAST_INSERT_ID();
        SET p_status = 'SUCCESS';
        SET p_message = CONCAT('Donor registered with ID: ', p_donor_id);
        
        INSERT INTO activity_logs (user_id, user_type, action, table_name, record_id, description)
        VALUES (p_registered_by, 'Admin', 'DONOR_REGISTERED', 'donors', p_donor_id,
                CONCAT('Registered: ', p_first_name, ' ', p_last_name));
        
        COMMIT;
    END IF;
END//

-- Procedure: Approve Blood Request
CREATE PROCEDURE sp_ApproveBloodRequest(
    IN p_request_id INT, IN p_units INT, IN p_approved_by INT, IN p_notes TEXT,
    OUT p_status VARCHAR(20), OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE v_blood_group VARCHAR(5);
    DECLARE v_current_stock INT;
    DECLARE v_request_status VARCHAR(20);
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_status = 'ERROR';
        SET p_message = 'Database error during approval';
    END;
    
    START TRANSACTION;
    
    SELECT blood_group_requested, status INTO v_blood_group, v_request_status
    FROM blood_requests WHERE request_id = p_request_id;
    
    IF v_blood_group IS NULL THEN
        SET p_status = 'ERROR';
        SET p_message = 'Request not found';
        ROLLBACK;
    ELSEIF v_request_status != 'Pending' THEN
        SET p_status = 'ERROR';
        SET p_message = CONCAT('Cannot approve. Status: ', v_request_status);
        ROLLBACK;
    ELSE
        SELECT units_available INTO v_current_stock FROM blood_stock WHERE group_code = v_blood_group;
        
        IF v_current_stock < p_units THEN
            SET p_status = 'ERROR';
            SET p_message = CONCAT('Insufficient stock. Available: ', v_current_stock);
            ROLLBACK;
        ELSE
            UPDATE blood_requests
            SET status = 'Approved', units_approved = p_units, approved_by = p_approved_by,
                approved_date = CURRENT_TIMESTAMP, notes = p_notes
            WHERE request_id = p_request_id;
            
            SET p_status = 'SUCCESS';
            SET p_message = CONCAT('Approved ', p_units, ' units');
            COMMIT;
        END IF;
    END IF;
END//

-- Procedure: Generate Stock Summary
CREATE PROCEDURE sp_GenerateStockSummary()
BEGIN
    SELECT bg.group_code, bg.group_name,
        COALESCE(bs.units_available, 0) AS units_available,
        COALESCE(bs.units_reserved, 0) AS units_reserved,
        COALESCE(bs.minimum_threshold, 5) AS min_threshold,
        CASE 
            WHEN COALESCE(bs.units_available, 0) < COALESCE(bs.minimum_threshold, 5) THEN 'Critical'
            WHEN COALESCE(bs.units_available, 0) <= (COALESCE(bs.minimum_threshold, 5) + 2) THEN 'Low'
            ELSE 'Normal'
        END AS stock_status
    FROM blood_groups bg
    LEFT JOIN blood_stock bs ON bg.group_code = bs.group_code
    ORDER BY bg.group_code;
END//

-- Procedure: Get Dashboard Stats
CREATE PROCEDURE sp_GetDashboardStats()
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM donors WHERE status = 'Active') AS total_donors,
        (SELECT COUNT(*) FROM donors WHERE is_eligible = TRUE AND status = 'Active') AS eligible_donors,
        (SELECT COUNT(*) FROM patients WHERE status = 'Admitted') AS admitted_patients,
        (SELECT COALESCE(SUM(units_available), 0) FROM blood_stock) AS total_blood_units,
        (SELECT COUNT(*) FROM blood_requests WHERE status = 'Pending') AS pending_requests,
        (SELECT COUNT(*) FROM donations WHERE MONTH(donation_date) = MONTH(CURDATE())) AS donations_this_month,
        (SELECT COUNT(*) FROM notification_logs WHERE is_read = FALSE) AS unread_notifications;
END//

-- Procedure: Search Donors
CREATE PROCEDURE sp_SearchDonors(
    IN p_blood_group VARCHAR(5), IN p_city VARCHAR(100), IN p_eligible_only BOOLEAN
)
BEGIN
    SELECT donor_id, CONCAT(first_name, ' ', last_name) AS donor_name,
        blood_group, phone, email, city, age, is_eligible, total_donations, last_donation_date
    FROM donors
    WHERE status = 'Active'
      AND (p_blood_group IS NULL OR blood_group = p_blood_group)
      AND (p_city IS NULL OR city LIKE CONCAT('%', p_city, '%'))
      AND (p_eligible_only = FALSE OR is_eligible = TRUE)
    ORDER BY is_eligible DESC, last_donation_date ASC;
END//

DELIMITER ;

-- ============================================================================
-- SAMPLE DATA
-- ============================================================================

-- Blood Groups
INSERT INTO blood_groups (group_code, group_name, can_donate_to, can_receive_from, description) VALUES
('A+', 'A Positive', 'A+, AB+', 'A+, A-, O+, O-', 'Second most common blood type'),
('A-', 'A Negative', 'A+, A-, AB+, AB-', 'A-, O-', 'Universal platelet donor'),
('B+', 'B Positive', 'B+, AB+', 'B+, B-, O+, O-', 'Found in about 8% of population'),
('B-', 'B Negative', 'B+, B-, AB+, AB-', 'B-, O-', 'Rare blood type'),
('AB+', 'AB Positive', 'AB+', 'All Types', 'Universal recipient'),
('AB-', 'AB Negative', 'AB+, AB-', 'AB-, A-, B-, O-', 'Rarest blood type'),
('O+', 'O Positive', 'O+, A+, B+, AB+', 'O+, O-', 'Most common blood type'),
('O-', 'O Negative', 'All Types', 'O-', 'Universal donor');

-- Admin (Password: admin123)
INSERT INTO admins (username, email, password_hash, full_name, phone) VALUES
('admin', 'admin@bbms.com', '$2y$10$8K1p/a0dR1xqM8k3Z9Q8VeQwEFP7T5H5F5H5F5H5F5H5F5H5F5H5u', 'System Administrator', '9999999999');

-- Blood Stock
INSERT INTO blood_stock (group_code, units_available, units_reserved, minimum_threshold) VALUES
('A+', 15, 0, 5), ('A-', 8, 0, 5), ('B+', 12, 0, 5), ('B-', 5, 0, 5),
('AB+', 7, 0, 5), ('AB-', 3, 0, 5), ('O+', 20, 0, 5), ('O-', 10, 0, 5);

-- Hospitals
INSERT INTO hospitals (hospital_name, hospital_type, phone, city, is_verified, is_active) VALUES
('City General Hospital', 'Government', '9876543210', 'Mumbai', TRUE, TRUE),
('Apollo Hospital', 'Private', '9876543211', 'Mumbai', TRUE, TRUE),
('Fortis Healthcare', 'Private', '9876543212', 'Mumbai', TRUE, TRUE);

-- Donors
INSERT INTO donors (first_name, last_name, date_of_birth, gender, blood_group, phone, email, city, weight, is_eligible, registered_by) VALUES
('Rahul', 'Sharma', '1990-05-15', 'Male', 'A+', '9812345001', 'rahul@email.com', 'Mumbai', 70.5, TRUE, 1),
('Priya', 'Patel', '1988-08-22', 'Female', 'B+', '9812345002', 'priya@email.com', 'Mumbai', 58.0, TRUE, 1),
('Amit', 'Kumar', '1992-03-10', 'Male', 'O+', '9812345003', 'amit@email.com', 'Pune', 75.0, TRUE, 1),
('Neha', 'Singh', '1995-11-28', 'Female', 'AB+', '9812345004', 'neha@email.com', 'Mumbai', 55.0, TRUE, 1),
('Vikram', 'Reddy', '1985-07-05', 'Male', 'O-', '9812345005', 'vikram@email.com', 'Pune', 80.0, TRUE, 1);

-- Patients
INSERT INTO patients (first_name, last_name, date_of_birth, gender, blood_group_needed, phone, city, hospital_id, doctor_name, status) VALUES
('Arjun', 'Kapoor', '1975-03-20', 'Male', 'A+', '9823456001', 'Mumbai', 1, 'Dr. Heart', 'Admitted'),
('Lakshmi', 'Iyer', '1982-07-15', 'Female', 'B+', '9823456002', 'Mumbai', 2, 'Dr. Care', 'Admitted');

-- Sample Blood Requests
INSERT INTO blood_requests (hospital_id, requester_name, requester_phone, blood_group_requested, units_requested, urgency_level, required_date, status) VALUES
(1, 'Dr. Heart', '9834567001', 'A+', 2, 'Urgent', DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'Pending'),
(2, 'Dr. Care', '9834567002', 'B+', 3, 'Critical', CURDATE(), 'Pending');

-- Welcome Notification
INSERT INTO notification_logs (notification_type, title, message, severity) VALUES
('System', 'Welcome to BBMS', 'Blood Bank Management System is now active.', 'Info');

-- Initial Activity Log
INSERT INTO activity_logs (user_type, action, description) VALUES
('System', 'SYSTEM_INIT', 'Database initialized with sample data');

-- ============================================================================
-- END OF COMPLETE DATABASE SETUP
-- ============================================================================

SELECT 'BBMS Database Setup Complete!' AS Status;
SELECT 'Login: admin / admin123' AS Credentials;
