<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'admin/includes/bootstrap.php';
    require_once 'admin/includes/content.php';
    require_once 'admin/includes/crud.php';
    
    $pdo = Database::connection();
    $companyId = get_company_id_by_code($pdo, 'KOCH');
    $dbSliders = get_active_sliders($pdo, $companyId);
    $dbPartners = get_active_partners($pdo, $companyId);
    $dbProducts = get_active_featured_products($pdo);
    
    echo "<h1>KOCH Website Test</h1>";
    echo "<h2>Sliders: " . count($dbSliders) . "</h2>";
    echo "<h2>Partners: " . count($dbPartners) . "</h2>";
    echo "<h2>Featured Products: " . count($dbProducts) . "</h2>";
    
    echo "<h3>Sample Slider:</h3>";
    if (!empty($dbSliders)) {
        echo "<p>" . htmlspecialchars($dbSliders[0]['title']) . "</p>";
    }
    
    echo "<h3>Sample Partner:</h3>";
    if (!empty($dbPartners)) {
        echo "<p>" . htmlspecialchars($dbPartners[0]['name']) . "</p>";
    }
    
    echo "<h3>Sample Product:</h3>";
    if (!empty($dbProducts)) {
        echo "<p>" . htmlspecialchars($dbProducts[0]['name']) . "</p>";
    }
    
    echo "<hr>";
    echo "<a href='koch/main/index.php'>Go to KOCH Index</a> | ";
    echo "<a href='tnb/main/index.php'>Go to TNB Index</a> | ";
    echo "<a href='admin/dashboard.php'>Admin Dashboard</a>";
    
} catch (Exception $e) {
    echo "<h1>Error: " . htmlspecialchars($e->getMessage()) . "</h1>";
    echo "<p>File: " . htmlspecialchars($e->getFile()) . " Line: " . $e->getLine() . "</p>";
}
?>
