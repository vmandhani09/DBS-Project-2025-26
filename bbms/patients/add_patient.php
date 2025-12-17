<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 
include '../header.php'; 
$groups = $conn->query("SELECT group_code, description FROM blood_groups ORDER BY group_code"); 
$error = isset($_GET["error"]) ? $_GET["error"] : "";
$success = isset($_GET["success"]) ? $_GET["success"] : "";
?>
<style>
  .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
  .form-group { margin-bottom: 0; }
  .error-message { display: none; color: #dc3545; font-size: 0.85rem; margin-top: 4px; }
  .error-message.show { display: block; }
  .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px; }
  .alert-danger { background: #fee; color: #dc3545; border: 1px solid #fcc; }
  .alert-success { background: #e8f5e9; color: #28a745; border: 1px solid #c8e6c9; }
  @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }
</style>
<div class="card" style="max-width: none;">
  <h5 style="color: #333; margin-bottom: 24px; font-weight: 700;"><i class="bi bi-person-badge me-2"></i>Add Patient</h5>
  
  <?php if($error): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if($success): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  
  <form method="POST" action="add_patient_action.php" data-validate-form>
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Full Name *</label>
        <input name="name" class="form-control" data-validate="required,name" placeholder="Enter full name" required>
        <div class="error-message"></div>
      </div>
      <div class="form-group">
        <label class="form-label">Age *</label>
        <input name="age" type="number" class="form-control" data-validate="required,age" placeholder="0-120" min="0" max="120" required>
        <div class="error-message"></div>
      </div>
      <div class="form-group">
        <label class="form-label">Gender *</label>
        <select name="gender" class="form-control" data-validate="required" required>
          <option value="">Select Gender</option>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
          <option value="Other">Other</option>
        </select>
        <div class="error-message"></div>
      </div>
      <div class="form-group">
        <label class="form-label">Blood Group *</label>
        <select name="blood_group" class="form-control" data-validate="required" required>
          <option value="">Select blood group</option>
          <?php while($g=$groups->fetch_assoc()): ?>
            <option value="<?= $g["group_code"] ?>"><?= $g["group_code"] ?> - <?= htmlspecialchars($g["description"]) ?></option>
          <?php endwhile; ?>
        </select>
        <div class="error-message"></div>
      </div>
      <div class="form-group">
        <label class="form-label">Phone *</label>
        <input name="phone" class="form-control" data-validate="required,phone" placeholder="10-digit number" required>
        <div class="error-message"></div>
      </div>
      <div class="form-group">
        <label class="form-label">Disease / Condition</label>
        <input name="disease" class="form-control" placeholder="e.g., Surgery, Accident, Anemia">
      </div>
    </div>
    
    <div style="margin-top: 1.5rem;">
      <label class="form-label">Address</label>
      <textarea name="address" class="form-control" placeholder="Patient address" rows="2"></textarea>
    </div>
    
    <div style="margin-top: 2rem; display: flex; gap: 0.75rem;">
      <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Add Patient</button>
      <a href="view_patients.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<script src="/bbms/assets/app.js"></script>
<?php include '../footer.php'; ?>