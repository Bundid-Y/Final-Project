<?php
declare(strict_types=1);

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output

try {
    require_once __DIR__ . '/../../includes/bootstrap.php';
    require_once __DIR__ . '/../../includes/admin.php';
    
    $pdo = Database::connection();
    $user = require_admin_user();
    
    if (!verify_csrf_token($_GET['_csrf'] ?? null)) {
        die('Security token mismatch.');
    }
    
    $type = sanitize_text((string) ($_GET['type'] ?? ''));
    $format = sanitize_text((string) ($_GET['format'] ?? ''));
    
    // Export filters - specific to each type
    $filters = [
        'date_from' => sanitize_text((string) ($_GET['date_from'] ?? '')),
        'date_to' => sanitize_text((string) ($_GET['date_to'] ?? '')),
        'company_id' => sanitize_text((string) ($_GET['company_id'] ?? '')),
        'user' => sanitize_text((string) ($_GET['filter_user'] ?? '')),
        'status' => sanitize_text((string) ($_GET['filter_status'] ?? '')),
        'role' => sanitize_text((string) ($_GET['filter_role'] ?? '')),
        'product_type' => sanitize_text((string) ($_GET['filter_product_type'] ?? '')),
        'action' => sanitize_text((string) ($_GET['filter_action'] ?? '')),
        'company_mode' => sanitize_text((string) ($_GET['company_mode'] ?? 'all')),
    ];
    
    $validTypes = ['users', 'quotations', 'activity', 'reports'];
    $validFormats = ['csv', 'excel', 'pdf', 'word'];
    
    if (!in_array($type, $validTypes, true) || !in_array($format, $validFormats, true)) {
        die('Invalid type or format.');
    }
    
    // Get data based on type
    $data = [
        'users' => get_export_users($pdo, $filters),
        'quotations' => get_export_quotations($pdo, $filters),
        'activity' => get_export_activity($pdo, $filters),
        'reports' => get_export_reports($pdo, $filters)
    ];
    
    $data = $data[$type] ?? [];
    
    if (empty($data)) {
        die('No data found.');
    }
    
    // Try to log activity (but don't fail if it doesn't work)
    try {
        require_once __DIR__ . '/../../../admin/includes/activity.php';
        
        $exportTypeLabels = [
            'users' => 'Users',
            'quotations' => 'KOCH Quotations',
            'activity' => 'Activity Logs',
            'reports' => 'Reports'
        ];
        $exportTypeLabel = $exportTypeLabels[$type] ?? ucfirst($type);
        $formatLabel = strtoupper($format);
        $recordCount = count($data);
        
        log_activity($pdo, $user['id'], 'DATA_EXPORTED', 'exports', 0, [], [
            'export_type' => $exportTypeLabel,
            'format' => $formatLabel,
            'record_count' => $recordCount,
            'filters_applied' => !empty(array_filter($filters, function($v) { return !empty($v); })),
            'company_mode' => $user['company_code'] ?? 'all'
        ], $user['company_id'] ?? null);
        
        // Try to create notifications
        $exportTypeLabelTh = match($type) {
            'users' => 'ผู้ใช้งาน',
            'quotations' => 'ใบเสนอราคา KOCH',
            'activity' => 'บันทึกกิจกรรม',
            'reports' => 'รายงาน',
            default => $exportTypeLabel
        };

        $notificationTitle = "ส่งออกข้อมูลแล้ว: {$exportTypeLabelTh}";
        $notificationMessage = "ทำการส่งออกข้อมูลจำนวน {$recordCount} รายการจากส่วน {$exportTypeLabelTh} ในรูปแบบ {$formatLabel} เป็นที่เรียบร้อยครับ" . 
                               (!empty(array_filter($filters, function($v) { return !empty($v); })) ? " (มีการใช้ตัวกรอง)" : "");
        
        $adminStmt = $pdo->prepare("SELECT id FROM users WHERE role IN ('super_admin', 'admin') AND status = 'active' AND id != :current_user");
        $adminStmt->execute([':current_user' => $user['id']]);
        $admins = $adminStmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($admins as $adminId) {
            create_notification($pdo, (int) $adminId, $notificationTitle, $notificationMessage, 'info', 'exports', 0, 'normal', $user['company_id'] ?? null);
        }
    } catch (Exception $logError) {
        // Continue with export even if logging fails
        error_log("Export logging failed: " . $logError->getMessage());
    }
    
    // Export the data
    export_data($data, $type, $format);
    
} catch (Throwable $e) {
    // Log the error
    error_log("Export failed: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    
    // Try to log failure
    try {
        require_once __DIR__ . '/../../../admin/includes/activity.php';
        log_activity($pdo, $user['id'] ?? 1, 'DATA_EXPORT_FAILED', 'exports', 0, [], [
            'export_type' => ucfirst($type ?? 'unknown'),
            'format' => strtoupper($format ?? 'unknown'),
            'error' => $e->getMessage(),
            'company_mode' => $user['company_code'] ?? 'all'
        ], $user['company_id'] ?? null);
    } catch (Exception $logError) {
        error_log("Failed to log export failure: " . $logError->getMessage());
    }
    
    die('Export failed: ' . $e->getMessage());
}

function get_export_users(PDO $pdo, array $filters = []): array
{
    $sql = 'SELECT id, username, email, role, company_id, status, created_at FROM users WHERE 1=1';
    $params = [];
    
    if (!empty($filters['user'])) {
        $sql .= ' AND username LIKE :user';
        $params[':user'] = '%' . $filters['user'] . '%';
    }
    
    if (!empty($filters['role'])) {
        $sql .= ' AND role = :role';
        $params[':role'] = $filters['role'];
    }
    
    if (!empty($filters['status'])) {
        $sql .= ' AND status = :status';
        $params[':status'] = $filters['status'];
    }
    
    if (!empty($filters['company_id'])) {
        $sql .= ' AND company_id = :company_id';
        $params[':company_id'] = $filters['company_id'];
    }
    
    if (!empty($filters['date_from'])) {
        $sql .= ' AND created_at >= :date_from';
        $params[':date_from'] = $filters['date_from'] . ' 00:00:00';
    }
    
    if (!empty($filters['date_to'])) {
        $sql .= ' AND created_at <= :date_to';
        $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
    }
    
    $sql .= ' ORDER BY created_at DESC';
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll() ?: [];
}

function get_export_quotations(PDO $pdo, array $filters = []): array
{
    // Select table based on company mode
    $companyMode = $filters['company_mode'] ?? 'all';
    if ($companyMode === 'tnb') {
        $tables = ['tnb_quotations'];
    } elseif ($companyMode === 'koch') {
        $tables = ['koch_quotations'];
    } else {
        $tables = ['koch_quotations', 'tnb_quotations'];
    }
    
    $allResults = [];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'koch_tnb_system' AND table_name = '$table'");
            $result = $stmt->fetch();
            if ($result && $result['count'] > 0) {
                $sql = "SELECT q.*, u.username as customer_name FROM $table q LEFT JOIN users u ON u.id = q.user_id WHERE 1=1";
                $params = [];
                
                if (!empty($filters['user'])) {
                    $sql .= ' AND u.username LIKE :user';
                    $params[':user'] = '%' . $filters['user'] . '%';
                }
                
                if (!empty($filters['status'])) {
                    $sql .= ' AND q.status = :status';
                    $params[':status'] = $filters['status'];
                }
                
                if (!empty($filters['product_type'])) {
                    $sql .= ' AND q.product_type = :product_type';
                    $params[':product_type'] = $filters['product_type'];
                }
                
                if (!empty($filters['date_from'])) {
                    $sql .= ' AND q.created_at >= :date_from';
                    $params[':date_from'] = $filters['date_from'] . ' 00:00:00';
                }
                
                if (!empty($filters['date_to'])) {
                    $sql .= ' AND q.created_at <= :date_to';
                    $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
                }
                
                $sql .= ' ORDER BY q.created_at DESC';
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $rows = $stmt->fetchAll() ?: [];
                $allResults = array_merge($allResults, $rows);
            }
        } catch (Exception $e) {
            continue;
        }
    }
    
    return $allResults;
}

function get_export_transport(PDO $pdo, array $filters = []): array
{
    // Try different possible table names for transport
    $tables = ['tnb_quotations', 'transport_requests', 'tnb_transport', 'transport'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'koch_tnb_system' AND table_name = '$table'");
            $result = $stmt->fetch();
            if ($result && $result['count'] > 0) {
                // Table exists, build query with filters
                $sql = "SELECT tr.*, u.username as customer_name FROM $table tr LEFT JOIN users u ON u.id = tr.user_id WHERE 1=1";
                $params = [];
                
                if (!empty($filters['user'])) {
                    $sql .= ' AND u.username LIKE :user';
                    $params[':user'] = '%' . $filters['user'] . '%';
                }
                
                if (!empty($filters['company_id'])) {
                    $sql .= ' AND tr.company_id = :company_id';
                    $params[':company_id'] = $filters['company_id'];
                }
                
                if (!empty($filters['status'])) {
                    $sql .= ' AND tr.status = :status';
                    $params[':status'] = $filters['status'];
                }
                
                if (!empty($filters['service_type'])) {
                    $sql .= ' AND tr.service_type = :service_type';
                    $params[':service_type'] = $filters['service_type'];
                }
                
                if (!empty($filters['date_from'])) {
                    $sql .= ' AND tr.created_at >= :date_from';
                    $params[':date_from'] = $filters['date_from'] . ' 00:00:00';
                }
                
                if (!empty($filters['date_to'])) {
                    $sql .= ' AND tr.created_at <= :date_to';
                    $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
                }
                
                $sql .= ' ORDER BY tr.created_at DESC';
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                return $stmt->fetchAll() ?: [];
            }
        } catch (Exception $e) {
            continue;
        }
    }
    
    // Return empty if no transport table found
    return [];
}

function get_export_activity(PDO $pdo, array $filters = []): array
{
    $sql = 'SELECT a.*, u.username as admin_name FROM activity_logs a LEFT JOIN users u ON u.id = a.user_id WHERE 1=1';
    $params = [];
    
    if (!empty($filters['user'])) {
        $sql .= ' AND u.username LIKE :user';
        $params[':user'] = '%' . $filters['user'] . '%';
    }
    
    if (!empty($filters['action'])) {
        $sql .= ' AND a.action = :action';
        $params[':action'] = $filters['action'];
    }
    
    if (!empty($filters['company_id'])) {
        $sql .= ' AND a.company_id = :company_id';
        $params[':company_id'] = $filters['company_id'];
    }
    
    if (!empty($filters['date_from'])) {
        $sql .= ' AND a.created_at >= :date_from';
        $params[':date_from'] = $filters['date_from'] . ' 00:00:00';
    }
    
    if (!empty($filters['date_to'])) {
        $sql .= ' AND a.created_at <= :date_to';
        $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
    }
    
    $sql .= ' ORDER BY a.created_at DESC LIMIT 1000';
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll() ?: [];
}

function get_export_reports(PDO $pdo, array $filters = []): array
{
    $reports = [];
    
    // Users by company
    try {
        $stmt = $pdo->query('SELECT c.name as company, COUNT(u.id) as user_count FROM companies c LEFT JOIN users u ON u.company_id = c.id GROUP BY c.id');
        $reports['users_by_company'] = $stmt->fetchAll() ?: [];
    } catch (Exception $e) {
        $reports['users_by_company'] = [];
    }
    
    // Quotations by status - check for existing tables
    $quotation_tables = ['koch_quotations', 'quotations', 'koch_quotation', 'quotation_requests'];
    foreach ($quotation_tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'koch_tnb_system' AND table_name = '$table'");
            $result = $stmt->fetch();
            if ($result && $result['count'] > 0) {
                $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM $table GROUP BY status");
                $reports['quotations_by_status'] = $stmt->fetchAll() ?: [];
                break;
            }
        } catch (Exception $e) {
            continue;
        }
    }
    if (!isset($reports['quotations_by_status'])) {
        $reports['quotations_by_status'] = [];
    }
    
    // Transport requests by status - check for existing tables
    $transport_tables = ['tnb_quotations', 'transport_requests', 'tnb_transport', 'transport'];
    foreach ($transport_tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'koch_tnb_system' AND table_name = '$table'");
            $result = $stmt->fetch();
            if ($result && $result['count'] > 0) {
                $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM $table GROUP BY status");
                $reports['transport_by_status'] = $stmt->fetchAll() ?: [];
                break;
            }
        } catch (Exception $e) {
            continue;
        }
    }
    if (!isset($reports['transport_by_status'])) {
        $reports['transport_by_status'] = [];
    }
    
    return $reports;
}

function export_data(array $data, string $type, string $format): void
{
    $filename = $type . '_export_' . date('Y-m-d_H-i-s');
    
    match ($format) {
        'csv' => export_csv($data, $filename),
        'excel' => export_excel($data, $filename),
        'pdf' => export_pdf($data, $filename),
        'word' => export_word($data, $filename),
        default => die('Unsupported format.')
    };
}

function export_csv(array $data, string $filename): void
{
    if (empty($data)) {
        die('No data to export.');
    }
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Header
    fputcsv($output, array_keys((array) $data[0]));
    
    // Data
    foreach ($data as $row) {
        fputcsv($output, (array) $row);
    }
    
    fclose($output);
    exit;
}

function export_excel(array $data, string $filename): void
{
    // Create Excel-compatible CSV file with proper formatting
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    
    $output = fopen('php://output', 'w');
    
    if (!empty($data)) {
        // Add BOM for proper UTF-8 encoding in Excel
        fwrite($output, "\xEF\xBB\xBF");
        
        // Header row
        $headers = [];
        foreach (array_keys((array) $data[0]) as $header) {
            $headers[] = ucwords(str_replace('_', ' ', $header));
        }
        fputcsv($output, $headers);
        
        // Data rows
        foreach ($data as $row) {
            $cleanRow = [];
            foreach ((array) $row as $cell) {
                // Clean data for Excel
                $value = is_null($cell) ? '' : (string) $cell;
                // Remove problematic characters and escape for CSV
                $value = str_replace(["\r", "\n", "\t"], ' ', $value);
                $value = trim($value);
                $cleanRow[] = $value;
            }
            fputcsv($output, $cleanRow);
        }
    }
    
    fclose($output);
    exit;
}

function export_pdf(array $data, string $filename): void
{
    // Export as HTML file that can be opened in browser and printed to PDF
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="' . $filename . '.html"');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    
    $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' . ucfirst($filename) . '</title>';
    $html .= '<style>
        @media print {
            body { margin: 0.5in; }
            .no-print { display: none; }
        }
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.4; }
        h1 { color: #2E74B5; font-size: 18pt; border-bottom: 2px solid #2E74B5; padding-bottom: 10px; margin-bottom: 20px; }
        h2 { color: #2E74B5; font-size: 14pt; margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; page-break-inside: auto; }
        th { background-color: #2E74B5; color: white; font-weight: bold; padding: 8px; text-align: left; border: 1px solid #2E74B5; }
        td { border: 1px solid #ddd; padding: 6px; vertical-align: top; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .meta { color: #666666; font-size: 10pt; margin-bottom: 15px; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        .print-btn { 
            background: #2E74B5; color: white; padding: 10px 20px; border: none; border-radius: 4px; 
            cursor: pointer; font-size: 12pt; margin: 20px 0; text-decoration: none; display: inline-block;
        }
        .print-btn:hover { background: #1E5490; }
        .instructions { 
            background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 15px; 
            margin: 20px 0; font-size: 11pt; color: #495057;
        }
    </style>';
    $html .= '</head><body>';
    
    $html .= '<div class="no-print">';
    $html .= '<button class="print-btn" onclick="window.print()">🖨️ Print to PDF</button>';
    $html .= '<div class="instructions">';
    $html .= '<strong>📋 Instructions:</strong><br>';
    $html .= '1. Click "Print to PDF" button above<br>';
    $html .= '2. In print dialog, choose "Save as PDF"<br>';
    $html .= '3. Adjust settings if needed and click Save<br>';
    $html .= '4. File will be saved as proper PDF format';
    $html .= '</div>';
    $html .= '</div>';
    
    $html .= '<h1>' . ucfirst(str_replace('_', ' ', $filename)) . '</h1>';
    $html .= '<div class="meta">Generated on: ' . date('Y-m-d H:i:s') . ' | Total Records: ' . count($data) . '</div>';
    
    if (!empty($data)) {
        $html .= '<table>';
        
        // Header row
        $html .= '<tr>';
        foreach (array_keys((array) $data[0]) as $header) {
            $html .= '<th>' . htmlspecialchars(ucwords(str_replace('_', ' ', $header))) . '</th>';
        }
        $html .= '</tr>';
        
        // Data rows
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ((array) $row as $cell) {
                $value = is_null($cell) ? '-' : htmlspecialchars((string) $cell);
                $html .= '<td>' . $value . '</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '</table>';
    } else {
        $html .= '<h2>No Data Available</h2>';
        $html .= '<p>There is no data to export for this selection.</p>';
    }
    
    $html .= '<div class="no-print" style="margin-top: 40px; text-align: center; color: #666; font-size: 10pt;">';
    $html .= '<p>This file was generated from KOCH/TNB Admin Dashboard</p>';
    $html .= '</div>';
    
    $html .= '</body></html>';
    
    echo $html;
    exit;
}

function export_word(array $data, string $filename): void
{
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . $filename . '.docx"');
    
    $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' . ucfirst($filename) . '</title>';
    $html .= '<style>
        body { font-family: "Calibri", Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1 { color: #2E74B5; font-size: 18pt; border-bottom: 2px solid #2E74B5; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background-color: #2E74B5; color: white; font-weight: bold; padding: 8px; text-align: left; }
        td { border: 1px solid #D9D9D9; padding: 6px; }
        tr:nth-child(even) { background-color: #F2F2F2; }
        .meta { color: #666666; font-size: 10pt; margin-bottom: 15px; }
    </style>';
    $html .= '</head><body>';
    
    $html .= '<h1>' . ucfirst(str_replace('_', ' ', $filename)) . '</h1>';
    $html .= '<div class="meta">Generated on: ' . date('Y-m-d H:i:s') . '</div>';
    
    if (!empty($data)) {
        $html .= '<table>';
        
        // Header row
        $html .= '<tr>';
        foreach (array_keys((array) $data[0]) as $header) {
            $html .= '<th>' . htmlspecialchars(ucwords(str_replace('_', ' ', $header))) . '</th>';
        }
        $html .= '</tr>';
        
        // Data rows
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ((array) $row as $cell) {
                $value = is_null($cell) ? '-' : htmlspecialchars((string) $cell);
                $html .= '<td>' . $value . '</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '</table>';
    } else {
        $html .= '<p>No data available.</p>';
    }
    
    $html .= '</body></html>';
    
    echo $html;
    exit;
}
