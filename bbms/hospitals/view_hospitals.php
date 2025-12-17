<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 
include '../header.php'; 

$res = $conn->query("SELECT h.*, 
    (SELECT COUNT(*) FROM blood_requests r WHERE r.hospital_id = h.hospital_id) as request_count
    FROM hospitals h 
    ORDER BY h.hospital_name");
?>
<style>
  .status-badge { padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
  .status-Active { background: #e8f5e9; color: #28a745; }
  .status-Inactive { background: #ffebee; color: #dc3545; }
  .request-badge { background: #e3f2fd; color: #1976d2; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; }
  .hospital-contact { font-size: 0.85rem; color: #666; }
</style>
<div class="card table-modern">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0"><i class="bi bi-building me-2"></i>Hospitals</h5>
    <a class="btn btn-sm btn-brand" href="add_hospital.php"><i class="bi bi-plus-circle me-1"></i>Add Hospital</a>
  </div>
  
  <?php if(isset($_GET["added"])): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i>Hospital added successfully!</div>
  <?php endif; ?>
  <?php if(isset($_GET["updated"])): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i>Hospital updated successfully!</div>
  <?php endif; ?>
  <?php if(isset($_GET["deleted"])): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i>Hospital deleted successfully!</div>
  <?php endif; ?>
  
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Hospital Name</th>
          <th>Contact</th>
          <th>Address</th>
          <th>Requests</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if($res->num_rows > 0): ?>
          <?php while($r=$res->fetch_assoc()): ?>
          <tr>
            <td><?= $r['hospital_id'] ?></td>
            <td><strong><?= htmlspecialchars($r['hospital_name']) ?></strong></td>
            <td class="hospital-contact">
              <?php if($r['contact_person']): ?>
                <div><?= htmlspecialchars($r['contact_person']) ?></div>
              <?php endif; ?>
              <?php if($r['phone']): ?>
                <div><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($r['phone']) ?></div>
              <?php endif; ?>
              <?php if($r['email']): ?>
                <div><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($r['email']) ?></div>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($r['address'] ?? '-') ?></td>
            <td><span class="request-badge"><?= $r['request_count'] ?> requests</span></td>
            <td><span class="status-badge status-<?= $r['status'] ?>"><?= $r['status'] ?></span></td>
            <td>
              <a href="edit_hospital.php?id=<?= $r['hospital_id'] ?>" class="btn btn-sm btn-ghost" title="Edit"><i class="bi bi-pencil"></i></a>
              <a href="delete_hospital.php?id=<?= $r['hospital_id'] ?>" class="btn btn-sm btn-ghost" style="color:#dc3545" onclick="return confirm('Delete this hospital?')" title="Delete"><i class="bi bi-trash"></i></a>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center py-4 text-muted">No hospitals registered yet</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../footer.php'; ?>
