<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 
include '../header.php'; 

$res = $conn->query("SELECT d.*, dn.name as donor_name, dn.blood_group 
    FROM donations d 
    LEFT JOIN donors dn ON d.donor_id = dn.donor_id 
    ORDER BY d.donation_date DESC 
    LIMIT 100");
?>
<style>
  .status-badge { padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
  .status-Completed { background: #e8f5e9; color: #28a745; }
  .status-Pending { background: #fff8e6; color: #f0ad4e; }
  .status-Rejected { background: #ffebee; color: #dc3545; }
  .units-badge { background: #e3f2fd; color: #1976d2; padding: 4px 8px; border-radius: 8px; font-weight: 600; }
</style>
<div class="card table-modern">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0"><i class="bi bi-heart-pulse-fill me-2"></i>Donations</h5>
    <a class="btn btn-sm btn-brand" href="add_donation.php"><i class="bi bi-plus-circle me-1"></i>Record Donation</a>
  </div>
  
  <?php if(isset($_GET['added'])): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i>Donation recorded successfully! Stock updated.</div>
  <?php endif; ?>
  
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Donor</th>
          <th>Blood Group</th>
          <th>Units</th>
          <th>Date</th>
          <th>Status</th>
          <th>Notes</th>
        </tr>
      </thead>
      <tbody>
        <?php if($res->num_rows > 0): ?>
          <?php while($r=$res->fetch_assoc()): ?>
          <tr>
            <td><?= $r['donation_id'] ?></td>
            <td><strong><?= htmlspecialchars($r['donor_name']) ?></strong></td>
            <td><strong style="color: var(--secondary-color);"><?= $r['blood_group'] ?></strong></td>
            <td><span class="units-badge"><?= $r['units_donated'] ?> unit(s)</span></td>
            <td><?= date('M d, Y', strtotime($r['donation_date'])) ?></td>
            <td><span class="status-badge status-<?= $r['status'] ?>"><?= $r['status'] ?></span></td>
            <td><?= htmlspecialchars($r['notes'] ?? '-') ?></td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center py-4 text-muted">No donations recorded yet</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../footer.php'; ?>
