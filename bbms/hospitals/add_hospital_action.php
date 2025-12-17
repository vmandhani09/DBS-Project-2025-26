<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 

if ($_SERVER["REQUEST_METHOD"] !== "POST") { 
    header("Location: add_hospital.php"); 
    exit; 
}

$hospital_name = trim($_POST["hospital_name"] ?? "");
$contact_person = trim($_POST["contact_person"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$email = trim($_POST["email"] ?? "");
$address = trim($_POST["address"] ?? "");

$errors = array();

if (empty($hospital_name)) {
    $errors[] = "Hospital name is required";
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format";
}

// Check for duplicate name
$check = $conn->prepare("SELECT hospital_id FROM hospitals WHERE hospital_name = ?");
$check->bind_param("s", $hospital_name);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    $errors[] = "A hospital with this name already exists";
}

if (!empty($errors)) {
    header("Location: add_hospital.php?error=" . urlencode(implode(". ", $errors)));
    exit;
}

// Insert hospital
$email = !empty($email) ? $email : null;
$contact_person = !empty($contact_person) ? $contact_person : null;
$phone = !empty($phone) ? $phone : null;
$address = !empty($address) ? $address : null;

$stmt = $conn->prepare("INSERT INTO hospitals (hospital_name, contact_person, phone, email, address, status) VALUES (?, ?, ?, ?, ?, 'Active')");
$stmt->bind_param("sssss", $hospital_name, $contact_person, $phone, $email, $address);

if ($stmt->execute()) {
    $hospital_id = $conn->insert_id;
    
    if (function_exists("log_activity")) {
        log_activity($conn, "hospital_add", "Hospital registered: $hospital_name", "hospitals", $hospital_id);
    }
    
    header("Location: view_hospitals.php?added=1");
} else {
    header("Location: add_hospital.php?error=" . urlencode("Database error: " . $stmt->error));
}
?>
