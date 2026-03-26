<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/admin.php';

$pdo = Database::connection();
$user = require_admin_user();

if (!verify_csrf_token($_GET['_csrf'] ?? null)) {
    die('Security token mismatch.');
}

$type = sanitize_text((string) ($_GET['type'] ?? ''));
$format = sanitize_text((string) ($_GET['format'] ?? ''));

$validTypes = ['users', 'quotations', 'transport', 'activity', 'contacts', 'reports'];
$validFormats = ['csv', 'excel', 'pdf', 'word'];

if (!in_array($type, $validTypes, true) || !in_array($format, $validFormats, true)) {
    die('Invalid type or format.');
}

try {
    $data = match ($type) {
        'users' => get_export_users($pdo),
        'quotations' => get_export_quotations($pdo),
        'transport' => get_export_transport($pdo),
        'activity' => get_export_activity($pdo),
        'contacts' => get_export_contacts($pdo),
        'reports' => get_export_reports($pdo),
        default => []
    };
    
    if (empty($data)) {
        die('No data found.');
    }
    
    export_data($data, $type, $format);
    
} catch (Throwable $e) {
    die('Export failed: ' . $e->getMessage());
}

function get_export_users(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT id, username, email, role, company_id, status, created_at FROM users ORDER BY created_at DESC');
    return $stmt->fetchAll() ?: [];
}

function get_export_quotations(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT q.*, u.username as customer_name FROM koch_quotations q LEFT JOIN users u ON u.id = q.user_id ORDER BY q.created_at DESC');
    return $stmt->fetchAll() ?: [];
}

function get_export_transport(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT tr.*, u.username as customer_name FROM tnb_quotations tr LEFT JOIN users u ON u.id = tr.user_id ORDER BY tr.created_at DESC');
    return $stmt->fetchAll() ?: [];
}

function get_export_activity(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT a.*, u.username as admin_name FROM activity_logs a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.created_at DESC LIMIT 1000');
    return $stmt->fetchAll() ?: [];
}

function get_export_contacts(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM contact_messages ORDER BY created_at DESC');
    return $stmt->fetchAll() ?: [];
}

function get_export_reports(PDO $pdo): array
{
    $reports = [];
    
    // Users by company
    $stmt = $pdo->query('SELECT c.name as company, COUNT(u.id) as user_count FROM companies c LEFT JOIN users u ON u.company_id = c.id GROUP BY c.id');
    $reports['users_by_company'] = $stmt->fetchAll() ?: [];
    
    // Quotations by status
    $stmt = $pdo->query('SELECT status, COUNT(*) as count FROM quotations GROUP BY status');
    $reports['quotations_by_status'] = $stmt->fetchAll() ?: [];
    
    // Transport requests by status
    $stmt = $pdo->query('SELECT status, COUNT(*) as count FROM transport_requests GROUP BY status');
    $reports['transport_by_status'] = $stmt->fetchAll() ?: [];
    
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
    // For now, export as CSV with .xlsx extension
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
    
    $output = fopen('php://output', 'w');
    
    if (!empty($data)) {
        fputcsv($output, array_keys((array) $data[0]));
        foreach ($data as $row) {
            fputcsv($output, (array) $row);
        }
    }
    
    fclose($output);
    exit;
}

function export_pdf(array $data, string $filename): void
{
    // Simple HTML to PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
    
    $html = '<h1>' . ucfirst($filename) . '</h1>';
    $html .= '<table border="1">';
    
    if (!empty($data)) {
        $html .= '<tr><th>' . implode('</th><th>', array_keys((array) $data[0])) . '</th></tr>';
        foreach ($data as $row) {
            $html .= '<tr><td>' . implode('</td><td>', (array) $row) . '</td></tr>';
        }
    }
    
    $html .= '</table>';
    
    // For demo purposes, output as HTML with PDF headers
    echo $html;
    exit;
}

function export_word(array $data, string $filename): void
{
    header('Content-Type: application/msword');
    header('Content-Disposition: attachment; filename="' . $filename . '.doc"');
    
    $html = '<html><head><title>' . $filename . '</title></head><body>';
    $html .= '<h1>' . ucfirst($filename) . '</h1>';
    $html .= '<table border="1">';
    
    if (!empty($data)) {
        $html .= '<tr><th>' . implode('</th><th>', array_keys((array) $data[0])) . '</th></tr>';
        foreach ($data as $row) {
            $html .= '<tr><td>' . implode('</td><td>', (array) $row) . '</td></tr>';
        }
    }
    
    $html .= '</table></body></html>';
    
    echo $html;
    exit;
}
