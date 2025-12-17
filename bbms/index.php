<?php
require_once 'db.php';
include 'auth.php'; require_login();
include 'header.php';

// Get dashboard statistics
$total_donors = $conn->query("SELECT COUNT(*) as cnt FROM donors WHERE status = 'Active'")->fetch_assoc()['cnt'];
$total_patients = $conn->query("SELECT COUNT(*) as cnt FROM patients")->fetch_assoc()['cnt'];
$stock_res = $conn->query("SELECT SUM(units_available) AS total_units FROM blood_stock")->fetch_assoc();
$total_units = $stock_res['total_units'] ?? 0;
$pending_requests = $conn->query("SELECT COUNT(*) as cnt FROM blood_requests WHERE status = 'Pending'")->fetch_assoc()['cnt'];

// Get low stock alerts
$low_stock = $conn->query("SELECT group_code, units_available, minimum_threshold FROM blood_stock WHERE units_available <= minimum_threshold ORDER BY units_available ASC");

// Get recent blood issues  
$issues = $conn->query("SELECT bi.issue_id, bi.blood_group, bi.units_issued, bi.issue_date, bi.issued_to,
    p.name as patient_name
    FROM blood_issue bi 
    LEFT JOIN patients p ON bi.patient_id = p.patient_id 
    ORDER BY bi.issue_date DESC LIMIT 8");

// Get unread notifications
$notifications = $conn->query("SELECT * FROM notification_logs WHERE is_read = FALSE ORDER BY created_at DESC LIMIT 5");
$unread_count = $conn->query("SELECT COUNT(*) as cnt FROM notification_logs WHERE is_read = FALSE")->fetch_assoc()['cnt'];
?>
<style>
  .kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
  }
  .kpi-card {
    background: var(--bg-white);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }
  .kpi-card:hover {
    box-shadow: 0 4px 12px rgba(212,165,165,0.15);
    transform: translateY(-4px);
  }
  .kpi-icon {
    font-size: 2.5rem;
    margin-bottom: 12px;
    display: flex;
    justify-content: center;
  }
  .kpi-icon.donors { color: #ff6b9d; }
  .kpi-icon.patients { color: #d4a5a5; }
  .kpi-icon.requests { color: #f0ad4e; }
  .kpi-value {
    font-size: 2.2rem;
    font-weight: 700;
    color: var(--text-dark);
    margin: 12px 0;
  }
  .kpi-label {
    color: var(--text-muted);
    font-size: 0.9rem;
    font-weight: 500;
  }
  .dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
  }
  @media (max-width: 1200px) {
    .dashboard-grid {
      grid-template-columns: 1fr;
    }
  }
  .card h5 {
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  .badge-low {
    background: #fff5f5;
    color: #dc3545;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
  }
  .alert-success-box {
    background: #f0fff4;
    color: #28a745;
    padding: 12px;
    border-radius: 8px;
    border-left: 4px solid #28a745;
  }
  .alert-warning-box {
    background: #fff8e6;
    color: #f0ad4e;
    padding: 12px;
    border-radius: 8px;
    border-left: 4px solid #f0ad4e;
    margin-bottom: 1rem;
  }
  .stock-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid var(--border-color);
  }
  .stock-item:last-child {
    border-bottom: none;
  }
  .stock-badge {
    background: #ffe0e6;
    color: #dc3545;
    padding: 4px 10px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.85rem;
  }
  .action-buttons {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }
  .action-buttons a {
    padding: 11px 16px;
    text-align: center;
    border-radius: 8px;
    transition: all 0.2s ease;
    text-decoration: none;
    font-weight: 500;
  }
  .badge-pending {
    background: #fff8e6;
    color: #f0ad4e;
    padding: 4px 10px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.8rem;
  }
  .notification-item {
    padding: 10px 12px;
    border-radius: 8px;
    margin-bottom: 8px;
    border-left: 4px solid #17a2b8;
    background: #f8f9fa;
  }
  .notification-item.Critical { border-left-color: #dc3545; background: #fff5f5; }
  .notification-item.Warning { border-left-color: #f0ad4e; background: #fff8e6; }
</style>

<?php if(isset($_GET['issued'])): ?>
<div class="alert alert-success mb-4">
  <i class="bi bi-check-circle me-2"></i>Blood issued successfully!
</div>
<?php endif; ?>

<div class="kpi-grid">
  <div class="kpi-card">
    <div class="kpi-icon donors"><i class="bi bi-people-fill"></i></div>
    <div class="kpi-value"><?= $total_donors ?></div>
    <div class="kpi-label">Active Donors</div>
  </div>
  <div class="kpi-card">
    <div class="kpi-icon patients"><i class="bi bi-hospital"></i></div>
    <div class="kpi-value"><?= $total_patients ?></div>
    <div class="kpi-label">Total Patients</div>
  </div>
  <div class="kpi-card">
    <div class="kpi-icon stock"><i class="bi bi-droplet-fill"></i></div>
    <div class="kpi-value"><?= $total_units ?></div>
    <div class="kpi-label">Blood Units</div>
  </div>
  <div class="kpi-card">
    <div class="kpi-icon requests"><i class="bi bi-clipboard-check"></i></div>
    <div class="kpi-value"><?= $pending_requests ?></div>
    <div class="kpi-label">Pending Requests</div>
  </div>
</div>
<div class="dashboard-grid">
  <div class="card">
    <h5><i class="bi bi-clock-history"></i> Recent Issues</h5>
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Recipient</th>
            <th>Group</th>
            <th>Units</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if($issues && $issues->num_rows > 0): ?>
            <?php while($r=$issues->fetch_assoc()): ?>
              <tr>
                <td><?= $r['issue_id'] ?></td>
                <td><?= htmlspecialchars($r['patient_name'] ?: $r['issued_to']) ?></td>
                <td><strong><?= $r['blood_group'] ?></strong></td>
                <td><?= $r['units_issued'] ?></td>
                <td><?= date('M d, Y', strtotime($r['issue_date'])) ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="5" class="text-center py-3">No issues recorded yet</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card">
    <h5><i class="bi bi-droplet-half"></i> Stock Status</h5>
    <?php if($low_stock->num_rows==0): ?>
      <div class="alert-success-box"><i class="bi bi-check-circle me-2"></i>All blood groups are in healthy stock</div>
    <?php else: ?>
      <div class="alert-warning-box"><i class="bi bi-exclamation-triangle me-2"></i><strong>Low Stock Alert</strong></div>
      <?php while($l=$low_stock->fetch_assoc()): ?>
        <div class="stock-item">
          <span><strong><?=$l['group_code']?></strong></span>
          <span class="stock-badge"><?=$l['units_available']?> units</span>
        </div>
      <?php endwhile; ?>
    <?php endif; ?>
    <hr style="margin: 1.5rem 0;">
    <div class="action-buttons">
      <a class="btn btn-ghost" href="/bbms/donors/add_donor.php"><i class="bi bi-person-plus me-2"></i>Add Donor</a>
      <a class="btn btn-ghost" href="/bbms/patients/add_patient.php"><i class="bi bi-person-badge me-2"></i>Add Patient</a>
      <a class="btn btn-brand" href="/bbms/requests/add_request.php"><i class="bi bi-clipboard-plus me-2"></i>Blood Request</a>
    </div>
  </div>
</div>

<?php if($notifications && $notifications->num_rows > 0): ?>
<div class="card">
  <h5><i class="bi bi-bell"></i> Notifications <span class="badge-pending"><?=$unread_count?> new</span></h5>
  <?php while($n = $notifications->fetch_assoc()): ?>
    <div class="notification-item <?= $n['severity'] ?>">
      <strong><?= htmlspecialchars($n['title']) ?></strong>
      <div class="small text-muted"><?= date('M d, H:i', strtotime($n['created_at'])) ?></div>
    </div>
  <?php endwhile; ?>
</div>
<?php endif; ?>
<?php include 'footer.php'; ?>