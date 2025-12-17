<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: view_donors.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM donors WHERE donor_id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$donor = $stmt->get_result()->fetch_assoc();

if (!$donor) {
    header('Location: view_donors.php?error=Donor not found');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $gender = $_POST['gender'] ?? '';
    $blood_group = $_POST['blood_group'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $weight = floatval($_POST['weight'] ?? 0);
    $status = $_POST['status'] ?? 'Active';
    $address = trim($_POST['address'] ?? '');
    $medical_notes = trim($_POST['medical_notes'] ?? '');
    
    $errors = [];
    
    if (empty($name) || strlen($name) < 2) {
        $errors[] = "Name is required";
    }
    if ($age < 18 || $age > 65) {
        $errors[] = "Age must be between 18 and 65";
    }
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = "Phone must be 10 digits";
    }
    if ($weight < 50) {
        $errors[] = "Weight must be at least 50 kg";
    }
    
    // Check phone uniqueness (excluding current donor)
    $check = $conn->prepare("SELECT donor_id FROM donors WHERE phone = ? AND donor_id != ?");
    $check->bind_param('si', $phone, $id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $errors[] = "Phone number already exists";
    }
    
    if (empty($errors)) {
        $email = !empty($email) ? $email : null;
        
        $stmt = $conn->prepare("UPDATE donors SET name=?, email=?, age=?, gender=?, blood_group=?, phone=?, weight=?, status=?, address=?, medical_notes=? WHERE donor_id=?");
        $stmt->bind_param('ssisssdsssi', $name, $email, $age, $gender, $blood_group, $phone, $weight, $status, $address, $medical_notes, $id);
        
        if ($stmt->execute()) {
            if (function_exists('log_activity')) {
                log_activity($conn, 'donor_update', "Donor updated: $name", 'donors', $id);
            }
            header('Location: view_donors.php?updated=1');
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
  <h5 style="color: #333; margin-bottom: 24px; font-weight: 700;"><i class="bi bi-pencil-square me-2"></i>Edit Donor</h5>
  
  <?php if(!empty($errors)): ?>
    <div class="alert alert-danger">
      <i class="bi bi-exclamation-circle me-2"></i>
      <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
    </div>
  <?php endif; ?>
  
  <form method="POST">
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Full Name *</label>
        <input name="name" class="form-control" value="<?= htmlspecialchars($donor['name']) ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input name="email" type="email" class="form-control" value="<?= htmlspecialchars($donor['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Age *</label>
        <input name="age" type="number" class="form-control" value="<?= $donor['age'] ?>" min="18" max="65" required>
      </div>
      <div class="form-group">
        <label class="form-label">Gender *</label>
        <select name="gender" class="form-control" required>
          <option value="Male" <?= $donor['gender']=='Male'?'selected':''?>>Male</option>
          <option value="Female" <?= $donor['gender']=='Female'?'selected':''?>>Female</option>
          <option value="Other" <?= $donor['gender']=='Other'?'selected':''?>>Other</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Blood Group *</label>
        <select name="blood_group" class="form-control" required>
          <?php while($g=$groups->fetch_assoc()): ?>
            <option value="<?= $g['group_code'] ?>" <?= $donor['blood_group']==$g['group_code']?'selected':''?>><?= $g['group_code'] ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Phone *</label>
        <input name="phone" class="form-control" value="<?= htmlspecialchars($donor['phone']) ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Weight (kg) *</label>
        <input name="weight" type="number" step="0.1" class="form-control" value="<?= $donor['weight'] ?? 50 ?>" min="50" required>
      </div>
      <div class="form-group">
        <label class="form-label">Status *</label>
        <select name="status" class="form-control" required>
          <option value="Active" <?= $donor['status']=='Active'?'selected':''?>>Active</option>
          <option value="Inactive" <?= $donor['status']=='Inactive'?'selected':''?>>Inactive</option>
          <option value="Deferred" <?= $donor['status']=='Deferred'?'selected':''?>>Deferred</option>
        </select>
      </div>
    </div>
    
    <div style="margin-top: 1.5rem;">
      <label class="form-label">Address</label>
      <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($donor['address'] ?? '') ?></textarea>
    </div>
    
    <div style="margin-top: 1rem;">
      <label class="form-label">Medical Notes</label>
      <textarea name="medical_notes" class="form-control" rows="2"><?= htmlspecialchars($donor['medical_notes'] ?? '') ?></textarea>
    </div>
    
    <div style="margin-top: 2rem; display: flex; gap: 0.75rem;">
      <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Update Donor</button>
      <a href="view_donors.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<?php include '../footer.php'; ?>
