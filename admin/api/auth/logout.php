<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

$pdo = Database::connection();
$currentUser = authenticated_user();

// Determine company from current URL or user data
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$company = 'koch'; // default

if (strpos($requestUri, '/tnb/') !== false) {
    $company = 'tnb';
} elseif (strpos($requestUri, '/koch/') !== false) {
    $company = 'koch';
} elseif ($currentUser !== null) {
    $company = company_slug_from_code((string) ($currentUser['company_code'] ?? 'KOCH'));
}

if ($currentUser !== null) {
    deactivate_session_record($pdo, (string) ($currentUser['session_token'] ?? ''));
    log_activity($pdo, (int) $currentUser['id'], 'LOGOUT', 'users', (int) $currentUser['id']);
}

destroy_authenticated_session();
set_flash('success_message', 'ออกจากระบบเรียบร้อยแล้ว');

$redirectUrl = login_page_by_company(company_code_from_slug($company));

if (request_expects_json()) {
    json_response(true, 'Logged out successfully.', ['redirect' => $redirectUrl]);
}

redirect_to($redirectUrl);
