<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    header('Location: add_donation.php'); 
    exit; 
}

$donor_id = intval($_POST['donor_id'] ?? 0);
$donation_date = $_POST['donation_date'] ?? date('Y-m-d');
$units_donated = intval($_POST['units_donated'] ?? 1);
$collection_center = trim($_POST['collection_center'] ?? '');
$notes = trim($_POST['notes'] ?? '');

$errors = [];

// Validate donor
if (!$donor_id) {
    $errors[] = "Please select a donor";
} else {
    $stmt = $conn->prepare("SELECT donor_id, name, blood_group, last_donation_date, status FROM donors WHERE donor_id = ?");
    $stmt->bind_param('i', $donor_id);
    $stmt->execute();
    $donor = $stmt->get_result()->fetch_assoc();
    
    if (!$donor) {
        $errors[] = "Donor not found";
    } elseif ($donor['status'] != 'Active') {
        $errors[] = "Donor is not active and cannot donate";
    } else {
        // Check eligibility (56 days = 8 weeks between donations)
        if ($donor['last_donation_date']) {
            $last = new DateTime($donor['last_donation_date']);
            $now = new DateTime($donation_date);
            $diff = $last->diff($now)->days;
            
            if ($diff < 56) {
                $remaining = 56 - $diff;
                $errors[] = "Donor must wait $remaining more days before donating again (56-day rule)";
            }
        }
    }
}

// Validate date
if (strtotime($donation_date) > time()) {
    $errors[] = "Donation date cannot be in the future";
}

if ($units_donated < 1 || $units_donated > 2) {
    $errors[] = "Units must be 1 or 2";
}

if (!empty($errors)) {
    $redirect = $donor_id ? "add_donation.php?donor_id=$donor_id" : "add_donation.php";
    header("Location: $redirect&error=" . urlencode(implode('. ', $errors)));
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Insert donation (trigger will update stock automatically)
    $stmt = $conn->prepare("INSERT INTO donations (donor_id, donation_date, units_donated, collection_center, notes, status) VALUES (?, ?, ?, ?, ?, 'Completed')");
    $stmt->bind_param('isiss', $donor_id, $donation_date, $units_donated, $collection_center, $notes);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to record donation: " . $stmt->error);
    }
    
    $donation_id = $conn->insert_id;
    
    // Update donor's last donation date
    $stmt = $conn->prepare("UPDATE donors SET last_donation_date = ? WHERE donor_id = ?");
    $stmt->bind_param('si', $donation_date, $donor_id);
    $stmt->execute();
    
    // Log activity
    if (function_exists('log_activity')) {
        log_activity($conn, 'donation_add', "Donation recorded: {$donor['name']} ({$donor['blood_group']}) - $units_donated unit(s)", 'donations', $donation_id);
    }
    
    $conn->commit();
    header('Location: view_donations.php?added=1');
    
} catch (Exception $e) {
    $conn->rollback();
    $redirect = $donor_id ? "add_donation.php?donor_id=$donor_id" : "add_donation.php";
    header("Location: $redirect&error=" . urlencode($e->getMessage()));
}
?>
