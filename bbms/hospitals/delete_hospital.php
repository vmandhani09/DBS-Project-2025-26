<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 

$id = intval($_GET["id"] ?? 0);
if (!$id) {
    header("Location: view_hospitals.php");
    exit;
}

// Check for related requests
$check = $conn->prepare("SELECT COUNT(*) as cnt FROM blood_requests WHERE hospital_id = ?");
$check->bind_param("i", $id);
$check->execute();
$requests = $check->get_result()->fetch_assoc()["cnt"];

if ($requests > 0) {
    // Mark as inactive instead of deleting
    $stmt = $conn->prepare("UPDATE hospitals SET status = 'Inactive' WHERE hospital_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: view_hospitals.php?updated=1");
} else {
    // Safe to delete
    $stmt = $conn->prepare("DELETE FROM hospitals WHERE hospital_id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: view_hospitals.php?deleted=1");
    } else {
        header("Location: view_hospitals.php?error=" . urlencode("Could not delete hospital"));
    }
}
?>
