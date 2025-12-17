<?php 
require_once '../db.php'; 
include '../auth.php'; 
require_login(); 

if ($_SERVER["REQUEST_METHOD"] === "POST") { 
    $group = $_POST["group_code"] ?? "";
    $units = intval($_POST["units"] ?? 0);
    
    if (empty($group)) {
        header("Location: view_stock.php?error=Invalid blood group");
        exit;
    }
    
    if ($units < 0) {
        header("Location: view_stock.php?error=Units cannot be negative");
        exit;
    }
    
    // Get old value for logging
    $old = $conn->query("SELECT units_available FROM blood_stock WHERE group_code = '" . $conn->real_escape_string($group) . "'")->fetch_assoc();
    $old_units = $old ? $old["units_available"] : 0;
    
    $stmt = $conn->prepare("UPDATE blood_stock SET units_available = ?, last_updated = NOW() WHERE group_code = ?");
    $stmt->bind_param("is", $units, $group);
    
    if ($stmt->execute()) {
        // Log the activity
        if (function_exists("log_activity")) {
            $diff = $units - $old_units;
            $action = $diff >= 0 ? "increased" : "decreased";
            log_activity($conn, "stock_update", "Stock $action for $group: $old_units -> $units units", "blood_stock", null);
        }
        
        header("Location: view_stock.php?updated=1");
    } else {
        header("Location: view_stock.php?error=" . urlencode("Error: " . $stmt->error));
    }
} else {
    header("Location: view_stock.php");
}
?>