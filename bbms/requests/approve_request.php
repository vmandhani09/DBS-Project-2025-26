<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: view_requests.php');
    exit;
}

// Get request details
$stmt = $conn->prepare("SELECT r.*, p.name as patient_name FROM blood_requests r LEFT JOIN patients p ON r.patient_id = p.patient_id WHERE r.request_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$request = $stmt->get_result()->fetch_assoc();

if (!$request || $request['status'] != 'Pending') {
    header('Location: view_requests.php?error=Invalid request');
    exit;
}

// Check stock availability
$stock_check = $conn->prepare("SELECT units_available FROM blood_stock WHERE group_code = ?");
$stock_check->bind_param("s", $request['blood_group']);
$stock_check->execute();
$stock = $stock_check->get_result()->fetch_assoc();

if (!$stock || $stock['units_available'] < $request['units_required']) {
    header('Location: view_requests.php?error=' . urlencode('Insufficient stock for ' . $request['blood_group']));
    exit;
}

// Approve the request
$admin_id = $_SESSION['admin_id'] ?? null;
$stmt = $conn->prepare("UPDATE blood_requests SET status = 'Approved', approved_by = ?, approved_date = NOW() WHERE request_id = ?");
$stmt->bind_param("ii", $admin_id, $id);

if ($stmt->execute()) {
    // Log activity
    if (function_exists('log_activity')) {
        log_activity($conn, 'request_approve', "Blood request approved: {$request['patient_name']} - {$request['blood_group']}", 'blood_requests', $id);
    }
    
    header('Location: view_requests.php?approved=1');
} else {
    header('Location: view_requests.php?error=' . urlencode('Could not approve request'));
}
?>
