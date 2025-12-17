<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    header('Location: add_donor.php'); 
    exit; 
}

// Collect form data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$age = intval($_POST['age'] ?? 0);
$gender = $_POST['gender'] ?? '';
$blood_group = $_POST['blood_group'] ?? '';
$phone = trim($_POST['phone'] ?? '');
$weight = floatval($_POST['weight'] ?? 0);
$last_donation_date = $_POST['last_donation_date'] ?? null;
$address = trim($_POST['address'] ?? '');
$medical_notes = trim($_POST['medical_notes'] ?? '');

// Server-side validation
$errors = [];

if (empty($name) || strlen($name) < 2) {
    $errors[] = "Name is required and must be at least 2 characters";
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format";
}

if ($age < 18 || $age > 65) {
    $errors[] = "Age must be between 18 and 65 years";
}

if (!in_array($gender, ['Male', 'Female', 'Other'])) {
    $errors[] = "Please select a valid gender";
}

if (empty($blood_group)) {
    $errors[] = "Blood group is required";
}

if (!preg_match('/^[0-9]{10}$/', $phone)) {
    $errors[] = "Phone must be a 10-digit number";
}

if ($weight < 50) {
    $errors[] = "Weight must be at least 50 kg to donate blood";
}

// Check if phone already exists
$check = $conn->prepare("SELECT donor_id FROM donors WHERE phone = ?");
$check->bind_param('s', $phone);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    $errors[] = "A donor with this phone number already exists";
}

// Check if email already exists (if provided)
if (!empty($email)) {
    $check = $conn->prepare("SELECT donor_id FROM donors WHERE email = ?");
    $check->bind_param('s', $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $errors[] = "A donor with this email already exists";
    }
}

if (!empty($errors)) {
    header('Location: add_donor.php?error=' . urlencode(implode('. ', $errors)));
    exit;
}

// Format last donation date
$last_donation_date = !empty($last_donation_date) ? $last_donation_date : null;

// Insert donor - note: no longer updating stock directly (use donations table)
$stmt = $conn->prepare("INSERT INTO donors (name, email, age, gender, blood_group, phone, weight, last_donation_date, address, medical_notes, status) VALUES (?,?,?,?,?,?,?,?,?,?, 'Active')");
$stmt->bind_param('ssisssdsss', $name, $email, $age, $gender, $blood_group, $phone, $weight, $last_donation_date, $address, $medical_notes);

if ($stmt->execute()) {
    $donor_id = $conn->insert_id;
    
    // Log activity
    if (function_exists('log_activity')) {
        log_activity($conn, 'donor_add', "New donor registered: $name ($blood_group)", 'donors', $donor_id);
    }
    
    header('Location: view_donors.php?added=1');
} else {
    header('Location: add_donor.php?error=' . urlencode('Database error: ' . $stmt->error));
}
?>