<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 

$id = intval($_GET["id"] ?? 0);
if (!$id) {
    header("Location: view_patients.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM patients WHERE patient_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

if (!$patient) {
    header("Location: view_patients.php?error=Patient not found");
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $age = intval($_POST["age"] ?? 0);
    $gender = $_POST["gender"] ?? "";
    $blood_group = $_POST["blood_group"] ?? "";
    $phone = trim($_POST["phone"] ?? "");
    $disease = trim($_POST["disease"] ?? "");
    $address = trim($_POST["address"] ?? "");
    
    $errors = array();
    
    if (empty($name) || strlen($name) < 2) {
        $errors[] = "Name is required";
    }
    if ($age < 0 || $age > 120) {
        $errors[] = "Age must be between 0 and 120";
    }
    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        $errors[] = "Phone must be 10 digits";
    }
    
    // Check phone uniqueness (excluding current)
    $check = $conn->prepare("SELECT patient_id FROM patients WHERE phone = ? AND patient_id != ?");
    $check->bind_param("si", $phone, $id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $errors[] = "Phone number already exists";
    }
    
    if (empty($errors)) {
        $disease = !empty($disease) ? $disease : null;
        $address = !empty($address) ? $address : null;
        
        $stmt = $conn->prepare("UPDATE patients SET name=?, age=?, gender=?, blood_group=?, phone=?, disease=?, address=? WHERE patient_id=?");
        $stmt->bind_param("sisssssi", $name, $age, $gender, $blood_group, $phone, $disease, $address, $id);
        
        if ($stmt->execute()) {
            header("Location: view_patients.php?updated=1");
            exit;
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
    }
}

include '../header.php'; 
$groups = $conn->query("SELECT group_code, description FROM blood_groups ORDER BY group_code"); 
?>
<style>
  .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
  .form-group { margin-bottom: 0; }
  .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 1.5rem; }
  .alert-danger { background: #fee; color: #dc3545; border: 1px solid #fcc; }
  @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }
</style>
<div class="card" style="max-width: none;">
  <h5 style="color: #333; margin-bottom: 24px; font-weight: 700;"><i class="bi bi-pencil-square me-2"></i>Edit Patient</h5>
  
  <?php if(!empty($errors)): ?>
    <div class="alert alert-danger">
      <i class="bi bi-exclamation-circle me-2"></i>
      <?= implode("<br>", array_map("htmlspecialchars", $errors)) ?>
    </div>
  <?php endif; ?>
  
  <form method="POST">
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Full Name *</label>
        <input name="name" class="form-control" value="<?= htmlspecialchars($patient["name"]) ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Age *</label>
        <input name="age" type="number" class="form-control" value="<?= $patient["age"] ?>" min="0" max="120" required>
      </div>
      <div class="form-group">
        <label class="form-label">Gender *</label>
        <select name="gender" class="form-control" required>
          <option value="Male" <?= $patient["gender"]=="Male"?"selected":"" ?>>Male</option>
          <option value="Female" <?= $patient["gender"]=="Female"?"selected":"" ?>>Female</option>
          <option value="Other" <?= $patient["gender"]=="Other"?"selected":"" ?>>Other</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Blood Group *</label>
        <select name="blood_group" class="form-control" required>
          <?php while($g=$groups->fetch_assoc()): ?>
            <option value="<?= $g["group_code"] ?>" <?= $patient["blood_group"]==$g["group_code"]?"selected":"" ?>><?= $g["group_code"] ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Phone *</label>
        <input name="phone" class="form-control" value="<?= htmlspecialchars($patient["phone"]) ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Disease / Condition</label>
        <input name="disease" class="form-control" value="<?= htmlspecialchars($patient["disease"] ?? "") ?>">
      </div>
    </div>
    
    <div style="margin-top: 1.5rem;">
      <label class="form-label">Address</label>
      <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($patient["address"] ?? "") ?></textarea>
    </div>
    
    <div style="margin-top: 2rem; display: flex; gap: 0.75rem;">
      <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Update Patient</button>
      <a href="view_patients.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<?php include '../footer.php'; ?>
