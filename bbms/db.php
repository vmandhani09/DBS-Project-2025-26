<?php
/**
 * Database Connection File
 * Blood Bank Management System (BBMS)
 */

$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'bbms';

// Create connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Include the advanced Database class if needed
if (file_exists(__DIR__ . '/includes/Database.php')) {
    require_once __DIR__ . '/includes/Database.php';
}

/**
 * Helper function to log activity (simple version used by action files)
 */
function log_activity($conn, $action, $description, $table_name = null, $record_id = null) {
    $user_id = $_SESSION['admin_id'] ?? null;
    $user_type = $user_id ? 'Admin' : 'System';
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    
    $stmt = $conn->prepare(
        "INSERT INTO activity_logs (user_id, user_type, action, table_name, record_id, ip_address, description) 
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    
    if ($stmt) {
        $stmt->bind_param("ississs", $user_id, $user_type, $action, $table_name, $record_id, $ip_address, $description);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Helper function to log activity (detailed version)
 */
function logActivity($conn, $userId, $action, $tableName = null, $recordId = null, $description = '') {
    $userType = $userId ? 'Admin' : 'System';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    
    $stmt = $conn->prepare(
        "INSERT INTO activity_logs (user_id, user_type, action, table_name, record_id, ip_address, description) 
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    
    $stmt->bind_param("issssss", $userId, $userType, $action, $tableName, $recordId, $ipAddress, $description);
    $stmt->execute();
    $stmt->close();
}

/**
 * Helper function to get blood stock
 */
function getBloodStock($conn) {
    $result = $conn->query("SELECT * FROM view_available_blood");
    $stock = [];
    while ($row = $result->fetch_assoc()) {
        $stock[] = $row;
    }
    return $stock;
}

/**
 * Helper function to get pending requests count
 */
function getPendingRequestsCount($conn) {
    $result = $conn->query("SELECT COUNT(*) as count FROM blood_requests WHERE status = 'Pending'");
    return $result->fetch_assoc()['count'];
}

/**
 * Helper function to get unread notifications count
 */
function getUnreadNotificationsCount($conn) {
    $result = $conn->query("SELECT COUNT(*) as count FROM notification_logs WHERE is_read = FALSE");
    return $result->fetch_assoc()['count'];
}

/**
 * Helper function to get dashboard stats
 */
function getDashboardStats($conn) {
    $stats = [];
    
    // Total donors
    $result = $conn->query("SELECT COUNT(*) as count FROM donors WHERE status = 'Active'");
    $stats['total_donors'] = $result->fetch_assoc()['count'];
    
    // Total patients
    $result = $conn->query("SELECT COUNT(*) as count FROM patients");
    $stats['total_patients'] = $result->fetch_assoc()['count'];
    
    // Total blood units
    $result = $conn->query("SELECT SUM(units_available) as total FROM blood_stock");
    $stats['total_blood_units'] = $result->fetch_assoc()['total'] ?? 0;
    
    // Pending requests
    $stats['pending_requests'] = getPendingRequestsCount($conn);
    
    return $stats;
}
?>