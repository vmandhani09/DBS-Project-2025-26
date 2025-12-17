<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 

if ($_SERVER["REQUEST_METHOD"] !== "POST") { 
    header("Location: issue_blood.php"); 
    exit; 
}

$patient_id = intval($_POST["patient_id"] ?? 0);
$blood_group = $_POST["blood_group"] ?? "";
$units_issued = intval($_POST["units_issued"] ?? 1);
$hospital_id = intval($_POST["hospital_id"] ?? 0);
$issued_to = trim($_POST["issued_to"] ?? "");
$issue_date = $_POST["issue_date"] ?? date("Y-m-d");
$notes = trim($_POST["notes"] ?? "");
$request_id = intval($_POST["request_id"] ?? 0);

$errors = array();

// Validate patient
if (!$patient_id) {
    $errors[] = "Please select a patient";
}

// Validate blood group
if (empty($blood_group)) {
    $errors[] = "Please select a blood group";
}

// Validate units
if ($units_issued < 1 || $units_issued > 10) {
    $errors[] = "Units must be between 1 and 10";
}

// Validate issued_to
if (empty($issued_to)) {
    $errors[] = "Recipient name is required";
}

// Check stock availability
$stmt = $conn->prepare("SELECT units_available FROM blood_stock WHERE group_code = ?");
$stmt->bind_param("s", $blood_group);
$stmt->execute();
$stock = $stmt->get_result()->fetch_assoc();

if (!$stock) {
    $errors[] = "Invalid blood group";
} elseif ($stock["units_available"] < $units_issued) {
    $errors[] = "Insufficient stock. Available: " . $stock["units_available"] . " units";
}

if (!empty($errors)) {
    $redirect = $request_id ? "issue_blood.php?request_id=$request_id" : "issue_blood.php";
    header("Location: $redirect&error=" . urlencode(implode(". ", $errors)));
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Format optional fields
    $hospital_id = $hospital_id ?: null;
    $notes = !empty($notes) ? $notes : null;
    
    // Insert blood issue record (trigger will update stock)
    $stmt = $conn->prepare("INSERT INTO blood_issue (patient_id, blood_group, units_issued, hospital_id, issued_to, issue_date, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isiisss", $patient_id, $blood_group, $units_issued, $hospital_id, $issued_to, $issue_date, $notes);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to record issue: " . $stmt->error);
    }
    
    $issue_id = $conn->insert_id;
    
    // If fulfilling a request, update its status
    if ($request_id) {
        $stmt = $conn->prepare("UPDATE blood_requests SET status = 'Fulfilled' WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
    }
    
    // Log activity
    if (function_exists("log_activity")) {
        log_activity($conn, "blood_issue", "Blood issued: $blood_group x $units_issued units to $issued_to", "blood_issue", $issue_id);
    }
    
    $conn->commit();
    header("Location: /bbms/index.php?issued=1");
    
} catch (Exception $e) {
    $conn->rollback();
    $redirect = $request_id ? "issue_blood.php?request_id=$request_id" : "issue_blood.php";
    header("Location: $redirect&error=" . urlencode($e->getMessage()));
}
?>