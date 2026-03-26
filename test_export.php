<?php
require_once 'admin/includes/bootstrap.php';
require_once 'admin/includes/session.php';

// สร้าง session token จริง
start_app_session();
$csrfToken = generate_csrf_token();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Export Test</title>
</head>
<body>
    <h1>Export Data Test</h1>
    
    <h2>Test Export Links</h2>
    <ul>
        <li><a href="admin/api/export/handler.php?type=users&format=csv&_csrf=<?php echo $csrfToken; ?>" target="_blank">Export Users (CSV)</a></li>
        <li><a href="admin/api/export/handler.php?type=users&format=excel&_csrf=<?php echo $csrfToken; ?>" target="_blank">Export Users (Excel)</a></li>
        <li><a href="admin/api/export/handler.php?type=quotations&format=csv&_csrf=<?php echo $csrfToken; ?>" target="_blank">Export Quotations (CSV)</a></li>
        <li><a href="admin/api/export/handler.php?type=transport&format=csv&_csrf=<?php echo $csrfToken; ?>" target="_blank">Export Transport (CSV)</a></li>
        <li><a href="admin/api/export/handler.php?type=activity&format=csv&_csrf=<?php echo $csrfToken; ?>" target="_blank">Export Activity (CSV)</a></li>
        <li><a href="admin/api/export/handler.php?type=contacts&format=csv&_csrf=<?php echo $csrfToken; ?>" target="_blank">Export Contacts (CSV)</a></li>
        <li><a href="admin/api/export/handler.php?type=reports&format=csv&_csrf=<?php echo $csrfToken; ?>" target="_blank">Export Reports (CSV)</a></li>
    </ul>
    
    <h2>Dashboard Export Section</h2>
    <a href="admin/dashboard.php?section=export_data" target="_blank">Go to Export Section in Dashboard</a>
</body>
</html>
