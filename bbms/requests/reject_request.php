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

if (!$request || $request["status"] != "Pending") {
    header("Location: view_requests.php?error=Invalid request");
    exit;
}

// Reject the request
$stmt = $conn->prepare("UPDATE blood_requests SET status = 'Rejected' WHERE request_id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Log activity
    if (function_exists('log_activity')) {
        log_activity($conn, 'request_reject', "Blood request rejected: {$request['patient_name']} - {$request['blood_group']}", 'blood_requests', $id);
    }
    
    header('Location: view_requests.php?rejected=1');
} else {
    header('Location: view_requests.php?error=' . urlencode('Could not reject request'));
}
?>
