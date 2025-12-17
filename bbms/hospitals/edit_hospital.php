<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 

$id = intval($_GET["id"] ?? 0);
if (!$id) {
    header("Location: view_hospitals.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM hospitals WHERE hospital_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$hospital = $stmt->get_result()->fetch_assoc();

if (!$hospital) {
    header("Location: view_hospitals.php?error=Hospital not found");
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $hospital_name = trim($_POST["hospital_name"] ?? "");
    $contact_person = trim($_POST["contact_person"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $status = $_POST["status"] ?? "Active";
    
    $errors = array();
    
    if (empty($hospital_name)) {
        $errors[] = "Hospital name is required";
    }
    
    // Check for duplicate name (excluding current)
    $check = $conn->prepare("SELECT hospital_id FROM hospitals WHERE hospital_name = ? AND hospital_id != ?");
    $check->bind_param("si", $hospital_name, $id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $errors[] = "A hospital with this name already exists";
    }
    
    if (empty($errors)) {
        $email = !empty($email) ? $email : null;
        $contact_person = !empty($contact_person) ? $contact_person : null;
        $phone = !empty($phone) ? $phone : null;
        $address = !empty($address) ? $address : null;
        
        $stmt = $conn->prepare("UPDATE hospitals SET hospital_name=?, contact_person=?, phone=?, email=?, address=?, status=? WHERE hospital_id=?");
        $stmt->bind_param("ssssssi", $hospital_name, $contact_person, $phone, $email, $address, $status, $id);
        
        if ($stmt->execute()) {
            header("Location: view_hospitals.php?updated=1");
            exit;
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
    }
}

include '../header.php'; 
?>
<style>
  .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
  .form-group { margin-bottom: 0; }
  .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 1.5rem; }
  .alert-danger { background: #fee; color: #dc3545; border: 1px solid #fcc; }
  @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }
</style>
<div class="card" style="max-width: none;">
  <h5 style="color: #333; margin-bottom: 24px; font-weight: 700;"><i class="bi bi-pencil-square me-2"></i>Edit Hospital</h5>
  
  <?php if(!empty($errors)): ?>
    <div class="alert alert-danger">
      <i class="bi bi-exclamation-circle me-2"></i>
      <?= implode("<br>", array_map("htmlspecialchars", $errors)) ?>
    </div>
  <?php endif; ?>
  
  <form method="POST">
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Hospital Name *</label>
        <input name="hospital_name" class="form-control" value="<?= htmlspecialchars($hospital["hospital_name"]) ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Contact Person</label>
        <input name="contact_person" class="form-control" value="<?= htmlspecialchars($hospital["contact_person"] ?? "") ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Phone</label>
        <input name="phone" class="form-control" value="<?= htmlspecialchars($hospital["phone"] ?? "") ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input name="email" type="email" class="form-control" value="<?= htmlspecialchars($hospital["email"] ?? "") ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Status *</label>
        <select name="status" class="form-control" required>
          <option value="Active" <?= $hospital["status"]=="Active"?"selected":"" ?>>Active</option>
          <option value="Inactive" <?= $hospital["status"]=="Inactive"?"selected":"" ?>>Inactive</option>
        </select>
      </div>
    </div>
    
    <div style="margin-top: 1.5rem;">
      <label class="form-label">Address</label>
      <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($hospital["address"] ?? "") ?></textarea>
    </div>
    
    <div style="margin-top: 2rem; display: flex; gap: 0.75rem;">
      <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Update Hospital</button>
      <a href="view_hospitals.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<?php include '../footer.php'; ?>
