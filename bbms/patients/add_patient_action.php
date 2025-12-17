<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 

if ($_SERVER["REQUEST_METHOD"] !== "POST") { 
    header("Location: add_patient.php"); 
    exit; 
}

$name = trim($_POST["name"] ?? "");
$age = intval($_POST["age"] ?? 0);
$gender = $_POST["gender"] ?? "";
$blood_group = $_POST["blood_group"] ?? "";
$phone = trim($_POST["phone"] ?? "");
$disease = trim($_POST["disease"] ?? "");
$address = trim($_POST["address"] ?? "");

$errors = array();

if (empty($name) || strlen($name) < 2) {
    $errors[] = "Name is required and must be at least 2 characters";
}

if ($age < 0 || $age > 120) {
    $errors[] = "Age must be between 0 and 120";
}

if (!in_array($gender, array("Male", "Female", "Other"))) {
    $errors[] = "Please select a valid gender";
}

if (empty($blood_group)) {
    $errors[] = "Blood group is required";
}

if (!preg_match("/^[0-9]{10}$/", $phone)) {
    $errors[] = "Phone must be a 10-digit number";
}

// Check if phone already exists
$check = $conn->prepare("SELECT patient_id FROM patients WHERE phone = ?");
$check->bind_param("s", $phone);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    $errors[] = "A patient with this phone number already exists";
}

if (!empty($errors)) {
    header("Location: add_patient.php?error=" . urlencode(implode(". ", $errors)));
    exit;
}

// Insert patient
$disease = !empty($disease) ? $disease : null;
$address = !empty($address) ? $address : null;

$stmt = $conn->prepare("INSERT INTO patients (name, age, gender, blood_group, phone, disease, address) VALUES (?,?,?,?,?,?,?)");
$stmt->bind_param("sisssss", $name, $age, $gender, $blood_group, $phone, $disease, $address);

if ($stmt->execute()) {
    $patient_id = $conn->insert_id;
    
    if (function_exists("log_activity")) {
        log_activity($conn, "patient_add", "Patient registered: $name ($blood_group)", "patients", $patient_id);
    }
    
    header("Location: view_patients.php?added=1");
} else {
    header("Location: add_patient.php?error=" . urlencode("Database error: " . $stmt->error));
}
?>