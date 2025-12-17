<?php
/**
 * ============================================================================
 * BLOOD BANK MANAGEMENT SYSTEM (BBMS) - PHP HELPER EXAMPLES
 * Examples for calling stored procedures, transactions, and views
 * ============================================================================
 */

// Include the database class
require_once __DIR__ . '/Database.php';

/**
 * ============================================================================
 * EXAMPLE 1: Using Stored Procedures
 * ============================================================================
 */

/**
 * Example: Register a new donor using stored procedure
 */
function exampleRegisterDonor() {
    $db = Database::getInstance();
    
    $donorData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'dob' => '1990-05-15',
        'gender' => 'Male',
        'blood_group' => 'O+',
        'phone' => '9876543210',
        'email' => 'john.doe@email.com',
        'address' => '123 Main Street',
        'city' => 'Mumbai',
        'state' => 'Maharashtra',
        'pincode' => '400001',
        'weight' => 70.5,
        'medical_conditions' => 'None',
        'emergency_contact_name' => 'Jane Doe',
        'emergency_contact_phone' => '9876543211',
        'registered_by' => 1 // Admin ID
    ];
    
    $result = $db->registerDonor($donorData);
    
    if ($result['status'] === 'SUCCESS') {
        echo "Donor registered! ID: " . $result['donor_id'];
        echo "\nMessage: " . $result['message'];
    } else {
        echo "Error: " . $result['message'];
    }
    
    return $result;
}

/**
 * Example: Add a donation using stored procedure
 */
function exampleAddDonation() {
    $db = Database::getInstance();
    
    $donationData = [
        'donor_id' => 1,
        'units' => 1,
        'donation_type' => 'Whole Blood',
        'hemoglobin' => 14.5,
        'blood_pressure' => '120/80',
        'pulse_rate' => 72,
        'temperature' => 98.4,
        'collection_center' => 'BBMS Main Center',
        'collected_by' => 'Nurse Rekha',
        'bag_number' => 'BAG-' . date('Y') . '-' . rand(1000, 9999)
    ];
    
    $result = $db->addDonation($donationData);
    
    if ($result['status'] === 'SUCCESS') {
        echo "Donation recorded! ID: " . $result['donation_id'];
    } else {
        echo "Error: " . $result['message'];
    }
    
    return $result;
}

/**
 * Example: Approve a blood request using stored procedure
 */
function exampleApproveRequest($requestId) {
    $db = Database::getInstance();
    
    $result = $db->approveBloodRequest(
        $requestId,      // Request ID
        2,               // Units to approve
        1,               // Approved by (Admin ID)
        'Approved after verification' // Notes
    );
    
    if ($result['status'] === 'SUCCESS') {
        echo "Request approved: " . $result['message'];
    } else {
        echo "Error: " . $result['message'];
    }
    
    return $result;
}

/**
 * Example: Issue blood using stored procedure
 */
function exampleIssueBlood($requestId) {
    $db = Database::getInstance();
    
    $issueData = [
        'request_id' => $requestId,
        'issued_by' => 1,
        'receiver_name' => 'Ravi Kumar',
        'receiver_phone' => '9876543210',
        'receiver_relation' => 'Son',
        'receiver_id_type' => 'Aadhar',
        'receiver_id_number' => '1234-5678-9012',
        'notes' => 'Blood issued successfully'
    ];
    
    $result = $db->issueBlood($issueData);
    
    if ($result['status'] === 'SUCCESS') {
        echo "Blood issued! Issue ID: " . $result['issue_id'];
    } else {
        echo "Error: " . $result['message'];
    }
    
    return $result;
}

/**
 * ============================================================================
 * EXAMPLE 2: Using Transactions
 * ============================================================================
 */

/**
 * Example: Complex operation with transaction
 */
function exampleTransactionOperation() {
    $db = Database::getInstance();
    
    try {
        // Start transaction
        $db->beginTransaction();
        
        // Step 1: Insert a record
        $result1 = $db->execute(
            "INSERT INTO donors (first_name, last_name, date_of_birth, gender, blood_group, phone, status) VALUES (?, ?, ?, ?, ?, ?, 'Active')",
            ['Test', 'Donor', '1990-01-01', 'Male', 'O+', '9999999999'],
            'ssssss'
        );
        
        if (!$result1['success']) {
            throw new Exception("Failed to insert donor");
        }
        
        $donorId = $result1['insert_id'];
        
        // Step 2: Update related data
        $result2 = $db->execute(
            "UPDATE donors SET email = ? WHERE donor_id = ?",
            ['test@email.com', $donorId],
            'si'
        );
        
        if (!$result2['success']) {
            throw new Exception("Failed to update donor email");
        }
        
        // Step 3: Log the activity
        $db->logActivity(1, 'DONOR_CREATED', 'donors', $donorId, 'Test donor created with transaction');
        
        // All operations successful - commit
        $db->commit();
        
        echo "Transaction completed successfully! Donor ID: " . $donorId;
        return ['success' => true, 'donor_id' => $donorId];
        
    } catch (Exception $e) {
        // Something went wrong - rollback
        $db->rollback();
        echo "Transaction failed: " . $e->getMessage();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * ============================================================================
 * EXAMPLE 3: Using Views
 * ============================================================================
 */

/**
 * Example: Get available blood stock from view
 */
function exampleGetAvailableBlood() {
    $db = Database::getInstance();
    
    $bloodStock = $db->getAvailableBlood();
    
    echo "<h3>Available Blood Stock</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Blood Group</th><th>Available</th><th>Reserved</th><th>Free</th><th>Status</th></tr>";
    
    foreach ($bloodStock as $stock) {
        echo "<tr>";
        echo "<td>" . $stock['group_code'] . "</td>";
        echo "<td>" . $stock['units_available'] . "</td>";
        echo "<td>" . $stock['units_reserved'] . "</td>";
        echo "<td>" . $stock['units_free'] . "</td>";
        echo "<td>" . $stock['availability_status'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    return $bloodStock;
}

/**
 * Example: Get pending requests from view
 */
function exampleGetPendingRequests() {
    $db = Database::getInstance();
    
    $requests = $db->getPendingRequests();
    
    echo "<h3>Pending Blood Requests</h3>";
    
    if (empty($requests)) {
        echo "<p>No pending requests.</p>";
        return [];
    }
    
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Requester</th><th>Blood Group</th><th>Units</th><th>Urgency</th><th>Required By</th></tr>";
    
    foreach ($requests as $request) {
        $rowClass = $request['urgency_level'] === 'Critical' ? 'background: #ffcccc;' : '';
        echo "<tr style='$rowClass'>";
        echo "<td>" . $request['request_id'] . "</td>";
        echo "<td>" . $request['requester_name'] . "</td>";
        echo "<td>" . $request['blood_group_requested'] . "</td>";
        echo "<td>" . $request['units_requested'] . "</td>";
        echo "<td>" . $request['urgency_level'] . "</td>";
        echo "<td>" . $request['required_date'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    return $requests;
}

/**
 * Example: Get blood group statistics from view
 */
function exampleGetBloodGroupStats() {
    $db = Database::getInstance();
    
    $stats = $db->getBloodGroupStatistics();
    
    echo "<h3>Blood Group Statistics</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Blood Group</th><th>Total Donors</th><th>Eligible</th><th>Current Stock</th><th>Pending Requests</th></tr>";
    
    foreach ($stats as $stat) {
        echo "<tr>";
        echo "<td>" . $stat['group_code'] . "</td>";
        echo "<td>" . $stat['total_donors'] . "</td>";
        echo "<td>" . $stat['eligible_donors'] . "</td>";
        echo "<td>" . $stat['current_stock'] . "</td>";
        echo "<td>" . $stat['pending_requests'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    return $stats;
}

/**
 * Example: Get donor directory from view
 */
function exampleGetDonorDirectory() {
    $db = Database::getInstance();
    
    $donors = $db->getDonorDirectory();
    
    echo "<h3>Donor Directory</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Name</th><th>Blood Group</th><th>Phone</th><th>City</th><th>Eligible</th><th>Total Donations</th></tr>";
    
    foreach ($donors as $donor) {
        echo "<tr>";
        echo "<td>" . $donor['full_name'] . "</td>";
        echo "<td>" . $donor['blood_group'] . "</td>";
        echo "<td>" . $donor['phone'] . "</td>";
        echo "<td>" . $donor['city'] . "</td>";
        echo "<td>" . ($donor['is_eligible'] ? 'Yes' : 'No') . "</td>";
        echo "<td>" . $donor['total_donations'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    return $donors;
}

/**
 * ============================================================================
 * EXAMPLE 4: Activity Logging
 * ============================================================================
 */

/**
 * Example: Log user activity
 */
function exampleLogActivity($adminId, $action, $description) {
    $db = Database::getInstance();
    
    $db->logActivity(
        $adminId,           // User ID
        $action,            // Action (e.g., 'LOGIN', 'DONOR_ADDED', 'REQUEST_APPROVED')
        'activity_logs',    // Table name (optional)
        null,               // Record ID (optional)
        $description,       // Description
        null,               // Old values (for updates)
        null                // New values (for updates)
    );
    
    echo "Activity logged: $action";
}

/**
 * Example: Log activity with before/after values (for auditing)
 */
function exampleLogActivityWithChanges($adminId, $recordId, $oldData, $newData) {
    $db = Database::getInstance();
    
    $db->logActivity(
        $adminId,
        'RECORD_UPDATED',
        'donors',
        $recordId,
        'Donor information updated',
        $oldData,  // ['name' => 'Old Name', 'phone' => '1234567890']
        $newData   // ['name' => 'New Name', 'phone' => '0987654321']
    );
    
    echo "Activity with changes logged";
}

/**
 * ============================================================================
 * EXAMPLE 5: Dashboard Data
 * ============================================================================
 */

/**
 * Example: Get all dashboard statistics
 */
function exampleGetDashboardData() {
    $db = Database::getInstance();
    
    // Get stats from stored procedure
    $stats = $db->getDashboardStats();
    
    // Get notifications
    $notifications = $db->getUnreadNotifications();
    
    // Get recent activity
    $activity = $db->getRecentActivity(10);
    
    // Get expiring blood
    $expiring = $db->getExpiringBlood(7);
    
    return [
        'stats' => $stats,
        'notifications' => $notifications,
        'recent_activity' => $activity,
        'expiring_blood' => $expiring
    ];
}

/**
 * ============================================================================
 * EXAMPLE 6: Search Donors
 * ============================================================================
 */

/**
 * Example: Search donors with filters
 */
function exampleSearchDonors() {
    $db = Database::getInstance();
    
    // Search all eligible O+ donors in Mumbai
    $donors = $db->searchDonors('O+', 'Mumbai', true);
    
    echo "<h3>Search Results: Eligible O+ Donors in Mumbai</h3>";
    
    if (empty($donors)) {
        echo "<p>No donors found matching criteria.</p>";
        return [];
    }
    
    foreach ($donors as $donor) {
        echo "<p>";
        echo "Name: " . $donor['donor_name'] . "<br>";
        echo "Phone: " . $donor['phone'] . "<br>";
        echo "Last Donation: " . $donor['last_donation_info'];
        echo "</p><hr>";
    }
    
    return $donors;
}
