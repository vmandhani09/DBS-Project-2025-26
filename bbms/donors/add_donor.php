<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 
include '../header.php'; 
$groups = $conn->query("SELECT group_code, description FROM blood_groups ORDER BY group_code"); 
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>
<style>
  .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
  .form-grid.full { grid-column: 1 / -1; }
  .form-group { margin-bottom: 0; }
  .error-message { display: none; color: #dc3545; font-size: 0.85rem; margin-top: 4px; }
  .error-message.show { display: block; }
  .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px; }
  .alert-danger { background: #fee; color: #dc3545; border: 1px solid #fcc; }
  .alert-success { background: #e8f5e9; color: #28a745; border: 1px solid #c8e6c9; }
  @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }
</style>
<div class="card" style="max-width: none;">
  <h5 style="color: #333; margin-bottom: 24px; font-weight: 700;"><i class="bi bi-person-plus me-2"></i>Add New Donor</h5>
  
  <?php if($error): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if($success): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  
  <form method="POST" action="add_donor_action.php" data-validate-form>
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Full Name *</label>
        <input name="name" class="form-control" data-validate="required,name" placeholder="Enter full name" required>
        <div class="error-message"></div>
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input name="email" type="email" class="form-control" data-validate="email" placeholder="donor@example.com">
        <div class="error-message"></div>
      </div>
      <div class="form-group">
        <label class="form-label">Age *</label>
        <input name="age" type="number" class="form-control" data-validate="required,age" placeholder="18-65" min="18" max="65" required>
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
            <option value="<?= $g['group_code'] ?>"><?= $g['group_code'] ?> - <?= htmlspecialchars($g['description']) ?></option>
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
        <label class="form-label">Weight (kg) *</label>
        <input name="weight" type="number" step="0.1" class="form-control" placeholder="Min 50kg" min="50" required>
        <div class="error-message"></div>
      </div>
      <div class="form-group">
        <label class="form-label">Last Donation Date</label>
        <input name="last_donation_date" type="date" class="form-control" max="<?= date('Y-m-d') ?>">
        <div class="error-message"></div>
      </div>
    </div>
    
    <div style="margin-top: 1.5rem;">
      <label class="form-label">Address</label>
      <textarea name="address" class="form-control" placeholder="Enter complete address" rows="2" style="resize: vertical;"></textarea>
    </div>
    
    <div style="margin-top: 1rem;">
      <label class="form-label">Medical Notes</label>
      <textarea name="medical_notes" class="form-control" placeholder="Any relevant medical history (optional)" rows="2" style="resize: vertical;"></textarea>
    </div>
    
    <div style="margin-top: 2rem; display: flex; gap: 0.75rem;">
      <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Add Donor</button>
      <a href="view_donors.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<script src="/bbms/assets/app.js"></script>
<?php include '../footer.php'; ?>