<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

$pdo = Database::connection();
$currentUser = authenticated_user();

// Determine company BEFORE destroying session
$company = 'koch'; // default fallback

// Priority 1: Check company cookie (most reliable)
if (isset($_COOKIE['app_company']) && $_COOKIE['app_company'] !== '') {
    $company = strtolower((string) $_COOKIE['app_company']);
}
// Priority 2: Check current user data
elseif ($currentUser !== null) {
    $company = company_slug_from_code((string) ($currentUser['company_code'] ?? 'KOCH'));
}
// Priority 3: Check HTTP referer (where logout was clicked from)
elseif (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] !== '') {
    $referer = $_SERVER['HTTP_REFERER'];
    if (strpos($referer, '/tnb/') !== false) {
        $company = 'tnb';
    } elseif (strpos($referer, '/koch/') !== false) {
        $company = 'koch';
    }
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
