<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 
include '../header.php'; 

$error = isset($_GET["error"]) ? $_GET["error"] : "";
?>
<style>
  .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
  .form-group { margin-bottom: 0; }
  .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px; }
  .alert-danger { background: #fee; color: #dc3545; border: 1px solid #fcc; }
  @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }
</style>
<div class="card" style="max-width: none;">
  <h5 style="color: #333; margin-bottom: 24px; font-weight: 700;"><i class="bi bi-building-add me-2"></i>Add Hospital</h5>
  
  <?php if($error): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  
  <form method="POST" action="add_hospital_action.php" data-validate-form>
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Hospital Name *</label>
        <input name="hospital_name" class="form-control" data-validate="required" placeholder="Enter hospital name" required>
        <div class="error-message"></div>
      </div>
      <div class="form-group">
        <label class="form-label">Contact Person</label>
        <input name="contact_person" class="form-control" placeholder="Contact person name">
      </div>
      <div class="form-group">
        <label class="form-label">Phone</label>
        <input name="phone" class="form-control" placeholder="Phone number">
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input name="email" type="email" class="form-control" placeholder="hospital@example.com">
      </div>
    </div>
    
    <div style="margin-top: 1.5rem;">
      <label class="form-label">Address</label>
      <textarea name="address" class="form-control" placeholder="Hospital address" rows="2"></textarea>
    </div>
    
    <div style="margin-top: 2rem; display: flex; gap: 0.75rem;">
      <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Add Hospital</button>
      <a href="view_hospitals.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<script src="/bbms/assets/app.js"></script>
<?php include '../footer.php'; ?>
