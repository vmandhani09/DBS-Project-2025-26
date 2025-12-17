<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 
include '../header.php'; 

$blood_group_filter = isset($_GET["blood_group"]) ? $_GET["blood_group"] : "";
$where = $blood_group_filter ? "WHERE p.blood_group = '" . $conn->real_escape_string($blood_group_filter) . "'" : "";

$res = $conn->query("SELECT p.*, g.description,
    (SELECT COUNT(*) FROM blood_requests r WHERE r.patient_id = p.patient_id) as request_count
    FROM patients p 
    LEFT JOIN blood_groups g ON p.blood_group = g.group_code 
    $where
    ORDER BY p.created_at DESC");

$groups = $conn->query("SELECT group_code FROM blood_groups ORDER BY group_code");
?>
<style>
  .filter-bar { display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; align-items: center; }
  .filter-bar select { padding: 6px 12px; border-radius: 6px; border: 1px solid #ddd; }
  .request-badge { background: #e3f2fd; color: #1976d2; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; }
  .patient-disease { font-size: 0.8rem; color: #888; }
</style>
<div class="card table-modern">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Patients</h5>
    <a class="btn btn-sm btn-brand" href="add_patient.php"><i class="bi bi-person-plus me-1"></i>Add Patient</a>
  </div>
  
  <div class="filter-bar">
    <form method="GET" class="d-flex gap-2 flex-wrap">
      <select name="blood_group" onchange="this.form.submit()">
        <option value="">All Blood Groups</option>
        <?php while($g=$groups->fetch_assoc()): ?>
          <option value="<?= $g["group_code"] ?>" <?= $blood_group_filter==$g["group_code"]?"selected":"" ?>><?= $g["group_code"] ?></option>
        <?php endwhile; ?>
      </select>
      <?php if($blood_group_filter): ?>
        <a href="view_patients.php" class="btn btn-sm btn-ghost">Clear</a>
      <?php endif; ?>
    </form>
  </div>
  
  <?php if(isset($_GET["added"])): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i>Patient registered successfully!</div>
  <?php endif; ?>
  <?php if(isset($_GET["updated"])): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i>Patient updated successfully!</div>
  <?php endif; ?>
  <?php if(isset($_GET["deleted"])): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i>Patient deleted successfully!</div>
  <?php endif; ?>
  
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Blood Group</th>
          <th>Age/Gender</th>
          <th>Phone</th>
          <th>Requests</th>
          <th>Registered</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if($res->num_rows > 0): ?>
          <?php while($r=$res->fetch_assoc()): ?>
          <tr>
            <td><?= $r["patient_id"] ?></td>
            <td>
              <strong><?= htmlspecialchars($r["name"]) ?></strong>
              <?php if(isset($r["disease"]) && $r["disease"]): ?>
                <div class="patient-disease"><?= htmlspecialchars($r["disease"]) ?></div>
              <?php endif; ?>
            </td>
            <td><strong style="color: var(--secondary-color);"><?= $r["blood_group"] ?></strong></td>
            <td><?= $r["age"] ?> / <?= $r["gender"] ?></td>
            <td><?= htmlspecialchars($r["phone"]) ?></td>
            <td><span class="request-badge"><?= $r["request_count"] ?> requests</span></td>
            <td><?= date("M d, Y", strtotime($r["created_at"])) ?></td>
            <td>
              <a href="edit_patient.php?id=<?= $r["patient_id"] ?>" class="btn btn-sm btn-ghost" title="Edit"><i class="bi bi-pencil"></i></a>
              <a href="/bbms/requests/add_request.php?patient_id=<?= $r["patient_id"] ?>" class="btn btn-sm btn-brand" title="Blood Request"><i class="bi bi-clipboard-plus"></i></a>
              <a href="delete_patient.php?id=<?= $r["patient_id"] ?>" class="btn btn-sm btn-ghost" style="color:#dc3545" onclick="return confirm('Delete this patient?')" title="Delete"><i class="bi bi-trash"></i></a>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="8" class="text-center py-4 text-muted">No patients found</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../footer.php'; ?>