<?php
/**
 * ============================================================================
 * BLOOD BANK MANAGEMENT SYSTEM (BBMS) - DATABASE CLASS
 * Advanced Database Handler with Stored Procedures, Transactions, and Views
 * ============================================================================
 */

class Database {
    private static $instance = null;
    private $connection;
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database = 'bbms';
    private $charset = 'utf8mb4';
    
    /**
     * Private constructor for singleton pattern
     */
    private function __construct() {
        try {
            $this->connection = new mysqli(
                $this->host,
                $this->username,
                $this->password,
                $this->database
            );
            
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            $this->connection->set_charset($this->charset);
            
        } catch (Exception $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get the mysqli connection
     */
    public function getConnection() {
        return $this->connection;
    }
    
    // ========================================================================
    // TRANSACTION METHODS
    // ========================================================================
    
    /**
     * Begin a transaction
     */
    public function beginTransaction() {
        $this->connection->autocommit(false);
        $this->connection->begin_transaction();
    }
    
    /**
     * Commit the transaction
     */
    public function commit() {
        $this->connection->commit();
        $this->connection->autocommit(true);
    }
    
    /**
     * Rollback the transaction
     */
    public function rollback() {
        $this->connection->rollback();
        $this->connection->autocommit(true);
    }
    
    // ========================================================================
    // STORED PROCEDURE METHODS
    // ========================================================================
    
    /**
     * Call stored procedure to register a new donor
     */
    public function registerDonor($data) {
        $stmt = $this->connection->prepare(
            "CALL sp_RegisterDonor(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @donor_id, @status, @message)"
        );
        
        $stmt->bind_param(
            "sssssssssssdsssi",
            $data['first_name'],
            $data['last_name'],
            $data['dob'],
            $data['gender'],
            $data['blood_group'],
            $data['phone'],
            $data['email'],
            $data['address'],
            $data['city'],
            $data['state'],
            $data['pincode'],
            $data['weight'],
            $data['medical_conditions'],
            $data['emergency_contact_name'],
            $data['emergency_contact_phone'],
            $data['registered_by']
        );
        
        $stmt->execute();
        $stmt->close();
        
        // Get output parameters
        $result = $this->connection->query("SELECT @donor_id AS donor_id, @status AS status, @message AS message");
        return $result->fetch_assoc();
    }
    
    /**
     * Call stored procedure to add a donation
     */
    public function addDonation($data) {
        $stmt = $this->connection->prepare(
            "CALL sp_AddDonation(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @donation_id, @status, @message)"
        );
        
        $stmt->bind_param(
            "iisdsidss",
            $data['donor_id'],
            $data['units'],
            $data['donation_type'],
            $data['hemoglobin'],
            $data['blood_pressure'],
            $data['pulse_rate'],
            $data['temperature'],
            $data['collection_center'],
            $data['collected_by'],
            $data['bag_number']
        );
        
        $stmt->execute();
        $stmt->close();
        
        $result = $this->connection->query("SELECT @donation_id AS donation_id, @status AS status, @message AS message");
        return $result->fetch_assoc();
    }
    
    /**
     * Call stored procedure to approve blood request
     */
    public function approveBloodRequest($requestId, $unitsToApprove, $approvedBy, $notes = '') {
        $stmt = $this->connection->prepare(
            "CALL sp_ApproveBloodRequest(?, ?, ?, ?, @status, @message)"
        );
        
        $stmt->bind_param("iiis", $requestId, $unitsToApprove, $approvedBy, $notes);
        $stmt->execute();
        $stmt->close();
        
        $result = $this->connection->query("SELECT @status AS status, @message AS message");
        return $result->fetch_assoc();
    }
    
    /**
     * Call stored procedure to reject blood request
     */
    public function rejectBloodRequest($requestId, $rejectedBy, $reason) {
        $stmt = $this->connection->prepare(
            "CALL sp_RejectBloodRequest(?, ?, ?, @status, @message)"
        );
        
        $stmt->bind_param("iis", $requestId, $rejectedBy, $reason);
        $stmt->execute();
        $stmt->close();
        
        $result = $this->connection->query("SELECT @status AS status, @message AS message");
        return $result->fetch_assoc();
    }
    
    /**
     * Call stored procedure to issue blood
     */
    public function issueBlood($data) {
        $stmt = $this->connection->prepare(
            "CALL sp_IssueBlood(?, ?, ?, ?, ?, ?, ?, ?, @issue_id, @status, @message)"
        );
        
        $stmt->bind_param(
            "iissssss",
            $data['request_id'],
            $data['issued_by'],
            $data['receiver_name'],
            $data['receiver_phone'],
            $data['receiver_relation'],
            $data['receiver_id_type'],
            $data['receiver_id_number'],
            $data['notes']
        );
        
        $stmt->execute();
        $stmt->close();
        
        $result = $this->connection->query("SELECT @issue_id AS issue_id, @status AS status, @message AS message");
        return $result->fetch_assoc();
    }
    
    /**
     * Call stored procedure to get stock summary
     */
    public function getStockSummary() {
        $result = $this->connection->query("CALL sp_GenerateStockSummary()");
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $result->close();
        $this->connection->next_result(); // Clear result set
        return $data;
    }
    
    /**
     * Call stored procedure to update donor eligibility
     */
    public function updateDonorEligibility() {
        $result = $this->connection->query("CALL sp_UpdateDonorEligibility()");
        $data = $result->fetch_assoc();
        $result->close();
        $this->connection->next_result();
        return $data;
    }
    
    /**
     * Call stored procedure to check expiring blood
     */
    public function getExpiringBlood($daysThreshold = 7) {
        $stmt = $this->connection->prepare("CALL sp_CheckExpiringBlood(?)");
        $stmt->bind_param("i", $daysThreshold);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        $this->connection->next_result();
        return $data;
    }
    
    /**
     * Call stored procedure to get dashboard stats
     */
    public function getDashboardStats() {
        $result = $this->connection->query("CALL sp_GetDashboardStats()");
        $data = $result->fetch_assoc();
        $result->close();
        $this->connection->next_result();
        return $data;
    }
    
    /**
     * Call stored procedure to search donors
     */
    public function searchDonors($bloodGroup = null, $city = null, $eligibleOnly = false) {
        $stmt = $this->connection->prepare("CALL sp_SearchDonors(?, ?, ?)");
        $eligibleOnlyInt = $eligibleOnly ? 1 : 0;
        $stmt->bind_param("ssi", $bloodGroup, $city, $eligibleOnlyInt);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        $this->connection->next_result();
        return $data;
    }
    
    // ========================================================================
    // VIEW METHODS
    // ========================================================================
    
    /**
     * Get available blood from view
     */
    public function getAvailableBlood() {
        return $this->fetchView('view_available_blood');
    }
    
    /**
     * Get pending requests from view
     */
    public function getPendingRequests() {
        return $this->fetchView('view_pending_requests');
    }
    
    /**
     * Get donation summary from view
     */
    public function getDonationSummary($limit = 50) {
        $stmt = $this->connection->prepare("SELECT * FROM view_donation_summary LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        return $data;
    }
    
    /**
     * Get blood group statistics from view
     */
    public function getBloodGroupStatistics() {
        return $this->fetchView('view_blood_group_statistics');
    }
    
    /**
     * Get donor directory from view
     */
    public function getDonorDirectory() {
        return $this->fetchView('view_donor_directory');
    }
    
    /**
     * Get patient records from view
     */
    public function getPatientRecords() {
        return $this->fetchView('view_patient_records');
    }
    
    /**
     * Get recent activity from view
     */
    public function getRecentActivity($limit = 50) {
        $stmt = $this->connection->prepare("SELECT * FROM view_recent_activity LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        return $data;
    }
    
    /**
     * Get unread notifications from view
     */
    public function getUnreadNotifications() {
        return $this->fetchView('view_unread_notifications');
    }
    
    /**
     * Get blood issue history from view
     */
    public function getBloodIssueHistory() {
        return $this->fetchView('view_blood_issue_history');
    }
    
    /**
     * Get expiring blood units from view
     */
    public function getExpiringBloodUnits() {
        return $this->fetchView('view_expiring_blood');
    }
    
    /**
     * Helper method to fetch all rows from a view
     */
    private function fetchView($viewName) {
        $result = $this->connection->query("SELECT * FROM " . $this->connection->real_escape_string($viewName));
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    
    // ========================================================================
    // ACTIVITY LOGGING
    // ========================================================================
    
    /**
     * Log an activity
     */
    public function logActivity($userId, $action, $tableName = null, $recordId = null, $description = '', $oldValues = null, $newValues = null) {
        $userType = $userId ? 'Admin' : 'System';
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $oldValuesJson = $oldValues ? json_encode($oldValues) : null;
        $newValuesJson = $newValues ? json_encode($newValues) : null;
        
        $stmt = $this->connection->prepare(
            "INSERT INTO activity_logs (user_id, user_type, action, table_name, record_id, old_values, new_values, ip_address, user_agent, description) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        
        $stmt->bind_param(
            "isssssssss",
            $userId,
            $userType,
            $action,
            $tableName,
            $recordId,
            $oldValuesJson,
            $newValuesJson,
            $ipAddress,
            $userAgent,
            $description
        );
        
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * Mark notification as read
     */
    public function markNotificationRead($notificationId, $readBy) {
        $stmt = $this->connection->prepare(
            "UPDATE notification_logs SET is_read = TRUE, read_by = ?, read_at = CURRENT_TIMESTAMP WHERE notification_id = ?"
        );
        $stmt->bind_param("ii", $readBy, $notificationId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    // ========================================================================
    // GENERIC QUERY METHODS
    // ========================================================================
    
    /**
     * Execute a prepared statement with parameters
     */
    public function execute($sql, $params = [], $types = '') {
        $stmt = $this->connection->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $result = $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $insertId = $stmt->insert_id;
        $stmt->close();
        
        return [
            'success' => $result,
            'affected_rows' => $affectedRows,
            'insert_id' => $insertId
        ];
    }
    
    /**
     * Fetch all rows from a query
     */
    public function fetchAll($sql, $params = [], $types = '') {
        $stmt = $this->connection->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        $stmt->close();
        return $data;
    }
    
    /**
     * Fetch single row from a query
     */
    public function fetchOne($sql, $params = [], $types = '') {
        $stmt = $this->connection->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        
        return $data;
    }
    
    /**
     * Escape string for safe queries
     */
    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }
    
    /**
     * Get last error
     */
    public function getError() {
        return $this->connection->error;
    }
    
    /**
     * Close connection
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
