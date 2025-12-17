<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    header('Location: add_request.php'); 
    exit; 
}

$patient_id = intval($_POST['patient_id'] ?? 0);
$hospital_id = intval($_POST['hospital_id'] ?? 0);
$blood_group = $_POST['blood_group'] ?? '';
$units_required = intval($_POST['units_required'] ?? 1);
$priority = $_POST['priority'] ?? 'Normal';
$required_date = $_POST['required_date'] ?? null;
$reason = trim($_POST['reason'] ?? '');

$errors = [];

// Validate patient
if (!$patient_id) {
    $errors[] = "Please select a patient";
} else {
    $check = $conn->prepare("SELECT patient_id, name FROM patients WHERE patient_id = ?");
    $check->bind_param("i", $patient_id);
    $check->execute();
    $patient = $check->get_result()->fetch_assoc();
    if (!$patient) {
        $errors[] = "Invalid patient selected";
    }
}

// Validate blood group
if (empty($blood_group)) {
    $errors[] = "Blood group is required";
}

// Validate units
if ($units_required < 1 || $units_required > 10) {
    $errors[] = "Units must be between 1 and 10";
}

// Validate priority
if (!in_array($priority, ['Normal', 'High', 'Critical'])) {
    $errors[] = "Invalid priority level";
}

// Check stock availability (warning only)
$stock_check = $conn->prepare("SELECT units_available FROM blood_stock WHERE group_code = ?");
$stock_check->bind_param('s', $blood_group);
$stock_check->execute();
$stock = $stock_check->get_result()->fetch_assoc();
$stock_warning = ($stock && $stock['units_available'] < $units_required) ? "Note: Current stock may be insufficient" : "";

if (!empty($errors)) {
    header('Location: add_request.php?error=' . urlencode(implode('. ', $errors)));
    exit;
}

// Format optional fields
$hospital_id = $hospital_id ?: null;
$required_date = !empty($required_date) ? $required_date : null;

// Insert request
$stmt = $conn->prepare("INSERT INTO blood_requests (patient_id, hospital_id, blood_group, units_required, priority, required_date, reason, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
$stmt->bind_param('iisisss', $patient_id, $hospital_id, $blood_group, $units_required, $priority, $required_date, $reason);

if ($stmt->execute()) {
    $request_id = $conn->insert_id;
    
    // Log activity
    if (function_exists('log_activity')) {
        log_activity($conn, 'request_add', "Blood request submitted: {$patient['name']} - $blood_group x $units_required units ($priority)", 'blood_requests', $request_id);
    }
    
    // Create notification for critical requests
    if ($priority == 'Critical') {
        $conn->query("INSERT INTO notification_logs (title, message, severity) VALUES ('Critical Blood Request', 'Critical blood request for $blood_group submitted', 'Critical')");
    }
    
    header('Location: view_requests.php?added=1');
} else {
    header('Location: add_request.php?error=' . urlencode('Database error: ' . $stmt->error));
}
?>
