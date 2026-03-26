<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Debug Test Starting...\n";

try {
    echo "1. Testing bootstrap...\n";
    require_once 'admin/includes/bootstrap.php';
    echo "✅ Bootstrap loaded\n";
    
    echo "2. Testing database connection...\n";
    $pdo = Database::connection();
    echo "✅ Database connected\n";
    
    echo "3. Testing content functions...\n";
    require_once 'admin/includes/content.php';
    echo "✅ Content functions loaded\n";
    
    echo "3.1. Testing crud functions...\n";
    require_once 'admin/includes/crud.php';
    echo "✅ CRUD functions loaded\n";
    
    echo "4. Testing company functions...\n";
    $kochId = get_company_id_by_code($pdo, 'KOCH');
    echo "✅ KOCH ID: $kochId\n";
    
    echo "5. Testing get_active_sliders...\n";
    $sliders = get_active_sliders($pdo, $kochId);
    echo "✅ Sliders count: " . count($sliders) . "\n";
    
    echo "6. Testing get_active_partners...\n";
    $partners = get_active_partners($pdo, $kochId);
    echo "✅ Partners count: " . count($partners) . "\n";
    
    echo "7. Testing get_active_featured_products...\n";
    $products = get_active_featured_products($pdo);
    echo "✅ Featured products count: " . count($products) . "\n";
    
    echo "\n🎉 All tests passed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
} catch (Error $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
?>
