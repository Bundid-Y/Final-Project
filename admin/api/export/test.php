<?php
// Simple test file to debug export issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Test Export Debug<br>";

try {
    require_once __DIR__ . '/../../includes/bootstrap.php';
    echo "Bootstrap loaded<br>";
    
    require_once __DIR__ . '/../../includes/admin.php';
    echo "Admin loaded<br>";
    
    require_once __DIR__ . '/../../../admin/includes/activity.php';
    echo "Activity loaded<br>";
    
    $pdo = Database::connection();
    echo "Database connected<br>";
    
    // Test log_activity function
    log_activity($pdo, 1, 'TEST_EXPORT', 'test', 0, [], ['test' => 'data'], 1);
    echo "Log activity test successful<br>";
    
    // Test create_notification function
    create_notification($pdo, 1, 'Test Notification', 'Test message', 'info', 'test', 1, 'normal', 1);
    echo "Notification test successful<br>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}
?>
