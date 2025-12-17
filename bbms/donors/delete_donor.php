<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: view_donors.php');
    exit;
}

// Check if donor exists
$stmt = $conn->prepare("SELECT name, blood_group FROM donors WHERE donor_id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$donor = $stmt->get_result()->fetch_assoc();

if (!$donor) {
    header('Location: view_donors.php?error=Donor not found');
    exit;
}

// Check for related donations
$check = $conn->prepare("SELECT COUNT(*) as cnt FROM donations WHERE donor_id = ?");
$check->bind_param('i', $id);
$check->execute();
$donations = $check->get_result()->fetch_assoc()['cnt'];

if ($donations > 0) {
    // Don't delete - mark as inactive instead
    $stmt = $conn->prepare("UPDATE donors SET status = 'Inactive' WHERE donor_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    
    if (function_exists('log_activity')) {
        log_activity($conn, 'donor_deactivate', "Donor deactivated (had $donations donations): {$donor['name']}", 'donors', $id);
    }
    
    header('Location: view_donors.php?updated=1&note=deactivated');
} else {
    // Safe to delete
    $stmt = $conn->prepare("DELETE FROM donors WHERE donor_id = ?");
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        if (function_exists('log_activity')) {
            log_activity($conn, 'donor_delete', "Donor deleted: {$donor['name']} ({$donor['blood_group']})", 'donors', $id);
        }
        header('Location: view_donors.php?deleted=1');
    } else {
        header('Location: view_donors.php?error=' . urlencode('Could not delete donor'));
    }
}
?>
