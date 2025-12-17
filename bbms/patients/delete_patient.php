<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 

$id = intval($_GET["id"] ?? 0);
if (!$id) {
    header("Location: view_patients.php");
    exit;
}

// Check for related blood requests
$check = $conn->prepare("SELECT COUNT(*) as cnt FROM blood_requests WHERE patient_id = ?");
$check->bind_param("i", $id);
$check->execute();
$requests = $check->get_result()->fetch_assoc()["cnt"];

if ($requests > 0) {
    header("Location: view_patients.php?error=" . urlencode("Cannot delete patient with blood requests"));
    exit;
}

// Safe to delete
$stmt = $conn->prepare("DELETE FROM patients WHERE patient_id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: view_patients.php?deleted=1");
} else {
    header("Location: view_patients.php?error=" . urlencode("Could not delete patient"));
}
?>
