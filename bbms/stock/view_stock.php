<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 
include '../header.php'; 

$res = $conn->query("SELECT s.*, g.description FROM blood_stock s JOIN blood_groups g ON s.group_code = g.group_code ORDER BY s.group_code");

// Get statistics
$total_units = $conn->query("SELECT SUM(units_available) as total FROM blood_stock")->fetch_assoc()["total"] ?? 0;
$low_stock_count = $conn->query("SELECT COUNT(*) as cnt FROM blood_stock WHERE units_available < 10")->fetch_assoc()["cnt"];
?>
<style>
  .stock-summary { display: flex; gap: 1.5rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
  .stock-summary-card { background: white; padding: 1rem 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
  .stock-summary-card h3 { font-size: 1.8rem; font-weight: 700; margin: 0; }
  .stock-summary-card p { margin: 0; color: #888; font-size: 0.85rem; }
  .stock-level { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
  .stock-critical { background: #ffebee; color: #dc3545; }
  .stock-low { background: #fff8e6; color: #f0ad4e; }
  .stock-good { background: #e8f5e9; color: #28a745; }
  .progress-bar-stock { height: 8px; border-radius: 4px; background: #e9ecef; overflow: hidden; width: 100px; }
  .progress-bar-stock .fill { height: 100%; border-radius: 4px; }
  .fill-critical { background: #dc3545; }
  .fill-low { background: #f0ad4e; }
  .fill-good { background: #28a745; }
</style>

<div class="stock-summary">
  <div class="stock-summary-card">
    <h3 style="color: var(--secondary-color);"><?= $total_units ?></h3>
    <p>Total Units Available</p>
  </div>
  <div class="stock-summary-card">
    <h3 style="color: <?= $low_stock_count > 0 ? "#dc3545" : "#28a745" ?>;"><?= $low_stock_count ?></h3>
    <p>Low Stock Groups</p>
  </div>
</div>

<div class="card table-modern">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0"><i class="bi bi-droplet-half me-2"></i>Blood Stock</h5>
    <a href="/bbms/donations/add_donation.php" class="btn btn-sm btn-brand"><i class="bi bi-plus-circle me-1"></i>Add via Donation</a>
  </div>
  
  <?php if(isset($_GET["updated"])): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i>Stock updated successfully!</div>
  <?php endif; ?>
  
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Blood Group</th>
          <th>Description</th>
          <th>Units Available</th>
          <th>Level</th>
          <th>Last Updated</th>
          <th>Quick Adjust</th>
        </tr>
      </thead>
      <tbody>
        <?php while($r=$res->fetch_assoc()): 
          $units = $r["units_available"];
          $level_class = $units < 5 ? "critical" : ($units < 10 ? "low" : "good");
          $level_text = $units < 5 ? "Critical" : ($units < 10 ? "Low" : "Good");
          $fill_percent = min(100, ($units / 50) * 100);
        ?>
        <tr>
          <td><strong style="font-size: 1.1rem; color: var(--secondary-color);"><?= $r["group_code"] ?></strong></td>
          <td><?= htmlspecialchars($r["description"]) ?></td>
          <td>
            <strong><?= $units ?></strong> units
            <div class="progress-bar-stock mt-1">
              <div class="fill fill-<?= $level_class ?>" style="width: <?= $fill_percent ?>%"></div>
            </div>
          </td>
          <td><span class="stock-level stock-<?= $level_class ?>"><?= $level_text ?></span></td>
          <td><?= date("M d, Y H:i", strtotime($r["last_updated"])) ?></td>
          <td>
            <form method="POST" action="edit_stock_action.php" class="d-flex gap-2 align-items-center">
              <input type="hidden" name="group_code" value="<?= $r["group_code"] ?>">
              <input type="number" name="units" class="form-control form-control-sm" style="width:80px" value="<?= $units ?>" min="0" required>
              <button class="btn btn-sm btn-ghost" title="Update"><i class="bi bi-check-lg"></i></button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  
  <div class="mt-3 text-muted small">
    <i class="bi bi-info-circle me-1"></i>
    Stock is automatically updated when donations are recorded or blood is issued. Manual adjustments should be used for corrections only.
  </div>
</div>
<?php include '../footer.php'; ?>