<?php
require_once 'admin/includes/bootstrap.php';
require_once 'admin/includes/session.php';

// Include export functions without executing main code
include_once 'admin/api/export/handler.php';
$csrfToken = bin2hex(random_bytes(16)); // Simple token for test

// Mock admin user
$_SESSION['admin_user'] = ['id' => 1, 'role' => 'super_admin'];

// ทดสอบฟังก์ชันโดยตรง
try {
    $pdo = Database::connection();
    
    // ทดสอง get_export_users
    echo "Testing get_export_users...\n";
    $users = get_export_users($pdo);
    echo "Users count: " . count($users) . "\n";
    if (!empty($users)) {
        echo "Sample user: " . print_r($users[0], true) . "\n";
    }
    
    // ทดสอง get_export_quotations
    echo "\nTesting get_export_quotations...\n";
    $quotations = get_export_quotations($pdo);
    echo "Quotations count: " . count($quotations) . "\n";
    if (!empty($quotations)) {
        echo "Sample quotation: " . print_r($quotations[0], true) . "\n";
    }
    
    // ทดสอง get_export_activity
    echo "\nTesting get_export_activity...\n";
    $activity = get_export_activity($pdo);
    echo "Activity count: " . count($activity) . "\n";
    if (!empty($activity)) {
        echo "Sample activity: " . print_r($activity[0], true) . "\n";
    }
    
    echo "\n✅ All export functions working!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
