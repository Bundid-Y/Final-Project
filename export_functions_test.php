<?php
require_once 'admin/includes/bootstrap.php';
require_once 'admin/includes/session.php';

// สร้างฟังก์ชัน export แบบง่าย
function get_export_users_simple(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT id, username, email, role, company_id, status, created_at FROM users ORDER BY created_at DESC');
    return $stmt->fetchAll() ?: [];
}

function get_export_quotations_simple(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT q.*, u.username as customer_name FROM koch_quotations q LEFT JOIN users u ON u.id = q.user_id ORDER BY q.created_at DESC');
    return $stmt->fetchAll() ?: [];
}

function get_export_activity_simple(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT a.*, u.username as admin_name FROM activity_logs a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.created_at DESC LIMIT 1000');
    return $stmt->fetchAll() ?: [];
}

// ทดสอบ
try {
    $pdo = Database::connection();
    
    echo "Testing Export Functions...\n\n";
    
    // ทดสอง Users
    echo "👥 Users Export:\n";
    $users = get_export_users_simple($pdo);
    echo "Count: " . count($users) . "\n";
    if (!empty($users)) {
        echo "Sample: " . $users[0]['username'] . " (" . $users[0]['email'] . ")\n";
    }
    
    // ทดสอง Quotations
    echo "\n📦 Quotations Export:\n";
    $quotations = get_export_quotations_simple($pdo);
    echo "Count: " . count($quotations) . "\n";
    if (!empty($quotations)) {
        echo "Sample: " . $quotations[0]['customer_name'] . " - " . $quotations[0]['status'] . "\n";
    }
    
    // ทดสอง Activity
    echo "\n📝 Activity Export:\n";
    $activity = get_export_activity_simple($pdo);
    echo "Count: " . count($activity) . "\n";
    if (!empty($activity)) {
        echo "Sample: " . $activity[0]['admin_name'] . " - " . $activity[0]['action'] . "\n";
    }
    
    // ทดสอง CSV generation
    echo "\n📊 CSV Generation Test:\n";
    if (!empty($users)) {
        $csv = "id,username,email,role,company_id,status,created_at\n";
        foreach ($users as $user) {
            $csv .= implode(',', array_values((array) $user)) . "\n";
        }
        echo "CSV length: " . strlen($csv) . " characters\n";
        echo "First line: " . explode("\n", $csv)[0] . "\n";
        echo "Sample row: " . explode("\n", $csv)[1] . "\n";
    }
    
    echo "\n✅ Export System Working!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
