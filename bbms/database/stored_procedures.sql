-- ============================================================================
-- BLOOD BANK MANAGEMENT SYSTEM (BBMS) - STORED PROCEDURES
-- MySQL 8+ Compatible
-- ============================================================================

USE bbms;

-- ============================================================================
-- PROCEDURE 1: Register New Donor
-- ============================================================================
DELIMITER //

CREATE PROCEDURE sp_RegisterDonor(
    IN p_first_name VARCHAR(60),
    IN p_last_name VARCHAR(60),
    IN p_dob DATE,
    IN p_gender ENUM('Male', 'Female', 'Other'),
    IN p_blood_group VARCHAR(5),
    IN p_phone VARCHAR(20),
    IN p_email VARCHAR(150),
    IN p_address TEXT,
    IN p_city VARCHAR(100),
    IN p_state VARCHAR(100),
    IN p_pincode VARCHAR(10),
    IN p_weight DECIMAL(5,2),
    IN p_medical_conditions TEXT,
    IN p_emergency_contact_name VARCHAR(120),
    IN p_emergency_contact_phone VARCHAR(20),
    IN p_registered_by INT,
    OUT p_donor_id INT,
    OUT p_status VARCHAR(20),
    OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_status = 'ERROR';
        SET p_message = 'Database error occurred during donor registration';
        SET p_donor_id = 0;
    END;
    
    START TRANSACTION;
    
    -- Validate blood group exists
    IF NOT EXISTS (SELECT 1 FROM blood_groups WHERE group_code = p_blood_group) THEN
        SET p_status = 'ERROR';
        SET p_message = 'Invalid blood group specified';
        SET p_donor_id = 0;
        ROLLBACK;
    ELSE
        -- Check for duplicate phone
        IF EXISTS (SELECT 1 FROM donors WHERE phone = p_phone) THEN
            SET p_status = 'ERROR';
            SET p_message = 'A donor with this phone number already exists';
            SET p_donor_id = 0;
            ROLLBACK;
        ELSE
            -- Check age (must be 18-65)
            IF TIMESTAMPDIFF(YEAR, p_dob, CURDATE()) < 18 OR TIMESTAMPDIFF(YEAR, p_dob, CURDATE()) > 65 THEN
                SET p_status = 'ERROR';
                SET p_message = 'Donor must be between 18 and 65 years old';
                SET p_donor_id = 0;
                ROLLBACK;
            ELSE
                -- Check weight (must be at least 45kg)
                IF p_weight < 45 THEN
                    SET p_status = 'ERROR';
                    SET p_message = 'Donor must weigh at least 45 kg';
                    SET p_donor_id = 0;
                    ROLLBACK;
                ELSE
                    -- Insert donor
                    INSERT INTO donors (
                        first_name, last_name, date_of_birth, gender, blood_group,
                        phone, email, address, city, state, pincode, weight,
                        medical_conditions, emergency_contact_name, emergency_contact_phone,
                        registered_by, is_eligible, status
                    ) VALUES (
                        p_first_name, p_last_name, p_dob, p_gender, p_blood_group,
                        p_phone, p_email, p_address, p_city, p_state, p_pincode, p_weight,
                        p_medical_conditions, p_emergency_contact_name, p_emergency_contact_phone,
                        p_registered_by, TRUE, 'Active'
                    );
                    
                    SET p_donor_id = LAST_INSERT_ID();
                    
                    -- Log activity
                    INSERT INTO activity_logs (user_id, user_type, action, table_name, record_id, description)
                    VALUES (p_registered_by, 'Admin', 'DONOR_REGISTERED', 'donors', p_donor_id,
                            CONCAT('New donor registered: ', p_first_name, ' ', p_last_name, ' (', p_blood_group, ')'));
                    
                    SET p_status = 'SUCCESS';
                    SET p_message = CONCAT('Donor registered successfully with ID: ', p_donor_id);
                    
                    COMMIT;
                END IF;
            END IF;
        END IF;
    END IF;
END//

DELIMITER ;

-- ============================================================================
-- PROCEDURE 2: Add Donation
-- ============================================================================
DELIMITER //

CREATE PROCEDURE sp_AddDonation(
    IN p_donor_id INT,
    IN p_units INT,
    IN p_donation_type ENUM('Whole Blood', 'Plasma', 'Platelets', 'RBC'),
    IN p_hemoglobin DECIMAL(4,2),
    IN p_blood_pressure VARCHAR(20),
    IN p_pulse_rate INT,
    IN p_temperature DECIMAL(4,2),
    IN p_collection_center VARCHAR(200),
    IN p_collected_by VARCHAR(120),
    IN p_bag_number VARCHAR(50),
    OUT p_donation_id INT,
    OUT p_status VARCHAR(20),
    OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE v_blood_group VARCHAR(5);
    DECLARE v_is_eligible BOOLEAN;
    DECLARE v_last_donation DATE;
    DECLARE v_expiry_days INT DEFAULT 42; -- Whole blood expiry
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_status = 'ERROR';
        SET p_message = 'Database error occurred during donation recording';
        SET p_donation_id = 0;
    END;
    
    START TRANSACTION;
    
    -- Get donor details
    SELECT blood_group, is_eligible, last_donation_date 
    INTO v_blood_group, v_is_eligible, v_last_donation
    FROM donors WHERE donor_id = p_donor_id;
    
    IF v_blood_group IS NULL THEN
        SET p_status = 'ERROR';
        SET p_message = 'Donor not found';
        SET p_donation_id = 0;
        ROLLBACK;
    ELSEIF NOT v_is_eligible THEN
        SET p_status = 'ERROR';
        SET p_message = CONCAT('Donor is not eligible. Last donation was on ', IFNULL(v_last_donation, 'N/A'));
        SET p_donation_id = 0;
        ROLLBACK;
    ELSEIF p_hemoglobin < 12.5 THEN
        SET p_status = 'ERROR';
        SET p_message = 'Hemoglobin level too low (minimum 12.5 g/dL required)';
        SET p_donation_id = 0;
        ROLLBACK;
    ELSE
        -- Set expiry based on donation type
        IF p_donation_type = 'Platelets' THEN
            SET v_expiry_days = 5;
        ELSEIF p_donation_type = 'Plasma' THEN
            SET v_expiry_days = 365;
        ELSEIF p_donation_type = 'RBC' THEN
            SET v_expiry_days = 42;
        END IF;
        
        -- Insert donation
        INSERT INTO donations (
            donor_id, blood_group, units_donated, donation_date, donation_time,
            hemoglobin_level, blood_pressure, pulse_rate, temperature,
            donation_type, collection_center, collected_by, bag_number,
            expiry_date, status
        ) VALUES (
            p_donor_id, v_blood_group, p_units, CURDATE(), CURTIME(),
            p_hemoglobin, p_blood_pressure, p_pulse_rate, p_temperature,
            p_donation_type, p_collection_center, p_collected_by, p_bag_number,
            DATE_ADD(CURDATE(), INTERVAL v_expiry_days DAY), 'Collected'
        );
        
        SET p_donation_id = LAST_INSERT_ID();
        SET p_status = 'SUCCESS';
        SET p_message = CONCAT('Donation recorded successfully. ID: ', p_donation_id, '. Expiry: ', DATE_ADD(CURDATE(), INTERVAL v_expiry_days DAY));
        
        COMMIT;
    END IF;
END//

DELIMITER ;

-- ============================================================================
-- PROCEDURE 3: Approve Blood Request
-- ============================================================================
DELIMITER //

CREATE PROCEDURE sp_ApproveBloodRequest(
    IN p_request_id INT,
    IN p_units_to_approve INT,
    IN p_approved_by INT,
    IN p_notes TEXT,
    OUT p_status VARCHAR(20),
    OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE v_blood_group VARCHAR(5);
    DECLARE v_units_requested INT;
    DECLARE v_current_stock INT;
    DECLARE v_request_status VARCHAR(20);
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_status = 'ERROR';
        SET p_message = 'Database error occurred during request approval';
    END;
    
    START TRANSACTION;
    
    -- Get request details
    SELECT blood_group_requested, units_requested, status
    INTO v_blood_group, v_units_requested, v_request_status
    FROM blood_requests WHERE request_id = p_request_id;
    
    IF v_blood_group IS NULL THEN
        SET p_status = 'ERROR';
        SET p_message = 'Blood request not found';
        ROLLBACK;
    ELSEIF v_request_status != 'Pending' THEN
        SET p_status = 'ERROR';
        SET p_message = CONCAT('Request cannot be approved. Current status: ', v_request_status);
        ROLLBACK;
    ELSE
        -- Get current stock
        SELECT units_available INTO v_current_stock
        FROM blood_stock WHERE group_code = v_blood_group;
        
        IF v_current_stock IS NULL OR v_current_stock < p_units_to_approve THEN
            SET p_status = 'ERROR';
            SET p_message = CONCAT('Insufficient stock. Available: ', IFNULL(v_current_stock, 0), ' units');
            ROLLBACK;
        ELSE
            -- Update request
            UPDATE blood_requests
            SET status = IF(p_units_to_approve >= v_units_requested, 'Approved', 'Partially Approved'),
                units_approved = p_units_to_approve,
                approved_by = p_approved_by,
                approved_date = CURRENT_TIMESTAMP,
                notes = CONCAT(IFNULL(notes, ''), '\n[Approval Note]: ', IFNULL(p_notes, 'N/A'))
            WHERE request_id = p_request_id;
            
            -- Log activity
            INSERT INTO activity_logs (user_id, user_type, action, table_name, record_id, description)
            VALUES (p_approved_by, 'Admin', 'REQUEST_APPROVED', 'blood_requests', p_request_id,
                    CONCAT('Blood request #', p_request_id, ' approved for ', p_units_to_approve, ' units of ', v_blood_group));
            
            SET p_status = 'SUCCESS';
            SET p_message = CONCAT('Request approved for ', p_units_to_approve, ' units. Stock reserved.');
            
            COMMIT;
        END IF;
    END IF;
END//

DELIMITER ;

-- ============================================================================
-- PROCEDURE 4: Reject Blood Request
-- ============================================================================
DELIMITER //

CREATE PROCEDURE sp_RejectBloodRequest(
    IN p_request_id INT,
    IN p_rejected_by INT,
    IN p_reason TEXT,
    OUT p_status VARCHAR(20),
    OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE v_request_status VARCHAR(20);
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_status = 'ERROR';
        SET p_message = 'Database error occurred during request rejection';
    END;
    
    START TRANSACTION;
    
    SELECT status INTO v_request_status
    FROM blood_requests WHERE request_id = p_request_id;
    
    IF v_request_status IS NULL THEN
        SET p_status = 'ERROR';
        SET p_message = 'Blood request not found';
        ROLLBACK;
    ELSEIF v_request_status NOT IN ('Pending', 'Approved', 'Partially Approved') THEN
        SET p_status = 'ERROR';
        SET p_message = CONCAT('Request cannot be rejected. Current status: ', v_request_status);
        ROLLBACK;
    ELSE
        UPDATE blood_requests
        SET status = 'Rejected',
            rejection_reason = p_reason,
            updated_at = CURRENT_TIMESTAMP
        WHERE request_id = p_request_id;
        
        -- Log activity
        INSERT INTO activity_logs (user_id, user_type, action, table_name, record_id, description)
        VALUES (p_rejected_by, 'Admin', 'REQUEST_REJECTED', 'blood_requests', p_request_id,
                CONCAT('Blood request #', p_request_id, ' rejected. Reason: ', IFNULL(p_reason, 'Not specified')));
        
        SET p_status = 'SUCCESS';
        SET p_message = 'Request rejected successfully';
        
        COMMIT;
    END IF;
END//

DELIMITER ;

-- ============================================================================
-- PROCEDURE 5: Issue Blood
-- ============================================================================
DELIMITER //

CREATE PROCEDURE sp_IssueBlood(
    IN p_request_id INT,
    IN p_issued_by INT,
    IN p_receiver_name VARCHAR(120),
    IN p_receiver_phone VARCHAR(20),
    IN p_receiver_relation VARCHAR(50),
    IN p_receiver_id_type VARCHAR(50),
    IN p_receiver_id_number VARCHAR(50),
    IN p_notes TEXT,
    OUT p_issue_id INT,
    OUT p_status VARCHAR(20),
    OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE v_blood_group VARCHAR(5);
    DECLARE v_units_approved INT;
    DECLARE v_request_status VARCHAR(20);
    DECLARE v_patient_id INT;
    DECLARE v_requester_name VARCHAR(120);
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_status = 'ERROR';
        SET p_message = 'Database error occurred during blood issue';
        SET p_issue_id = 0;
    END;
    
    START TRANSACTION;
    
    -- Get request details
    SELECT blood_group_requested, units_approved, status, patient_id, requester_name
    INTO v_blood_group, v_units_approved, v_request_status, v_patient_id, v_requester_name
    FROM blood_requests WHERE request_id = p_request_id;
    
    IF v_blood_group IS NULL THEN
        SET p_status = 'ERROR';
        SET p_message = 'Blood request not found';
        SET p_issue_id = 0;
        ROLLBACK;
    ELSEIF v_request_status NOT IN ('Approved', 'Partially Approved') THEN
        SET p_status = 'ERROR';
        SET p_message = CONCAT('Blood cannot be issued. Request status: ', v_request_status);
        SET p_issue_id = 0;
        ROLLBACK;
    ELSE
        -- Create issue record
        INSERT INTO blood_issues (
            request_id, patient_id, blood_group, units_issued, issued_to,
            issued_by, receiver_name, receiver_phone, receiver_relation,
            receiver_id_type, receiver_id_number, notes
        ) VALUES (
            p_request_id, v_patient_id, v_blood_group, v_units_approved, v_requester_name,
            p_issued_by, p_receiver_name, p_receiver_phone, p_receiver_relation,
            p_receiver_id_type, p_receiver_id_number, p_notes
        );
        
        SET p_issue_id = LAST_INSERT_ID();
        
        -- Update request status
        UPDATE blood_requests
        SET status = 'Issued',
            issued_by = p_issued_by,
            issued_date = CURRENT_TIMESTAMP
        WHERE request_id = p_request_id;
        
        -- Log activity
        INSERT INTO activity_logs (user_id, user_type, action, table_name, record_id, description)
        VALUES (p_issued_by, 'Admin', 'BLOOD_ISSUED', 'blood_issues', p_issue_id,
                CONCAT('Blood issued: ', v_units_approved, ' units of ', v_blood_group, ' for request #', p_request_id));
        
        SET p_status = 'SUCCESS';
        SET p_message = CONCAT('Blood issued successfully. Issue ID: ', p_issue_id);
        
        COMMIT;
    END IF;
END//

DELIMITER ;

-- ============================================================================
-- PROCEDURE 6: Generate Stock Summary Report
-- ============================================================================
DELIMITER //

CREATE PROCEDURE sp_GenerateStockSummary()
BEGIN
    SELECT 
        bg.group_code,
        bg.group_name,
        COALESCE(bs.units_available, 0) AS units_available,
        COALESCE(bs.units_reserved, 0) AS units_reserved,
        (COALESCE(bs.units_available, 0) - COALESCE(bs.units_reserved, 0)) AS units_free,
        COALESCE(bs.minimum_threshold, 5) AS minimum_threshold,
        COALESCE(bs.maximum_capacity, 100) AS maximum_capacity,
        CASE 
            WHEN COALESCE(bs.units_available, 0) < COALESCE(bs.minimum_threshold, 5) THEN 'Critical'
            WHEN COALESCE(bs.units_available, 0) <= (COALESCE(bs.minimum_threshold, 5) + 2) THEN 'Low'
            WHEN COALESCE(bs.units_available, 0) >= (COALESCE(bs.maximum_capacity, 100) - 5) THEN 'Near Capacity'
            ELSE 'Normal'
        END AS stock_status,
        bs.last_updated
    FROM blood_groups bg
    LEFT JOIN blood_stock bs ON bg.group_code = bs.group_code
    ORDER BY bg.group_code;
END//

DELIMITER ;

-- ============================================================================
-- PROCEDURE 7: Check and Update Donor Eligibility
-- ============================================================================
DELIMITER //

CREATE PROCEDURE sp_UpdateDonorEligibility()
BEGIN
    DECLARE v_updated_count INT DEFAULT 0;
    
    -- Update donors who are now eligible (90 days since last donation)
    UPDATE donors
    SET is_eligible = TRUE,
        updated_at = CURRENT_TIMESTAMP
    WHERE is_eligible = FALSE
      AND last_donation_date IS NOT NULL
      AND DATEDIFF(CURDATE(), last_donation_date) >= 90
      AND status = 'Active';
    
    SET v_updated_count = ROW_COUNT();
    
    -- Log the eligibility update
    IF v_updated_count > 0 THEN
        INSERT INTO activity_logs (user_type, action, description)
        VALUES ('System', 'ELIGIBILITY_UPDATE', 
                CONCAT('Updated eligibility for ', v_updated_count, ' donors'));
    END IF;
    
    SELECT v_updated_count AS donors_updated;
END//

DELIMITER ;

-- ============================================================================
-- PROCEDURE 8: Check Expiring Blood Units
-- ============================================================================
DELIMITER //

CREATE PROCEDURE sp_CheckExpiringBlood(IN p_days_threshold INT)
BEGIN
    -- Get units expiring within threshold days
    SELECT 
        d.donation_id,
        d.bag_number,
        d.blood_group,
        d.units_donated,
        d.donation_date,
        d.expiry_date,
        DATEDIFF(d.expiry_date, CURDATE()) AS days_until_expiry,
        CONCAT(dn.first_name, ' ', dn.last_name) AS donor_name,
        d.status
    FROM donations d
    JOIN donors dn ON d.donor_id = dn.donor_id
    WHERE d.status = 'Available'
      AND d.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL p_days_threshold DAY)
    ORDER BY d.expiry_date ASC;
END//

DELIMITER ;

-- ============================================================================
-- PROCEDURE 9: Get Dashboard Statistics
-- ============================================================================
DELIMITER //

CREATE PROCEDURE sp_GetDashboardStats()
BEGIN
    -- Total donors
    SELECT 
        (SELECT COUNT(*) FROM donors WHERE status = 'Active') AS total_active_donors,
        (SELECT COUNT(*) FROM donors WHERE is_eligible = TRUE AND status = 'Active') AS eligible_donors,
        (SELECT COUNT(*) FROM patients WHERE status = 'Admitted') AS admitted_patients,
        (SELECT SUM(units_available) FROM blood_stock) AS total_blood_units,
        (SELECT COUNT(*) FROM blood_requests WHERE status = 'Pending') AS pending_requests,
        (SELECT COUNT(*) FROM blood_requests WHERE status IN ('Approved', 'Partially Approved')) AS approved_requests,
        (SELECT COUNT(*) FROM donations WHERE MONTH(donation_date) = MONTH(CURDATE()) AND YEAR(donation_date) = YEAR(CURDATE())) AS donations_this_month,
        (SELECT COUNT(*) FROM blood_issues WHERE MONTH(issue_date) = MONTH(CURDATE()) AND YEAR(issue_date) = YEAR(CURDATE())) AS issues_this_month,
        (SELECT COUNT(*) FROM notification_logs WHERE is_read = FALSE) AS unread_notifications;
END//

DELIMITER ;

-- ============================================================================
-- PROCEDURE 10: Search Donors
-- ============================================================================
DELIMITER //

CREATE PROCEDURE sp_SearchDonors(
    IN p_blood_group VARCHAR(5),
    IN p_city VARCHAR(100),
    IN p_eligible_only BOOLEAN
)
BEGIN
    SELECT 
        d.donor_id,
        CONCAT(d.first_name, ' ', d.last_name) AS donor_name,
        d.blood_group,
        d.phone,
        d.email,
        d.city,
        d.age,
        d.is_eligible,
        d.total_donations,
        d.last_donation_date,
        CASE 
            WHEN d.last_donation_date IS NULL THEN 'Never donated'
            ELSE CONCAT(DATEDIFF(CURDATE(), d.last_donation_date), ' days ago')
        END AS last_donation_info
    FROM donors d
    WHERE d.status = 'Active'
      AND (p_blood_group IS NULL OR d.blood_group = p_blood_group)
      AND (p_city IS NULL OR d.city LIKE CONCAT('%', p_city, '%'))
      AND (p_eligible_only = FALSE OR d.is_eligible = TRUE)
    ORDER BY d.is_eligible DESC, d.last_donation_date ASC;
END//

DELIMITER ;

-- ============================================================================
-- END OF STORED PROCEDURES
-- ============================================================================
