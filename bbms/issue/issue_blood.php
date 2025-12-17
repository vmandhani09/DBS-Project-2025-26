<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 
include '../header.php'; 

$request_id = intval($_GET["request_id"] ?? 0);
$request = null;

// If coming from an approved request
if ($request_id) {
    $stmt = $conn->prepare("SELECT r.*, p.name as patient_name FROM blood_requests r LEFT JOIN patients p ON r.patient_id = p.patient_id WHERE r.request_id = ? AND r.status = 'Approved'");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $request = $stmt->get_result()->fetch_assoc();
}

$patients = $conn->query("SELECT patient_id, name, blood_group FROM patients ORDER BY name");
$groups = $conn->query("SELECT s.group_code, s.units_available FROM blood_stock s ORDER BY s.group_code");
$hospitals = $conn->query("SELECT hospital_id, hospital_name FROM hospitals WHERE status = 'Active' ORDER BY hospital_name");

$error = isset($_GET["error"]) ? $_GET["error"] : "";
?>
<style>
  .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
  .form-group { margin-bottom: 0; }
  .error-message { display: none; color: #dc3545; font-size: 0.85rem; margin-top: 4px; }
  .error-message.show { display: block; }
  .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px; }
  .alert-danger { background: #fee; color: #dc3545; border: 1px solid #fcc; }
  .alert-info { background: #e3f2fd; color: #1976d2; border: 1px solid #bbdefb; }
  .request-info { background: #e8f5e9; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #c8e6c9; }
  .request-info h6 { margin: 0 0 8px 0; color: #28a745; }
  .stock-hint { font-size: 0.8rem; color: #888; margin-top: 4px; }
  @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }
</style>
<div class="card" style="max-width: none;">
  <h5 style="color: #333; margin-bottom: 24px; font-weight: 700;"><i class="bi bi-box-seam me-2"></i>Issue Blood</h5>
  
  <?php if($error): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  
  <?php if($request): ?>
    <div class="request-info">
      <h6><i class="bi bi-clipboard-check me-2"></i>Fulfilling Approved Request #<?= $request_id ?></h6>
      <div>Patient: <strong><?= htmlspecialchars($request["patient_name"]) ?></strong> | 
           Blood Group: <strong><?= $request["blood_group"] ?></strong> | 
           Units: <strong><?= $request["units_required"] ?></strong> |
           Priority: <strong><?= $request["priority"] ?></strong></div>
    </div>
  <?php endif; ?>
  
  <div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    Issuing blood will automatically decrease stock. Ensure proper verification before proceeding.
  </div>
  
  <form method="POST" action="issue_blood_action.php" class="issue-form" data-validate-form>
    <?php if($request_id): ?>
      <input type="hidden" name="request_id" value="<?= $request_id ?>">
    <?php endif; ?>
    
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Patient *</label>
        <select name="patient_id" class="form-control" data-validate="required" required <?= $request ? "disabled" : "" ?>>
          <option value="">-- Select Patient --</option>
          <?php while($p = $patients->fetch_assoc()): ?>
            <option value="<?= $p["patient_id"] ?>" <?= ($request && $request["patient_id"]==$p["patient_id"]) ? "selected" : "" ?>><?= htmlspecialchars($p["name"]) ?> (<?= $p["blood_group"] ?>)</option>
          <?php endwhile; ?>
        </select>
        <?php if($request): ?>
          <input type="hidden" name="patient_id" value="<?= $request["patient_id"] ?>">
        <?php endif; ?>
        <div class="error-message"></div>
      </div>
      <div class="form-group">
        <label class="form-label">Blood Group *</label>
        <select name="blood_group" id="blood_group" class="form-control" data-validate="required" required <?= $request ? "disabled" : "" ?>>
          <option value="">-- Select Blood Group --</option>
          <?php while($g = $groups->fetch_assoc()): ?>
            <option value="<?= $g["group_code"] ?>" data-stock="<?= $g["units_available"] ?>" <?= ($request && $request["blood_group"]==$g["group_code"]) ? "selected" : "" ?>><?= $g["group_code"] ?> (<?= $g["units_available"] ?> available)</option>
          <?php endwhile; ?>
        </select>
        <?php if($request): ?>
          <input type="hidden" name="blood_group" value="<?= $request["blood_group"] ?>">
        <?php endif; ?>
        <div class="stock-hint" id="stockHint"></div>
        <div class="error-message"></div>
      </div>
      <div class="form-group">
        <label class="form-label">Units *</label>
        <input type="number" name="units_issued" class="form-control" data-validate="required,numeric" value="<?= $request ? $request["units_required"] : 1 ?>" min="1" max="10" placeholder="Number of units" required>
        <div class="error-message"></div>
      </div>
      <div class="form-group">
        <label class="form-label">Hospital (Optional)</label>
        <select name="hospital_id" class="form-control">
          <option value="">-- Select Hospital --</option>
          <?php while($h = $hospitals->fetch_assoc()): ?>
            <option value="<?= $h["hospital_id"] ?>" <?= ($request && isset($request["hospital_id"]) && $request["hospital_id"]==$h["hospital_id"]) ? "selected" : "" ?>><?= htmlspecialchars($h["hospital_name"]) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Issued To (Name) *</label>
        <input name="issued_to" class="form-control" data-validate="required,name" value="<?= $request ? htmlspecialchars($request["patient_name"]) : "" ?>" placeholder="Recipient name" required>
        <div class="error-message"></div>
      </div>
      <div class="form-group">
        <label class="form-label">Issue Date *</label>
        <input type="date" name="issue_date" class="form-control" value="<?= date("Y-m-d") ?>" max="<?= date("Y-m-d") ?>" required>
        <div class="error-message"></div>
      </div>
    </div>
    
    <div style="margin-top: 1.5rem;">
      <label class="form-label">Notes</label>
      <textarea name="notes" class="form-control" placeholder="Any additional notes" rows="2"></textarea>
    </div>
    
    <div style="margin-top: 2rem; display: flex; gap: 0.75rem;">
      <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Issue Blood</button>
      <a href="/bbms/index.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>

<script>
document.getElementById("blood_group").addEventListener("change", function() {
  const selected = this.options[this.selectedIndex];
  const stock = selected.getAttribute("data-stock");
  const hint = document.getElementById("stockHint");
  if (stock !== null) {
    hint.textContent = "Available: " + stock + " units";
    hint.style.color = stock < 5 ? "#dc3545" : (stock < 10 ? "#f0ad4e" : "#28a745");
  } else {
    hint.textContent = "";
  }
});
</script>
<script src="/bbms/assets/app.js"></script>
<?php include '../footer.php'; ?>