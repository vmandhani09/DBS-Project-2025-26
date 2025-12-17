-- ============================================================================
-- BLOOD BANK MANAGEMENT SYSTEM (BBMS) - DATABASE VIEWS
-- MySQL 8+ Compatible
-- ============================================================================

USE bbms;

-- ============================================================================
-- VIEW 1: Available Blood Stock
-- ============================================================================
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

-- ============================================================================
-- VIEW 2: Pending Blood Requests
-- ============================================================================
CREATE OR REPLACE VIEW view_pending_requests AS
SELECT 
    r.request_id,
    r.requester_name,
    r.requester_phone,
    r.requester_email,
    r.blood_group_requested,
    r.units_requested,
    r.urgency_level,
    r.purpose,
    r.required_date,
    r.request_date,
    DATEDIFF(r.required_date, CURDATE()) AS days_until_required,
    CASE 
        WHEN r.required_date < CURDATE() THEN 'Overdue'
        WHEN DATEDIFF(r.required_date, CURDATE()) <= 1 THEN 'Urgent'
        WHEN DATEDIFF(r.required_date, CURDATE()) <= 3 THEN 'Soon'
        ELSE 'Normal'
    END AS time_priority,
    p.first_name AS patient_first_name,
    p.last_name AS patient_last_name,
    h.hospital_name,
    COALESCE(bs.units_available, 0) AS current_stock
FROM blood_requests r
LEFT JOIN patients p ON r.patient_id = p.patient_id
LEFT JOIN hospitals h ON r.hospital_id = h.hospital_id
LEFT JOIN blood_stock bs ON r.blood_group_requested = bs.group_code
WHERE r.status = 'Pending'
ORDER BY 
    CASE r.urgency_level 
        WHEN 'Critical' THEN 1 
        WHEN 'Urgent' THEN 2 
        ELSE 3 
    END,
    r.required_date ASC;

-- ============================================================================
-- VIEW 3: Donation Summary
-- ============================================================================
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
    d.status,
    d.collection_center,
    d.collected_by,
    CASE 
        WHEN d.status = 'Expired' THEN 'Expired'
        WHEN d.expiry_date < CURDATE() THEN 'Expired'
        WHEN DATEDIFF(d.expiry_date, CURDATE()) <= 7 THEN 'Expiring Soon'
        ELSE 'Valid'
    END AS expiry_status
FROM donations d
JOIN donors dn ON d.donor_id = dn.donor_id
ORDER BY d.donation_date DESC;

-- ============================================================================
-- VIEW 4: Blood Group Statistics
-- ============================================================================
CREATE OR REPLACE VIEW view_blood_group_statistics AS
SELECT 
    bg.group_code,
    bg.group_name,
    COUNT(DISTINCT d.donor_id) AS total_donors,
    COUNT(DISTINCT CASE WHEN d.is_eligible = TRUE THEN d.donor_id END) AS eligible_donors,
    COALESCE(bs.units_available, 0) AS current_stock,
    COALESCE(SUM(CASE WHEN dn.status = 'Available' THEN dn.units_donated ELSE 0 END), 0) AS available_units,
    COUNT(DISTINCT CASE WHEN dn.donation_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN dn.donation_id END) AS donations_last_30_days,
    COUNT(DISTINCT CASE WHEN br.status = 'Pending' THEN br.request_id END) AS pending_requests,
    COALESCE(SUM(CASE WHEN br.status = 'Pending' THEN br.units_requested ELSE 0 END), 0) AS pending_units_requested
FROM blood_groups bg
LEFT JOIN donors d ON bg.group_code = d.blood_group AND d.status = 'Active'
LEFT JOIN blood_stock bs ON bg.group_code = bs.group_code
LEFT JOIN donations dn ON bg.group_code = dn.blood_group
LEFT JOIN blood_requests br ON bg.group_code = br.blood_group_requested
GROUP BY bg.group_code, bg.group_name, bs.units_available
ORDER BY bg.group_code;

-- ============================================================================
-- VIEW 5: Donor Directory
-- ============================================================================
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
    d.state,
    d.is_eligible,
    d.total_donations,
    d.last_donation_date,
    CASE 
        WHEN d.last_donation_date IS NULL THEN 'Never donated'
        WHEN DATEDIFF(CURDATE(), d.last_donation_date) > 365 THEN 'More than a year ago'
        WHEN DATEDIFF(CURDATE(), d.last_donation_date) > 180 THEN 'More than 6 months ago'
        WHEN DATEDIFF(CURDATE(), d.last_donation_date) > 90 THEN 'More than 3 months ago'
        ELSE CONCAT(DATEDIFF(CURDATE(), d.last_donation_date), ' days ago')
    END AS last_donation_status,
    d.status,
    d.created_at AS registered_on
FROM donors d
WHERE d.status = 'Active'
ORDER BY d.first_name, d.last_name;

-- ============================================================================
-- VIEW 6: Patient Records
-- ============================================================================
CREATE OR REPLACE VIEW view_patient_records AS
SELECT 
    p.patient_id,
    CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
    p.age,
    p.gender,
    p.blood_group_needed,
    p.phone,
    h.hospital_name,
    p.ward_number,
    p.doctor_name,
    p.disease_diagnosis,
    p.status AS patient_status,
    p.admitted_on,
    p.discharged_on,
    DATEDIFF(COALESCE(p.discharged_on, CURDATE()), p.admitted_on) AS days_admitted
FROM patients p
LEFT JOIN hospitals h ON p.hospital_id = h.hospital_id
ORDER BY p.admitted_on DESC;

-- ============================================================================
-- VIEW 7: Recent Activity
-- ============================================================================
CREATE OR REPLACE VIEW view_recent_activity AS
SELECT 
    al.log_id,
    al.action,
    al.table_name,
    al.record_id,
    al.description,
    al.user_type,
    COALESCE(a.username, 'System') AS performed_by,
    al.ip_address,
    al.created_at,
    CASE 
        WHEN TIMESTAMPDIFF(MINUTE, al.created_at, NOW()) < 60 THEN CONCAT(TIMESTAMPDIFF(MINUTE, al.created_at, NOW()), ' minutes ago')
        WHEN TIMESTAMPDIFF(HOUR, al.created_at, NOW()) < 24 THEN CONCAT(TIMESTAMPDIFF(HOUR, al.created_at, NOW()), ' hours ago')
        ELSE CONCAT(TIMESTAMPDIFF(DAY, al.created_at, NOW()), ' days ago')
    END AS time_ago
FROM activity_logs al
LEFT JOIN admins a ON al.user_id = a.admin_id
ORDER BY al.created_at DESC
LIMIT 100;

-- ============================================================================
-- VIEW 8: Unread Notifications
-- ============================================================================
CREATE OR REPLACE VIEW view_unread_notifications AS
SELECT 
    n.notification_id,
    n.notification_type,
    n.title,
    n.message,
    n.severity,
    n.related_table,
    n.related_id,
    n.created_at,
    CASE 
        WHEN TIMESTAMPDIFF(MINUTE, n.created_at, NOW()) < 60 THEN CONCAT(TIMESTAMPDIFF(MINUTE, n.created_at, NOW()), ' min ago')
        WHEN TIMESTAMPDIFF(HOUR, n.created_at, NOW()) < 24 THEN CONCAT(TIMESTAMPDIFF(HOUR, n.created_at, NOW()), ' hrs ago')
        ELSE DATE_FORMAT(n.created_at, '%b %d, %Y')
    END AS time_ago
FROM notification_logs n
WHERE n.is_read = FALSE
ORDER BY 
    CASE n.severity 
        WHEN 'Critical' THEN 1 
        WHEN 'Warning' THEN 2 
        ELSE 3 
    END,
    n.created_at DESC;

-- ============================================================================
-- VIEW 9: Blood Issue History
-- ============================================================================
CREATE OR REPLACE VIEW view_blood_issue_history AS
SELECT 
    bi.issue_id,
    bi.blood_group,
    bi.units_issued,
    bi.issued_to,
    bi.receiver_name,
    bi.receiver_phone,
    bi.issue_date,
    CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
    h.hospital_name,
    br.urgency_level AS request_urgency,
    COALESCE(a.username, 'Unknown') AS issued_by_admin
FROM blood_issues bi
LEFT JOIN patients p ON bi.patient_id = p.patient_id
LEFT JOIN blood_requests br ON bi.request_id = br.request_id
LEFT JOIN hospitals h ON br.hospital_id = h.hospital_id
LEFT JOIN admins a ON bi.issued_by = a.admin_id
ORDER BY bi.issue_date DESC;

-- ============================================================================
-- VIEW 10: Expiring Blood Units
-- ============================================================================
CREATE OR REPLACE VIEW view_expiring_blood AS
SELECT 
    d.donation_id,
    d.bag_number,
    d.blood_group,
    d.units_donated,
    d.donation_date,
    d.expiry_date,
    DATEDIFF(d.expiry_date, CURDATE()) AS days_until_expiry,
    CONCAT(dn.first_name, ' ', dn.last_name) AS donor_name,
    d.collection_center,
    CASE 
        WHEN DATEDIFF(d.expiry_date, CURDATE()) <= 0 THEN 'Expired'
        WHEN DATEDIFF(d.expiry_date, CURDATE()) <= 3 THEN 'Critical'
        WHEN DATEDIFF(d.expiry_date, CURDATE()) <= 7 THEN 'Warning'
        ELSE 'OK'
    END AS expiry_alert
FROM donations d
JOIN donors dn ON d.donor_id = dn.donor_id
WHERE d.status = 'Available'
  AND d.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 14 DAY)
ORDER BY d.expiry_date ASC;

-- ============================================================================
-- END OF VIEWS
-- ============================================================================
