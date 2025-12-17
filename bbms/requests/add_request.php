<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 
include '../header.php'; 

$groups = $conn->query("SELECT group_code, description FROM blood_groups ORDER BY group_code");
$hospitals = $conn->query("SELECT hospital_id, hospital_name FROM hospitals WHERE status = 'Active' ORDER BY hospital_name");
$patients = $conn->query("SELECT patient_id, name, blood_group FROM patients ORDER BY name");

$error = $_GET['error'] ?? '';
?>
<style>
  .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
  .form-group { margin-bottom: 0; }
  .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px; }
  .alert-danger { background: #fee; color: #dc3545; border: 1px solid #fcc; }
  .alert-info { background: #e3f2fd; color: #1976d2; border: 1px solid #bbdefb; }
  @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }
</style>
<div class="card" style="max-width: none;">
  <h5 style="color: #333; margin-bottom: 24px; font-weight: 700;"><i class="bi bi-clipboard-plus me-2"></i>New Blood Request</h5>
  
  <?php if($error): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  
  <div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    Requests will be reviewed and approved before blood can be issued.
  </div>
  
  <form method="POST" action="add_request_action.php" data-validate-form>
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Patient *</label>
        <select name="patient_id" class="form-control" required>
          <option value="">Select patient...</option>
          <?php while($p=$patients->fetch_assoc()): ?>
            <option value="<?= $p['patient_id'] ?>" data-blood="<?= $p['blood_group'] ?>">
              <?= htmlspecialchars($p['name']) ?> (<?= $p['blood_group'] ?>)
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      
      <div class="form-group">
        <label class="form-label">Hospital (Optional)</label>
        <select name="hospital_id" class="form-control">
          <option value="">Select hospital...</option>
          <?php while($h=$hospitals->fetch_assoc()): ?>
            <option value="<?= $h['hospital_id'] ?>"><?= htmlspecialchars($h['hospital_name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      
      <div class="form-group">
        <label class="form-label">Blood Group Required *</label>
        <select name="blood_group" id="blood_group" class="form-control" required>
          <option value="">Select blood group...</option>
          <?php while($g=$groups->fetch_assoc()): ?>
            <option value="<?= $g['group_code'] ?>"><?= $g['group_code'] ?> - <?= htmlspecialchars($g['description']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      
      <div class="form-group">
        <label class="form-label">Units Required *</label>
        <input name="units_required" type="number" class="form-control" min="1" max="10" value="1" required>
      </div>
      
      <div class="form-group">
        <label class="form-label">Priority *</label>
        <select name="priority" class="form-control" required>
          <option value="Normal">Normal</option>
          <option value="High">High</option>
          <option value="Critical">Critical - Emergency</option>
        </select>
      </div>
      
      <div class="form-group">
        <label class="form-label">Required By Date</label>
        <input name="required_date" type="date" class="form-control" min="<?= date('Y-m-d') ?>">
      </div>
    </div>
    
    <div style="margin-top: 1.5rem;">
      <label class="form-label">Reason / Notes</label>
      <textarea name="reason" class="form-control" placeholder="Reason for blood request (surgery, accident, etc.)" rows="2"></textarea>
    </div>
    
    <div style="margin-top: 2rem; display: flex; gap: 0.75rem;">
      <button type="submit" class="btn btn-brand"><i class="bi bi-send me-1"></i>Submit Request</button>
      <a href="view_requests.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>

<script>
// Auto-select blood group when patient is selected
document.querySelector('select[name="patient_id"]').addEventListener('change', function() {
  const selected = this.options[this.selectedIndex];
  const blood = selected.getAttribute('data-blood');
  if (blood) {
    document.getElementById('blood_group').value = blood;
  }
});
</script>
<script src="/bbms/assets/app.js"></script>
<?php include '../footer.php'; ?>
