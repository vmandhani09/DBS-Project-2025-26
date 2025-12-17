<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 
include '../header.php'; 

$status_filter = $_GET['status'] ?? '';
$where = $status_filter ? "WHERE r.status = '" . $conn->real_escape_string($status_filter) . "'" : "";

$res = $conn->query("SELECT r.*, h.hospital_name, p.name as patient_name, bg.description as blood_desc
    FROM blood_requests r 
    LEFT JOIN hospitals h ON r.hospital_id = h.hospital_id 
    LEFT JOIN patients p ON r.patient_id = p.patient_id
    LEFT JOIN blood_groups bg ON r.blood_group = bg.group_code
    $where
    ORDER BY r.request_date DESC 
    LIMIT 100");
?>
<style>
  .status-badge { padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
  .status-Pending { background: #fff8e6; color: #f0ad4e; }
  .status-Approved { background: #e8f5e9; color: #28a745; }
  .status-Rejected { background: #ffebee; color: #dc3545; }
  .status-Fulfilled { background: #e3f2fd; color: #1976d2; }
  .priority-badge { padding: 2px 8px; border-radius: 8px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
  .priority-Critical { background: #dc3545; color: white; }
  .priority-High { background: #f0ad4e; color: white; }
  .priority-Normal { background: #6c757d; color: white; }
  .filter-bar { display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; align-items: center; }
  .filter-bar select { padding: 6px 12px; border-radius: 6px; border: 1px solid #ddd; }
  .action-btns { display: flex; gap: 4px; }
  .action-btns a, .action-btns button { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; border: none; cursor: pointer; }
</style>
<div class="card table-modern">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Blood Requests</h5>
    <a class="btn btn-sm btn-brand" href="add_request.php"><i class="bi bi-plus-circle me-1"></i>New Request</a>
  </div>
  
  <div class="filter-bar">
    <form method="GET" class="d-flex gap-2">
      <select name="status" onchange="this.form.submit()">
        <option value="">All Status</option>
        <option value="Pending" <?= $status_filter=='Pending'?'selected':''?>>Pending</option>
        <option value="Approved" <?= $status_filter=='Approved'?'selected':''?>>Approved</option>
        <option value="Rejected" <?= $status_filter=='Rejected'?'selected':''?>>Rejected</option>
        <option value="Fulfilled" <?= $status_filter=='Fulfilled'?'selected':''?>>Fulfilled</option>
      </select>
      <?php if($status_filter): ?>
        <a href="view_requests.php" class="btn btn-sm btn-ghost">Clear</a>
      <?php endif; ?>
    </form>
  </div>
  
  <?php if(isset($_GET['added'])): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i>Blood request submitted successfully!</div>
  <?php endif; ?>
  <?php if(isset($_GET['approved'])): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i>Request approved!</div>
  <?php endif; ?>
  <?php if(isset($_GET['rejected'])): ?>
    <div class="alert alert-info mb-3"><i class="bi bi-info-circle me-2"></i>Request rejected.</div>
  <?php endif; ?>
  
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Patient/Hospital</th>
          <th>Blood Group</th>
          <th>Units</th>
          <th>Priority</th>
          <th>Date</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if($res->num_rows > 0): ?>
          <?php while($r=$res->fetch_assoc()): ?>
          <tr>
            <td><?= $r['request_id'] ?></td>
            <td>
              <strong><?= htmlspecialchars($r['patient_name'] ?? 'N/A') ?></strong>
              <?php if($r['hospital_name']): ?>
                <div class="small text-muted"><?= htmlspecialchars($r['hospital_name']) ?></div>
              <?php endif; ?>
            </td>
            <td><strong style="color: var(--secondary-color);"><?= $r['blood_group'] ?></strong></td>
            <td><?= $r['units_required'] ?></td>
            <td><span class="priority-badge priority-<?= $r['priority'] ?>"><?= $r['priority'] ?></span></td>
            <td><?= date('M d, Y', strtotime($r['request_date'])) ?></td>
            <td><span class="status-badge status-<?= $r['status'] ?>"><?= $r['status'] ?></span></td>
            <td class="action-btns">
              <?php if($r['status'] == 'Pending'): ?>
                <a href="approve_request.php?id=<?= $r['request_id'] ?>" class="btn btn-sm btn-brand" onclick="return confirm('Approve this request?')"><i class="bi bi-check-lg"></i></a>
                <a href="reject_request.php?id=<?= $r['request_id'] ?>" class="btn btn-sm btn-ghost" style="color:#dc3545" onclick="return confirm('Reject this request?')"><i class="bi bi-x-lg"></i></a>
              <?php elseif($r['status'] == 'Approved'): ?>
                <a href="/bbms/issue/issue_blood.php?request_id=<?= $r['request_id'] ?>" class="btn btn-sm btn-brand"><i class="bi bi-box-seam me-1"></i>Issue</a>
              <?php else: ?>
                <span class="text-muted small">-</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="8" class="text-center py-4 text-muted">No requests found</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../footer.php'; ?>
