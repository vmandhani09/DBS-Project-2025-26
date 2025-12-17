<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 
include '../header.php'; 

// Filter options
$status_filter = $_GET['status'] ?? '';
$blood_group_filter = $_GET['blood_group'] ?? '';

$where = "WHERE 1=1";
if ($status_filter) $where .= " AND d.status = '" . $conn->real_escape_string($status_filter) . "'";
if ($blood_group_filter) $where .= " AND d.blood_group = '" . $conn->real_escape_string($blood_group_filter) . "'";

$res = $conn->query("SELECT d.*, g.description, 
    (SELECT COUNT(*) FROM donations dn WHERE dn.donor_id = d.donor_id) as donation_count
    FROM donors d 
    LEFT JOIN blood_groups g ON d.blood_group = g.group_code 
    $where 
    ORDER BY d.created_at DESC");

$groups = $conn->query("SELECT group_code FROM blood_groups ORDER BY group_code");
?>
<style>
  .filter-bar { display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; align-items: center; }
  .filter-bar select { padding: 6px 12px; border-radius: 6px; border: 1px solid #ddd; }
  .status-badge { padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
  .status-Active { background: #e8f5e9; color: #28a745; }
  .status-Inactive { background: #ffebee; color: #dc3545; }
  .status-Deferred { background: #fff8e6; color: #f0ad4e; }
  .donor-actions { display: flex; gap: 6px; }
  .donor-actions a { padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; }
  .donor-email { font-size: 0.8rem; color: #888; }
  .donation-badge { background: #e3f2fd; color: #1976d2; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; }
</style>
<div class="card table-modern">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Donors</h5>
    <a class="btn btn-sm btn-brand" href="add_donor.php"><i class="bi bi-person-plus me-1"></i>Add Donor</a>
  </div>
  
  <div class="filter-bar">
    <form method="GET" class="d-flex gap-2 flex-wrap">
      <select name="status" onchange="this.form.submit()">
        <option value="">All Status</option>
        <option value="Active" <?= $status_filter=='Active'?'selected':''?>>Active</option>
        <option value="Inactive" <?= $status_filter=='Inactive'?'selected':''?>>Inactive</option>
        <option value="Deferred" <?= $status_filter=='Deferred'?'selected':''?>>Deferred</option>
      </select>
      <select name="blood_group" onchange="this.form.submit()">
        <option value="">All Blood Groups</option>
        <?php while($g=$groups->fetch_assoc()): ?>
          <option value="<?=$g['group_code']?>" <?= $blood_group_filter==$g['group_code']?'selected':''?>><?=$g['group_code']?></option>
        <?php endwhile; ?>
      </select>
      <?php if($status_filter || $blood_group_filter): ?>
        <a href="view_donors.php" class="btn btn-sm btn-ghost">Clear</a>
      <?php endif; ?>
    </form>
  </div>
  
  <?php if(isset($_GET['added'])): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i>Donor registered successfully!</div>
  <?php endif; ?>
  <?php if(isset($_GET['deleted'])): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i>Donor deleted successfully.</div>
  <?php endif; ?>
  <?php if(isset($_GET['updated'])): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i>Donor updated successfully.</div>
  <?php endif; ?>
  
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Blood Group</th>
          <th>Phone</th>
          <th>Age/Gender</th>
          <th>Donations</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if($res->num_rows > 0): ?>
          <?php while($r=$res->fetch_assoc()): ?>
          <tr>
            <td><?= $r['donor_id'] ?></td>
            <td>
              <strong><?= htmlspecialchars($r['name']) ?></strong>
              <?php if($r['email']): ?>
                <div class="donor-email"><?= htmlspecialchars($r['email']) ?></div>
              <?php endif; ?>
            </td>
            <td><strong style="color: var(--secondary-color);"><?= $r['blood_group'] ?></strong></td>
            <td><?= htmlspecialchars($r['phone']) ?></td>
            <td><?= $r['age'] ?> / <?= $r['gender'] ?></td>
            <td><span class="donation-badge"><?= $r['donation_count'] ?> donations</span></td>
            <td><span class="status-badge status-<?= $r['status'] ?>"><?= $r['status'] ?></span></td>
            <td class="donor-actions">
              <a href="edit_donor.php?id=<?= $r['donor_id'] ?>" class="btn btn-sm btn-ghost" title="Edit"><i class="bi bi-pencil"></i></a>
              <a href="/bbms/donations/add_donation.php?donor_id=<?= $r['donor_id'] ?>" class="btn btn-sm btn-brand" title="Record Donation"><i class="bi bi-plus-circle"></i></a>
              <a href="delete_donor.php?id=<?= $r['donor_id'] ?>" class="btn btn-sm btn-ghost" style="color:#dc3545" onclick="return confirm('Are you sure?')" title="Delete"><i class="bi bi-trash"></i></a>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="8" class="text-center py-4 text-muted">No donors found</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../footer.php'; ?>