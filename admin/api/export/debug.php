<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Debug Export Handler<br>";

try {
    require_once __DIR__ . '/../../includes/bootstrap.php';
    echo "✓ Bootstrap loaded<br>";
    
    require_once __DIR__ . '/../../includes/admin.php';
    echo "✓ Admin loaded<br>";
    
    require_once __DIR__ . '/../includes/activity.php';
    echo "✓ Activity loaded<br>";
    
    $pdo = Database::connection();
    echo "✓ Database connected<br>";
    
    // Test basic export without logging first
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="debug_export.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Name', 'Email']);
    fputcsv($output, [1, 'Test User', 'test@example.com']);
    fclose($output);
    
    echo "✓ Export completed<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "❌ File: " . $e->getFile() . "<br>";
    echo "❌ Line: " . $e->getLine() . "<br>";
    
    // Log to error file
    error_log("Export Debug Error: " . $e->getMessage());
}
?>
