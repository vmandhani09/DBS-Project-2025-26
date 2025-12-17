# BBMS Database Quick Reference

## Installation

### Option 1: Single File Import (Recommended)
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click "Import" tab
3. Select `database/bbms_complete.sql`
4. Click "Go"

### Option 2: Individual Files
Run in MySQL/phpMyAdmin in this order:
```sql
SOURCE database/schema.sql;
SOURCE database/views.sql;
SOURCE database/triggers.sql;
SOURCE database/stored_procedures.sql;
SOURCE database/sample_data.sql;
```

## Default Credentials
- **Username:** admin
- **Password:** admin123

---

## Database Structure

### Tables (12)
| Table | Description |
|-------|-------------|
| `blood_groups` | Blood type master data |
| `admins` | System administrators |
| `donors` | Registered blood donors |
| `hospitals` | Partner hospitals |
| `patients` | Patients needing blood |
| `blood_stock` | Current inventory levels |
| `donations` | Donation records |
| `blood_requests` | Blood requests |
| `blood_issues` | Issue/distribution records |
| `notification_logs` | System alerts |
| `activity_logs` | Audit trail |
| `donor_eligibility_logs` | Eligibility history |

### Views (7)
| View | Purpose |
|------|---------|
| `view_available_blood` | Current stock with status |
| `view_pending_requests` | Pending requests by urgency |
| `view_donation_summary` | Donations with donor info |
| `view_blood_group_statistics` | Stats per blood group |
| `view_donor_directory` | Active donor listing |
| `view_recent_activity` | Recent system activity |
| `view_unread_notifications` | Unread alerts |

### Triggers (5)
| Trigger | Event | Action |
|---------|-------|--------|
| `trg_after_donation_available` | Donation status → Available | Increases stock |
| `trg_after_donation_insert` | New donation | Updates donor stats |
| `trg_low_stock_alert` | Stock below threshold | Creates notification |
| `trg_after_request_approved` | Request approved | Reserves stock |
| `trg_urgent_request_notification` | Urgent request | Creates alert |

### Stored Procedures (5)
| Procedure | Parameters | Purpose |
|-----------|------------|---------|
| `sp_RegisterDonor` | Donor data, OUT: status | Register with validation |
| `sp_ApproveBloodRequest` | request_id, units, admin_id | Approve request |
| `sp_GenerateStockSummary` | None | Stock report |
| `sp_GetDashboardStats` | None | Dashboard data |
| `sp_SearchDonors` | blood_group, city, eligible_only | Search donors |

---

## PHP Usage Examples

### Using Views
```php
<?php
// Get available blood stock
$result = $conn->query("SELECT * FROM view_available_blood");
while ($row = $result->fetch_assoc()) {
    echo $row['group_code'] . ': ' . $row['units_available'] . ' units<br>';
}
?>
```

### Calling Stored Procedures
```php
<?php
// Get dashboard stats
$result = $conn->query("CALL sp_GetDashboardStats()");
$stats = $result->fetch_assoc();
echo "Total Donors: " . $stats['total_donors'];
$result->close();
$conn->next_result(); // Clear result set
?>
```

### Using Database Class
```php
<?php
require_once 'includes/Database.php';
$db = Database::getInstance();

// Register donor
$result = $db->registerDonor($donorData);
if ($result['status'] === 'SUCCESS') {
    echo "Donor ID: " . $result['donor_id'];
}

// Get stock
$stock = $db->getAvailableBlood();

// Search donors
$donors = $db->searchDonors('O+', 'Mumbai', true);
?>
```

### Transactions
```php
<?php
$db = Database::getInstance();

try {
    $db->beginTransaction();
    
    // Multiple operations...
    
    $db->commit();
} catch (Exception $e) {
    $db->rollback();
    echo "Error: " . $e->getMessage();
}
?>
```

---

## File Structure
```
bbms/
├── database/
│   ├── bbms_complete.sql      # Single file - ALL in one
│   ├── schema.sql             # Tables only
│   ├── views.sql              # Views only
│   ├── triggers.sql           # Triggers only
│   ├── stored_procedures.sql  # Procedures only
│   ├── sample_data.sql        # Sample data only
│   └── install.sql            # Master installer
├── includes/
│   ├── Database.php           # Advanced DB class
│   └── helpers.php            # Example functions
├── docs/
│   └── DOCUMENTATION.md       # Full documentation
├── db.php                     # Connection file
└── ...
```

---

## Normalization Summary

### 1NF ✓
- All attributes atomic
- No repeating groups
- Primary keys defined

### 2NF ✓
- All in 1NF
- No partial dependencies
- Non-key attributes depend on entire key

### 3NF ✓
- All in 2NF
- No transitive dependencies
- Non-key attributes depend only on key

---

## Key Features

✅ **Automatic Stock Management**
- Stock increases when donation marked 'Available'
- Stock decreases when blood issued
- Stock reserved when request approved

✅ **Automatic Notifications**
- Low stock alerts (below threshold)
- Urgent request alerts
- Expiry warnings

✅ **Donor Eligibility**
- Auto-set ineligible after donation
- 90-day waiting period tracked
- Eligibility history logged

✅ **Complete Audit Trail**
- All actions logged
- Before/after values stored
- IP address tracked

✅ **Transaction Safety**
- COMMIT/ROLLBACK in procedures
- Error handling
- Data integrity guaranteed
