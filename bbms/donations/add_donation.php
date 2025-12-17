<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 
include '../header.php'; 

$donor_id = intval($_GET['donor_id'] ?? 0);
$donor = null;

if ($donor_id) {
    $stmt = $conn->prepare("SELECT * FROM donors WHERE donor_id = ? AND status = 'Active'");
    $stmt->bind_param('i', $donor_id);
    $stmt->execute();
    $donor = $stmt->get_result()->fetch_assoc();
}

// Get all active donors for dropdown
$donors = $conn->query("SELECT donor_id, name, blood_group, last_donation_date FROM donors WHERE status = 'Active' ORDER BY name");

$error = $_GET['error'] ?? '';
?>
<style>
  .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
  .form-group { margin-bottom: 0; }
  .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px; }
  .alert-danger { background: #fee; color: #dc3545; border: 1px solid #fcc; }
  .alert-info { background: #e3f2fd; color: #1976d2; border: 1px solid #bbdefb; }
  .donor-info { background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
  .donor-info h6 { margin: 0 0 8px 0; color: #333; }
  .donor-info .detail { font-size: 0.9rem; color: #666; }
  @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }
</style>
<div class="card" style="max-width: none;">
  <h5 style="color: #333; margin-bottom: 24px; font-weight: 700;"><i class="bi bi-heart-pulse me-2"></i>Record Blood Donation</h5>
  
  <?php if($error): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  
  <?php if($donor): ?>
    <div class="donor-info">
      <h6><i class="bi bi-person-check me-2"></i>Selected Donor</h6>
      <div class="detail">
        <strong><?= htmlspecialchars($donor['name']) ?></strong> - 
        Blood Group: <strong><?= $donor['blood_group'] ?></strong>
        <?php if($donor['last_donation_date']): ?>
          | Last Donation: <?= date('M d, Y', strtotime($donor['last_donation_date'])) ?>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
  
  <div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    Recording a donation will automatically update the blood stock. Ensure donor eligibility before proceeding.
  </div>
  
  <form method="POST" action="add_donation_action.php" data-validate-form>
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Select Donor *</label>
        <select name="donor_id" class="form-control" required <?= $donor ? 'disabled' : '' ?>>
          <option value="">Choose a donor...</option>
          <?php while($d=$donors->fetch_assoc()): ?>
            <option value="<?= $d['donor_id'] ?>" <?= $donor_id == $d['donor_id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($d['name']) ?> (<?= $d['blood_group'] ?>)
              <?php if($d['last_donation_date']): ?> - Last: <?= date('M d, Y', strtotime($d['last_donation_date'])) ?><?php endif; ?>
            </option>
          <?php endwhile; ?>
        </select>
        <?php if($donor): ?>
          <input type="hidden" name="donor_id" value="<?= $donor_id ?>">
        <?php endif; ?>
      </div>
      
      <div class="form-group">
        <label class="form-label">Donation Date *</label>
        <input name="donation_date" type="date" class="form-control" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
      </div>
      
      <div class="form-group">
        <label class="form-label">Units Donated *</label>
        <select name="units_donated" class="form-control" required>
          <option value="1" selected>1 Unit (450ml)</option>
          <option value="2">2 Units (rare)</option>
        </select>
      </div>
      
      <div class="form-group">
        <label class="form-label">Collection Center</label>
        <input name="collection_center" class="form-control" placeholder="e.g., Main Blood Bank">
      </div>
    </div>
    
    <div style="margin-top: 1.5rem;">
      <label class="form-label">Notes</label>
      <textarea name="notes" class="form-control" placeholder="Any observations during donation (optional)" rows="2"></textarea>
    </div>
    
    <div style="margin-top: 2rem; display: flex; gap: 0.75rem;">
      <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Record Donation</button>
      <a href="view_donations.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<script src="/bbms/assets/app.js"></script>
<?php include '../footer.php'; ?>
