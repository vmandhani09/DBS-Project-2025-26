-- ============================================================================
-- BLOOD BANK MANAGEMENT SYSTEM (BBMS) - SAMPLE DATA
-- MySQL 8+ Compatible
-- ============================================================================

USE bbms;

-- ============================================================================
-- 1. INSERT BLOOD GROUPS
-- ============================================================================
INSERT INTO blood_groups (group_code, group_name, can_donate_to, can_receive_from, description) VALUES
('A+', 'A Positive', 'A+, AB+', 'A+, A-, O+, O-', 'Second most common blood type'),
('A-', 'A Negative', 'A+, A-, AB+, AB-', 'A-, O-', 'Rare blood type, universal platelet donor'),
('B+', 'B Positive', 'B+, AB+', 'B+, B-, O+, O-', 'Found in about 8% of population'),
('B-', 'B Negative', 'B+, B-, AB+, AB-', 'B-, O-', 'Rare blood type'),
('AB+', 'AB Positive', 'AB+', 'All Types', 'Universal plasma donor, universal recipient'),
('AB-', 'AB Negative', 'AB+, AB-', 'AB-, A-, B-, O-', 'Rarest blood type'),
('O+', 'O Positive', 'O+, A+, B+, AB+', 'O+, O-', 'Most common blood type'),
('O-', 'O Negative', 'All Types', 'O-', 'Universal donor for red blood cells');

-- ============================================================================
-- 2. INSERT ADMIN USER
-- Password: admin123 (bcrypt hashed)
-- ============================================================================
INSERT INTO admins (username, email, password_hash, full_name, phone, is_active) VALUES
('admin', 'admin@bbms.com', '$2y$10$8K1p/a0dR1xqM8k3Z9Q8VeQwEFP7T5H5F5H5F5H5F5H5F5H5F5H5u', 'System Administrator', '9999999999', TRUE);

-- ============================================================================
-- 3. INSERT BLOOD STOCK (Initialize with 0)
-- ============================================================================
INSERT INTO blood_stock (group_code, units_available, units_reserved, minimum_threshold, maximum_capacity) VALUES
('A+', 15, 0, 5, 100),
('A-', 8, 0, 5, 100),
('B+', 12, 0, 5, 100),
('B-', 5, 0, 5, 100),
('AB+', 7, 0, 5, 100),
('AB-', 3, 0, 5, 100),
('O+', 20, 0, 5, 100),
('O-', 10, 0, 5, 100);

-- ============================================================================
-- 4. INSERT SAMPLE HOSPITALS
-- ============================================================================
INSERT INTO hospitals (hospital_name, hospital_type, registration_number, contact_person, phone, email, address, city, state, pincode, is_verified, is_active) VALUES
('City General Hospital', 'Government', 'GOV-HOS-001', 'Dr. Rajesh Kumar', '9876543210', 'info@citygeneralhospital.com', '123 Main Street', 'Mumbai', 'Maharashtra', '400001', TRUE, TRUE),
('Apollo Hospital', 'Private', 'PVT-HOS-002', 'Dr. Priya Sharma', '9876543211', 'contact@apollomumbai.com', '456 Park Avenue', 'Mumbai', 'Maharashtra', '400002', TRUE, TRUE),
('Fortis Healthcare', 'Private', 'PVT-HOS-003', 'Dr. Amit Patel', '9876543212', 'info@fortismumbai.com', '789 Health Road', 'Mumbai', 'Maharashtra', '400003', TRUE, TRUE),
('Government Medical College', 'Government', 'GOV-HOS-004', 'Dr. Sunita Reddy', '9876543213', 'gmc@gov.in', '101 Medical Lane', 'Pune', 'Maharashtra', '411001', TRUE, TRUE),
('Lilavati Hospital', 'Trust', 'TRU-HOS-005', 'Dr. Neha Gupta', '9876543214', 'info@lilavatihospital.com', '202 Trust Road', 'Mumbai', 'Maharashtra', '400050', TRUE, TRUE);

-- ============================================================================
-- 5. INSERT SAMPLE DONORS
-- ============================================================================
INSERT INTO donors (first_name, last_name, date_of_birth, gender, blood_group, phone, email, address, city, state, pincode, weight, total_donations, is_eligible, status, registered_by) VALUES
('Rahul', 'Sharma', '1990-05-15', 'Male', 'A+', '9812345001', 'rahul.sharma@email.com', '101 Donor Street', 'Mumbai', 'Maharashtra', '400001', 70.5, 5, TRUE, 'Active', 1),
('Priya', 'Patel', '1988-08-22', 'Female', 'B+', '9812345002', 'priya.patel@email.com', '102 Donor Street', 'Mumbai', 'Maharashtra', '400002', 58.0, 3, TRUE, 'Active', 1),
('Amit', 'Kumar', '1992-03-10', 'Male', 'O+', '9812345003', 'amit.kumar@email.com', '103 Donor Street', 'Pune', 'Maharashtra', '411001', 75.0, 8, TRUE, 'Active', 1),
('Neha', 'Singh', '1995-11-28', 'Female', 'AB+', '9812345004', 'neha.singh@email.com', '104 Donor Street', 'Mumbai', 'Maharashtra', '400003', 55.0, 2, TRUE, 'Active', 1),
('Vikram', 'Reddy', '1985-07-05', 'Male', 'O-', '9812345005', 'vikram.reddy@email.com', '105 Donor Street', 'Pune', 'Maharashtra', '411002', 80.0, 12, TRUE, 'Active', 1),
('Anjali', 'Gupta', '1993-02-18', 'Female', 'A-', '9812345006', 'anjali.gupta@email.com', '106 Donor Street', 'Mumbai', 'Maharashtra', '400004', 52.0, 4, TRUE, 'Active', 1),
('Rajesh', 'Verma', '1987-09-30', 'Male', 'B-', '9812345007', 'rajesh.verma@email.com', '107 Donor Street', 'Mumbai', 'Maharashtra', '400005', 72.0, 6, TRUE, 'Active', 1),
('Sunita', 'Joshi', '1991-12-12', 'Female', 'AB-', '9812345008', 'sunita.joshi@email.com', '108 Donor Street', 'Pune', 'Maharashtra', '411003', 60.0, 1, TRUE, 'Active', 1),
('Karan', 'Malhotra', '1994-06-25', 'Male', 'A+', '9812345009', 'karan.malhotra@email.com', '109 Donor Street', 'Mumbai', 'Maharashtra', '400006', 68.0, 3, TRUE, 'Active', 1),
('Meera', 'Nair', '1989-04-08', 'Female', 'O+', '9812345010', 'meera.nair@email.com', '110 Donor Street', 'Mumbai', 'Maharashtra', '400007', 56.0, 7, TRUE, 'Active', 1);

-- ============================================================================
-- 6. INSERT SAMPLE PATIENTS
-- ============================================================================
INSERT INTO patients (first_name, last_name, date_of_birth, gender, blood_group_needed, phone, address, city, hospital_id, ward_number, disease_diagnosis, doctor_name, attendant_name, attendant_phone, status) VALUES
('Arjun', 'Kapoor', '1975-03-20', 'Male', 'A+', '9823456001', '201 Patient Lane', 'Mumbai', 1, 'W-101', 'Cardiac Surgery', 'Dr. Heart Specialist', 'Sanjay Kapoor', '9823456101', 'Admitted'),
('Lakshmi', 'Iyer', '1982-07-15', 'Female', 'B+', '9823456002', '202 Patient Lane', 'Mumbai', 2, 'W-202', 'Accident - Blood Loss', 'Dr. Trauma Care', 'Ravi Iyer', '9823456102', 'Admitted'),
('Mohammed', 'Khan', '1968-11-30', 'Male', 'O-', '9823456003', '203 Patient Lane', 'Pune', 4, 'ICU-01', 'Major Surgery', 'Dr. Surgeon', 'Ahmed Khan', '9823456103', 'Admitted'),
('Deepika', 'Rao', '1990-05-10', 'Female', 'AB+', '9823456004', '204 Patient Lane', 'Mumbai', 3, 'W-305', 'Anemia Treatment', 'Dr. Hematologist', 'Prakash Rao', '9823456104', 'Admitted'),
('Suresh', 'Menon', '1955-09-25', 'Male', 'O+', '9823456005', '205 Patient Lane', 'Mumbai', 5, 'W-410', 'Kidney Transplant', 'Dr. Nephrologist', 'Ramesh Menon', '9823456105', 'Admitted');

-- ============================================================================
-- 7. INSERT SAMPLE DONATIONS
-- ============================================================================
INSERT INTO donations (donor_id, blood_group, units_donated, donation_date, donation_time, hemoglobin_level, blood_pressure, pulse_rate, temperature, donation_type, collection_center, collected_by, bag_number, expiry_date, test_result, status) VALUES
(1, 'A+', 1, DATE_SUB(CURDATE(), INTERVAL 100 DAY), '10:30:00', 14.5, '120/80', 72, 98.4, 'Whole Blood', 'BBMS Main Center', 'Nurse Rekha', 'BAG-2024-001', DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 100 DAY), INTERVAL 42 DAY), 'Safe', 'Issued'),
(2, 'B+', 1, DATE_SUB(CURDATE(), INTERVAL 95 DAY), '11:00:00', 13.2, '118/78', 70, 98.2, 'Whole Blood', 'BBMS Main Center', 'Nurse Priya', 'BAG-2024-002', DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 95 DAY), INTERVAL 42 DAY), 'Safe', 'Issued'),
(3, 'O+', 1, DATE_SUB(CURDATE(), INTERVAL 30 DAY), '09:45:00', 15.0, '122/82', 74, 98.6, 'Whole Blood', 'BBMS Main Center', 'Nurse Rekha', 'BAG-2024-003', DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 30 DAY), INTERVAL 42 DAY), 'Safe', 'Available'),
(4, 'AB+', 1, DATE_SUB(CURDATE(), INTERVAL 25 DAY), '14:30:00', 12.8, '116/76', 68, 98.0, 'Whole Blood', 'BBMS Main Center', 'Nurse Sunita', 'BAG-2024-004', DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 25 DAY), INTERVAL 42 DAY), 'Safe', 'Available'),
(5, 'O-', 1, DATE_SUB(CURDATE(), INTERVAL 20 DAY), '16:00:00', 14.8, '124/84', 76, 98.4, 'Whole Blood', 'City Blood Camp', 'Nurse Anjali', 'BAG-2024-005', DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 20 DAY), INTERVAL 42 DAY), 'Safe', 'Available'),
(6, 'A-', 1, DATE_SUB(CURDATE(), INTERVAL 15 DAY), '10:00:00', 13.5, '120/80', 72, 98.2, 'Whole Blood', 'BBMS Main Center', 'Nurse Rekha', 'BAG-2024-006', DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 15 DAY), INTERVAL 42 DAY), 'Safe', 'Available'),
(7, 'B-', 1, DATE_SUB(CURDATE(), INTERVAL 10 DAY), '11:30:00', 14.2, '118/78', 70, 98.4, 'Whole Blood', 'BBMS Main Center', 'Nurse Priya', 'BAG-2024-007', DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 10 DAY), INTERVAL 42 DAY), 'Safe', 'Available'),
(8, 'AB-', 1, DATE_SUB(CURDATE(), INTERVAL 5 DAY), '15:00:00', 13.0, '120/80', 74, 98.0, 'Whole Blood', 'City Blood Camp', 'Nurse Sunita', 'BAG-2024-008', DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 5 DAY), INTERVAL 42 DAY), 'Safe', 'Available'),
(9, 'A+', 1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), '09:00:00', 14.0, '122/82', 72, 98.6, 'Whole Blood', 'BBMS Main Center', 'Nurse Rekha', 'BAG-2024-009', DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 3 DAY), INTERVAL 42 DAY), 'Safe', 'Available'),
(10, 'O+', 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '10:15:00', 13.8, '118/78', 70, 98.2, 'Whole Blood', 'BBMS Main Center', 'Nurse Anjali', 'BAG-2024-010', DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 1 DAY), INTERVAL 42 DAY), 'Safe', 'Available');

-- ============================================================================
-- 8. INSERT SAMPLE BLOOD REQUESTS
-- ============================================================================
INSERT INTO blood_requests (patient_id, hospital_id, requester_name, requester_phone, requester_email, blood_group_requested, units_requested, urgency_level, purpose, required_date, status) VALUES
(1, 1, 'Dr. Heart Specialist', '9834567001', 'heart@cityhospital.com', 'A+', 2, 'Urgent', 'Pre-operative requirement for cardiac surgery', DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'Pending'),
(2, 2, 'Dr. Trauma Care', '9834567002', 'trauma@apollo.com', 'B+', 3, 'Critical', 'Emergency blood transfusion after accident', CURDATE(), 'Pending'),
(3, 4, 'Dr. Surgeon', '9834567003', 'surgeon@gmc.gov.in', 'O-', 2, 'Urgent', 'Major surgery scheduled', DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'Pending'),
(4, 3, 'Dr. Hematologist', '9834567004', 'hema@fortis.com', 'AB+', 1, 'Normal', 'Anemia treatment', DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'Pending'),
(5, 5, 'Dr. Nephrologist', '9834567005', 'nephro@lilavati.com', 'O+', 4, 'Urgent', 'Kidney transplant surgery', DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'Pending');

-- ============================================================================
-- 9. INSERT SAMPLE NOTIFICATIONS
-- ============================================================================
INSERT INTO notification_logs (notification_type, title, message, severity, related_table, related_id) VALUES
('Low Stock', 'Low Stock Alert: AB-', 'Blood group AB- stock is critically low. Current units: 3. Minimum threshold: 5. Please arrange for blood donation camps.', 'Critical', 'blood_stock', 6),
('Request Alert', 'Critical Blood Request - B+', 'URGENT: Critical blood request received for 3 units of B+. Required immediately for accident victim.', 'Critical', 'blood_requests', 2),
('System', 'Welcome to BBMS', 'Blood Bank Management System is now active and ready for use.', 'Info', NULL, NULL);

-- ============================================================================
-- 10. INSERT SAMPLE ACTIVITY LOGS
-- ============================================================================
INSERT INTO activity_logs (user_id, user_type, action, table_name, record_id, description, ip_address) VALUES
(1, 'Admin', 'LOGIN', 'admins', 1, 'Admin logged in successfully', '127.0.0.1'),
(1, 'Admin', 'DONOR_REGISTERED', 'donors', 1, 'New donor registered: Rahul Sharma (A+)', '127.0.0.1'),
(1, 'Admin', 'DONATION_RECORDED', 'donations', 1, 'New donation recorded for donor #1', '127.0.0.1'),
(NULL, 'System', 'STOCK_UPDATE', 'blood_stock', NULL, 'Blood stock initialized for all blood groups', NULL),
(1, 'Admin', 'REQUEST_CREATED', 'blood_requests', 1, 'New blood request created for A+ blood', '127.0.0.1');

-- ============================================================================
-- END OF SAMPLE DATA
-- ============================================================================
