-- ============================================================================
-- BLOOD BANK MANAGEMENT SYSTEM (BBMS) - TRIGGERS
-- MySQL 8+ Compatible
-- ============================================================================

USE bbms;

-- ============================================================================
-- TRIGGER 1: Auto-update blood stock after donation marked as available
-- ============================================================================
DELIMITER //

CREATE TRIGGER trg_after_donation_available
AFTER UPDATE ON donations
FOR EACH ROW
BEGIN
    -- When donation status changes to 'Available', increase stock
    IF NEW.status = 'Available' AND OLD.status != 'Available' THEN
        UPDATE blood_stock 
        SET units_available = units_available + NEW.units_donated,
            last_updated = CURRENT_TIMESTAMP
        WHERE group_code = NEW.blood_group;
        
        -- Log the activity
        INSERT INTO activity_logs (user_type, action, table_name, record_id, description)
        VALUES ('System', 'STOCK_INCREASE', 'blood_stock', NULL, 
                CONCAT('Stock increased by ', NEW.units_donated, ' units for blood group ', NEW.blood_group, ' from donation #', NEW.donation_id));
    END IF;
    
    -- When donation is issued, decrease stock
    IF NEW.status = 'Issued' AND OLD.status = 'Available' THEN
        UPDATE blood_stock 
        SET units_available = units_available - NEW.units_donated,
            last_updated = CURRENT_TIMESTAMP
        WHERE group_code = NEW.blood_group;
        
        -- Log the activity
        INSERT INTO activity_logs (user_type, action, table_name, record_id, description)
        VALUES ('System', 'STOCK_DECREASE', 'blood_stock', NULL, 
                CONCAT('Stock decreased by ', NEW.units_donated, ' units for blood group ', NEW.blood_group, ' - Donation issued #', NEW.donation_id));
    END IF;
END//

DELIMITER ;

-- ============================================================================
-- TRIGGER 2: Auto-update donor statistics after donation
-- ============================================================================
DELIMITER //

CREATE TRIGGER trg_after_donation_insert
AFTER INSERT ON donations
FOR EACH ROW
BEGIN
    -- Update donor's total donations and last donation date
    UPDATE donors 
    SET total_donations = total_donations + 1,
        last_donation_date = NEW.donation_date,
        updated_at = CURRENT_TIMESTAMP
    WHERE donor_id = NEW.donor_id;
    
    -- Log the activity
    INSERT INTO activity_logs (user_type, action, table_name, record_id, description)
    VALUES ('System', 'DONATION_RECORDED', 'donations', NEW.donation_id, 
            CONCAT('New donation recorded for donor #', NEW.donor_id, ' - Blood Group: ', NEW.blood_group));
END//

DELIMITER ;

-- ============================================================================
-- TRIGGER 3: Low stock alert trigger
-- ============================================================================
DELIMITER //

CREATE TRIGGER trg_low_stock_alert
AFTER UPDATE ON blood_stock
FOR EACH ROW
BEGIN
    -- Check if stock falls below minimum threshold
    IF NEW.units_available < NEW.minimum_threshold AND 
       (OLD.units_available >= OLD.minimum_threshold OR NEW.units_available != OLD.units_available) THEN
        INSERT INTO notification_logs (
            notification_type, 
            title, 
            message, 
            severity, 
            related_table, 
            related_id
        ) VALUES (
            'Low Stock',
            CONCAT('Low Stock Alert: ', NEW.group_code),
            CONCAT('Blood group ', NEW.group_code, ' stock is critically low. Current units: ', 
                   NEW.units_available, '. Minimum threshold: ', NEW.minimum_threshold, 
                   '. Please arrange for blood donation camps or contact donors.'),
            'Critical',
            'blood_stock',
            NEW.stock_id
        );
    END IF;
    
    -- Warning when stock is approaching threshold (within 2 units)
    IF NEW.units_available >= NEW.minimum_threshold AND 
       NEW.units_available <= (NEW.minimum_threshold + 2) AND
       OLD.units_available > (OLD.minimum_threshold + 2) THEN
        INSERT INTO notification_logs (
            notification_type, 
            title, 
            message, 
            severity, 
            related_table, 
            related_id
        ) VALUES (
            'Low Stock',
            CONCAT('Stock Warning: ', NEW.group_code),
            CONCAT('Blood group ', NEW.group_code, ' stock is approaching minimum threshold. Current units: ', 
                   NEW.units_available, '. Consider planning a donation drive.'),
            'Warning',
            'blood_stock',
            NEW.stock_id
        );
    END IF;
END//

DELIMITER ;

-- ============================================================================
-- TRIGGER 4: Blood expiry alert trigger
-- ============================================================================
DELIMITER //

CREATE TRIGGER trg_donation_expiry_check
BEFORE UPDATE ON donations
FOR EACH ROW
BEGIN
    -- Auto-mark as expired if past expiry date
    IF NEW.expiry_date < CURDATE() AND NEW.status NOT IN ('Issued', 'Discarded', 'Expired') THEN
        SET NEW.status = 'Expired';
        
        -- If it was available, decrease stock
        IF OLD.status = 'Available' THEN
            UPDATE blood_stock 
            SET units_available = units_available - OLD.units_donated
            WHERE group_code = OLD.blood_group;
        END IF;
    END IF;
END//

DELIMITER ;

-- ============================================================================
-- TRIGGER 5: After expiry status change - create notification
-- ============================================================================
DELIMITER //

CREATE TRIGGER trg_after_donation_expiry
AFTER UPDATE ON donations
FOR EACH ROW
BEGIN
    IF NEW.status = 'Expired' AND OLD.status != 'Expired' THEN
        INSERT INTO notification_logs (
            notification_type, 
            title, 
            message, 
            severity, 
            related_table, 
            related_id
        ) VALUES (
            'Expiry Alert',
            CONCAT('Blood Unit Expired: Bag #', NEW.bag_number),
            CONCAT('Blood unit with bag number ', IFNULL(NEW.bag_number, 'N/A'), 
                   ' (Blood Group: ', NEW.blood_group, ') has expired. ',
                   'Expiry Date: ', NEW.expiry_date, '. Please dispose of it properly.'),
            'Warning',
            'donations',
            NEW.donation_id
        );
    END IF;
END//

DELIMITER ;

-- ============================================================================
-- TRIGGER 6: Request approval - reserve stock
-- ============================================================================
DELIMITER //

CREATE TRIGGER trg_after_request_approved
AFTER UPDATE ON blood_requests
FOR EACH ROW
BEGIN
    -- When request is approved, reserve stock
    IF NEW.status = 'Approved' AND OLD.status = 'Pending' THEN
        UPDATE blood_stock 
        SET units_reserved = units_reserved + NEW.units_approved,
            last_updated = CURRENT_TIMESTAMP
        WHERE group_code = NEW.blood_group_requested;
        
        -- Create notification
        INSERT INTO notification_logs (
            notification_type, 
            title, 
            message, 
            severity, 
            related_table, 
            related_id
        ) VALUES (
            'Request Alert',
            CONCAT('Blood Request Approved #', NEW.request_id),
            CONCAT('Blood request for ', NEW.units_approved, ' units of ', 
                   NEW.blood_group_requested, ' has been approved. Requester: ', NEW.requester_name),
            'Info',
            'blood_requests',
            NEW.request_id
        );
    END IF;
    
    -- When request is issued, release reservation and decrease stock
    IF NEW.status = 'Issued' AND OLD.status = 'Approved' THEN
        UPDATE blood_stock 
        SET units_reserved = units_reserved - NEW.units_approved,
            units_available = units_available - NEW.units_approved,
            last_updated = CURRENT_TIMESTAMP
        WHERE group_code = NEW.blood_group_requested;
    END IF;
    
    -- When request is cancelled or rejected, release reservation
    IF NEW.status IN ('Cancelled', 'Rejected') AND OLD.status = 'Approved' THEN
        UPDATE blood_stock 
        SET units_reserved = units_reserved - OLD.units_approved,
            last_updated = CURRENT_TIMESTAMP
        WHERE group_code = OLD.blood_group_requested;
    END IF;
END//

DELIMITER ;

-- ============================================================================
-- TRIGGER 7: Urgent request notification
-- ============================================================================
DELIMITER //

CREATE TRIGGER trg_urgent_request_notification
AFTER INSERT ON blood_requests
FOR EACH ROW
BEGIN
    IF NEW.urgency_level IN ('Urgent', 'Critical') THEN
        INSERT INTO notification_logs (
            notification_type, 
            title, 
            message, 
            severity, 
            related_table, 
            related_id
        ) VALUES (
            'Request Alert',
            CONCAT(NEW.urgency_level, ' Blood Request - ', NEW.blood_group_requested),
            CONCAT('URGENT: ', NEW.urgency_level, ' blood request received for ', 
                   NEW.units_requested, ' units of ', NEW.blood_group_requested, 
                   '. Requester: ', NEW.requester_name, '. Required by: ', NEW.required_date),
            IF(NEW.urgency_level = 'Critical', 'Critical', 'Warning'),
            'blood_requests',
            NEW.request_id
        );
    END IF;
END//

DELIMITER ;

-- ============================================================================
-- TRIGGER 8: Donor eligibility check after donation
-- ============================================================================
DELIMITER //

CREATE TRIGGER trg_donor_eligibility_after_donation
AFTER INSERT ON donations
FOR EACH ROW
BEGIN
    -- Set donor as ineligible for 3 months after donation
    UPDATE donors
    SET is_eligible = FALSE,
        updated_at = CURRENT_TIMESTAMP
    WHERE donor_id = NEW.donor_id;
    
    -- Log eligibility change
    INSERT INTO donor_eligibility_logs (
        donor_id,
        check_date,
        is_eligible,
        reason,
        next_eligible_date
    ) VALUES (
        NEW.donor_id,
        CURDATE(),
        FALSE,
        CONCAT('Donated blood on ', NEW.donation_date, '. Must wait 90 days before next donation.'),
        DATE_ADD(NEW.donation_date, INTERVAL 90 DAY)
    );
END//

DELIMITER ;

-- ============================================================================
-- END OF TRIGGERS
-- ============================================================================
