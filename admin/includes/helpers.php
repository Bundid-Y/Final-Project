<?php
declare(strict_types=1);

function project_base_url(): string
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $adminPosition = strpos($scriptName, '/admin/');

    if ($adminPosition !== false) {
        return rtrim(substr($scriptName, 0, $adminPosition), '/');
    }

    $parts = explode('/', trim($scriptName, '/'));
    return isset($parts[0]) && $parts[0] !== '' ? '/' . $parts[0] : '';
}

function project_url(string $path = ''): string
{
    $base = project_base_url();
    $normalized = ltrim($path, '/');

    if ($normalized === '') {
        return $base !== '' ? $base : '/';
    }

    return ($base !== '' ? $base : '') . '/' . $normalized;
}

function sanitize_text(?string $value): string
{
    $value = trim((string) $value);
    $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
    return $value;
}

function post_string(string $key, string $default = ''): string
{
    return isset($_POST[$key]) ? sanitize_text((string) $_POST[$key]) : $default;
}

function post_int(string $key, int $default = 0): int
{
    return isset($_POST[$key]) ? (int) $_POST[$key] : $default;
}

function post_float(string $key, float $default = 0): float
{
    return isset($_POST[$key]) ? (float) $_POST[$key] : $default;
}

function get_client_ip(): string
{
    $keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];

    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $value = explode(',', (string) $_SERVER[$key])[0];
            return trim($value);
        }
    }

    return '0.0.0.0';
}

function company_code_from_slug(string $slug): string
{
    $slug = strtolower(trim($slug));
    return $slug === 'tnb' ? 'TNB' : 'KOCH';
}

function company_slug_from_code(string $code): string
{
    return strtoupper(trim($code)) === 'TNB' ? 'tnb' : 'koch';
}

function login_page_by_company(string $companyCode): string
{
    $slug = company_slug_from_code($companyCode);
    return $slug === 'tnb' ? project_url('tnb/main/Login.php') : project_url('koch/main/login.php');
}

function user_page_by_company(string $companyCode): string
{
    $slug = company_slug_from_code($companyCode);
    return project_url($slug . '/main/user.php');
}

function current_url(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    return $uri !== '' ? $uri : '/';
}

function request_expects_json(): bool
{
    $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
    $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
    return str_contains($accept, 'application/json') || $requestedWith === 'xmlhttprequest' || post_string('response_type') === 'json';
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function upload_public_path(string $absolutePath): string
{
    $normalized = str_replace('\\', '/', $absolutePath);
    $root = str_replace('\\', '/', dirname(__DIR__));
    $relative = str_replace($root . '/', '', $normalized);
    return 'admin/' . ltrim($relative, '/');
}

/**
 * Resolve an image URL stored in DB (e.g. "admin/uploads/products/xxx.png")
 * into a full project URL that works from any page.
 */
function resolve_image_url(?string $dbPath): string
{
    if ($dbPath === null || $dbPath === '') {
        return '';
    }
    // If it's already an absolute URL, return as-is
    if (str_starts_with($dbPath, 'http://') || str_starts_with($dbPath, 'https://') || str_starts_with($dbPath, '//')) {
        return $dbPath;
    }
    // Convert relative DB path to full project URL
    return project_url($dbPath);
}

/**
 * Generate a Thai localized title for CRUD notifications based on action and entity
 */
function crud_notification_title_th(string $action, string $entity): string
{
    $actionMap = [
        'create' => 'สร้าง',
        'update' => 'อัปเดต',
        'delete' => 'ลบ',
        'update_role' => 'เปลี่ยนสิทธิ์',
        'update_status' => 'เปลี่ยนสถานะ',
        'update_admin_emails' => 'อัปเดตอีเมลแจ้งเตือน',
        'update_smtp_config' => 'อัปเดตการตั้งค่า SMTP'
    ];
    
    $entityMap = [
        'slider' => 'สไลเดอร์',
        'partner' => 'พันธมิตร',
        'product' => 'สินค้า',
        'truck_card' => 'บริการขนส่ง',
        'email_template' => 'เทมเพลตอีเมล',
        'user' => 'ผู้ใช้งาน',
        'featured_product' => 'สินค้าแนะนำ',
        'system_settings' => 'การตั้งค่าระบบ'
    ];
    
    $thAction = $actionMap[$action] ?? $action;
    $thEntity = $entityMap[$entity] ?? ucfirst(str_replace('_', ' ', $entity));
    
    if ($entity === 'system_settings' && in_array($action, ['update_admin_emails', 'update_smtp_config'])) {
        return $thAction . 'สำเร็จ';
    }
    
    return $thAction . $thEntity . 'สำเร็จ';
}
